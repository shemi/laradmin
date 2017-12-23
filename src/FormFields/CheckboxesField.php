<?php

namespace Shemi\Laradmin\FormFields;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Shemi\Laradmin\JsonSchema\Blueprint;
use Shemi\Laradmin\JsonSchema\ObjectBlueprint;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\Type;

class CheckboxesField extends FormField
{

    protected $codename = "checkboxes";

    public function createContent(Field $field, Type $type, Model $model, $data)
    {
        return view('laradmin::formFields.checkboxes', compact(
            'field',
            'type',
            'model',
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

}