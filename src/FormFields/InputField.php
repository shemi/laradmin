<?php

namespace Shemi\Laradmin\FormFields;

use Illuminate\Database\Eloquent\Model;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\Type;

class InputField extends FormField
{

    protected $codename = "input";

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