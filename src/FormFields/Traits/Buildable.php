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

    protected $subFields = null;

    protected $templateOptionsSizes = [
        'default',
        'is-small',
        'is-medium',
        'is-large'
    ];

    public function getSubTypes()
    {
        return $this->subFields;
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
            'name' => $this->getName(),
            'supportSubFields' => $this->isSupportingSubFields()
        ];
    }

}