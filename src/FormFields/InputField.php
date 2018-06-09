<?php

namespace Shemi\Laradmin\FormFields;

use Illuminate\Support\Collection;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Data\Model;
use Shemi\Laradmin\Models\Setting;

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

    public function createContent(Field $field, Model $type, $data)
    {
        return view('laradmin::formFields.input', compact(
            'field',
            'type',
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

    public function getSettingsValueType(Field $field)
    {
        if($field->field_type === 'number') {
            return Setting::TYPE_NUMERIC;
        }

        return Setting::TYPE_STRING;
    }

}