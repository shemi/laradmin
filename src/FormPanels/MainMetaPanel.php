<?php

namespace Shemi\Laradmin\FormPanels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\View\View;
use Shemi\Laradmin\Models\Panel;
use Shemi\Laradmin\Models\Type;

class MainMetaPanel extends FormPanel
{

    protected $isProtected = true;

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
        return view('laradmin::panels.main-meta-panel', compact(
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