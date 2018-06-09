<?php

namespace Shemi\Laradmin\FormPanels;

use Shemi\Laradmin\Data\Model;
use Illuminate\View\View;
use Shemi\Laradmin\Models\Panel;

class SimplePanel extends FormPanel
{

    /**
     * @param Panel $panel
     * @param Model $type
     * @param string $viewType
     * @param $data
     *
     * @return View
     */
    public function createContent(Panel $panel, Model $type, $viewType, $data)
    {
        return view('laradmin::formPanels.simple-panel', compact(
            'panel',
            'type',
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