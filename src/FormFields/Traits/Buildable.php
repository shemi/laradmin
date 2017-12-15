<?php

namespace Shemi\Laradmin\FormFields\Traits;

trait Buildable
{
    use HasJsonStructure, HasJsonSchema;

    protected $visibilityOptions = [
        "browse",
        "create",
        "edit",
        "view",
        "export",
        "import"
    ];

    protected $templateOptionsSizes = [
        'default',
        'is-small',
        'is-medium',
        'is-large'
    ];

    public function getSubTypes()
    {
        return property_exists($this, 'subFields') ?
            $this->subFields :
            null;
    }

    public function getVisibilityOptions()
    {
        return $this->visibilityOptions;
    }

    public function getBuilderData()
    {
        return [
            'schema' => $this->schema()->toArray(),
            'structure' => $this->structure(),
            'subTypes' => $this->getSubTypes(),
            'name' => $this->getName()
        ];
    }

}