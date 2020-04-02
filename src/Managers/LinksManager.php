<?php

namespace Shemi\Laradmin\Managers;

use Illuminate\Database\Eloquent\Model;

use Shemi\Laradmin\Contracts\Managers\ManagerContract;
use Shemi\Laradmin\Models\Type;

class LinksManager implements ManagerContract
{
    public function restore(Type $type, $model = null)
    {
        return $this->typeLink($type, 'restore', $model);
    }

    public function edit(Type $type, $model = null)
    {
        return $this->typeLink($type, 'edit', $model);
    }

    public function destroy(Type $type, $model = null)
    {
        return $this->typeLink($type, 'destroy', $model);
    }

    public function destroyMany(Type $type)
    {
        return $this->typeLink($type, 'destroyMany');
    }

    public function restoreMany(Type $type)
    {
        return $this->typeLink($type, 'restoreMany');
    }

    public function create(Type $type)
    {
        return $this->typeLink($type, 'create');
    }

    public function isCreate()
    {
        return ends_with(request()->route()->getName(), 'create');
    }

    public function typeLink(Type $type, $action, $modelOrKey = null)
    {
        if($modelOrKey === null) {
            return $this->route("laradmin.{$type->slug}.{$action}");
        }

        $link = $this->route("laradmin.{$type->slug}.{$action}", ["{$type->slug}" => "__primaryKey__"]);

        if($modelOrKey && $modelOrKey instanceof Model) {
            return str_replace('__primaryKey__', $modelOrKey->getKey(), $link);
        }

        if($modelOrKey && is_int($modelOrKey)) {
            return str_replace('__primaryKey__', $modelOrKey, $link);
        }

        return str_replace('__primaryKey__', "'+ props.row.{$modelOrKey} +'", $link);
    }

    public function serveMedia($mediaId, $fileName, $pc = null)
    {
        return $this->route('laradmin.serve', compact('mediaId', 'fileName', 'pc'));
    }

    public function route($name, $parameters = [])
    {
        return route($name, $parameters);
    }

    public function getManagerName()
    {
        return 'links';
    }
}
