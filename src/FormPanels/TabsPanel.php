<?php

namespace Shemi\Laradmin\FormPanels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\View\View;
use Shemi\Laradmin\Models\Panel;
use Shemi\Laradmin\Models\Type;

class TabsPanel extends FormPanel
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
        return view('laradmin::panels.simple-panel', compact(
            'panel',
            'type',
            'model',
            'data',
            'viewType'
        ));
    }

    public function structure()
    {
        $structure = parent::structure();

        $structure['tabs'] = (array) [
            ['id' => str_random(5), 'title' => 'First Tab', 'icon' => 'certificate']
        ];

        return $structure;
    }

    public function getOptions()
    {
        return array_merge(parent::getOptions(), [
            [
                'label' => 'Tabs',
                'key' => 'tabs',
                'type' => 'la-tabs-builder',
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