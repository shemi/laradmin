<?php

namespace Shemi\Laradmin\Data;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Eloquent\Concerns\GuardsAttributes;
use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Concerns\HidesAttributes;
use Illuminate\Database\Eloquent\JsonEncodingException;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use JsonSerializable;

class Model implements Arrayable, Jsonable, JsonSerializable
{
    use HasAttributes,
        HidesAttributes,
        GuardsAttributes,
        HasTimestamps;

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';

    protected $location;

    protected $fileExt;

    protected $dataable = true;

    public $exists = false;

    public $wasRecentlyCreated = false;

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    protected $isSaving = false;

    protected $jsonIgnore = [];

    public $incrementing = true;

    public function __construct(array $attributes = [])
    {
        $this->syncOriginal();

        $this->fill($attributes);
    }

    public function fill(array $attributes)
    {
        $totallyGuarded = $this->totallyGuarded();

        foreach ($this->fillableFromArray($attributes) as $key => $value) {
            if ($this->isFillable($key)) {
                $this->setAttribute($key, $value);
            } elseif ($totallyGuarded) {
                throw new MassAssignmentException($key);
            }
        }

        return $this;
    }

    public function forceFill(array $attributes)
    {
        return static::unguarded(function () use ($attributes) {
            return $this->fill($attributes);
        });
    }

    public function newInstance($attributes = [], $exists = false)
    {
        $model = new static((array) $attributes);

        $model->exists = $exists;

        return $model;
    }

    public function newFromManager($attributes = [])
    {
        $model = $this->newInstance([], true);

        $model->setRawAttributes((array) $attributes, true);

        return $model;
    }

    public function getIncrementing()
    {
        return $this->incrementing;
    }

    /**
     * Get the primary key for the model.
     *
     * @return string
     */
    public function getKeyName()
    {
        return $this->primaryKey;
    }

    /**
     * Get the primary key value for the model.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->{$this->primaryKey};
    }

    /**
     * Set the primary key for the model.
     *
     * @param  string  $key
     * @return $this
     */
    public function setKeyName($key)
    {
        $this->primaryKey = $key;

        return $this;
    }

    /**
     * Get a relationship.
     *
     * @param  string  $key
     * @return void
     */
    public function getRelationValue($key)
    {
        // Right now there is no need for relationships...
    }

    public function getLocation()
    {
        if (! isset($this->location)) {
            return str_replace('\\', '', Str::snake(Str::plural(class_basename($this))));
        }

        return $this->location;
    }

    public function getFileExt()
    {
        return $this->fileExt ?: 'json';
    }

    /**
     * Save the model to the database.
     *
     * @param  array  $options
     * @return bool
     */
    public function save()
    {
        $this->isSaving = true;

        if ($this->exists) {
            $saved = $this->isDirty() ?
                $this->performUpdate() : true;
        } else {
            $saved = $this->performInsert();
        }

        $this->isSaving = false;

        return $saved;
    }

    protected function performUpdate()
    {
        if ($this->usesTimestamps()) {
            $this->updateTimestamps();
        }

        $dirty = $this->getDirty();

        if (count($dirty) > 0) {
            $this->newManager()->save();
        }

        return true;
    }

    protected function performInsert()
    {
        if ($this->usesTimestamps()) {
            $this->updateTimestamps();
        }

        $this->newManager()->save();

        $this->exists = true;

        $this->wasRecentlyCreated = true;

        return true;
    }

    public function getKeyType()
    {
        return $this->keyType;
    }


    /**
     * Delete the model from the database.
     *
     * @return bool|null
     *
     * @throws \Exception
     */
    public function delete()
    {
        if (is_null($this->getKeyName())) {
            throw new \Exception('No primary key defined on model.');
        }

        if (! $this->exists) {
            return null;
        }

        return $this->newManager()->delete($this->getKey());
    }

    /**
     * Reload a fresh model instance from the file.
     *
     * @return static|null
     */
    public function fresh()
    {
        if (! $this->exists) {
            return null;
        }

        return $this->newManager()->findOrFail($this->getKey());
    }

    /**
     * Reload the current model instance with fresh attributes from the file.
     *
     * @return $this
     */
    public function refresh()
    {
        if (! $this->exists) {
            return $this;
        }

        $this->setRawAttributes(
            $this->newManager()->load($this->getKey())
        );

        return $this;
    }

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->attributesToArray();
    }

    /**
     * Convert the model instance to JSON.
     *
     * @param  int  $options
     * @return string
     *
     * @throws \Illuminate\Database\Eloquent\JsonEncodingException
     */
    public function toJson($options = 0)
    {
        $json = json_encode($this->jsonSerialize(), $options);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw JsonEncodingException::forModel($this, json_last_error_msg());
        }

        return $json;
    }

    protected function castBuilderAttribute($value, $type)
    {
        if(is_null($value) || is_null($type)) {
            return $value;
        }

        $types = explode('|', $type);
        $type = array_shift($types);
        $subType = empty($types) ? null : implode('|', $types);

        switch ($type) {

            case 'array':
                if($subType && ! Arr::accessible($value)) {
                    return $this->castBuilderAttribute($value, $subType);
                }

                return (array) $value;

            case 'object':
                if($subType && ! Arr::accessible($value)) {
                    return $this->castBuilderAttribute($value, $subType);
                }

                return (object) $value;

            case 'string':
                if($subType && ! is_string($value)) {
                    return $this->castBuilderAttribute($value, $subType);
                }

                return (string) $value;

            case 'bool':
            case 'boolean':
                return (boolean) $value;

            case 'int':
                return (int) $value;

            case 'float':
                return (float) $value;

        }

        return $value;
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $array = $this->toArray();

        if(! empty($this->jsonIgnore)) {
            foreach ($this->jsonIgnore as $attribute) {
                if(isset($array[$attribute])) {
                    unset($array[$attribute]);
                }
            }
        }

        return $array;
    }

    /**
     * Get the format for database stored dates.
     *
     * @return string
     */
    protected function getDateFormat()
    {
        return $this->dateFormat ?: "Y-m-d H:i:s.u";
    }

    private function newManager()
    {
        $manager = new DataManager();

        return $manager->setModel($this);
    }

    public function newCollection($items = [])
    {
        return new Collection($items);
    }

    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    public function __isset($key)
    {
        return ! is_null($this->getAttribute($key));
    }

    public function __unset($key)
    {
        unset($this->attributes[$key], $this->relations[$key]);
    }

    public function __call($method, $parameters)
    {
        if($this->dataable) {
            return $this->newManager()->$method(...$parameters);
        }
    }

    public static function __callStatic($method, $parameters)
    {
        return (new static)->$method(...$parameters);
    }

}