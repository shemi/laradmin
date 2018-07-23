<?php

namespace Shemi\Laradmin\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Shemi\Laradmin\Contracts\Repositories\CreateUpdateRepository as CreateUpdateRepositoryContract;

use Shemi\Laradmin\Contracts\Repositories\SyncMediaRepository;
use Shemi\Laradmin\Exceptions\CreateUpdateRelationModelNotFoundException;
use Shemi\Laradmin\Exceptions\CreateUpdateTransformCantFindCopyFieldOrAttributeException;
use Shemi\Laradmin\Exceptions\CantDeleteMediaException;
use Shemi\Laradmin\Exceptions\MediaModelNotFoundException;
use Shemi\Laradmin\Exceptions\SyncMedia\SyncMediaException;
use Shemi\Laradmin\Exceptions\UnableToClearMediaException;
use Shemi\Laradmin\Exceptions\UnableToSaveMediaException;
use Shemi\Laradmin\Exceptions\CreateUpdateUnableToSaveModelException;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\Type;
use Shemi\Laradmin\Transformers\Request\RequestTransformer;
use Spatie\MediaLibrary\Media;

class CreateUpdateRepository implements CreateUpdateRepositoryContract
{

    /**
     * @var array $data
     */
    protected $rawData;

    /**
     * @var array $data
     */
    protected $data;

    /**
     * @var Model $model
     */
    protected $model;

    /**
     * @var Type|null $type
     */
    protected $type = null;

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
     * @var RequestTransformer $transformer
     */
    protected $transformer;

    public function __construct()
    {
        $this->mediaFields = Collection::make([]);
        $this->relationFields = Collection::make([]);
        $this->modelFields = Collection::make([]);
        $this->transformer = new RequestTransformer();
    }

    public function initCreateOrUpdate($data, Model $model, Type $type = null, Collection $fields = null, $transformed = false)
    {
        $this->rawData = $data;
        $this->model = $model;
        $this->type = $type;

        $this->setFields($fields);

        if($transformed) {
            $this->data = $data;
        } else {
            $this->data = $this->transformer->transform($data, $this->fields);
        }

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
    public function createOrUpdate($data, Model $model, Type $type = null, Collection $fields = null)
    {
        $this->initCreateOrUpdate($data, $model, $type, $fields);

        DB::transaction(function () {

            if(! $this->model->exists) {
                $this->saveModel();
            }

            $this->syncRelationData();
            $this->syncMedia();

            event('laradmin::before-save-model', $this->model, $this->type);

            $this->saveModel();

            event('laradmin::after-model-saved', $this->model, $this->type);

        });

        return $this;
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

            if($this->model->exists && $field->is_password && ! array_get($this->rawData, $field->key)) {
                return;
            }

            $value = $this->getFieldValue($field);

            if($field->is_support_sub_fields) {
                $value = $this->getComplexRepo()
                    ->transform($value, $field, $this->model);
            }

            $this->model->setAttribute($field->key, $value);
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

            if($field->is_repeater_like && $field->has_relationship_type) {
                $this->syncRelationRepeaterRows($field, $value);

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

                $this->createUpdateSubModel($field, $model, $relation, $value, $type);
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
     * @param $rows
     * @return void
     * @throws CreateUpdateUnableToSaveModelException
     * @throws SyncMediaException
     */
    protected function syncRelationRepeaterRows(Field $field, $rows)
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

        foreach ($rows as $index => $row) {
            $exists = false;

            if($id = array_get($row, $primaryKey)) {
                $exists = $currentRows->where($primaryKey, $id)->first();
            }

            if($exists) {
                $model = $exists;
            } else {
                $model = app($type->model);
            }

            $this->createUpdateSubModel(
                $field,
                $model,
                $relation,
                $row,
                $type,
                $index + 1
            );
        }

    }

    /**
     * @param Field $field
     * @param Model $model
     * @param $relation
     * @param $data
     * @param Type|null $type
     * @param null $order
     * @return Model
     * @throws CreateUpdateUnableToSaveModelException
     * @throws SyncMediaException
     */
    protected function createUpdateSubModel(Field $field, Model $model, $relation, $data, Type $type = null, $order = null)
    {
        $inst = new static();
        $exists = $model->exists;
        $model = $inst->initCreateOrUpdate($data, $model, $type, $field->getSubFields(), true);
        $pivot = [];

        if(! is_null($order) && $field->relation_order_key) {
            $orderKey = $field->relation_order_key;

            if(starts_with($orderKey, 'pivot_')) {
                $orderKey = str_replace('pivot_', '', $orderKey);
                $pivot[$orderKey] = $orderKey;
            } else {
                $model->{$orderKey} = $order;
            }
        }

        try {
            if ($relation instanceof BelongsToMany) {
                $inst->saveModel();

                if(! $exists) {
                    $this->model->{$field->key}()
                        ->attach($model->getKey(), $pivot);
                }
            }

            else {
                $this->model->{$field->key}()
                    ->save($model, $pivot);
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

    /**
     * @throws SyncMediaException
     */
    public function syncMedia()
    {
        /** @var Field $field */
        foreach ($this->mediaFields as $field) {
            $this->getSyncMediaRepo()
                ->sync(
                    $this->getFieldValue($field),
                    $this->model,
                    $field
                );
        }
    }

    /**
     * @param Field $field
     * @return mixed
     */
    protected function getFieldValue(Field $field)
    {
        return data_get($this->data, $field->key);
    }

    /**
     * @return SyncMediaRepository
     */
    protected function getSyncMediaRepo()
    {
        return app(SyncMediaRepository::class)
            ->fresh();
    }

    /**
     * @return SyncComplexValueRepository
     */
    protected function getComplexRepo()
    {
        return app(SyncComplexValueRepository::class)
            ->fresh();
    }

    public function fresh()
    {
        return new static;
    }
}
