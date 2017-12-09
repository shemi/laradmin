<?php

namespace Shemi\Laradmin\FormFields\Schema;

use Closure;

class ObjectBlueprint implements Schemable
{
    public $nullable;

    public $type = "object";

    protected $parent;

    protected $title = "";

    protected $additionalProperties = null;

    protected $minProperties = null;

    protected $maxProperties = null;

    protected $isRequired = null;

    protected $required = [];

    protected $properties;



    /**
     * @param Closure|null $callback
     * @return static
     */
    public static function create(Closure $callback = null)
    {
        $objectBlueprint = new static();

        if($callback) {
            $objectBlueprint->properties($callback);
        }

        return $objectBlueprint;
    }

    public function properties(Closure $callback)
    {
        $blueprint = new Blueprint();

        $callback($blueprint);

        $this->properties = $blueprint;

        return $this;
    }

    /**
     * @param string $title
     *
     * @return ObjectBlueprint
     */
    public function title($title = "")
    {
        $this->title = $title;

        return $this;
    }

    public function required()
    {
        $this->isRequired = true;

        return;
    }

    public function nullable()
    {
        $this->nullable = true;

        return;
    }

    /**
     * @param bool $additionalProperties
     *
     * @return ObjectBlueprint
     */
    public function additionalProperties($additionalProperties = true)
    {
        $this->additionalProperties = $additionalProperties;

        return $this;
    }

    protected function extractRequired(array $properties)
    {
        $newProperties = [];

        foreach ($properties as $name => $property) {
            $type = isset($property['type']) ? $property['type'] : null;

            if($type !== 'object' && isset($property['required'])) {
                if($property['required']) {
                    $this->required[] = $name;
                }

                unset($property['required']);
            }
            elseif(isset($property['isRequired'])) {
                if($property['isRequired']) {
                    $this->required[] = $name;
                }

                unset($property['isRequired']);
            }

            $newProperties[$name] = $property;
        }

        return $newProperties;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        if($this->properties instanceof Blueprint) {
            $this->properties = $this->properties->toArray();
        }

        $schema = [
            'title' => $this->title,
            'type' => $this->type
        ];


        if (is_array($this->properties) && ! empty($this->properties)) {
            $schema['properties'] = $this->extractRequired($this->properties);
        }

        if(! is_null($this->additionalProperties)) {
            $schema['additionalProperties'] = $this->additionalProperties;
        }

        if(! is_null($this->minProperties)) {
            $schema['minProperties'] = $this->minProperties;
        }

        if(! is_null($this->minProperties)) {
            $schema['minProperties'] = $this->minProperties;
        }

        if($this->required && ! empty($this->required)) {
            $schema['required'] = $this->required;
        }

        if($this->isRequired) {
            $schema['isRequired'] = true;
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