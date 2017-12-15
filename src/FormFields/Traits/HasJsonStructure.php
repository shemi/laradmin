<?php

namespace Shemi\Laradmin\FormFields\Traits;

trait HasJsonStructure
{

    public function structure()
    {
        return [
            'id' => null,
            'type' => $this->getCodename(),
            'label' => '',
            'key' => '',
            'show_label' => true,
            'read_only' => false,
            'default_value' => null,
            'validation' => [],
            'relationship' => null,
            'visibility' => ["browse", "create", "edit"],
            'template_options' => [
                'size' => null,
                'show_if' => null
            ],
            'browse_settings' => [
                'order' => null,
                'sortable' => false,
                'searchable' => false,
                'search_comparison' => 'like'
            ]
        ];
    }

}