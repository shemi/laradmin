<?php

namespace Shemi\Laradmin\Managers;

use Illuminate\Database\Eloquent\Model;

use Shemi\Laradmin\Contracts\Managers\ManagerContract;
use Shemi\Laradmin\Models\Type;

class LinksManager implements ManagerContract
{

    public function editLink(Type $type, $model = null)
    {
        return $this->typeLink($type, 'edit', $model);
    }

    public function destroyLink(Type $type, $model = null)
    {
        return $this->typeLink($type, 'destroy', $model);
    }

    public function destroyManyLink(Type $type)
    {
        return $this->typeLink($type, 'destroyMany');
    }

    public function typeLink(Type $type, $action, $modelOrKey = null)
    {
        if($modelOrKey === null) {
            return route("laradmin.{$type->slug}.{$action}");
        }

        $link = route("laradmin.{$type->slug}.{$action}", ["{$type->slug}" => "__primaryKey__"]);

        if($modelOrKey && $modelOrKey instanceof Model) {
            return str_replace('__primaryKey__', $modelOrKey->getKey(), $link);
        }

        if($modelOrKey && is_int($modelOrKey)) {
            return str_replace('__primaryKey__', $modelOrKey, $link);
        }

        return str_replace('__primaryKey__', "'+ props.row.{$modelOrKey} +'", $link);
    }

    public function getManagerName()
    {
        return 'links';
    }
}