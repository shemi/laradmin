<?php

namespace Shemi\Laradmin\FormFields;

use Illuminate\Database\Eloquent\Model;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\Type;

class RepeaterField extends FormField
{

    protected $codename = "repeater";

    public function createContent(Field $field, Type $type, Model $model, $data)
    {
        return view('laradmin::formFields.repeater', compact(
            'field',
            'type',
            'model',
            'data'
        ));
    }

}