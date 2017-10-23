<?php

namespace Shemi\Laradmin\FormFields;

use Illuminate\Database\Eloquent\Model;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\Type;

class TagsField extends FormField
{

    protected $codename = "tags";

    public function createContent(Field $field, Type $type, Model $model, $data)
    {
        return view('laradmin::formFields.tags', compact(
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