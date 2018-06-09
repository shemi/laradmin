<?php

namespace Shemi\Laradmin\FormFields;

use Shemi\Laradmin\Data\Model;
use Shemi\Laradmin\JsonSchema\Blueprint;
use Shemi\Laradmin\JsonSchema\ObjectBlueprint;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\Setting;

class TagsField extends FormFormField
{

    protected $codename = "tags";

    public function createContent(Field $field, Model $type, $data)
    {
        return view('laradmin::formFields.tags', compact(
            'field',
            'type',
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

    public function structure()
    {
        $structure = parent::structure();

        return array_replace_recursive($structure, [
            'template_options' => [
                'icon' => null,
                'items_label' => null,
                'item_label' => null,
                'placeholder' => null,
                'show_if' => null
            ]
        ]);
    }

    protected function customSchema(Blueprint $schema, ObjectBlueprint $root)
    {
        $schema->template_options->properties(function(Blueprint $schema) {
            $schema->string('items_label')->nullable();
            $schema->string('item_label')->nullable();
        });
    }

    public function getSettingsValueType(Field $field)
    {
        return Setting::TYPE_RELATIONSHIP;
    }

}