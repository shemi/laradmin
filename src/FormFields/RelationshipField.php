<?php

namespace Shemi\Laradmin\FormFields;

use Illuminate\Database\Eloquent\Model;
use Shemi\Laradmin\JsonSchema\Blueprint;
use Shemi\Laradmin\JsonSchema\ObjectBlueprint;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\Type;

class RelationshipField extends FormField
{

    protected $codename = "relationship";

    public function createContent(Field $field, Type $type, Model $model, $data)
    {
        return view('laradmin::formFields.relationship', compact(
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

    public function structure()
    {
        $structure = parent::structure();

        return array_replace_recursive($structure, [
            'template_options' => [
                'items_label' => null,
                'item_label' => null,
                'show_if' => null
            ],
            'relationship' => (object) [
                'ajax_powered' => true,
                'label' => null,
                'image' => null,
                'type' => null
            ]
        ]);
    }

    protected function customSchema(Blueprint $schema, ObjectBlueprint $root)
    {
        $schema->relationship();

        $schema->template_options->properties(function(Blueprint $schema) {
            $schema->string('items_label')->nullable();
            $schema->string('item_label')->nullable();
        });
    }

}