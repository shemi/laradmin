<?php

namespace Shemi\Laradmin\FormFields\Traits;

trait HasBuilderSchema
{

    protected $defaultBuilderSchema = [
        'id' => null,
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


    public function getBuilderSchema()
    {
        $schema = $this->defaultBuilderSchema;

        $schema['type'] = $this->getCodename();

        if(property_exists($this, 'builderSchema')) {
            $schema = array_replace_recursive($schema, $this->builderSchema);
        }

        return $schema;
    }


}