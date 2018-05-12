<?php

namespace Shemi\Laradmin\Widgets;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Collection;
use Shemi\Laradmin\Exceptions\InvalidArgumentException;
use Shemi\Laradmin\Models\Type;

abstract class Widget implements Renderable
{

    const MAX_WIDGETS_WIDTH_SIZE_PER_ROW = 12;

    protected $codename;

    /**
     * @var string
     */
    protected $typeSlug;

    /**
     * @var Type
     */
    protected $type;

    /**
     * @var string
     */
    protected $view;

    public function __construct($typeSlug = null)
    {
        $this->typeSlug = $typeSlug;

        if($this->typeSlug) {
            $this->type = Type::whereSlug($this->typeSlug);

            if(! $this->type) {
                throw new InvalidArgumentException("The widget type slug {$this->typeSlug} not found.");
            }
        }
    }

    public static function start($typeSlug = null)
    {
        return (new static($typeSlug));
    }

    public function getCodename()
    {
        if (! empty($this->codename)) {
            return $this->codename;
        }

        $name = class_basename($this);

        if (ends_with($name, 'Widget')) {
            $name = substr($name, 0, -strlen('Widget'));
        }

        if($this->type) {
            $name .= '_' . $this->type->slug;
        }

        $this->codename = snake_case($name);

        return $this->codename;
    }

    /**
     * @return string
     * @throws \Throwable
     */
    public function render()
    {
        if(! view()->exists($this->view)) {
            return 'not';
        }

        return view($this->view, $this->getData())->render();
    }

    public function getCssClasses()
    {
        $classes = [];

        $classes[] = str_replace('_', '-', kebab_case($this->getCodename().'Widget'));

        switch ($this->getSize()) {
            case 10:
                $classes[] = 'is-four-fifths';
                break;

            case 9:
                $classes[] = 'is-three-quarters';
                break;

            case 8:
                $classes[] = 'is-two-thirds';
                break;

            case 7:
                $classes[] = 'is-three-fifths';
                break;

            case 6:
                $classes[] = 'is-half';
                break;

            case 5:
                $classes[] = 'is-two-fifths';
                break;

            case 4:
                $classes[] = 'is-one-third';
                break;

            case 3:
                $classes[] = 'is-one-quarter';
                break;

            case 2:
                $classes[] = 'is-one-fifth';
                break;
        }

        return implode(' ', $classes);
    }

    /**
     * @return Collection|integer
     */
    abstract public function query();

    /**
     * return the width of the widget from 1 to 12
     *
     * @return integer
     */
    abstract public function getSize();

    /**
     *
     * @return array
     */
    abstract public function getData();

}