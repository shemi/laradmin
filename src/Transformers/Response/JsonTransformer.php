<?php

namespace Shemi\Laradmin\Transformers\Response;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Repositories\MediaTransformer;

class JsonTransformer extends Transformer
{
    /**
     * @var Field $field
     */
    protected $field;

    /**
     * @var string $modelKey
     */
    protected $modelKey;

    /**
     * @var array $data
     */
    protected $data;

    /**
     * @var Collection $subFields
     */
    protected $subFields;

    /**
     * @var Model $model
     */
    protected $model;

    /**
     * @var Collection
     */
    protected $rows;

    /**
     * @var array $currentRow
     */
    protected $currentRow;

    /**
     * @var boolean $internal
     */
    protected $internal;

    /**
     * @param Field $field
     * @param $data
     * @param Model $model
     * @param bool $internal
     * @return mixed
     * @throws \Exception
     * @throws \Shemi\Laradmin\Exceptions\ManagerDoesNotExistsException
     */
    public function transform(Field $field, $data, Model $model, $internal = false)
    {
        $this->data = $data;
        $this->field = $field;
        $this->subFields = $field->getSubFields();
        $this->model = $model;
        $this->internal = $internal;

        if(! $data || empty($data)) {
            return $data;
        }

        if($this->field->is_repeater_like) {

            $this->rows = $data instanceof Collection ? $data : new Collection(array_values($data));
        } else {
            if(! Arr::isAssoc($data)) {
                $this->rows = new Collection(array_first($data));
            } else {
                $this->rows = new Collection([$data]);
            }
        }

        return $this->transformRows();
    }

    /**
     * @return array|mixed
     * @throws \Exception
     * @throws \Shemi\Laradmin\Exceptions\ManagerDoesNotExistsException
     */
    protected function transformRows()
    {
        $newData = new Collection([]);

        foreach ($this->rows as $row) {
            $this->currentRow = $row;

            $newData->push($this->transformRow());
        }

        return $this->field->is_repeater_like ? $newData->all() : $newData->first();
    }

    /**
     * @return array
     * @throws \Exception
     * @throws \Shemi\Laradmin\Exceptions\ManagerDoesNotExistsException
     */
    protected function transformRow()
    {
        $row = [];

        /** @var Field $field */
        foreach ($this->subFields as $field) {
            $value = $this->getValue($field->key);

            if($field->is_password) {
                $value = null;
            }

            elseif($value === null) {
                $value = $field->getDefaultValue();
            }

            elseif($field->is_relationship) {
                $value = $this->asRelationship($field);
            }

            elseif($field->is_media) {
                $value = $this->asMedia($field);
            }

            elseif($field->is_support_sub_fields) {
                $value = (new static)
                    ->transform($field, $value, $this->model, $this->internal);
            }

            $row[$field->key] = $value;
        }

        return $row;
    }

    protected function asRelationship(Field $field)
    {
        $original = $this->getValue($field->key);
        $model = $field->getRelationModelClass();
        $repo = $this->getRelationshipTransformer()
            ->init($field, $field->key, $model, $this->internal);

        $value = $model->whereIn($field->getRelationKeyName($model), (array) $original)
            ->get();

        if (is_array($original)) {
            return $repo->asRelationCollection($value);
        }

        $value = $value->first();

        if ($field->is_support_sub_fields && $value instanceof Model) {
            return $repo->asSubModel($value);
        }

        if ($value instanceof Model) {
            return $value->getAttribute($field->getRelationKeyName());
        }

        return $value;
    }

    /**
     * @param Field $field
     * @return array
     * @throws \Exception
     * @throws \Shemi\Laradmin\Exceptions\ManagerDoesNotExistsException
     */
    protected function asMedia(Field $field)
    {
        $value = (array) $this->getValue($field->key);
        $repo = $this->getMediaTransformer();

        $newValue = [];

        $collection = $repo->transform($field, $this->model, $this->internal);

        if(empty($collection) || Arr::isAssoc($collection)) {
            $collection = new Collection([$collection]);
        } else {
            $collection = new Collection($collection);
        }

        foreach ($collection as $media) {
            if(in_array(data_get($media, 'id'), $value)) {
                $newValue[] = $media;
            }
        }

        return $field->is_single_media ? array_first($newValue) : $newValue;
    }

    protected function getValue($key)
    {
        return data_get($this->currentRow, $key);
    }

}