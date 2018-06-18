<?php

namespace Shemi\Laradmin\FormFields;

use Illuminate\Support\Collection;
use Shemi\Laradmin\JsonSchema\Blueprint;
use Shemi\Laradmin\JsonSchema\ObjectBlueprint;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Data\Model;
use Shemi\Laradmin\Models\Setting;

class EditorField extends FormFormField
{

    protected $codename = "editor";

    public function createContent(Field $field, Model $type, $data)
    {
        return view('laradmin::formFields.editor', compact(
            'field',
            'type',
            'data'
        ));
    }

    public function structure()
    {
        $structure = parent::structure();

        return array_replace_recursive($structure, [
            'template_options' => [
                'show_if' => null,
                'mce' => [
                    'plugins' => null,
                    'toolbar1' => null,
                    'toolbar2' => null,
                    'otherOptions' => [
                        'menubar' => 'edit insert view format table tools',
                        'min_height' => 300,
                        'autoresize_max_height' => 700,
                        'templates' => [
                            [
                                'title' => 'Laradmin',
                                'description' => "Laradmin sample",
                                'content' => '<p><b>Laradmin</b> is so cool :)</p>'
                            ]
                        ]
                    ]
                ]
            ]
        ]);
    }

    public function transformResponse(Field $field, $data)
    {
        return $data;
    }

    public function getSettingsValueType(Field $field)
    {
        return Setting::TYPE_STRING;
    }

    protected function customSchema(Blueprint $schema, ObjectBlueprint $root)
    {
        $schema->template_options->properties(function (Blueprint $schema) {
            $schema->object('mce', function (Blueprint $schema) {
                $schema->array('plugins', ['string'])
                    ->nullable()
                    ->required();

                $schema->array('toolbar1', ['string'])
                    ->nullable()
                    ->required();

                $schema->array('toolbar2', ['string'])
                    ->nullable()
                    ->required();

                $schema->object('otherOptions', function (Blueprint $schema) {
                })
                    ->required();

            })
            ->title('more information at: https://www.tinymce.com/docs/')
            ->required();
        });
    }

}