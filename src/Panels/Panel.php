<?php

namespace Shemi\Laradmin\Panels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Shemi\Laradmin\Contracts\PanelContract;
use Shemi\Laradmin\Models\Type;
use Shemi\Laradmin\Panels\Traits\Buildable;
use Shemi\Laradmin\Traits\Renderable;
use Shemi\Laradmin\Models\Panel as PanelModel;

abstract class Panel implements PanelContract
{
    use Buildable, Renderable;

    protected $name;

    protected $codename;

    /**
     * @param PanelModel $panel
     * @param Type $type
     * @param Model $model
     * @param $data
     *
     * @return HtmlString
     */
    public function handle(PanelModel $panel, Type $type, Model $model, $data)
    {
        $content = $this->createContent($panel, $type, $model, $data);

        return $this->render($content);
    }

    public function getCodename()
    {
        if (empty($this->codename)) {
            $name = class_basename($this);

            if (ends_with($name, 'Panel')) {
                $name = substr($name, 0, -strlen('Panel'));
            }

            $this->codename = snake_case($name);
        }

        return $this->codename;
    }

    public function getName()
    {
        if (empty($this->name)) {
            $this->name = ucwords(str_replace('_', ' ', $this->getCodename()));
        }

        return $this->name;
    }

}