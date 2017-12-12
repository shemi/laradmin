<?php

namespace Shemi\Laradmin\FormFields\Schema;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Fluent;

class Blueprint implements Arrayable
{
    protected $properties = [];

    protected static function create()
    {
        return new static();
    }

    public function object($key = null, Closure $callback = null)
    {
        $objectBluePrint = ObjectBlueprint::create();

        if($callback) {
            $objectBluePrint->properties($callback);
        }

        return $this->setItem($key, $objectBluePrint);
    }

    /**
     * @param null $key
     * @param Closure|array|null $callback
     * @return ArrayBlueprint
     */
    public function array($key = null, $callback = null)
    {
        return $this->setItem(
            $key,
            ArrayBlueprint::create($callback)
        );
    }

    /**
     * @param $key
     * @param array $parameters
     * @return Fluent
     */
    public function string($key = null, $parameters = [])
    {
        return $this->setItem(
            $key,
            $this->createItem('string', $parameters)
        );
    }

    public function boolean($key = null, $parameters = [])
    {
        return $this->setItem(
            $key,
            $this->createItem('boolean', $parameters)
        );
    }

    public function null($key = null, $parameters = [])
    {
        return $this->setItem(
            $key,
            $this->createItem('null', $parameters)
        );
    }

    public function number($key = null, $parameters = [], $type = 'number')
    {
        return $this->setItem(
            $key,
            $this->createItem($type, $parameters)
        );
    }

    public function integer($key = null, $parameters = [])
    {
        return $this->number($key, $parameters, 'integer');
    }

    public function allOf($key = null, $items)
    {
        return $this->combine($key, $items, 'allOf');
    }

    public function anyOf($key = null, $items)
    {
        return $this->combine($key, $items, 'anyOf');
    }

    /**
     * @param null $key
     * @param $items
     * @return mixed
     */
    public function oneOf($key = null, $items)
    {
        return $this->combine($key, $items, 'oneOf');
    }

    public function not($key = null, $items)
    {
        return $this->combine($key, $items, 'not');
    }

    /**
     * @param null|string $key
     * @param array|Closure $items
     * @param string $type
     *
     * @return Combinator
     */
    public function combine($key = null, $items, $type)
    {
        $callback = $items;

        if(is_array($callback)) {
            $callback = function (Blueprint $schema) use ($items) {
                $schema->fillFromArray($items);
            };
        }

        return $this->setItem($key, Combinator::create($type, $callback));
    }

    public function fillFromArray($array)
    {
        foreach ($array as $name => $item) {
            if(is_string($name) && method_exists($this, $name)) {
                $this->{$name}(null, $item);
            }
            elseif(is_string($item) && method_exists($this, $item)) {
                $this->{$name}();
            }
            elseif(! is_string($name) && is_array($item)) {
                $type = isset($item['type']) ? $item['type'] : false;

                if(! $type || ! method_exists($this, $type)) {
                    continue;
                }

                unset($item['type']);

                $this->{$type}(null, $item);
            }
        }
    }

    /**
     * @param $type
     * @param array|Arrayable $parameters
     * @return Fluent
     */
    protected function createItem($type, $parameters = [])
    {
        if($parameters instanceof Arrayable) {
            $parameters = $parameters->toArray();
        }

        return new Fluent(
            array_merge(compact('type'), $parameters)
        );
    }

    /**
     * @param $key
     * @param $item
     * @return mixed
     */
    protected function setItem($key, $item)
    {
        if(is_string($key)) {
            $this->properties[$key] = $item;
        } else {
            $this->properties[] = $item;
        }

        return $item;
    }

    /**
     * @param string $name
     * @param Object $item
     * @return Object
     */
    protected function extractCombinations($name, $item)
    {
        if(! is_object($item)) {
            return $item;
        }

        if(isset($item->nullable)) {
            $required = false;
            $itemProps = $item->toArray();

            if(isset($itemProps['nullable'])) {
                unset($itemProps['nullable']);
            }

            if(isset($itemProps['required'])) {
                $required = $itemProps['required'];

                unset($itemProps['required']);
            }

            $item = $this->oneOf($name, [
                'null' => [],
                "{$item->type}" => $itemProps
            ]);

            if($required) {
                $item->required();
            }
        }

        return $item;
    }

    public function commonFormFieldSchema()
    {
        $this->string('key')
            ->minLength(1)
            ->required();

        $this->string('label')
            ->minLength(1)
            ->required();

        $this->string('type')
            ->enum(app('laradmin')->getFormFieldNames())
            ->required();

        $this->string('id')
            ->minLength(5)
            ->required();

        $this->boolean('nullable')
            ->required()
            ->nullable();

        $this->boolean('read_only')
            ->required()
            ->nullable();

        $this->boolean('show_label')
            ->required()
            ->nullable();

        $this->anyOf('default_value', function(Blueprint $schema) {
            $schema->null();
            $schema->string();
            $schema->object();
            $schema->boolean();
            $schema->array();
        })->required();

    }

    public function visibility($except = [])
    {
        return $this->array(
            'visibility',
            function(Blueprint $schema) use ($except) {
                $schema->string()
                    ->enum(Schema::getVisibilityOptions($except));
            }
        )->uniqueItems()
            ->required();
    }

    public function validation()
    {
        return $this->array('validation', function(Blueprint $schema) {
                $schema->string();
        })->uniqueItems()->required();
    }

    public function templateOptions()
    {
        return $this->object('template_options', function (Blueprint $schema) {

            $schema->string('placeholder')
                ->nullable();

            $schema->string('size')
                ->enum(['default', 'is-small', 'is-medium', 'is-large'])
                ->nullable();

            $schema->string('type')
                ->nullable();

            $schema->string('transform')
                ->nullable();

            $schema->string('position')
                ->enum(['is-left', 'is-center', 'is-right'])
                ->nullable();

            $schema->string('show_if')
                ->nullable();

            $schema->string('icon')
                ->nullable();

            $schema->boolean('grouped');

            $schema->boolean('group_multiline');

            $schema->number('max_length')->nullable();

        })->required();
    }

    public function browseSettings()
    {
        return $this->object('browse_settings', function (Blueprint $schema) {

            $schema->number('order')
                ->required()
                ->nullable();

            $schema->string('label')
                ->nullable();

            $schema->boolean('sortable');

            $schema->boolean('searchable');

            $schema->string('search_comparison')
                ->enum(['=', '>=', '>', 'like', '<', '<=']);

            $schema->string('date_format')
                ->nullable();

        })->required();
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function properties()
    {
        return collect($this->properties);
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return $this->properties()->isEmpty();
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        $array = [];

        foreach ($this->properties as $name => $item) {
            $item = $this->extractCombinations($name, $item);

            $array[$name] = $item instanceof Arrayable ?
                $item->toArray() : value($item);
        }

        return $array;
    }
}