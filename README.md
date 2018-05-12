# laradmin

Just another Laravel admin package

- 

## Installation Steps

1. Require this package with composer.

```shell
composer require shemi/laradmin
```

2. Run the install artisan command
- It published the assets, migrations, data and config files
```shell
php artisan laradmin::install
```

3. Run migrate
```shell
php artisan migrate
```

4. Make roles and permissions, run the following command 
```shell
php artisan laradmin::roles
```

5. Grant "Super Admin" role to a user, run the following command 
```shell
php artisan laradmin::admin your@email.com
```

## Roles and permissions
Laradmin uses the grate [spatie/laravel-permission](https://github.com/spatie/laravel-permission) package for 
creating roles and permissions

## Widgets
You can register widgets that will be displayed on the dashboard

to do so you need to register widgets at you AppServiceProvider.php

```php
use Illuminate\Support\ServiceProvider;
use Laradmin;
use Shemi\Laradmin\Widgets;

class AppServiceProvider extends ServiceProvider
{
    public function boot(){...}

    public function register()
    {
        ...
        
        Laradmin::registerWidgetsRow([
            CounterWidget::start('users', 'Users', 'users', '#8c67ef'),
            CounterWidget::start('roles', 'Roles', 'hand-stop-o', '#ff3860'),
            CounterWidget::start('permissions', 'Permissions', 'check-square-o', '#23d160')
        ]);

        Laradmin::registerWidgetsRow([
            LatestWidget::start('users', null, ['id', 'updated_at', 'roles'])
        ]);
    }
}
```
