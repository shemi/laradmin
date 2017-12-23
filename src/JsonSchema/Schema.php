<?php

namespace Shemi\Laradmin\JsonSchema;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class Schema implements Arrayable, Jsonable
{

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

    /**
     * @param $data
     *
     * @param string $keyPrefix
     * @return Validator
     */
    public function validate(&$data, $keyPrefix = '')
    {
        return Validator::create($this, $data)
            ->validate($keyPrefix);
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