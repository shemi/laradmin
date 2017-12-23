<?php

namespace Shemi\Laradmin\FormFields;

use Illuminate\Database\Eloquent\Model;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\Type;

class SwitchField extends FormField
{

    protected $codename = "switch";

    public function createContent(Field $field, Type $type, Model $model, $data)
    {
        return view('laradmin::formFields.switch', compact(
            'field',
            'type',
            'model',
            'data'
        ));
    }

    public function transformRequest(Field $field, $data)
    {
        if($field->nullable != false) {
            return $data == $field->nullable ? null : $data;
        }

        return (bool) $data;
    }

    public function transformResponse(Field $field, $data)
    {
        return (bool) $data;
    }

    public function structure()
    {
        $structure = parent::structure();

        return array_replace_recursive($structure, [
            'nullable' => false,
            'default_value' => false,
            'template_options' => [
                'size' => null,
                'show_if' => null
            ]
        ]);
    }

}