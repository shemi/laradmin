<?php

namespace Shemi\Laradmin\JsonSchema;

use Closure;
use Illuminate\Contracts\Support\Arrayable;

class ArrayBlueprint implements Schemable
{
    public $type = "array";

    public $nullable;

    protected $uniqueItems = null;

    protected $minItems = null;

    protected $maxItems = null;

    protected $required = null;

    protected $title = null;

    /**
     * @var null|Blueprint
     */
    protected $blueprint;


    /**
     * @param Closure|array|null $callback
     * @return static
     */
    public static function create($callback = null)
    {
        $arrayBlueprint = new static();

        if($callback) {
            if(is_array($callback)) {
                $arrayBlueprint->fromArray($callback);
            } else {
                $arrayBlueprint->items($callback);
            }
        }

        return $arrayBlueprint;
    }

    public function items(Closure $callback)
    {
        $blueprint = new Blueprint;

        $callback($blueprint);

        $this->blueprint = $blueprint;

        return $this;
    }

    public function fromArray($array)
    {
        $items = [];

        if(isset($array['items'])) {
            $items = $array['items'];

            unset($array['items']);
        }

        foreach ($array as $prop => $value) {
            if(! property_exists($this, $prop)) {
                continue;
            }

            $this->{$prop} = $value;
        }

        $callback = function(Blueprint $schema) use ($items) {
            $schema->fillFromArray($items);
        };

        return $this->items($callback);
    }

    /**
     * @param bool $uniqueItems
     *
     * @return $this
     */
    public function uniqueItems($uniqueItems = true)
    {
        $this->uniqueItems = $uniqueItems;

        return $this;
    }

    public function required($required = true)
    {
        $this->required = $required;

        return $this;
    }

    public function nullable($nullable = true)
    {
        $this->nullable = $nullable;

        return $this;
    }

    public function maxItems(int $number)
    {
        $this->maxItems = $number;

        return $this;
    }

    public function minItems(int $number = 0)
    {
        $this->minItems = $number;

        return $this;
    }

    public function title($title = "")
    {
        $this->title = $title;

        return $this;
    }

    protected function prepareItems()
    {
        if(! $this->blueprint || $this->blueprint->isEmpty()) {
            return;
        }

        if($this->blueprint->properties()->count() > 1) {
            $items = $this->blueprint->properties()->toArray();
            $this->blueprint = new Blueprint();
            $this->blueprint->oneOf(null, $items);
        }

        $this->blueprint = $this->blueprint->properties()->first();
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        $this->prepareItems();

        $schema = [
            'type' => $this->type
        ];

        if($this->blueprint instanceof Arrayable) {
            $schema['items'] = $this->blueprint->toArray();
        }

        if(! is_null($this->uniqueItems)) {
            $schema['uniqueItems'] = $this->uniqueItems;
        }

        if(! is_null($this->minItems)) {
            $schema['minItems'] = $this->minItems;
        }

        if(! is_null($this->maxItems)) {
            $schema['maxItems'] = $this->maxItems;
        }

        if($this->required) {
           $schema['required'] = $this->required;
        }

        if($this->title) {
           $schema['title'] = $this->title;
        }

        return $schema;
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

}