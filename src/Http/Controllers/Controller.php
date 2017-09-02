<?php

namespace Shemi\Laradmin\Http\Controllers;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Shemi\Laradmin\Data\DataNotFoundException;
use Illuminate\Database\Eloquent\Model;
use Shemi\Laradmin\Facades\Laradmin;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\Type;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $status_code = 200;

    protected $slug = null;

    public function __construct()
    {
        $user = null;

        if(Auth::check()) {
            $user = Laradmin::model('User')->find(Auth::id());
        }

        view()->share('user', $user);
    }

    /**
     * @param Request $request
     * @return string
     */
    protected function getSlug(Request $request)
    {
        if ($this->slug) {
            return $this->slug;
        }

        return explode('.', $request->route()->getName())[1];
    }

    /**
     * @param Request|string $request
     * @return Type
     */
    protected function getTypeBySlug($request)
    {
        $typeSlug = $request instanceof Request ?
            $this->getSlug($request) :
            $request;

        $type = Type::where('slug', $typeSlug)->first();

        if(! $type) {
            throw new DataNotFoundException($typeSlug);
        }

        return $type;
    }

    protected function insertCreateUpdateData(Request $request, Model $model, Type $type)
    {
        /** @var Collection $fields */
        $fields = $model->exists ? $type->edit_fields : $type->create_fields;
        $relationsData = [];
        $mediaData = [];

        /** @var Field $field */
        foreach ($fields as $field)
        {
            if($field->read_only) {
                continue;
            }

            $value = $field->transformRequest($request->input($field->key));

            if($field->is_media) {
                $mediaData[] = [
                    'field' => $field,
                    'value' => $value
                ];

                continue;
            }

            if($field->is_relationship) {
                $relation = $field->getRelationClass($model);

                if($relation instanceof HasOne) {

                    continue;
                } elseif ($relation instanceof BelongsTo) {
                    $model->{$relation->getForeignKey()} = $value;

                    continue;
                }

                $relationsData[$field->key] = $value;
            } else {
                $transform = explode(':', $field->getTemplateOption('transform', 'value'));
                if(! $value && count($transform) > 1) {
                    $value = $model->{$transform[1]};
                }

                $value = call_user_func($transform[0], $value);

                $model->{$field->key} = $value;
            }
        }

        $model->save();

        foreach ($relationsData as $key => $values) {
            $model->{$key}()->sync($values);
        }

        /** @var Collection $newMedia */
        foreach ($mediaData as $item) {
            /** @var Field $mediaField */
            $mediaField = $item['field'];
            $newMedia = $item['value'];
            $collection = $mediaField->key;

            /** @var Collection $currentCollectionMedia */
            $currentCollectionMedia = $model->getMedia($collection);

            if($newMedia->isEmpty() && $currentCollectionMedia->isNotEmpty()) {
                $model->clearMediaCollection($collection);

                continue;
            }

            $mediaToUpdate = $newMedia->reject(function($media) {
                return $media->is_new;
            });

            $mediaToInsert = $newMedia->reject(function($media) {
                return ! $media->is_new;
            });

            if($currentCollectionMedia->isNotEmpty()) {
                $currentCollectionMedia = $currentCollectionMedia->reject(function($media) use ($mediaToUpdate) {
                    if(! $mediaToUpdate->pluck('id')->contains($media->id)) {
                        $media->delete();

                        return true;
                    }

                    return false;
                });
            }

            foreach ($mediaToUpdate as $media) {
                $mediaModel = $currentCollectionMedia->first(function($model) use ($media) {
                    return $media->id === $model->id;
                });

                if(! $mediaModel) {
                    continue;
                }

                $mediaModel->name = $media->name ?: $mediaModel->name;
                $mediaModel->order_column = $media->order;
                $mediaModel->setCustomProperty('alt', $media->alt);
                $mediaModel->setCustomProperty('caption', $media->caption);
                $mediaModel->save();
            }

            foreach ($mediaToInsert as $media) {
                $mediaModel = $model
                    ->addMedia(storage_path('app/'.$media->temp_path))
                    ->usingName($media->name)
                    ->withCustomProperties([
                        'alt' => $media->alt,
                        'caption' => $media->caption
                    ])
                    ->toMediaCollection($collection, $mediaField->media_disc);

                $mediaModel->order_column = $media->order;
                $mediaModel->save();
            }

        }

        return $model;
    }

    protected function validateTypeRequest(Request $request, Model $model, Type $type)
    {
        $fields = $model->exists ? $type->edit_fields : $type->create_fields;

        $roles = [];

        /** @var Field $field */
        foreach ($fields as $field) {

            if($field->read_only) {
                continue;
            }

            $formField = $field->formField();
            $fieldRoles = $formField->getValidationRoles($field);

            if(! $fieldRoles || empty($fieldRoles)) {
                continue;
            }

            $roles = array_merge($roles, $fieldRoles);
        }

        $oldLocal = app()->getLocale();

        app()->setLocale('en');

        $this->validate($request, $roles);

        app()->setLocale($oldLocal);
    }

    /**
     * @return int
     */
    protected function getStatusCode()
    {
        return $this->status_code;
    }

    /**
     * @param int $status_code
     * @return $this
     */
    protected function setStatusCode($status_code)
    {
        $this->status_code = $status_code;

        return $this;
    }


    protected function responseNotFound($message = 'Not found.')
    {
        return $this->setStatusCode(404)->responseWithError($message);
    }

    protected function responseUnauthorized(Request $request, $message = 'Unauthorized.')
    {
        if(! $request->wantsJson()) {
            return response()
                ->view('laradmin::errors.403', [], 403);
        }

        return $this->setStatusCode(403)->responseWithError($message);
    }

    protected function responseNotAuthorized($message = 'not Authorized.')
    {
        return $this->setStatusCode(401)->responseWithError($message);
    }

    protected function responseValidationError($messages = 'Check all your fields.')
    {
        if(is_string($messages)) {
            $messages = [
                'form' => [$messages]
            ];
        }

        return response()->json($messages, 422);
    }

    protected function responseInternalError($message = 'Internal error.')
    {
        return $this->setStatusCode(500)->responseWithError($message);
    }

    protected function responseBadRequest($message = 'Bad Request')
    {
        return $this->setStatusCode(400)->responseWithError($message);
    }

    protected function responseWithError($message, $resultCode = 'err')
    {

        return response()->json([
            'message' => $message,
            'resultCode' => $resultCode,
            'code'    => $this->getStatusCode()
        ], $this->getStatusCode());
    }



    protected function response($data, $headers = [], $resultCode = 'ok')
    {
        $data = [
            'data' => $data,
            'resultCode' => $resultCode,
            'code'    => $this->getStatusCode()
        ];


        return response($data, $this->getStatusCode(), $headers);
    }
}