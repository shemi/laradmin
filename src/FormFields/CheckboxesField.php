<?php

namespace Shemi\Laradmin\FormFields;

use Illuminate\Database\Eloquent\Model;
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

}