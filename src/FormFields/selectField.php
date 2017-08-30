<?php

namespace Shemi\Laradmin\FormFields;

use Illuminate\Database\Eloquent\Model;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\Type;

class SelectField extends FormField
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

}