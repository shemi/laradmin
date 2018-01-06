<?php

namespace Shemi\Laradmin\FormFields;

use Illuminate\Database\Eloquent\Model;
use Shemi\Laradmin\JsonSchema\Blueprint;
use Shemi\Laradmin\JsonSchema\ObjectBlueprint;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\Type;

class SelectMultipleField extends FormFormField
{

    protected $codename = "select_multiple";

    public function createContent(Field $field, Type $type, Model $model, $data)
    {
        return view('laradmin::formFields.selectMultiple', compact(
            'field',
            'type',
            'model',
            'data'
        ));
    }

    public function transformRequest(Field $field, $data)
    {
        return collect($data)
            ->pluck('key')
            ->values()
            ->all();
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
                'multiselect' => [
                    'clearOnSelect' => false,
                    'closeOnSelect' => true,
                    'hideSelected' => true,
                    'preserveSearch' => true
                ],
                'show_if' => null
            ]
        ]);
    }

    protected function customSchema(Blueprint $schema, ObjectBlueprint $root)
    {
        $schema->relationship();
        $schema->options();

        $schema->template_options->properties(function(Blueprint $schema) {
            $schema->object('multiselect', function(Blueprint $schema) {
                $schema->boolean('clearOnSelect');
                $schema->boolean('closeOnSelect');
                $schema->boolean('hideSelected');
                $schema->boolean('preserveSearch');
            })->required();
        });

    }

}