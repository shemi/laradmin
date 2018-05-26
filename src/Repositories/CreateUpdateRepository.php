<?php

namespace Shemi\Laradmin\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Shemi\Laradmin\Contracts\Repositories\CreateUpdateRepository as CreateUpdateRepositoryContract;

use Shemi\Laradmin\Exceptions\CreateUpdateRelationModelNotFoundException;
use Shemi\Laradmin\Exceptions\CreateUpdateTransformCantFindCopyFieldOrAttributeException;
use Shemi\Laradmin\Exceptions\CreateUpdateDeleteMediaException;
use Shemi\Laradmin\Exceptions\CreateUpdateMediaModelNotFoundException;
use Shemi\Laradmin\Exceptions\CreateUpdateUnableToClearMediaException;
use Shemi\Laradmin\Exceptions\CreateUpdateUnableToSaveMediaException;
use Shemi\Laradmin\Exceptions\CreateUpdateUnableToSaveModelException;
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

    public function initCreateOrUpdate($data, Model $model, Type $type, Collection $fields = null)
    {
        $this->data = $data;
        $this->model = $model;
        $this->type = $type;

        $this->setFields($fields);

        $this->setModelData();

        $this->syncBelongsToRelationData();

        return $this->model;
    }

    /**
     * @param array $data
     * @param Model $model
     * @param Type $type
     * @param Collection|null $fields
     * @return $this
     */
    public function createOrUpdate($data, Model $model, Type $type, Collection $fields = null)
    {
        $this->initCreateOrUpdate($data, $model, $type, $fields);

        DB::transaction(function () {
            if(! $this->model->exists) {
                $this->saveModel();
            }

            if($this->failed) {
                return $this;
            }

            $this->syncRelationData();
            $this->syncMedia();

            event('laradmin::before-save-model', $this->model, $this->type);

            $this->saveModel();

            event('laradmin::after-model-saved', $this->model, $this->type);

            return $this;
        });
    }

    protected function setFields(Collection $fields = null)
    {

        if($fields) {
            $this->fields = $fields;
        } else {
            $this->fields = $this->model->exists ?
                $this->type->edit_fields :
                $this->type->create_fields;
        }

        $this->fields->each(function(Field $field) {

            if($field->read_only) {
                return;
            }

            if($field->is_media) {
                $this->mediaFields->push($field);
            }

            elseif ($field->is_relationship) {
                if(! $field->getRelationClass($this->model)) {
                    throw CreateUpdateRelationModelNotFoundException::create($field->key);
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

    public function syncRelationData()
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

            elseif ($relation instanceof HasOne && $field->is_support_sub_fields) {
                $type = $field->has_relationship_type ? $field->relationship_type : $this->type;
                $model = $field->has_relationship_type ? app($type->model) : $field->getRelationModelClass($this->model);
                
                if($id = array_get($value, $model->getKeyName())) {
                    $model = $model->find($id);
                }

                $this->createUpdateSubModel($type, $field, $model, $relation, $value);
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
     * @throws CreateUpdateUnableToSaveModelException
     */
    protected function createUpdateDeleteRepeaterRows(Field $field, $relation, $rows)
    {
        $type = $field->relationship_type;

        /** @var Model $model */
        $model = app($type->model);
        $primaryKey = $model->getKeyName();
        $relation = $field->getRelationClass($this->model);

        /** @var Collection $currentRows */
        $currentRows = $this->model->{$field->key};

        $rows = Collection::make($rows);
        $rowsIds = $rows->pluck($primaryKey)->values()->toArray();

        $rowsToDelete = $currentRows->reject(function(Model $row) use ($rowsIds) {
            return in_array($row->getKey(), $rowsIds);
        });

        if($rowsToDelete->isNotEmpty()) {
            if ($relation instanceof BelongsToMany) {
                $this->model->{$field->key}()
                    ->detach($rowsToDelete->pluck('id')->values()->toArray());
            }

            else {
                $rowsToDelete->each->delete();
            }
        }

        foreach ($rows as $row) {
            $id = $row[$primaryKey];
            $exists = $currentRows->where($primaryKey, $id)->first();

            if($exists) {
                $model = $exists;
            } else {
                $model = app($type->model);
            }

            $this->createUpdateSubModel($type, $field, $model, $relation, $row);
        }

    }

    /**
     * @param Type $type
     * @param Field $field
     * @param Model $model
     * @param $relation
     * @param $data
     * @return Model
     * @throws CreateUpdateUnableToSaveModelException
     */
    protected function createUpdateSubModel(Type $type, Field $field, Model $model, $relation, $data)
    {
        $inst = new static();
        $exists = $model->exists;
        $model = $inst->initCreateOrUpdate($data, $model, $type, $field->getSubFields());


        try {
            if ($relation instanceof BelongsToMany) {
                $inst->saveModel();

                if(! $exists) {
                    $this->model->{$field->key}()->attach($model->getKey());
                }
            }

            else {
                $this->model->{$field->key}()->save($model);
            }
        }

        catch (\Throwable $exception) {
            throw CreateUpdateUnableToSaveModelException::create(class_basename($model), $exception);
        }

        $inst->syncRelationData();
        $inst->syncMedia();

        return $model;
    }

    /**
     * @return bool
     * @throws CreateUpdateUnableToSaveModelException
     */
    public function saveModel()
    {
        $exists = $this->model->exists;

        try {
            $saved = $this->model->saveOrFail();
        }

        catch (\Throwable $exception) {
            throw CreateUpdateUnableToSaveModelException::create(class_basename($this->model), $exception);
        }

        if($exists) {
            $this->saved = $saved;
        }

        return true;
    }

    public function syncMedia()
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
                    throw CreateUpdateUnableToClearMediaException::create($exception);
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

    /**
     * @param Collection $modelMedia
     * @param Collection $toUpdate
     * @param Field $field
     * @return Collection
     */
    protected function deleteUnusedMedia(Collection $modelMedia, Collection $toUpdate, Field $field)
    {
        return $modelMedia->reject(function($media) use ($toUpdate, $field) {
            if(! $toUpdate->pluck('id')->contains($media->id)) {
                try {
                    $media->delete();
                }

                catch (\Exception $exception) {
                    throw CreateUpdateDeleteMediaException::create($media->id, $field->key, $exception);
                }

                return true;
            }

            return false;
        });
    }

    /**
     * @param Collection $toUpdate
     * @param Collection $modelMediaCollection
     * @return $this
     */
    protected function updateMedia(Collection $toUpdate, Collection $modelMediaCollection)
    {
        $toUpdate->each(function($media) use ($modelMediaCollection) {

            $mediaModel = $modelMediaCollection->first(function($mediaModel) use ($media) {
                return $media->id === $mediaModel->id;
            });

            if(! $mediaModel) {
                throw CreateUpdateMediaModelNotFoundException::create();
            }

            $mediaModel->name = $media->name ?: $mediaModel->name;
            $mediaModel->order_column = $media->order;
            $mediaModel->setCustomProperty('alt', $media->alt);
            $mediaModel->setCustomProperty('caption', $media->caption);

            $this->saveMediaModel($mediaModel);
        });

        return $this;
    }

    /**
     * @param Collection $toInsert
     * @param Field $field
     * @return $this
     */
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
                    ->toMediaCollection($field->key, $field->media_disk);
            }
            catch (\Exception $exception) {
                throw CreateUpdateUnableToSaveMediaException::create(
                    $media->name,
                    $field->key,
                    $field->media_disk,
                    $exception
                );
            }

            $mediaModel->order_column = $media->order;
            $this->saveMediaModel($mediaModel);
        });

        return $this;
    }

    /**
     * @param Media $media
     * @return bool
     * @throws CreateUpdateUnableToSaveMediaException
     */
    protected function saveMediaModel(Media $media)
    {
        try {
            $media->saveOrFail();
        }

        catch (\Throwable $exception) {
            throw CreateUpdateUnableToSaveMediaException::create(
                $media->name,
                $media->collection_name,
                $media->disk,
                $exception
            );
        }

        return true;
    }

    /**
     * @param Field $field
     * @return mixed
     * @throws CreateUpdateTransformCantFindCopyFieldOrAttributeException
     */
    protected function getFieldValue(Field $field)
    {
        return $this->transformValue(
            $field->transformRequest(data_get($this->data, $field->key)),
            $field
        );
    }

    /**
     * @param $value
     * @param Field $field
     * @return mixed
     * @throws CreateUpdateTransformCantFindCopyFieldOrAttributeException
     */
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
                throw CreateUpdateTransformCantFindCopyFieldOrAttributeException::create($copyKey);
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
