<?php

namespace Shemi\Laradmin\FormFields;

use Shemi\Laradmin\Data\Model;
use Illuminate\Support\Collection;
use Shemi\Laradmin\JsonSchema\Blueprint;
use Shemi\Laradmin\JsonSchema\ObjectBlueprint;
use Shemi\Laradmin\Models\Field;

class FilesField extends FormFormField
{

    protected $codename = "files";

    public function createContent(Field $field, Model $type, $data)
    {
        return view('laradmin::formFields.files', compact(
            'field',
            'type',
            'data'
        ));
    }

    public function transformRequest(Field $field, $data)
    {
        if($data instanceof Collection) {
            return $data;
        }

        $data = collect($data);

        if($data->isEmpty()) {
            return $data;
        }

        return $data->transform(function($file, $index) {
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

    public function getValidationRoles(Field $field)
    {
        return false;
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
            ],
            'template_options' => [
                'preview_conversion' => null
            ]
        ]);
    }

    protected function customSchema(Blueprint $schema, ObjectBlueprint $root)
    {
        $schema->media();
        $schema->template_options->properties(function(Blueprint $schema) {
            $schema->string('preview_conversion')
                ->nullable()
                ->required();
        });
    }

    public function getSettingsValueType(Field $field)
    {
        return "media";
    }

}