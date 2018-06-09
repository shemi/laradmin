<?php

namespace Shemi\Laradmin\FormFields;

use Shemi\Laradmin\JsonSchema\Blueprint;
use Shemi\Laradmin\JsonSchema\ObjectBlueprint;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Data\Model;
use Shemi\Laradmin\Models\Setting;

class SelectField extends FormFormField
{

    protected $codename = "select";

    public function createContent(Field $field, Model $type, $data)
    {
        return view('laradmin::formFields.select', compact(
            'field',
            'type',
            'data'
        ));
    }

    public function structure()
    {
        $structure = parent::structure();

        return array_replace_recursive($structure, [
            'options' => (array) [],
            'nullable' => false,
            'template_options' => [
                'icon' => null,
                'placeholder' => null,
                'size' => null,
                'show_if' => null
            ]
        ]);
    }

    protected function customSchema(Blueprint $schema, ObjectBlueprint $root)
    {
        $schema->options();
    }

    public function getSettingsValueType(Field $field)
    {
        if($field->is_relationship) {
            return Setting::TYPE_SINGLE_RELATIONSHIP;
        }

        return Setting::TYPE_STRING;
    }

}