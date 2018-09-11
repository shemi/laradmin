<?php

namespace Shemi\Laradmin;

use Illuminate\Support\ServiceProvider;
use Shemi\Laradmin\Facades\Laradmin as LaradminFacade;
use Shemi\Laradmin\Filters\Filter;
use Shemi\Laradmin\Widgets\Widget;

abstract class LaradminApplicationServiceProvider extends ServiceProvider
{

    /**
     * Register the application service
     */
    public function register()
    {
        LaradminFacade::init();
    }

    public function boot()
    {
        LaradminFacade::jsVars()->init();
        $this->registerWidgets();
        $this->registerFilters();
    }

    /**
     * @return array
     */
    abstract protected function filters();

    /**
     * @return array
     */
    abstract protected function actions();

    /**
     * @return array
     */
    abstract protected function views();

    /**
     * @return array
     */
    abstract protected function widgets();

    /**
     * @return array
     */
    abstract protected function pages();

    protected function registerFilters()
    {
        $filters = $this->filters();

        foreach ($filters as $filter) {
            if(! $filter instanceof Filter) {
                $filter = app($filter);
            }

            LaradminFacade::filters()->register($filter);
        }
    }

    protected function registerWidgets()
    {
        $widgets = $this->widgets();

        foreach ($widgets as $widget) {
            if($widgets instanceof Widget) {
                LaradminFacade::widgets()->register($widget);
            } elseif (is_array($widget)) {
                LaradminFacade::widgets()->registerRow($widget);
            }
        }
    }

}