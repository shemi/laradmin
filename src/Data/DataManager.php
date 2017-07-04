<?php

namespace Shemi\Laradmin\Data;

use Illuminate\Contracts\Filesystem\Filesystem;

class DataManager
{
    protected $filesystem;

    protected $locations;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;

        $this->setLocations();
    }

    protected function setLocations()
    {
        $this->locations = [
            '' => 'laradmin',
            'defaults' => 'laradmin/defaults',
            'types' => 'laradmin/types',
            'options' => 'laradmin/options',
        ];
    }

    public function location($key)
    {
        return $this->locations[$key];
    }

    public function path($name, $location = '')
    {
        return $this->location($location).
            DIRECTORY_SEPARATOR.
            trim($name, DIRECTORY_SEPARATOR);
    }

    public function load($name, $location = '')
    {
        $path = $this->path($name, $location).'.json';

        if(! $this->filesystem->exists($path)) {
            throw new DataNotFoundException($name);
        }

        return $this->create(
            $name,
            $location,
            json_decode($this->filesystem->get($path), true)
        );
    }

    public function create($name, $location = '', $data = [])
    {
        return new Data($name, $location, $data);
    }

    public function save(Data $data)
    {
        $json = $data->toJson(JSON_UNESCAPED_UNICODE);
        $path = $this->path($data->getName(), $data->getLocation()).'.json';

        return $this->filesystem->put($path, $json);
    }

}