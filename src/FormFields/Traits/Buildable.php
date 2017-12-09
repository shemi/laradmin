<?php

namespace Shemi\Laradmin\FormFields\Traits;

trait Buildable
{
    use HasBuilderOptions, HasBuilderSchema;

    protected $visibilityOptions = [
        "browse",
        "create",
        "edit",
        "view",
        "export",
        "import"
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


}