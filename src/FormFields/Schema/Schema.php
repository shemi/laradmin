<?php

namespace Shemi\Laradmin\FormFields\Schema;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class Schema implements Arrayable, Jsonable
{

    protected static $formFieldVisibilityOptions = [
        'browse',
        'create',
        'edit',
        'view',
        'export',
        'import'
    ];

    /**
     * @var Builder
     */
    protected $schema;

    /**
     * @param $name
     * @param Closure $callback
     *
     * @return static
     */
    public static function create($name, Closure $callback)
    {
        return tap(new static(), function(Schema $schema) use ($name, $callback) {
            $builder = new Builder();

            $builder->create($name, $callback);

            $schema->schema = $builder;
        });
    }

    public static function getVisibilityOptions($except = [])
    {
        $options = collect(static::$formFieldVisibilityOptions);

        if(! empty($except)) {
            $options = $options->reject(function($option) use ($except) {
                return in_array($option, $except);
            });
        }

        return $options->values()->toArray();
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->schema->build();
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