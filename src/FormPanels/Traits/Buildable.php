<?php

namespace Shemi\Laradmin\FormPanels\Traits;

trait Buildable
{
    use HasJsonStructure, HasJsonSchema;

    public function getBuilderData()
    {
        return [
            'schema' => $this->schema()->toArray(),
            'structure' => $this->structure(),
            'name' => $this->getName()
        ];
    }

}