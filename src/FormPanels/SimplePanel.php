<?php

namespace Shemi\Laradmin\FormPanels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\View\View;
use Shemi\Laradmin\Models\Panel;
use Shemi\Laradmin\Models\Type;

class SimplePanel extends FormPanel
{

    /**
     * @param Panel $panel
     * @param Type $type
     * @param Model $model
     * @param string $viewType
     * @param $data
     *
     * @return View
     */
    public function createContent(Panel $panel, Type $type, Model $model, $viewType, $data)
    {
        return view('laradmin::formPanels.simple-panel', compact(
            'panel',
            'type',
            'model',
            'data',
            'viewType'
        ));
    }


    public function getOptions()
    {
        return array_merge(parent::getOptions(), [
            [
                'label' => null,
                'slot' => '<span>Has Container</span>',
                'slot_el' => 'span',
                'key' => 'has_container',
                'type' => 'b-switch',
                'validation' => []
            ],
            [
                'label' => 'Fields',
                'key' => 'fields',
                'type' => 'la-fields-list',
                'validation' => []
            ]
        ]);
    }

}