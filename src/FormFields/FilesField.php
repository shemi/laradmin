<?php

namespace Shemi\Laradmin\FormFields;

use Illuminate\Database\Eloquent\Model;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\Type;

class FilesField extends FormField
{

    protected $codename = "files";

    public function createContent(Field $field, Type $type, Model $model, $data)
    {
        return view('laradmin::formFields.files', compact(
            'field',
            'type',
            'model',
            'data'
        ));
    }



}