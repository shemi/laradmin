<?php

namespace Shemi\Laradmin\FormFields;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Shemi\Laradmin\JsonSchema\Blueprint;
use Shemi\Laradmin\JsonSchema\ObjectBlueprint;
use Shemi\Laradmin\JsonSchema\Schema;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\Type;

class InputField extends FormFormField
{

    protected $codename = "input";

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

    public function structure()
    {
        $structure = parent::structure();

        return array_replace_recursive($structure, [
            'template_options' => [
                'icon' => null,
                'grouped' => false,
                'placeholder' => null,
                'type' => 'text',
                'size' => null,
                'max_length' => null,
                'show_if' => null
            ]
        ]);
    }


}