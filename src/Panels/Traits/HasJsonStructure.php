<?php

namespace Shemi\Laradmin\Panels\Traits;

trait HasJsonStructure
{

    public function structure()
    {
        return [
            'id' => null,
            'title' => 'New Panel',
            'type' => $this->getCodename(),
            'position' => null,
            'is_main_meta' => false,
            'fields' => (array) [],
            'has_container' => true,
            'style' => (object) []
        ];
    }

}