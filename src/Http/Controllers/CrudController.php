<?php

namespace Shemi\Laradmin\Http\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Shemi\Laradmin\Actions\Action;
use Shemi\Laradmin\Contracts\Repositories\CreateUpdateRepository;
use Shemi\Laradmin\Contracts\Repositories\TransformTypeModelDataRepository;
use Shemi\Laradmin\Contracts\Repositories\TypeModelQueryRepository;
use Shemi\Laradmin\Contracts\Repositories\TypeRequestValidatorRepository;
use Laradmin;
use Shemi\Laradmin\Exceptions\CreateUpdateException;
use Shemi\Laradmin\Filters\Filter;
use Shemi\Laradmin\Models\Type;
use Shemi\Laradmin\Repositories\QueryRepository;

class CrudController extends Controller
{


    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $type = $this->getTypeBySlug($request);

        if(! $this->userCanBrowse($type, $request)) {
            return $this->responseUnauthorized($request);
        }

        /** @var Model $model */
        $model = app($type->model);
        $columns = $type->browse_columns;
        $primaryKey = $model->getKeyName();

        $linksManager = app('laradmin')->links();
        $restoreRoute = $linksManager->restore($type, $primaryKey);
        $editRoute = $linksManager->edit($type, $primaryKey);
        $deleteRoute = $linksManager->destroy($type, $primaryKey);
        $deleteManyRoute = $linksManager->destroyMany($type);
        $restoreManyRoute = $linksManager->restoreMany($type);

        $controlsData = [
            'filters' => [],
            'actions' => [],
//            'softDeletes' => $model->
        ];

        $filters = $type->filters();

        /** @var Filter $filter */
        foreach ($filters as $filter) {
            if(! $filter->canFilter($this->user())) {
                continue;
            }

            $controlsData['filters'][$filter->getName()] = $filter->toArray($request);
        }

        $actions = $type->actions();

        /** @var Action $action */
        foreach ($actions as $action) {
            if(! $action->canRun($this->user())) {
                continue;
            }

            $controlsData['actions'][] = $action->toArray($request);
        }

        app('laradmin')->jsVars()
            ->set('controls', $controlsData);

        return view(
            'laradmin::crud.browse',
            compact(
                'type',
                'model',
                'columns',
                'primaryKey',
                'editRoute',
                'deleteRoute',
                'restoreRoute',
                'deleteManyRoute',
                'restoreManyRoute'
            )
        );
    }

    /**
     * @param Type $type
     * @param Request $request
     * @return boolean
     */
    protected function userCanBrowse(Type $type, Request $request)
    {
        return $this->user()->can("browse {$type->slug}");
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function query(Request $request)
    {
        $type = $this->getTypeBySlug($request);

        if(! $this->userCanBrowse($type, $request)) {
            return $this->responseUnauthorized($request);
        }

        $results = QueryRepository::query($request, $type);

        return $this->response($results->toArray());
    }

    public function createEditResponse($id = null, Request $request)
    {
        $user = $this->user();
        $type = $this->getTypeBySlug($request);
        $action = $id === null ? 'create' : 'edit';

        /** @var Model $model */
        $model = app($type->model);

        if($id) {
            $model = app(TypeModelQueryRepository::class)
                ->find($id, $type);
        }

        $form = app(TransformTypeModelDataRepository::class)
            ->transform($type, $model);

        $data = $type->getRelationData($model);
        $getDataMethod = camel_case("get_{$action}_data");

        if(method_exists($this, $getDataMethod)) {
            $userData = call_user_func_array([$this, $getDataMethod], [$type, $model]);

            if(is_array($data)) {
                $data = array_merge($data, $userData);
            }
        }

        $view = 'laradmin::crud.createEdit';

        if(view()->exists("laradmin::{$type->slug}.createEdit")) {
            $view = "laradmin::{$type->slug}.createEdit";
        }

        $saveRouteKey = $action === 'create' ?
            "laradmin.{$type->slug}.store" :
            "laradmin.{$type->slug}.update";

        $saveRouteParameters = $action === 'create' ? [] : ['id' => $model->getKey()];

        $fields = $model->exists ? $type->edit_fields : $type->create_fields;

        $jsObject = [
            'model' => $form,
            'routs.save' => route($saveRouteKey, $saveRouteParameters),
            'routs.upload' => route("laradmin.upload", ['type' => $type->slug]),
            'type' => [
                'action' => $action,
                'name' => $type->name,
                'singular_name' => str_singular($type->name),
                'slug' => $type->slug,
                'id' => $type->id,
                'types' => Type::getAllFieldTypes($fields),
                'modelPrimaryKey' => $model->getKeyName()
            ]
        ];

        if($action === 'edit') {
            $jsObject['routs.delete'] = route("laradmin.{$type->slug}.destroy", $saveRouteParameters);
        }

        app('laradmin')->jsVars()->set($jsObject);

        view()->share(compact('model'));

        return view($view, compact('type', 'model', 'form', 'data', 'user'));
    }

    /**
     * @param Type $type
     * @param Request $request
     * @return boolean
     */
    protected function userCanCreate(Type $type, Request $request)
    {
        return $this->user()->can("create {$type->slug}");
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $type = $this->getTypeBySlug($request);

        if(! $this->userCanCreate($type, $request)) {
            return $this->responseUnauthorized($request);
        }

        return $this->createEditResponse(null, $request);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $type = $this->getTypeBySlug($request);
        $model = app($type->model);

        if(! $this->userCanCreate($type, $request)) {
            return $this->responseUnauthorized($request);
        }

        $fields = $type->create_fields;

        app(TypeRequestValidatorRepository::class)
            ->validate($request, $type, $model, $fields);

        /** @var CreateUpdateRepository $createRepository */
        try {
            $createRepository = app(CreateUpdateRepository::class)
                ->createOrUpdate(
                    $request->only($fields->pluck('key')->toArray()),
                    $model,
                    $type
                );
        } catch (CreateUpdateException $exception) {
            return $this->responseInternalError($exception->getMessage());
        }

        $redirect = route("laradmin.{$type->slug}.edit", [
            'id' => $model->getKey()
        ]);

        $model = app(TypeModelQueryRepository::class)
            ->find($model->getKey(), $type);

        $model = app(TransformTypeModelDataRepository::class)
            ->transform($type, $model);

        return $this->response(compact('model', 'redirect'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * @param Type $type
     * @param Request $request
     * @return boolean
     */
    protected function userCanUpdate(Type $type, Request $request)
    {
        return $this->user()->can("update {$type->slug}");
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function edit($id, Request $request)
    {
        $type = $this->getTypeBySlug($request);

        if(! $this->userCanUpdate($type, $request)) {
            return $this->responseUnauthorized($request);
        }

        return $this->createEditResponse($id, $request);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $type = $this->getTypeBySlug($request);

        if(! $this->userCanUpdate($type, $request)) {
            return $this->responseUnauthorized($request);
        }

        $fields = $type->edit_fields;

        /** @var Model $model */
        $model = app(TypeModelQueryRepository::class)
            ->find($id, $type, $fields);

        app(TypeRequestValidatorRepository::class)
            ->validate($request, $type, $model, $fields);


        /** @var CreateUpdateRepository $createRepository */
        try {
            $createRepository = app(CreateUpdateRepository::class)
                ->createOrUpdate(
                    $request->only($fields->pluck('key')->toArray()),
                    $model,
                    $type
                );
        } catch (CreateUpdateException $exception) {
            return $this->responseInternalError($exception->getMessage());
        }

        $model = app(TypeModelQueryRepository::class)
            ->find($model->getKey(), $type);

        $model = app(TransformTypeModelDataRepository::class)
            ->transform($type, $model);

        $redirect = false;

        return $this->response(
            compact('model', 'redirect')
        );
    }

    /**
     * @param Type $type
     * @param Request $request
     * @return boolean
     */
    protected function userCanDelete(Type $type, Request $request)
    {
        return $this->user()->can("delete {$type->slug}");
    }

    /**
     * @param Type $type
     * @param Request $request
     * @return boolean
     */
    protected function userCanRestore(Type $type, Request $request)
    {
        return $this->user()->can("restore {$type->slug}");
    }

    public function destroyMany(Request $request)
    {
        $type = $this->getTypeBySlug($request);

        $this->validate($request, [
            QueryRepository::ALL_MATCHING_KEY => 'required|boolean',
            QueryRepository::WHERE_PRIMARY_KEYS_KEY => 'required|array',
            QueryRepository::IS_TRASH => 'nullable',
        ]);

        if(! $this->userCanDelete($type, $request)) {
            return $this->responseUnauthorized($request);
        }

        /** @var Collection $models */
        $models = QueryRepository::asCollection($request, $type);

        try {
            if ($type->soft_deletes && $request->input(QueryRepository::IS_TRASH)) {
                $models->each(function ($model) {
                    if ($model->trashed()) {
                        $model->forceDelete();
                    }
                });
            } else {
                $models->each->delete();
            }
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage());
        }

        return $this->response([
            'redirect' => null,
            'deleted' => $models->count()
        ]);
    }

    public function restoreMany(Request $request)
    {
        $type = $this->getTypeBySlug($request);

        $this->validate($request, [
            QueryRepository::ALL_MATCHING_KEY => 'required|boolean',
            QueryRepository::WHERE_PRIMARY_KEYS_KEY => 'required|array',
            QueryRepository::IS_TRASH => 'nullable',
        ]);

        if(! $this->userCanRestore($type, $request)) {
            return $this->responseUnauthorized($request);
        }

        /** @var Collection $models */
        $models = QueryRepository::asCollection($request, $type);

        try {
            $models->each(function ($model) {
                if ($model->trashed()) {
                    $model->restore();
                }
            });
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage());
        }

        return $this->response([
            'redirect' => null,
            'restored' => $models->count()
        ]);
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param  int $id
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function restore($id, Request $request)
    {
        $type = $this->getTypeBySlug($request);

        if(! $this->userCanRestore($type, $request)) {
            return $this->responseUnauthorized($request);
        }

        if (! $type->soft_deletes) {
            return $this->responseUnauthorized($request);
        }

        $model = app($type->model)->withTrashed()->findOrFail($id);

        $model->restore();

        return $this->response([
            'redirect' => route("laradmin.{$type->slug}.index")
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        $type = $this->getTypeBySlug($request);

        if(! $this->userCanDelete($type, $request)) {
            return $this->responseUnauthorized($request);
        }

        $model = app($type->model);

        if ($type->soft_deletes) {
            $model = $model->withTrashed();
        }

        $model = $model->findOrFail($id);

        if ($type->soft_deletes && $model->trashed()) {
            $model->forceDelete();
        } else {
            $model->delete();
        }

        return $this->response([
            'redirect' => route("laradmin.{$type->slug}.index")
        ]);
    }

}
