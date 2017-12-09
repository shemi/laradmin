<?php

namespace Shemi\Laradmin\FormFields\Schema;

use Closure;

class ArrayBlueprint implements Schemable
{
    public $type = "array";

    public $nullable;

    protected $additionalItems = null;

    protected $minItems = null;

    protected $maxItems = null;

    protected $required = null;


    protected $items;


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

        $this->items = $blueprint;

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
     * @param bool $additionalItems
     *
     * @return $this
     */
    public function additionalItems($additionalItems = true)
    {
        $this->additionalItems = $additionalItems;

        return $this;
    }

    public function required()
    {
        $this->required = true;

        return $this;
    }

    public function nullable()
    {
        $this->nullable = true;

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

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        $schema = [
            'type' => $this->type
        ];

        if($this->items instanceof Blueprint) {
            $schema['items'] = $this->items->toArray();
        }

        if(! is_null($this->additionalItems)) {
            $schema['additionalProperties'] = $this->additionalItems;
        }

        if(! is_null($this->minItems)) {
            $schema['minItems'] = $this->minItems;
        }

        if(! is_null($this->maxItems)) {
            $schema['maxItems'] = $this->maxItems;
        }

        if($this->required) {
           $schema['required'] = true;
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