<?php

namespace Shemi\Laradmin\FormFields;

use Shemi\Laradmin\Data\Model;
use Shemi\Laradmin\JsonSchema\Blueprint;
use Shemi\Laradmin\JsonSchema\ObjectBlueprint;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\Setting;

class CheckboxesField extends FormFormField
{

    protected $codename = "checkboxes";

    public function createContent(Field $field, Model $type, $data)
    {
        return view('laradmin::formFields.checkboxes', compact(
            'field',
            'type',
            'data'
        ));
    }

    public function transformRequest(Field $field, $data)
    {
        return (array) array_values($data);
    }

    public function structure()
    {
        return array_replace_recursive(parent::structure(), [
            'relationship' => null,
            'options' => (array) [],
            'template_options' => [
                'grouped' => false,
                'placeholder' => null,
                'size' => null,
            ]
        ]);
    }

    protected function customSchema(Blueprint $schema, ObjectBlueprint $root)
    {
        $schema->options();
        $schema->relationship();
    }

    public function getSettingsValueType(Field $field)
    {
        return Setting::TYPE_ARRAY;
    }

}