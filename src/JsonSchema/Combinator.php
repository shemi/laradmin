<?php

namespace Shemi\Laradmin\JsonSchema;

use Closure;

class Combinator implements Schemable
{

    /**
     * @var Blueprint
     */
    protected $items;

    /**
     * @var string
     */
    protected $type;

    protected $required;

    protected $title;

    /**
     * @param string $type
     * @param Closure $callback
     *
     * @return static
     */
    public static function create($type = "allOf", Closure $callback)
    {
        return tap(new static, function(Combinator $combinator) use ($type, $callback) {
            $combinator->type($type)
                ->items($callback);
        });
    }

    public function type($type)
    {
        $this->type = $type;

        return $this;
    }

    public function items(Closure $callback)
    {
        $blueprint = new Blueprint;

        $callback($blueprint);

        $this->items = $blueprint;

        return $this;
    }

    /**
     * @return $this
     */
    public function required()
    {
        $this->required = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function title($title = null)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        $array = [
            "{$this->type}" => $this->items->toArray()
        ];

        if($this->required) {
            $array['required'] = true;
        }

        if($this->title) {
            $array['title'] = $this->title;
        }

        return $array;
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