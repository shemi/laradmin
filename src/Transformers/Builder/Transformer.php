<?php

namespace Shemi\Laradmin\Transformers\Builder;

use Illuminate\Support\Arr;

abstract class Transformer
{

    protected function cast($value, $type)
    {
        if (is_null($value) || is_null($type)) {
            return $value;
        }

        $types = explode('|', $type);
        $type = array_shift($types);
        $subType = empty($types) ? null : implode('|', $types);

        switch ($type) {

            case 'array':
                if ($subType && ! Arr::accessible($value)) {
                    return $this->cast($value, $subType);
                }

                return (array) $value;

            case 'object':
                if ($subType && ! Arr::accessible($value)) {
                    return $this->cast($value, $subType);
                }

                return (object) $value;

            case 'string':
                if ($subType && ! is_string($value)) {
                    return $this->cast($value, $subType);
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

    protected static function arrayMergeRecursiveDistinct(array &$array1, array &$array2)
    {
        $merged = $array1;

        foreach ($array2 as $key => &$value) {
            if (is_array($value) && isset ($merged [$key]) && is_array($merged [$key])) {
                $merged[$key] = static::arrayMergeRecursiveDistinct($merged [$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

}