<?php

namespace Shemi\Laradmin\FormFields\Schema;

use Closure;

class Builder
{
    protected $schema;

    public function create($title, Closure $callback)
    {
        $this->schema = $this->createBlueprint(
            function(ObjectBlueprint $objectBlueprint) use ($title, $callback) {
                $objectBlueprint
                    ->title($title)
                    ->properties(
                        function(Blueprint $blueprint) use ($callback, $objectBlueprint) {
                            $callback($blueprint, $objectBlueprint);
                        }
                    );
            }
        );

        return $this;
    }

    protected function createBlueprint(Closure $callback)
    {
        return tap(
            ObjectBlueprint::create(),
            function (ObjectBlueprint $objectBlueprint) use ($callback) {
                $callback($objectBlueprint);
            }
        );
    }

    /**
     * @return array
     */
    protected function defaultProperties()
    {
        return [
            'key' => [
                'type' => 'string',
                'minLength' => 1
            ],
            'label' => [
                'type' => 'string',
                'minLength' => 1
            ],
            'nullable' => [
                'type' => 'boolean'
            ],
            'show_label' => [
                'type' => 'boolean'
            ],
            'read_only' => [
                'type' => 'boolean'
            ],
            'validation' => [
                'type' => 'array',
                'uniqueItems' => true,
                'items' => [
                    ['type' => 'string']
                ]
            ],
            'visibility' => [
                'type' => 'array',
                'uniqueItems' => true,
                'items' => [
                    [
                        'type' => 'string',
                        'enum' => $this->visibilityOptions()
                    ]
                ]
            ],
            'template_options' => $this->templateOptionsSchema()
        ];
    }

    protected function defaultRequired()
    {
        return [
            'key',
            'label',
            'visibility'
        ];
    }

    protected function templateOptionsSchema()
    {
        return [
            'type' => 'object',
            'properties' => [
                'size' => [
                    'oneOf' => [

                    ]
                ]
            ]
        ];
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function build()
    {
        return $this->schema->toArray();
    }
}