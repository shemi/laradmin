<?php

namespace Shemi\Laradmin\FormFields;

use Illuminate\Database\Eloquent\Model;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\Type;

class MessageField extends FormField
{

    protected $codename = "message";

    public function createContent(Field $field, Type $type, Model $model, $data)
    {
        return view('laradmin::formFields.message', compact(
            'field',
            'type',
            'model',
            'data'
        ));
    }

}