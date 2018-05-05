<?php

namespace Shemi\Laradmin\Http\Controllers;

use Illuminate\Http\Request;
use Shemi\Laradmin\Models\Type;

class ExportController extends Controller
{

    public function prepare(Request $request)
    {
        $type = $this->getTypeBySlug($request);

        if(! $this->userCanExport($type, $request)) {
            return $this->responseUnauthorized($request);
        }



    }

    /**
     * @param Type $type
     * @param Request $request
     * @return boolean
     */
    protected function userCanExport(Type $type, Request $request)
    {
        return $this->user()->can("export {$type->slug}");
    }

}