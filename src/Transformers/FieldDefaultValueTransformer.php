<?php

namespace Shemi\Laradmin\Transformers;

use Shemi\Laradmin\Models\Field;

class FieldDefaultValueTransformer
{

    public static function transform(Field $field)
    {
        if($field->default_value !== null) {
            return $field->default_value;
        }

        if($field->nullable) {
            return null;
        }

        switch ($field->type) {
            case 'number':
            case 'text':
            case 'text_area':
            case 'date':
            case 'datetime':
                return "";

            case 'switch':
            case 'checkbox':
                return false;

            case 'object':
            case 'group':
                return static::transformSubFields($field);

            case 'select_multiple':
            case 'checkboxes':
            case 'repeater':
            case 'files':
                return (array) [];

            default:
                return null;
        }
    }

    public static function transformSubFields(Field $field)
    {
        $return = [];

        if(! $field->is_support_sub_fields) {
            return $return;
        }

        /** @var Field $subField */
        foreach ($field->getSubFields() as $subField) {
            $return[$subField->key] = $subField->getDefaultValue();
        }

        return $return;
    }

}