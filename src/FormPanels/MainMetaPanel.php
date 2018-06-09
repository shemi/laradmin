<?php

namespace Shemi\Laradmin\FormPanels;

use Illuminate\View\View;
use Shemi\Laradmin\Models\Panel;
use Shemi\Laradmin\Data\Model;

class MainMetaPanel extends FormPanel
{

    protected $isProtected = true;

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
        return view('laradmin::formPanels.main-meta-panel', compact(
            'panel',
            'type',
            'data',
            'viewType'
        ));
    }

    public function structure()
    {
        $structure = parent::structure();

        $structure['is_main_meta'] = true;
        $structure['position'] = 'side';
        $structure['title'] = trans('laradmin::crud.publish');

        return $structure;
    }

    public function getOptions()
    {
        $options = parent::getOptions();

        $options[] = [
            'label' => 'Fields',
            'key' => 'fields',
            'type' => 'la-fields-list',
            'validation' => []
        ];

        return $options;
    }

}