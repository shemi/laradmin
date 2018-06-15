<?php

namespace Shemi\Laradmin\Transformers\Request;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Shemi\Laradmin\Exceptions\CreateUpdateTransformCantFindCopyFieldOrAttributeException;
use Shemi\Laradmin\Models\Field;

class RequestTransformer
{

    /**
     * @var Collection $fields
     */
    protected $fields;

    /**
     * @var $data
     */
    protected $data;

    protected $transformedData = [];

    /**
     * @param $data
     * @param Collection $fields
     * @return array
     */
    public function transform($data, Collection $fields)
    {
        $this->fields = $fields;
        $this->data = $data;
        $this->transformedData = $this->transformFields($this->fields);
        $this->transformedData = $this->manipulateValues($this->fields);

        return $this->transformedData;
    }

    /**
     * @param Collection $fields
     * @param null $data
     * @return array
     */
    protected function transformFields(Collection $fields, $data = null)
    {
        $row = [];

        /** @var Field $field */
        foreach ($fields as $field) {
            $value = $this->getFieldValue($field, $data);

            if($field->is_support_sub_fields && $field->is_repeater_like) {
                $value = $this->transformRows($value, $field);
            }

            elseif ($field->is_support_sub_fields) {
                $value = $this->transformFields($field->getSubFields(), $value);
            }

            array_set(
                $row,
                $field->key,
                $value
            );
        }

        return $row;
    }

    /**
     * @param $value
     * @param Field $field
     * @return array
     */
    protected function transformRows($value, Field $field)
    {
        $rows = [];

        if(! $value || ! Arr::accessible($value)) {
            return $value;
        }

        foreach ($value as $index => $row) {
            $rows[] = $this->transformFields($row, $field->getSubFields());
        }

        return $rows;
    }

    /**
     * @param Field $field
     * @param null $data
     * @return mixed
     */
    protected function getFieldValue(Field $field, $data = null)
    {
        $data = $data ?: $this->data;

        return $field->transformRequest(data_get($data, $field->key));
    }

    /**
     * @param Collection $fields
     * @param null $data
     * @return mixed
     */
    public function manipulateValues(Collection $fields = null, $data = null)
    {
        $fields = $fields ?: $this->fields;
        $data = $data ?: $this->transformedData;

        /** @var Field $field */
        foreach ($fields as $field) {
            $manipulation = $field->value_manipulation;

            if(! $manipulation || ! is_string($manipulation)) {
                if($field->is_support_sub_fields) {
                    array_set(
                        $data,
                        $field->key,
                        $this->manipulateValues($field->getSubFields(), array_get($data, $field->key))
                    );
                }

                continue;
            }

            $manipulation = explode(':', $manipulation);
            $value = array_get($data, $fields);

            if(! $value && count($manipulation) > 1) {
                $value = $this->getCopyFieldValue($manipulation[1]);
            }

            array_set(
                $data,
                $field->key,
                call_user_func($manipulation[0], $value)
            );
        }

        return $data;
    }

    protected function getCopyFieldValue($key)
    {
        $target = $this->transformedData;
        $key = is_array($key) ? $key : explode('.', $key);

        while (! is_null($segment = array_shift($key))) {
            if(! Arr::isAssoc($target) && Arr::exists($target, 0)) {
                $target = $target[0];
            }

            if (Arr::accessible($target) && Arr::exists($target, $segment)) {
                $target = $target[$segment];
            } elseif (is_object($target) && isset($target->{$segment})) {
                $target = $target->{$segment};
            } else {
                return null;
            }
        }

        return $target;
    }

}