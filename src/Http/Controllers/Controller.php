<?php

namespace Shemi\Laradmin\Http\Controllers;

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
     * @param Request $request
     * @return Type
     */
    protected function getTypeBySlug(Request $request)
    {
        $typeSlug = $this->getSlug($request);
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

        /** @var Field $field */
        foreach ($fields as $field)
        {
            if($field->read_only) {
                continue;
            }

            $value = $field->transformRequest($request->input($field->key));

            if($field->is_relationship) {
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

        return $model;
    }

    protected function validateTypeRequest(Request $request, Type $type)
    {

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