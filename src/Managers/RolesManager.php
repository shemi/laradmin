<?php

namespace Shemi\Laradmin\Managers;

use Shemi\Laradmin\Contracts\Managers\ManagerContract;
use Shemi\Laradmin\Models\Type;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesManager implements ManagerContract
{

    const DEFAULT_PERMISSIONS = [
        'access backend',
        'browse options',
        'update options',
        'browse menus',
        'update menus',
        'create menus',
        'delete menus',
        'browse settings',
        'update settings',
        'create settings',
        'delete settings',
        'browse types',
        'update types',
        'create types',
        'delete types'
    ];

    const PERMISSIONS_PREFIXES = [
        'import',
        'export',
        'create',
        'delete',
        'update',
        'browse',
        'view'
    ];

    const DEFAULT_ROLE = 'admin';

    protected $defaultGuard;

    protected $role;

    public function __construct()
    {
        $this->defaultGuard = config('laradmin.guard');
    }

    public function clearCache()
    {
        app()['cache']->forget('spatie.permission.cache');
    }

    /**
     * @return Role
     */
    public function role()
    {
        return $this->role ?: $this->loadOrCreateRole();
    }

    /**
     * @return Role
     */
    public function loadOrCreateRole()
    {
        $this->role = Role::firstOrNew([
            'name' => static::DEFAULT_ROLE,
            'guard_name' => $this->defaultGuard
        ]);

        if(! $this->role->exists) {
            $this->role->save();
        }

        return $this->role;
    }

    public function createDefaultPermissionsAndAssign()
    {
        foreach (static::DEFAULT_PERMISSIONS as $name) {
            $this->createPermissionAndAssign($name);
        }
    }

    public function createPermissionsForAllTypes()
    {
        $types = Type::all();

        foreach ($types as $type) {
            $this->createTypePermissions($type);
        }

        return $this;
    }

    public function createTypePermissions(Type $type)
    {
        $slug = $type->slug;

        foreach (static::PERMISSIONS_PREFIXES as $prefix) {
            $this->createPermissionAndAssign("{$prefix} {$slug}");
        }
    }

    public function createPermissionAndAssign($name)
    {
        $permission = $this->createPermission($name);
        $this->assign($permission);

        return $permission;
    }

    /**
     * @param string $name
     *
     * @return Permission
     */
    public function createPermission($name)
    {
        $permission = Permission::firstOrNew([
            'name' => $name,
            'guard_name' => $this->defaultGuard
        ]);

        if(! $permission->exists) {
            $permission->save();
        }

        return $permission;
    }

    /**
     * @param Permission $permission
     */
    public function assign(Permission $permission)
    {
        if(! $this->role()->hasPermissionTo($permission)) {
            $this->role()->givePermissionTo($permission);
        }
    }

    public function getDefaultGuard()
    {
        return $this->defaultGuard;
    }

    public function getDefaultRole()
    {
        return static::DEFAULT_ROLE;
    }

    public function getManagerName()
    {
        return 'roles';
    }
}