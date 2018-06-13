<?php

namespace Shemi\Laradmin\Managers;

use Illuminate\Support\Arr;
use Illuminate\Support\Traits\Macroable;
use ReflectionMethod;
use Shemi\Laradmin\Contracts\Managers\ManagerContract;

class DynamicsManager implements ManagerContract
{
    use Macroable;

    const REGEX = '/^(?P<function>\S+?)(:|$)(?P<params>\S+?)?(({(?P<keys>.*)})|$)/m';

    public function call($dynamic, $keyLabel = false)
    {
        if(! $this->validate($dynamic)) {
            throw new \Exception("not valid");
        }

        $data = $this->extract($dynamic);
        $function = $data['function'];
        $params = $data['params'];
        $keys = $data['keys'];

        $value = $this->{$function}(...$params);

        if($keys && is_array($keys) && ! empty($keys)) {
            $keyLabel = true;
        }

        return $keyLabel ? $this->transformKeyLabel($value, $keys) : $value;
    }

    public function transformKeyLabel($value, $keys)
    {
        $labelIndex = array_get($keys, 0, "data_value");
        $keyIndex = array_get($keys, 1, "data_index");

        if(! $value || ! is_array($value)) {
            return $value;
        }

        $return = [];

        foreach ($value as $index => $item) {
            $label = $item;
            $key = $index;

            if((is_array($item) || is_object($item))) {
                $label = data_get(
                    $item,
                    $labelIndex,
                    $labelIndex === "data_value" ? $item : null
                );

                $key = data_get(
                    $item,
                    $keyIndex,
                    $key
                );
            }

            $return[] = compact('label', 'key');
        }

        return $return;
    }

    protected function config($key, $default = null)
    {
        return config($key, $default);
    }

    public function validate($dynamic)
    {
        $data = $this->extract($dynamic);

        if(! isset($data['function']) || empty($data['function'])) {
            return false;
        }

        $function = $data['function'];
        $params = $data['params'];

        if(! method_exists($this, $function) && ! static::hasMacro($function)) {
            return false;
        }

        $method = new ReflectionMethod($this, $function);

        return $method->getNumberOfRequiredParameters() <= count($params);
    }

    public function extract($dynamic)
    {
        $matches = [];

        preg_match_all(static::REGEX, $dynamic, $matches, PREG_SET_ORDER, 0);

        $function = array_get($matches, '0.function');
        $params = array_get($matches, '0.params');
        $keys = array_get($matches, '0.keys');

        if($params) {
            $params = explode(',', $params);
        }

        if($keys) {
            $keys = explode(':', $keys, 2);
        }

        return compact('function', 'params', 'keys');
    }

    public function getManagerName()
    {
        return 'dynamics';
    }
}