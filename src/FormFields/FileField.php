<?php

namespace Shemi\Laradmin\FormFields;

use Shemi\Laradmin\Data\Model;
use Shemi\Laradmin\Contracts\FieldHasBrowseValue;
use Shemi\Laradmin\JsonSchema\Blueprint;
use Shemi\Laradmin\JsonSchema\ObjectBlueprint;
use Shemi\Laradmin\Models\Field;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Shemi\Laradmin\Models\Setting;

class FileField extends FormFormField implements FieldHasBrowseValue
{

    protected $codename = "file";

    public function createContent(Field $field, Model $type, $data)
    {
        return view('laradmin::formFields.file', compact(
            'field',
            'type',
            'data'
        ));
    }

    public function transformRequest(Field $field, $data)
    {
        if(! $data) {
            return collect([]);
        }

        $id = array_get($data, 'customAttributes.id', 0);

        $data = (object) [
            'is_new' => ! ((bool) $id),
            'id' => $id,
            'order' => 0,
            'temp_path' => array_get($data, 'customAttributes.temp_path', ""),
            'name' => array_get($data, 'name', ""),
            'hash_name' => array_get($data, 'customAttributes.md5_name', ""),
            'caption' => array_get($data, 'customAttributes.caption', ""),
            'alt' => array_get($data, 'customAttributes.alt', ""),
        ];

        return collect([$data]);
    }

    public function getValidationRoles(Field $field)
    {
        return false;
    }

    public function renderBrowseValue(Field $field, EloquentModel $model)
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

    public function structure()
    {
        $structure = parent::structure();

        return array_replace_recursive($structure, [
            'media' => [
                'disk' => config(
                    'medialibrary.defaultFilesystem',
                    config('filesystems.default')
                )
            ]
        ]);
    }

    protected function customSchema(Blueprint $schema, ObjectBlueprint $root)
    {
        $schema->media();
    }

    public function getSettingsValueType(Field $field)
    {
        return Setting::TYPE_SINGLE_MEDIA;
    }

}