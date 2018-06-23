<?php

namespace Shemi\Laradmin\Transformers\Builder;

use Shemi\Laradmin\Models\Contracts\Buildable;
use Shemi\Laradmin\Models\Panel;

class PanelTransformer extends Transformer
{
    /**
     * @var static $inst
     */
    protected static $inst;

    protected $map = [
        'id',
        'title',
        'type',
        'position',
        'has_container',
        'style',
        'tabs'
    ];

    public static function transform(Panel $panel)
    {
        if(! static::$inst) {
            static::$inst = new static;
        }

        return static::$inst->handle($panel);
    }

    public function handle(Panel $panel)
    {
        $return = [
            'object_type' => Panel::OBJECT_TYPE
        ];

        foreach ($this->map as $key) {
            $return[$key] = $panel->{$key};
        }

        $return['fields'] = (array) [];

        if ($panel->fields->isNotEmpty()) {
            $return['fields'] = $panel->fields->map(function (Buildable $field) {
                return $field->toBuilder();
            });
        }

        return $return;
    }

}