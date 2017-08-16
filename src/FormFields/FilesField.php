<?php

namespace Shemi\Laradmin\FormFields;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Testing\File;
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

    public function transformRequest(Field $field, $value)
    {
        $value = collect($value);

        if($value->isEmpty()) {
            return $value;
        }

        return $value->transform(function($file, $index) {
            $id = array_get($file, 'customAttributes.id', 0);

            return (object) [
                'is_new' => ! ((bool) $id),
                'id' => $id,
                'order' => $index,
                'temp_path' => array_get($file, 'customAttributes.temp_path', ""),
                'name' => array_get($file, 'name', ""),
                'hash_name' => array_get($file, 'customAttributes.md5_name', ""),
                'caption' => array_get($file, 'customAttributes.caption', ""),
                'alt' => array_get($file, 'customAttributes.alt', ""),
            ];
        });
    }

}