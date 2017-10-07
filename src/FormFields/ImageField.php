<?php

namespace Shemi\Laradmin\FormFields;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Testing\File;
use Illuminate\Support\HtmlString;
use Shemi\Laradmin\Contracts\FieldHasBrowseValue;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\Type;

class ImageField extends FormField implements FieldHasBrowseValue
{

    protected $codename = "image";

    public function createContent(Field $field, Type $type, Model $model, $data)
    {
        return view('laradmin::formFields.image', compact(
            'field',
            'type',
            'model',
            'data'
        ));
    }

    public function transformRequest(Field $field, $value)
    {
        if(! $value) {
            return collect([]);
        }

        $id = array_get($value, 'customAttributes.id', 0);

        $value = (object) [
            'is_new' => ! ((bool) $id),
            'id' => $id,
            'order' => 0,
            'temp_path' => array_get($value, 'customAttributes.temp_path', ""),
            'name' => array_get($value, 'name', ""),
            'hash_name' => array_get($value, 'customAttributes.md5_name', ""),
            'caption' => array_get($value, 'customAttributes.caption', ""),
            'alt' => array_get($value, 'customAttributes.alt', ""),
        ];

        return collect([$value]);
    }

    public function getValidationRoles(Field $field)
    {
        return false;
    }

    public function renderBrowseValue(Field $field, Model $model)
    {
        $media = $model->getMedia($field->key)->first();

        if(! $media) {
            return "";
        }

        $src = route('laradmin.serve', [
            'mediaId' => $media->id,
            'fileName' => $media->name,
            'pc' => $field->getTemplateOption('preview_conversion', null)
        ]);


        return "<div class='image' style='max-width: 96px'><img src='{$src}'></div>";
    }
}