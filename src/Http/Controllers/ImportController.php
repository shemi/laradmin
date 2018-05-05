<?php

namespace Shemi\Laradmin\Http\Controllers;

use Illuminate\Http\Request;
use Shemi\Laradmin\Models\Type;

class ImportController extends Controller
{

    public function prepare(Request $request)
    {
        $type = $this->getTypeBySlug($request);

        if(! $this->userCanImport($type, $request)) {
            return $this->responseUnauthorized($request);
        }



    }

    /**
     * @param Type $type
     * @param Request $request
     * @return boolean
     */
    protected function userCanImport(Type $type, Request $request)
    {
        return $this->user()->can("import {$type->slug}");
    }

}