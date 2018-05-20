<?php

namespace Shemi\Laradmin\FormPanels\Traits;

trait HasJsonStructure
{

    public function structure()
    {
        return [
            'id' => null,
            'title' => "New {$this->getName()} Panel",
            'type' => $this->getCodename(),
            'position' => 'main',
            'is_main_meta' => false,
            'fields' => (array) [],
            'has_container' => true,
            'style' => (object) []
        ];
    }

}