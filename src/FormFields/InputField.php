<?php

namespace Shemi\Laradmin\FormFields;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Shemi\Laradmin\FormFields\Schema\Blueprint;
use Shemi\Laradmin\FormFields\Schema\ObjectBlueprint;
use Shemi\Laradmin\FormFields\Schema\Schema;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\Type;

class InputField extends FormField
{

    protected $codename = "input";

    protected $builderSchema = [
        'type' => 'input',
        'template_options' => [
            'icon' => null,
            'grouped' => false,
            'placeholder' => null,
            'type' => 'text',
            'size' => null,
            'max_length' => null,
            'show_if' => null
        ],
    ];

    protected $subFields = [
        'text',
        'number',
        'email',
        'search',
        'password',
        'tel',
        'textarea'
    ];

    public function createContent(Field $field, Type $type, Model $model, $data)
    {
        return view('laradmin::formFields.input', compact(
            'field',
            'type',
            'model',
            'data'
        ));
    }

    protected function builderOptions(Collection $defaultOptions)
    {
        return $defaultOptions->merge([
            $this->getTemplateOptionsIsGroupedOption(),
            $this->getTemplateOptionsSizeOption(),
            $this->getTemplateOptionsPositionOption(),
            [
                'label' => 'Icon',
                'type' => 'la-icon-input',
                'key' => 'template_options.icon',
                'props' => (object) [],
                'validation' => []
            ],
            $this->getTemplateOptionsShowIfOption()
        ]);
    }

    public function schema()
    {
        $s = Schema::create('input', function(Blueprint $schema, ObjectBlueprint $root) {
//            $schema->string('key')
//                ->minLength(1)
//                ->nullable()
//                ->required();
//
//            $schema->string('label')
//                ->minLength(1)
//                ->required();
//
//            $schema->boolean('nullable');
//
//            $schema->null('options');
//
//            $schema->array('visibility')
//                ->items(function(Blueprint $schema) {
//                    $schema->string()
//                        ->enum(['browse', 'create', 'edit',
//                            'view', 'export', 'import']);
//                })
//                ->nullable()
//                ->maxItems(15);
//
//            $schema->object('template_options', function(Blueprint $schema) {
//                $schema->string('placeholder');
//                $schema->string('type')
//                    ->enum(['text', 'number', 'email', 'password'])
//                    ->required()
//                    ->nullable();
//                $schema->string('size')
//                    ->enum(['']);
//            });

            $schema->commonFormFieldSchema();
            $schema->visibility();
            $schema->validation();
            $schema->templateOptions();
            $schema->browseSettings();

        });

//        dd($s->toJson());

        return $s;
    }

}