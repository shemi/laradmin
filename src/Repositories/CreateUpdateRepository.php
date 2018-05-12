<?php

namespace Shemi\Laradmin\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Shemi\Laradmin\Contracts\Repositories\CreateUpdateRepository as CreateUpdateRepositoryContract;

use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\Type;
use Spatie\MediaLibrary\Media;

class CreateUpdateRepository implements CreateUpdateRepositoryContract
{

    /**
     * @var array $data
     */
    protected $data;

    /**
     * @var Model $model
     */
    protected $model;

    /**
     * @var Type $type
     */
    protected $type;

    /**
     * @var Collection $fields
     */
    protected $fields;

    /**
     * @var Collection $mediaFields
     */
    protected $mediaFields;

    /**
     * @var Collection $relationFields
     */
    protected $relationFields;

    /**
     * @var Collection $modelFields
     */
    protected $modelFields;

    /**
     * @var boolean $saved
     */
    protected $saved = false;

    /**
     * @var bool $failed
     */
    protected $failed = false;

    /**
     * @var Collection $errors
     */
    protected $errors;


    /**
     * @var Collection $warnings
     */
    protected $warnings;


    public function __construct()
    {
        $this->mediaFields = Collection::make([]);
        $this->relationFields = Collection::make([]);
        $this->modelFields = Collection::make([]);
        $this->errors = Collection::make([]);
        $this->warnings = Collection::make([]);
    }

    public function initCreateOrUpdate($data, Model $model, Type $type)
    {
        $this->data = $data;
        $this->model = $model;
        $this->type = $type;
        $this->setFields();

        $this->setModelData();

        $this->syncBelongsToRelationData();
    }

    /**
     * @param array $data
     * @param Model $model
     * @param Type $type
     *
     * @return $this
     */
    public function createOrUpdate($data, Model $model, Type $type)
    {
        $this->initCreateOrUpdate($data, $model, $type);

        if(! $this->model->exists) {
            $this->saveModel();
        }

        if($this->failed) {
            return $this;
        }

        $this->syncRelationData();
        $this->syncMedia();

        event('laradmin::before-save-model', $model, $type);

        $this->saveModel();

        event('laradmin::after-model-saved', $model, $type);

        return $this;
    }

    protected function setFields()
    {
        $this->fields = $this->model->exists ?
            $this->type->edit_fields :
            $this->type->create_fields;

        $this->fields->each(function(Field $field) {

            if($field->read_only) {
                return;
            }

            if($field->is_media) {
                $this->mediaFields->push($field);
            }

            elseif ($field->is_relationship) {
                if(! $field->getRelationClass($this->model)) {
                    $this->warnings->push(
                        "Relation: the field \"{$field->key}\" marked as relationship but does not returned as ".Relation::class
                    );

                    return;
                }

                $this->relationFields->push($field);
            }

            else {
                $this->modelFields->push($field);
            }
        });

        return $this;
    }

    protected function setModelData()
    {
        $this->modelFields->each(function(Field $field) {

            $value = array_get($this->data, $field->key);

            if($this->model->exists && $field->is_password && ! $value) {
                return;
            }

            $this->model->setAttribute($field->key, $this->getFieldValue($field));
        });

        return $this;
    }

    protected function syncBelongsToRelationData()
    {
        $this->relationFields->each(function(Field $field) {
            $relation = $field->getRelationClass($this->model);
            $value = $this->getFieldValue($field);

            if(! $relation instanceof BelongsTo) {
                return;
            }

            if($value) {
                $relation->associate($value);
            }

            else {
                $relation->dissociate();
            }
        });
    }

    protected function syncRelationData()
    {
        $this->relationFields->each(function(Field $field) {

            $relation = $field->getRelationClass($this->model);
            $value = $this->getFieldValue($field);

            if($field->type === 'repeater' && $field->has_relationship_type) {
                $this->createUpdateDeleteRepeaterRows($field, $relation, $value);

                return;
            }

            if($relation instanceof BelongsTo) {
                return;
            }

            elseif ($relation instanceof HasOneOrMany) {
                $relationModel = $field->getRelationModelClass($this->model);

                if(! $value || empty($value)) {
                    $relationModels = $relationModel
                        ->where($relation->getForeignKeyName(), $this->model->getKey())
                        ->get();

                    $relationModels->each(function($model) use ($relation) {
                        $model->setAttribute($relation->getForeignKeyName(), null);
                        $model->save();
                    });

                    return;
                }


                $ids = Collection::make($value)->values()->all();

                $current = $relationModel
                    ->where($relation->getForeignKeyName(), $this->model->getKey())
                    ->get();

                $detach = $current->reject(function($model) use ($ids) {
                    return in_array($model->getKey(), $ids);
                });

                if($detach->isNotEmpty()) {
                    $detach->each(function($model) use ($relation) {
                        $model->setAttribute($relation->getForeignKeyName(), null);
                        $model->save();
                    });
                }

                $relationModels = $relationModel->find($ids);
                $relation->saveMany($relationModels);
            }

            else {
                $this->model->{$field->key}()
                    ->sync($value);
            }

        });

        return $this;
    }

    /**
     * @param Field $field
     * @param MorphMany $relation
     * @param $rows
     * @return void
     */
    protected function createUpdateDeleteRepeaterRows(Field $field, $relation, $rows)
    {
        $type = $field->relationship_type;
        /** @var Model $model */
        $model = app($type->model);
        $currentRows = $this->model->refresh()->{$field->key};
        $rows = Collection::make($rows);

        $rowsIds = $rows->pluck($model->getKeyName())->values()->toArray();

        $rowsToCreateUpdate = $rows;

        $rowsToDelete = $currentRows->reject(function(Model $row) use ($rowsIds) {
            return in_array($row->getKey(), $rowsIds);
        });



    }

    protected function saveModel()
    {
        $exists = $this->model->exists;

        try {
            $saved = $this->model->saveOrFail();
        }

        catch (\Throwable $exception) {
            $this->errors->push($exception->getMessage());
            $this->failed = true;

            return false;
        }

        if($exists) {
            $this->saved = $saved;
        }

        return true;
    }

    protected function syncMedia()
    {
        $this->mediaFields->each(function(Field $field) {
            /** @var Collection $media */
            $media = $this->getFieldValue($field);

            /** @var Collection $modelMedia */
            $modelMedia = $this->model->getMedia($field->key);

            if($media->isEmpty() && $modelMedia->isNotEmpty()) {

                try {
                    $this->model->clearMediaCollection($field->key);
                }
                catch (\Exception $exception) {
                    $this->warnings->push(
                        "Unable to clear media: " . $exception->getMessage()
                    );
                }

                return;
            }

            $toUpdate = $media->reject(function($media) {
                return $media->is_new;
            });

            $toInsert = $media->reject(function($media) {
                return ! $media->is_new;
            });


            if($modelMedia->isNotEmpty()) {
                $modelMedia = $this->deleteUnusedMedia($modelMedia, $toUpdate, $field);
            }

            if($toUpdate->isNotEmpty()) {
                $this->updateMedia($toUpdate, $modelMedia);
            }

            if($toInsert->isNotEmpty()) {
                $this->insertMedia($toInsert, $field);
            }

        });
    }

    protected function deleteUnusedMedia(Collection $modelMedia, Collection $toUpdate, Field $field)
    {
        return $modelMedia->reject(function($media) use ($toUpdate, $field) {
            if(! $toUpdate->pluck('id')->contains($media->id)) {

                try {
                    $media->delete();
                }
                catch (\Exception $exception) {
                    $this->warnings->push(
                        "Unable to delete media with id: {$media->id} collection: \"{$field->key}\" ".
                        "Message: " . $exception->getMessage()
                    );
                }

                return true;
            }

            return false;
        });
    }

    protected function updateMedia(Collection $toUpdate, Collection $modelMediaCollection)
    {
        $toUpdate->each(function($media) use ($modelMediaCollection) {

            $mediaModel = $modelMediaCollection->first(function($mediaModel) use ($media) {
                return $media->id === $mediaModel->id;
            });

            if(! $mediaModel) {
                $this->warnings->push("Media module could not be found");

                return;
            }

            $mediaModel->name = $media->name ?: $mediaModel->name;
            $mediaModel->order_column = $media->order;
            $mediaModel->setCustomProperty('alt', $media->alt);
            $mediaModel->setCustomProperty('caption', $media->caption);

            $this->saveMediaModel($mediaModel);
        });

        return $this;
    }

    protected function insertMedia(Collection $toInsert, Field $field)
    {
        $toInsert->each(function($media) use ($field) {
            try {
                $mediaModel = $this->model
                    ->addMedia(storage_path('app/'.$media->temp_path))
                    ->usingName($media->name)
                    ->withCustomProperties([
                        'alt' => $media->alt,
                        'caption' => $media->caption
                    ])
                    ->toMediaCollection($field->key, $field->media_disc);
            }
            catch (\Exception $exception) {
                $this->warnings->push($exception->getMessage());

                return;
            }

            $mediaModel->order_column = $media->order;
            $this->saveMediaModel($mediaModel);
        });

        return $this;
    }

    protected function saveMediaModel(Media $media)
    {
        try {
            $media->saveOrFail();
        }

        catch (\Throwable $exception) {
            $this->warnings->push($exception->getMessage());

            return false;
        }

        return true;
    }

    protected function getFieldValue(Field $field)
    {
        return $this->transformValue(
            $field->transformRequest(array_get($this->data, $field->key)),
            $field
        );
    }

    protected function transformValue($value, Field $field)
    {
        $transform = explode(':', $field->getTemplateOption('transform', 'value'));

        if(! $value && count($transform) > 1) {
            $copyKey = $transform[1];

            $copyField = $this->fields
                ->where('key', $copyKey)
                ->first();

            if($copyField) {
                $value = $this->getFieldValue($copyField);
            }

            elseif ($this->model->offsetExists($copyKey)) {
                $value = $this->model->getAttribute($copyKey);
            }

            else {
                $this->warnings
                    ->push("Transform: There is no Field or Attribute called: \"{$copyKey}\"");
            }
        }

        return call_user_func($transform[0], $value);
    }


    public function saved()
    {
        return $this->saved;
    }

    public function failed()
    {
        return $this->failed;
    }

    public function errors()
    {
        return $this->errors;
    }

    public function hasErrors()
    {
        return $this->errors->isNotEmpty();
    }

    public function warnings()
    {
        return $this->warnings;
    }

    public function hasWarnings()
    {
        return $this->warnings->isNotEmpty();
    }

}
