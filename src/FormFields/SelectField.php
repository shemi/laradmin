<?php

namespace Shemi\Laradmin\FormFields;

use Illuminate\Database\Eloquent\Model;
use Shemi\Laradmin\JsonSchema\Blueprint;
use Shemi\Laradmin\JsonSchema\ObjectBlueprint;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\Type;

class SelectField extends FormFormField
{

    protected $codename = "select";

    public function createContent(Field $field, Type $type, Model $model, $data)
    {
        return view('laradmin::formFields.select', compact(
            'field',
            'type',
            'model',
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

}