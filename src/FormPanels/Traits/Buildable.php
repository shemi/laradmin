<?php

namespace Shemi\Laradmin\FormPanels\Traits;

trait Buildable
{
    use HasJsonStructure, HasJsonSchema;

    protected $isProtected = false;

    public function getBuilderData()
    {
        return [
            'schema' => $this->schema()->toArray(),
            'structure' => $this->structure(),
            'options' => $this->getOptions(),
            'name' => $this->getName(),
            'protected' => $this->isProtected
        ];
    }

}