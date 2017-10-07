<?php

namespace Shemi\Laradmin\FormFields;

use Illuminate\Database\Eloquent\Model;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\Type;

class InputField extends FormField
{

    protected $codename = "input";

    protected $builderSchema = [
        'type' => 'input',
        'template_options' => [
            'placeholder' => null,
            'type' => 'text',
            'size' => null,
            'max_length' => null,
            'show_if' => null
        ],
    ];

    protected $subFields = [
        'text',
        'number',
        'email',
        'search',
        'password',
        'tel',
        'textarea'
    ];

    public function createContent(Field $field, Type $type, Model $model, $data)
    {
        return view('laradmin::formFields.input', compact(
            'field',
            'type',
            'model',
            'data'
        ));
    }

}