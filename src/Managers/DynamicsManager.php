<?php

namespace Shemi\Laradmin\Managers;

use Illuminate\Support\Arr;
use Illuminate\Support\Traits\Macroable;
use ReflectionFunction;
use ReflectionMethod;
use Shemi\Laradmin\Contracts\Managers\ManagerContract;

class DynamicsManager implements ManagerContract
{
    use Macroable;

    const REGEX = '^(\*)(?<function>\S+?)(:|$)(?<params>.+?)?(({(?<keys>.*)})|$)';

    /**
     * @param $dynamic
     * @param bool $keyLabel
     * @return array
     * @throws \Exception
     */
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
                $defaultLabel = $labelIndex === "data_index" ? $index : ($labelIndex === "data_value" ? $item : null);

                $label = data_get(
                    $item,
                    $labelIndex,
                    $defaultLabel
                );

                $key = data_get(
                    $item,
                    $keyIndex,
                    $key
                );
            }
            elseif($labelIndex === "data_index" && (! $keyIndex || $keyIndex === "data_value")) {
                $label = $index;
                $key = $item;
            }

            $return[] = compact('label', 'key');
        }

        return $return;
    }

    protected function config($key, $default = null)
    {
        return config($key, $default);
    }

    protected function trans($key, $replace = [], $locale = null)
    {
        return __($key, $replace, $locale);
    }

    protected function trans_choice($key, $number, array $replace = [], $locale = null)
    {
        return trans_choice($key, $number, $replace, $locale);
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

        $numberOfRequiredParameters = 0;

        if(method_exists($this, $function)) {
            $method = new ReflectionMethod($this, $function);
            $numberOfRequiredParameters = $method->getNumberOfRequiredParameters();
        }

        elseif (static::hasMacro($function)) {
            $function = new ReflectionFunction(static::$macros[$function]);
            $numberOfRequiredParameters = $function->getNumberOfRequiredParameters();
        }

        return $numberOfRequiredParameters <= count($params);
    }

    public function extract($dynamic)
    {
        $pattern = static::REGEX;
        $matches = [];

        preg_match_all("/{$pattern}/m", $dynamic, $matches, PREG_SET_ORDER, 0);

        $function = array_get($matches, '0.function');
        $params = array_get($matches, '0.params');
        $keys = array_get($matches, '0.keys');

        if($params) {
            $params = explode(',', $params);
        } else {
            $params = [];
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