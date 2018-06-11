<?php

namespace Shemi\Laradmin\Transformers\Builder;


use Shemi\Laradmin\Models\SettingsPage;

class SettingsPageTransformer extends Transformer
{
    /**
     * @var static $inst
     */
    protected static $inst;

    protected $map = [
        'name',
        'slug',
        'updated_at',
        'created_at',
        'icon',
        'exists',
        'bucket'
    ];

    public static function transform(SettingsPage $page)
    {
        if(! static::$inst) {
            static::$inst = new static;
        }

        return static::$inst->handle($page);
    }

    public function handle(SettingsPage $page)
    {
        $return = [];

        foreach ($this->map as $key) {
            $return[$key] = $page->{$key};
        }

        $return['panels'] = (array) [];

        if($page->exists) {
            $return['panels'] = $page->panels->map(function($panel) {
                return PanelTransformer::transform($panel);
            });
        }

        return $return;
    }

}