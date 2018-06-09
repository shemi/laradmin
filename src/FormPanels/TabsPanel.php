<?php

namespace Shemi\Laradmin\FormPanels;

use Shemi\Laradmin\Data\Model;
use Illuminate\View\View;
use Shemi\Laradmin\Models\Panel;

class TabsPanel extends FormPanel
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
        return view('laradmin::formPanels.tabs-panel', compact(
            'panel',
            'type',
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