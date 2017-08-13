<?php

namespace Shemi\Laradmin\FormFields;

use Illuminate\Database\Eloquent\Model;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\Type;

class SelectMultipleField extends FormField
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

}