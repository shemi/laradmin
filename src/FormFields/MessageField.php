<?php

namespace Shemi\Laradmin\FormFields;

use Illuminate\Database\Eloquent\Model;
use Shemi\Laradmin\JsonSchema\Blueprint;
use Shemi\Laradmin\JsonSchema\ObjectBlueprint;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\Type;

class MessageField extends FormFormField
{

    protected $codename = "message";

    protected $visibilityOptions = [
        "create",
        "edit",
        "view"
    ];

    protected $subFields = [
        'is-white',
        'is-black',
        'is-light',
        'is-dark',
        'is-primary',
        'is-info',
        'is-success',
        'is-warning',
        'is-danger'
    ];

    public function createContent(Field $field, Type $type, Model $model, $data)
    {
        return view('laradmin::formFields.message', compact(
            'field',
            'type',
            'model',
            'data'
        ));
    }

    public function structure()
    {
        $structure = parent::structure();

        return array_replace($structure, [
            'read_only' => true,
            'template_options' => [
                'view' => '',
                'type' => 'is-info'
            ],
            'visibility' => [
                'create',
                'edit'
            ]
        ]);
    }

    protected function customSchema(Blueprint $schema, ObjectBlueprint $root)
    {
        $schema->template_options->properties(function(Blueprint $schema) {
            $schema->string('view')->required();
        });
    }

}