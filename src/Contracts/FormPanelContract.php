<?php

namespace Shemi\Laradmin\Contracts;

use Illuminate\Support\HtmlString;
use Illuminate\View\View;
use Shemi\Laradmin\Models\Panel;
use Shemi\Laradmin\Data\Model;

interface FormPanelContract
{

    /**
     * @param Panel $panel
     * @param Model $type
     * @param string $viewType
     * @param $data
     *
     * @return HtmlString
     */
    public function handle(Panel $panel, Model $type, $viewType, $data);

    /**
     * @param Panel $panel
     * @param Model $type
     * @param string $viewType
     * @param $data
     *
     * @return View
     */
    public function createContent(Panel $panel, Model $type, $viewType, $data);

    /**
     * @return string
     */
    public function getCodename();

    /**
     * @return string
     */
    public function getName();

}