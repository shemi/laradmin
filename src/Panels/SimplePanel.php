<?php

namespace Shemi\Laradmin\Panels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\View\View;
use Shemi\Laradmin\Models\Panel as PanelModel;
use Shemi\Laradmin\Models\Type;

class SimplePanel extends Panel
{

    /**
     * @param PanelModel $panel
     * @param Type $type
     * @param Model $model
     * @param $data
     *
     * @return View
     */
    public function createContent(PanelModel $panel, Type $type, Model $model, $data)
    {
        return view('laradmin::formFields.date', compact(
            'panel',
            'type',
            'model',
            'data'
        ));
    }



}