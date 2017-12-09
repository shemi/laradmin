<?php

namespace Shemi\Laradmin\FormFields\Traits;

use Shemi\Laradmin\Models\Field;

trait HasBuilderOptions
{

    protected $defaultBuilderOptions = [
        [
            'key' => 'show_label',
            'label' => null,
            'slot' => '<span>{{ field ? "Label Visible" : "Label Hidden" }}</span>',
            'slot_el' => 'span',
            'type' => 'b-switch',
            'validation' => []
        ],
        [
            'key' => 'read_only',
            'label' => null,
            'slot' => '<span>Read Only</span>',
            'slot_el' => 'span',
            'type' => 'b-switch',
            'validation' => []
        ],
    ];

    protected function getTemplateOptionsIsGroupedOption()
    {
        $types = json_encode(Field::$forceGroupedTypes);

        return [
            'key' => 'template_options.grouped',
            'label' => null,
            'slot' => '<span>Is Grouped</span>',
            'slot_el' => 'span',
            'props' => [
                ':disabled' => $types.'.indexOf(this.fieldType) >= 0'
            ],
            'type' => 'b-switch',
            'validation' => []
        ];
    }

    protected function getTemplateOptionsSizeOption()
    {
        return [
            'key' => 'template_options.size',
            'label' => "Size",
            'slot' => '<optgroup><option value="is-small">Small</option><option :value="null">Default</option><option value="is-medium">Medium</option><option value="is-large">Large</option></optgroup>',
            'slot_el' => 'div',
            'type' => 'b-select',
            'validation' => []
        ];
    }

    protected function getTemplateOptionsPositionOption()
    {
        return [
            'key' => 'template_options.position',
            'label' => "Position",
            'slot' => '<optgroup><option :value="null">Left</option><option value="is-center">Center</option><option value="is-right">Right</option></optgroup>',
            'slot_el' => 'div',
            'type' => 'b-select',
            'validation' => []
        ];
    }

    protected function getTemplateOptionsShowIfOption()
    {
        return [
            'label' => 'Show if',
            'type' => 'b-input',
            'key' => 'template_options.show_if',
            'props' => [
                'type' => 'text',
                'placeholder' => '',
            ],
            'validation' => []
        ];
    }

    public function getBuilderOptions()
    {
        $options = collect($this->defaultBuilderOptions);

        if(method_exists($this, 'builderOptions')) {
            $options = $this->builderOptions($options);
        }

        return $options;
    }

}