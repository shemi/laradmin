<?php

namespace Shemi\Laradmin\FormFields;

use Illuminate\Database\Eloquent\Model;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\Type;

class RepeaterField extends FormField
{

    protected $codename = "repeater";

    protected $builderSchema = [
        'fields' => []
    ];

    public function createContent(Field $field, Type $type, Model $model, $data)
    {
        return view('laradmin::formFields.repeater', compact(
            'field',
            'type',
            'model',
            'data'
        ));
    }

    protected function builderOptions($defaultOptions)
    {
        return array_merge($defaultOptions, [
            [
                'label' => 'Fields',
                'key' => 'fields',
                'type' => 'la-fields-list',
                'validation' => []
            ]
        ]);
    }

    public function getValidationRoles(Field $field)
    {
        $fields = $field->fields;

        $roles = [];

        if($field->validation && ! empty($field->validation)) {
            $roles[$field->key] = $field->validation;
        }

        $prefix = "{$field->key}.*.";

        foreach ($fields as $subField) {
            if($subField->read_only) {
                continue;
            }

            $formField = $subField->formField();
            $fieldRawRoles = $formField->getValidationRoles($subField);

            if(! $fieldRawRoles || empty($fieldRawRoles)) {
                continue;
            }

            $fieldRoles = [];

            foreach ($fieldRawRoles as $key => $role) {
                $fieldRoles[$prefix.$key] = $role;
            }

            $roles = array_merge($fieldRoles, $roles);
        }

        return empty($roles) ? false : $roles;
    }

    public function transformRequest(Field $field, $data)
    {
        $data = parent::transformRequest($field, $data);

        if(! is_array($data) || empty($data)) {
            return [];
        }

        $newData = [];

        foreach ($data as $row) {
            /** @var Field $childField */
            foreach ($field->fields as $childField) {
                $value = array_get($row, $childField->key, $childField->getDefaultValue());
                $row[$childField->key] = $childField->transformRequest($value);
            }

            $newData[] = $row;
        }

        return $newData;
    }

    public function transformResponse(Field $field, $values)
    {
        $values = collect($values);

        if($values->isEmpty()) {
            return $values;
        }

        $values->transform(function($row) use ($field) {
            foreach ($field->fields as $field) {
                $value = array_get($row, $field->key, $field->getDefaultValue());

                $row[$field->key] = $field->transformResponse($value);
            }

            return $row;
        });

        return $values;
    }

}