<?php

namespace Shemi\Laradmin\Traits;

use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

trait LaradminUser
{

    public function hasPermission($name)
    {
        if(config('laradmin.roles.system') === 'simple') {

        }

        if (! $this->relationLoaded('role')) {
            $this->load('role');
        }

        if (! $this->role->relationLoaded('permissions')) {
            $this->role->load('permissions');
        }

        return in_array($name, $this->role->permissions->pluck('key')->toArray());
    }

    public function hasPermissionOrFail($name)
    {
        if (! $this->hasPermission($name)) {
            throw new UnauthorizedHttpException(null);
        }

        return true;
    }

    public function hasPermissionOrAbort($name, $statusCode = 403)
    {
        if (! $this->hasPermission($name)) {
            return abort($statusCode);
        }

        return true;
    }

}