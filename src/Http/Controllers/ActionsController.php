<?php

namespace Shemi\Laradmin\Http\Controllers;

use Illuminate\Http\Request;
use Shemi\Laradmin\Actions\Action;
use Shemi\Laradmin\Exceptions\DataNotFoundException;
use Shemi\Laradmin\Repositories\QueryRepository;

class ActionsController extends Controller
{

    public function apply($typeSlug, $actionName, Request $request)
    {
        $type = $this->getTypeBySlug($typeSlug);

        $this->validate($request, [
            QueryRepository::ALL_MATCHING_KEY => 'required|boolean',
            QueryRepository::WHERE_PRIMARY_KEYS_KEY => 'required|array'
        ]);

        /** @var Action $action */
        $action = $type->actions()->first(function(Action $action) use ($actionName) {
            return $action->getName() === $actionName;
        });

        if(! $action) {
            throw new DataNotFoundException($typeSlug);
        }

        $models = QueryRepository::asCollection($request, $type);

        return $action->apply($models, $type, $request);
    }

    public function download(Request $request)
    {
        $data = $this->validate($request, [
            'path'     => 'required',
            'filename' => 'required',
        ]);

        return response()->download(
            $data['path'],
            $data['filename']
        )->deleteFileAfterSend(true);
    }

}