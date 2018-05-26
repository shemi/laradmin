<?php

namespace Shemi\Laradmin\FormFields;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Shemi\Laradmin\JsonSchema\Blueprint;
use Shemi\Laradmin\JsonSchema\ObjectBlueprint;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\Type;

class RepeaterField extends FormFormField
{

    protected $codename = "repeater";

    protected $subFieldsSupported = true;

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

    protected function builderOptions(Collection $defaultOptions)
    {
        return $defaultOptions->merge([
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
        $fields = $field->getSubFields();

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
        $primaryKey = null;

        if(! is_array($data) || empty($data)) {
            return [];
        }

        $newData = [];

        if($field->relationship_type) {
            $relationshipTypeModel = app($field->relationship_type->model);
            $primaryKey = $relationshipTypeModel->getKeyName();
        }

        foreach ($data as $row) {

            /** @var Field $childField */
            foreach ($field->getSubFields() as $childField) {
                $value = array_get($row, $childField->key, $childField->getDefaultValue());
                $row[$childField->key] = $childField->transformRequest($value);
            }

            if($field->relationship_type) {
                $row[$primaryKey] = array_get($row, $primaryKey, null);
            }

            $newData[] = $row;
        }

        return $newData;
    }

    public function transformResponse(Field $field, $values)
    {
        $values = collect($values);
        $primaryKey = null;

        if($values->isEmpty()) {
            return $values;
        }

        if($field->relationship_type) {
            $relationshipTypeModel = app($field->relationship_type->model);
            $primaryKey = $relationshipTypeModel->getKeyName();
        }

        $values->transform(function($row) use ($field, $primaryKey) {

            foreach ($field->getSubFields() as $childField) {
                $value = array_get($row, $childField->key, $childField->getDefaultValue());
                $row[$childField->key] = $childField->transformResponse($value);
            }

            if($field->relationship_type) {
                $row[$primaryKey] = array_get($row, $primaryKey, null);
            }

            return $row;
        });

        return $values;
    }

    public function structure()
    {
        return array_replace_recursive(parent::structure(), [
            'fields' => (array) [],
            'template_options' => [
                'repeater_items_label' => null,
                'repeater_item_label' => null,
                'repeater_add_text' => null,
            ]
        ]);
    }

    protected function customSchema(Blueprint $schema, ObjectBlueprint $root)
    {
        $schema->oneOf('relationship', function (Blueprint $schema) {

            $schema->null();

            $schema->object(null, function(Blueprint $schema) {



                $schema->array('exclude', function(Blueprint $schema) {
                    $schema->string();
                })->required();

                $schema->string('type')
                    ->enum(
                        Type::browseAll()
                            ->pluck('slug')
                            ->values()
                            ->unique()
                            ->values()
                            ->toArray()
                    )
                    ->required();
            });

        })->required();

        $schema->array('fields', function(Blueprint $schema) {
            $schema->object();
        });

        $schema->template_options->properties(function(Blueprint $schema) {
            $schema->string('repeater_items_label')->nullable()->required();
            $schema->string('repeater_item_label')->nullable()->required();
            $schema->string('repeater_add_text')->nullable()->required();
        });
    }

}