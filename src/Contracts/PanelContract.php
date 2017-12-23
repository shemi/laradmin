<?php

namespace Shemi\Laradmin\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Illuminate\View\View;
use Shemi\Laradmin\Models\Panel;
use Shemi\Laradmin\Models\Type;

interface PanelContract
{

    /**
     * @param Panel $panel
     * @param Type $type
     * @param Model $model
     * @param $data
     *
     * @return HtmlString
     */
    public function handle(Panel $panel, Type $type, Model $model, $data);

    /**
     * @param Panel $panel
     * @param Type $type
     * @param Model $model
     * @param $data
     *
     * @return View
     */
    public function createContent(Panel $panel, Type $type, Model $model, $data);

    /**
     * @return string
     */
    public function getCodename();

    /**
     * @return string
     */
    public function getName();

}