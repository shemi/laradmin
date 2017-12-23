<?php

namespace Shemi\Laradmin\Panels\Traits;

trait Buildable
{
    use HasJsonStructure, HasJsonSchema;

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