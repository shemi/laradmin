<?php

namespace Shemi\Laradmin\Data;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Shemi\Laradmin\Facades\Laradmin;

class DataManager
{
    const BASE_DIR = "laradmin";

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Model
     */
    protected $model;

    protected $location;

    /**
     * @var Collection
     */
    protected $files;

    /**
     * @var Schema
     */
    protected static $schema;

    public function __construct()
    {
        $this->filesystem = Laradmin::filesystem();
        $this->loadSchema();
    }

    /**
     * @param $location
     * @return static
     */
    public static function location($location)
    {
        $inst = new static();

        $inst->location = $location;

        return $inst;
    }

    public function setModel(Model $model)
    {
        $this->location = $model->getLocation();
        $this->model = $model;

        return $this;
    }

    public function path($name)
    {
        return
            static::BASE_DIR . DIRECTORY_SEPARATOR
            . $this->location . DIRECTORY_SEPARATOR
            . trim($name, DIRECTORY_SEPARATOR);
    }

    public function all()
    {
        return $this->dir();
    }

    public function firstOrCreate($attributes)
    {
        $files = $this->dir();

        foreach ($attributes as $key => $value) {
            $files = $files->where($key, $value);
        }

        if($files->isEmpty()) {
            return $this->create($attributes);
        }

        return $files->first();
    }

    public function findOrCreate($name)
    {
        if (! $data = $this->load($name)) {
            $data = $this->create($name);
        }

        return $data;
    }

    public function findOrFail($name)
    {
        if (! $data = $this->find($name)) {
            throw new DataNotFoundException($name);
        }

        return $data;
    }

    public function find($id)
    {
        if($this->files) {
            $file = $this->files
                ->where($this->model->getKeyName(), $id)
                ->first();

            if($file) {
                return $file;
            }
        }

        return $this->maybeNewModel($this->load($id));
    }

    protected function maybeNewModel(array $data)
    {
        if($this->model) {
            return $this->newModel($data);
        }

        return $data;
    }

    public function newModelInstance($attributes = [])
    {
        return $this->model->newInstance($attributes);
    }

    protected function newModel(array $data)
    {
        return $this->model->newFromManager($data);
    }

    public function getModel()
    {
        return $this->model;
    }

    public function load($name, $ext = 'json')
    {
        $path = $this->path($name.($ext ? '.'.$ext : ''));

        if (! $this->filesystem->exists($path)) {
            return false;
        }

        return json_decode($this->filesystem->get($path), true);
    }


    public function dir($path = null)
    {
        $path = $this->path($path);

        if(! $this->filesystem->exists($path)) {
            return $this->model->newCollection([]);
        }

        $names = $this->filesystem->allFiles($path);

        if(count($names) === 0) {
            return $this->model->newCollection([]);
        }

        $this->files = $this->model->newCollection([]);

        foreach ($names as $name) {
            $name = explode('.', basename($name));
            array_pop($name);
            $name = implode('.', $name);

            $file = $this->load($name);

            if($file) {
                $file = $this->maybeNewModel($file);
            }

            $this->files->add($file ?: []);
        }

        return $this->files;
    }

    public function create(array $attributes = [])
    {
        return tap($this->newModelInstance($attributes), function ($instance) {
            $instance->save();
        });
    }

    public function forceCreate(array $attributes)
    {
        return $this->model->unguarded(function () use ($attributes) {
            return $this->newModelInstance()->create($attributes);
        });
    }

    public function save(Model $model = null)
    {
        $model = $model ?: $this->model;

        if($model->getIncrementing() && ! $model->exists) {
            $model->setAttribute(
                $model->getKeyName(),
                $this->schema()->getAndIncrementNextId($this->location)
            );
        }

        $id = $model->{$model->getKeyName()};

        $json = $model->toJson(JSON_UNESCAPED_UNICODE);
        $path = $this->path($id . '.json');

        $this->filesystem->put($path, $json);

        return $id;
    }

    protected function loadSchema()
    {
        if(is_array(static::$schema)) {
            return static::$schema;
        }

        static::$schema = Schema::load($this->filesystem);

        return static::$schema;
    }

    /**
     * @return Schema
     */
    protected function schema()
    {
        if(! static::$schema) {
            $this->loadSchema();
        }

        return static::$schema;
    }

    public function __call($name, $arguments)
    {
        return $this->dir()->$name(...$arguments);
    }

    public static function __callStatic($name, $arguments)
    {
        return (new static)->$name(...$arguments);
    }

}