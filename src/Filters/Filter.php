<?php

namespace Shemi\Laradmin\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Shemi\Laradmin\Filters\Contracts\DifferedFilterContract;
use Shemi\Laradmin\Filters\Contracts\MultipleFilterContract;

abstract class Filter {

    /**
     * @var string $name
     */
    protected $name;

    /**
     * @var string $label
     */
    protected $label;

    protected $view = "default";

    /**
     * @param Request $request
     * @param Builder $query
     * @param $value
     * @return Builder|void
     */
    abstract public function apply(Request $request, Builder $query, $value);

    /**
     * @param Request $request
     * @return array
     */
    abstract public function options(Request $request);

    public function canFilter($user)
    {
        return true;
    }
    
    /**
     * @return string
     */
    public function getLabel()
    {
        if($this->label) {
            return $this->label;
        }

        $this->label = Str::title(Str::snake($this->getName(), ' '));

        return $this->label;
    }

    /**
     * @return string
     */
    public function getName()
    {
        if($this->name) {
            return $this->name;
        }

        $this->name = class_basename(static::class);

        return $this->name;
    }

    /**
     * @return bool
     */
    public function isDiffered()
    {
        return $this instanceof DifferedFilterContract;
    }

    /**
     * @return bool
     */
    public function isMultiple()
    {
        return $this instanceof MultipleFilterContract;
    }

    public function getOptions(Request $request = null)
    {
        $options = $this->options($request ?: app('request'));

        return is_array($options) ? $options : [];
    }

    public function transformValue($value)
    {
        if(! is_string($value)) {
            return $value;
        }

        if(is_numeric($value)) {
            if(str_contains($value, '.')) {
                return (float) $value;
            }

            return (integer) $value;
        }

        if(in_array($value, ['true', 'false'])) {
            return $value === 'true';
        }

        return $value;
    }

    public function render()
    {
        $view = view()->make("laradmin::filters.{$this->view}", [
            'key' => $this->getName(),
            'label' => $this->getLabel(),
            'filter' => $this
        ]);

        return new HtmlString($view->render());
    }

    public function toArray(Request $request = null)
    {
        return [
            'options' => ! $this->isDiffered() ? $this->getOptions($request) : [],
            'loaded' => ! $this->isDiffered(),
            'loading' => false
        ];
    }

}