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
     * @throws CreateUpdateTransformCantFindCopyFieldOrAttributeException
     */
    public function transform($data, Collection $fields)
    {
        $this->fields = $fields;
        $this->data = $data;
        $this->transformedData = $this->transformFields($this->fields);



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
     * @param $value
     * @param Field $field
     * @return mixed
     * @throws CreateUpdateTransformCantFindCopyFieldOrAttributeException
     */
    public function manipulateValues()
    {
        

        $transform = explode(':', $field->getTemplateOption('transform', 'value'));

        if(! $value && count($transform) > 1) {
            $copyKey = $transform[1];

            $copyField = $this->fields
                ->where('key', $copyKey)
                ->first();

            if(! $copyField) {
                throw CreateUpdateTransformCantFindCopyFieldOrAttributeException::create($copyKey);
            }

            $value = $this->getFieldValue($copyField);
        }

        return call_user_func($transform[0], $value);
    }

}