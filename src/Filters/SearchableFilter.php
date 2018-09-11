<?php

namespace Shemi\Laradmin\Filters;

use Shemi\Laradmin\Filters\Contracts\DifferedFilterContract;

abstract class SearchableFilter extends Filter implements DifferedFilterContract
{

    protected $view = "searchable";

    public function transformValue($value)
    {
        if(! is_array($value) || empty($value)) {
            return [];
        }

        $newValue = [];

        foreach ($value as $option) {
            if(is_string($option)) {
                $newValue[] = parent::transformValue($option);
            }

            if(isset($option['key'])) {
                $newValue[] = parent::transformValue($option['key']);
            }
        }

        return $this->isMultiple() ? $newValue : $newValue[0];
    }

}