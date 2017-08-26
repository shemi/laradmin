<?php

namespace Shemi\Laradmin\FormFields;

use Illuminate\Database\Eloquent\Model;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\Type;

class RepeaterField extends FormField
{

    protected $codename = "repeater";

    public function createContent(Field $field, Type $type, Model $model, $data)
    {
        return view('laradmin::formFields.repeater', compact(
            'field',
            'type',
            'model',
            'data'
        ));
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

}