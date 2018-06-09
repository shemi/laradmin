<?php

namespace Shemi\Laradmin\FormFields;

use Shemi\Laradmin\JsonSchema\Blueprint;
use Shemi\Laradmin\JsonSchema\ObjectBlueprint;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Data\Model;
use Shemi\Laradmin\Models\Setting;

class GroupField extends FormFormField
{

    protected $codename = "group";

    protected $subFieldsSupported = true;

    protected $builderSchema = [
        'fields' => []
    ];

    public function createContent(Field $field, Model $type, $data)
    {
        return view('laradmin::formFields.group', compact(
            'field',
            'type',
            'data'
        ));
    }

    public function getValidationRoles(Field $field)
    {
        $fields = $field->getSubFields();

        $roles = [];

        if($field->validation && ! empty($field->validation)) {
            $roles[$field->key] = $field->validation;
        }

        $prefix = "{$field->key}.";

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
        $primaryKey = null;

        if(! is_array($data) || empty($data)) {
            return [];
        }

        $newData = [];

        if($field->is_relationship) {
            $type = $field->getType();
            $relationshipTypeModel = $field->getRelationModelClass(app($type->model));
            $primaryKey = $relationshipTypeModel->getKeyName();
        }

        /** @var Field $childField */
        foreach ($field->getSubFields() as $childField) {
            $value = array_get($data, $childField->key, $childField->getDefaultValue());
            $newData[$childField->key] = $childField->transformRequest($value);
        }

        if($field->is_relationship) {
            $newData[$primaryKey] = array_get($data, $primaryKey, null);
        }


        return $newData;
    }

    public function transformResponse(Field $field, $data)
    {
        $primaryKey = null;

        foreach ($field->getSubFields() as $childField) {
            $value = array_get($data, $childField->key, $childField->getDefaultValue());
            $data[$childField->key] = $childField->transformResponse($value);
        }

        if($field->is_relationship) {
            $type = $field->getType();
            $relationshipTypeModel = $field->getRelationModelClass(app($type->model));
            $primaryKey = $relationshipTypeModel->getKeyName();
            $data[$primaryKey] = array_get($data, $primaryKey, null);
        }

        return $data;
    }

    public function structure()
    {
        return array_replace_recursive(parent::structure(), [
            'fields' => (array) [],
            'show_label' => false,
            'template_options' => [
                'horizontal' => true
            ]
        ]);
    }

    protected function customSchema(Blueprint $schema, ObjectBlueprint $root)
    {
        $schema->relationship();

        $schema->array('fields', function(Blueprint $schema) {
            $schema->object();
        });
    }

    public function getSettingsValueType(Field $field)
    {
        if($field->is_relationship) {
            return Setting::TYPE_SINGLE_RELATIONSHIP;
        }

        return Setting::TYPE_OBJECT;
    }

}