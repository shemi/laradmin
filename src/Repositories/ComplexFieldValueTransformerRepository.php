<?php

namespace Shemi\Laradmin\Repositories;

use Illuminate\Support\Arr;
use Shemi\Laradmin\Models\Field;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Shemi\Laradmin\Contracts\Repositories\CreateUpdateRepository;
use Shemi\Laradmin\Exceptions\CreateUpdateRelationModelNotFoundException;
use Shemi\Laradmin\Exceptions\CreateUpdateTransformCantFindCopyFieldOrAttributeException;
use Shemi\Laradmin\Contracts\Repositories\ComplexFieldValueTransformerRepository as Contract;

class ComplexFieldValueTransformerRepository implements Contract
{

    /**
     * @var Field $field
     */
    protected $field;

    /**
     * @var Collection $subFields
     */
    protected $subFields;

    /**
     * @var Collection $mediaFields
     */
    protected $mediaFields;

    /**
     * @var Collection $generalFields
     */
    protected $generalFields;

    /**
     * @var Collection $relationFields
     */
    protected $relationFields;

    /**
     * @var Collection $rows
     */
    protected $rows;

    /**
     * @var Model $model
     */
    protected $model;

    /**
     * @var mixed $currentRow
     */
    protected $currentRow;

    /**
     * @var mixed $currentValue
     */
    protected $currentValue;

    /**
     * @var Collection $value
     */
    protected $value;

    /**
     * @var boolean $expectSingle
     */
    protected $expectSingle;

    /**
     * @var string|null $modelKey
     */
    protected $modelKey;

    /**
     * @param $value
     * @param Field $field
     * @param Model $model
     * @param null $modelKey
     * @return mixed
     * @throws CreateUpdateRelationModelNotFoundException
     * @throws CreateUpdateTransformCantFindCopyFieldOrAttributeException
     * @throws \Shemi\Laradmin\Exceptions\SyncMedia\SyncMediaException
     */
    public function transform($value, Field $field, Model $model, $modelKey = null)
    {
        $this->field = $field;
        $this->value = new Collection([]);
        $this->model = $model;
        $this->modelKey = $modelKey;

        if(empty($value) || ! $this->field->is_support_sub_fields) {
            return $value;
        }

        $this->setSubFields($field->getSubFields());

        $this->expectSingle = Arr::isAssoc($value);

        if($this->expectSingle) {
            $this->rows = new Collection([$value]);
        } else {
            $this->rows = new Collection($value);
        }

        return $this->transformRows();
    }

    /**
     * @return array
     * @throws CreateUpdateTransformCantFindCopyFieldOrAttributeException
     * @throws CreateUpdateRelationModelNotFoundException
     * @throws \Shemi\Laradmin\Exceptions\SyncMedia\SyncMediaException
     */
    public function transformRows()
    {
        foreach ($this->rows as $row) {
            $this->currentValue = [];
            $this->currentRow = $row;

            $this->syncData()
                ->syncRelationData()
                ->syncMedia();

            $this->value->push($this->currentValue);
        }

        foreach ($this->mediaFields as $field) {
            $this->getSyncMediaRepo()
                ->deletePending($field);
        }

        return $this->expectSingle ? $this->value->first() : $this->value->all();
    }

    /**
     * @param Collection|null $fields
     * @return $this
     * @throws CreateUpdateRelationModelNotFoundException
     */
    protected function setSubFields(Collection $fields = null)
    {
        $this->subFields = $fields;
        $this->mediaFields = new Collection();
        $this->generalFields = new Collection();
        $this->relationFields = new Collection();

        /** @var Field $field */
        foreach ($this->subFields as $field) {
            if($field->read_only) {
                continue;
            }

            if($field->is_media) {
                $this->mediaFields->push($field);
            }

            elseif ($field->is_relationship) {
                if(! $field->has_relationship_type && ! isset($field->relationship['model'])) {
                    throw CreateUpdateRelationModelNotFoundException::create($field->key);
                }

                $this->relationFields->push($field);
            }

            else {
                $this->generalFields->push($field);
            }

        }

        return $this;
    }

    /**
     * @return $this
     * @throws CreateUpdateTransformCantFindCopyFieldOrAttributeException
     * @throws CreateUpdateRelationModelNotFoundException
     * @throws \Shemi\Laradmin\Exceptions\SyncMedia\SyncMediaException
     */
    protected function syncData()
    {
        /** @var Field $field */
        foreach ($this->generalFields as $field) {
            $value = $this->getFieldValue($field, true);

            if($field->is_password && ! $value) {
                continue;
            }

            $value = $this->getFieldValue($field);

            if($field->is_support_sub_fields) {
                $value = $this->fresh()->transform($value, $field, $this->model);
            }

            $this->setValue($field, $value);
        }

        return $this;
    }

    /**
     * @return $this
     * @throws CreateUpdateTransformCantFindCopyFieldOrAttributeException
     */
    public function syncRelationData()
    {
        /** @var Field $field */
        foreach ($this->relationFields as $field) {
            $value = $this->getFieldValue($field);

            if($field->is_repeater_like && $field->has_relationship_type) {
                $this->syncRepeaterRows($field, $value);
            }

            elseif ($field->is_support_sub_fields && $field->getRelationModelClass()) {
                $type = $field->relationship_type ?: null;
                $model = $field->getRelationModelClass();

                if($id = data_get($value, $model->getKeyName())) {
                    $model = $model->find($id);
                }

                $this->getCreateUpdateRepo()
                    ->createOrUpdate($value, $model, $type, $field->getSubFields());

                $this->setValue($field, $model->getKey());
            }

            else {
                $this->setValue($field, $value);
            }
        }

        return $this;
    }

    /**
     * @param Field $field
     * @param $rows
     * @return void
     */
    protected function syncRepeaterRows(Field $field, $rows)
    {
        $type = $field->relationship_type;

        /** @var Model $model */
        $model = app($type->model);
        $primaryKey = $model->getKeyName();

        $rows = Collection::make($rows);
        $currentRows = $this->model->getAttribute($this->modelKey ?: $field->key);

        if(empty($currentRows) || ! $currentRows instanceof Collection) {
            $currentRows = collect([]);
        }

        $newIds = [];

        foreach ($rows as $row) {
            $id = $row[$primaryKey];
            $exists = $currentRows->where($primaryKey, $id)->first();

            if($exists) {
                $model = $exists;
            } else {
                $model = app($type->model);
            }

            $this->getCreateUpdateRepo()
                ->createOrUpdate($row, $model, $type, $field->getSubFields());

            $newIds[] = $model->getKey();
        }

        $this->setValue($field, $newIds);
    }

    /**
     * @throws CreateUpdateTransformCantFindCopyFieldOrAttributeException
     * @throws \Shemi\Laradmin\Exceptions\SyncMedia\SyncMediaException
     */
    public function syncMedia()
    {
        /** @var Field $field */
        foreach ($this->mediaFields as $field) {
            $mediaRepo = $this->getSyncMediaRepo()
                ->sync(
                    $this->getFieldValue($field),
                    $this->model,
                    $field,
                    false
                );

            $ids = $mediaRepo->getCurrentIds();

            $this->setValue($field, $field->is_single_media ? array_first($ids) : $ids);
        }
    }

    protected function setValue(Field $field, $value)
    {
        data_set($this->currentValue, $field->key, $value);
    }

    /**
     * @param Field $field
     * @param bool $plain
     * @return mixed
     * @throws CreateUpdateTransformCantFindCopyFieldOrAttributeException
     */
    protected function getFieldValue(Field $field, $plain = false)
    {
        $value = data_get($this->currentRow, $field->key);

        if($plain) {
            return $value;
        }

        return $this->transformValue(
            $field->transformRequest($value),
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

            else {
                throw CreateUpdateTransformCantFindCopyFieldOrAttributeException::create($copyKey);
            }
        }

        return call_user_func($transform[0], $value);
    }

    /**
     * @return CreateUpdateRepository
     */
    protected function getCreateUpdateRepo()
    {
        return app(CreateUpdateRepository::class)->fresh();
    }

    /**
     * @return SyncMediaRepository
     */
    protected function getSyncMediaRepo()
    {
        return app(SyncMediaRepository::class)->fresh();
    }

    public function fresh()
    {
        return new static;
    }

}
