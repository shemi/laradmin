<?php

namespace Shemi\Laradmin\Transformers\Builder;

use Shemi\Laradmin\Models\Contracts\Buildable;
use Shemi\Laradmin\Models\Type;

class TypeTransformer extends Transformer
{
    /**
     * @var static $inst
     */
    protected static $inst;

    protected $map = [
        'name',
        'soft_deletes',
        'model',
        'slug',
        'public',
        'controller',
        'updated_at',
        'created_at',
        'side_panels',
        'icon',
        'exists',
        'records_per_page',
        'support_export',
        'export_controller',
        'support_import',
        'import_controller',
        'default_sort',
        'default_sort_direction'
    ];

    protected $extractMap = [
        'filters',
        'actions'
    ];

    public static function transform(Type $type)
    {
        if(! static::$inst) {
            static::$inst = new static;
        }

        return static::$inst->handle($type);
    }

    public function handle(Type $type)
    {
        $return = [];

        foreach ($this->map as $key) {
            $return[$key] = $type->{$key};
        }

        foreach ($this->extractMap as $key) {
            $return[$key] = [];

            foreach ($type->{$key}() as $object) {
                $return[$key][] = [
                    'label' => $object->getLabel(),
                    'key' => $object->getName()
                ];
            }
        }

        $return['panels'] = (array) [];

        if($type->exists) {
            $return['panels'] = $type->panels->map(function(Buildable $panel) {
                return $panel->toBuilder();
            });
        }

        return $return;
    }

}
