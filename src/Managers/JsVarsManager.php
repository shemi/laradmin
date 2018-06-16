<?php

namespace Shemi\Laradmin\Managers;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\HtmlString;
use Shemi\Laradmin\Contracts\Managers\ManagerContract;
use Shemi\Laradmin\Models\Type;

class JsVarsManager implements ManagerContract
{
    protected $object = [];

    protected $namespace = 'window.laradmin';

    public function init()
    {
        $this->set([
            'api_base' => app('laradmin')->links()->route('laradmin.dashboard'),
            'public_path' => "/vendor/laradmin/assets",
            'routs' => [
                'icons' => app('laradmin')->links()->route('laradmin.icons')
            ],
            'mixins' => (array) []
        ]);
    }

    public function set($key, $value = null)
    {
        $array = $key;

        if(is_string($key)) {
            $array = [$key => $value];
        }

        foreach ($array as $key => $value) {
            array_set($this->object, $key, $value);
        }
    }

    public function render()
    {
        $json = json_encode($this->object, JSON_UNESCAPED_UNICODE);

        return new HtmlString("<script>{$this->namespace} = {$json}</script>");
    }

    public function getManagerName()
    {
        return 'jsVars';
    }
}