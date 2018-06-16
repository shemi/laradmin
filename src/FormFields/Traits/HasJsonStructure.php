<?php

namespace Shemi\Laradmin\FormFields\Traits;

use Shemi\Laradmin\Models\Field;

trait HasJsonStructure
{

    public function structure()
    {
        return [
            'id' => null,
            'type' => $this->getCodename(),
            'label' => null,
            'key' => null,
            'show_label' => true,
            'read_only' => false,
            'nullable' => null,
            'default_value' => null,
            'value_manipulation' => null,
            'validation' => [],
            'relationship' => null,
            'visibility' => ["create", "edit"],
            'template_options' => [
                'show_if' => null
            ],
            'browse_settings' => [
                'order' => null,
                'sortable' => false,
                'searchable' => false,
                'horizontal' => false,
                'search_comparison' => 'like'
            ],
            'object_type' => Field::OBJECT_TYPE
        ];
    }

}