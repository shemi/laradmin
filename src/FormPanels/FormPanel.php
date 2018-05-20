<?php

namespace Shemi\Laradmin\FormPanels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Shemi\Laradmin\Contracts\FormPanelContract;
use Shemi\Laradmin\FormPanels\Traits\HasJsonSchema;
use Shemi\Laradmin\FormPanels\Traits\HasJsonStructure;
use Shemi\Laradmin\Models\Type;
use Shemi\Laradmin\FormPanels\Traits\Buildable;
use Shemi\Laradmin\Traits\Renderable;
use Shemi\Laradmin\Models\Panel;

abstract class FormPanel implements FormPanelContract
{
    use Buildable, Renderable;

    protected $name;

    protected $codename;

    protected $defaultBuilderOptions = [
        [
            'label' => 'Title',
            'type' => 'b-input',
            'key' => 'title',
            'props' => [
                'type' => 'text',
                'placeholder' => 'Enter Panel Name',
            ],
            'validation' => ['required']
        ]
    ];

    /**
     * @param Panel $panel
     * @param Type $type
     * @param Model $model
     * @param $viewType
     * @param $data
     *
     * @return HtmlString
     * @throws \Throwable
     */
    public function handle(Panel $panel, Type $type, Model $model, $viewType, $data)
    {
        return $this->render(
            $this->createContent($panel, $type, $model, $viewType, $data)
        );
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

    public function getOptions()
    {
        return $this->defaultBuilderOptions;
    }

    public function getName()
    {
        if (empty($this->name)) {
            $this->name = ucwords(str_replace('_', ' ', $this->getCodename()));
        }

        return $this->name;
    }

}