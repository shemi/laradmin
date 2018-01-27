<?php

namespace Shemi\Laradmin\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Shemi\Laradmin\Contracts\Repositories\CreateUpdateRepository;
use Shemi\Laradmin\Contracts\Repositories\TransformTypeModelDataRepository;
use Shemi\Laradmin\Contracts\Repositories\TypeModelQueryRepository;
use Shemi\Laradmin\Contracts\Repositories\TypeRequestValidatorRepository;
use Shemi\Laradmin\Models\Type;

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

        $editRoute = route("laradmin.{$type->slug}.edit", ["{$type->slug}" => "__primaryKey__"]);
        $editRoute = str_replace('__primaryKey__', "'+ props.row.{$primaryKey} +'", $editRoute);

        $deleteRoute = route("laradmin.{$type->slug}.destroy", ["{$type->slug}" => "__primaryKey__"]);
        $deleteRoute = str_replace('__primaryKey__', "'+ props.row.{$primaryKey} +'", $deleteRoute);

        return view(
            'laradmin::crud.browse',
            compact('type', 'model', 'columns', 'primaryKey', 'editRoute', 'deleteRoute')
        );
    }

    /**
     *
     *
     * @param Type $type
     * @param Request $request
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


        $orderBy = $request->input('order_by');
        $orderDirection = $request->input('order', 'desc');
        $search = $request->input('search');

        $model = app($type->model);
        $query = $model::select('*');
        $primaryKey = $model->getKeyName();

        if ($model->timestamps && ! $orderBy) {
            $orderBy = 'created_at';
        }

        $relationships = $type->relationships;
        $relationshipsSearch = [];

        if($search && ! empty($search)) {
            foreach ($type->searchable_fields as $index => $field) {
                $s = $field->search_comparison === 'like' ? "%{$search}%" : $search;

                if(count(explode('.', $field->key)) > 1) {
                    $keyParts = explode('.', $field->key);
                    $relationKey = array_shift($keyParts);

                    if(! array_key_exists($relationKey, $relationshipsSearch)) {
                        $relationshipsSearch[$relationKey] = [];
                    }

                    $relationshipsSearch[$relationKey][] = $field;
                } else {
                    if($index === 0) {
                        $query->where($field->key, $field->search_comparison, $s);
                    } else {
                        $query->orWhere($field->key, $field->search_comparison, $s);
                    }
                }
            }

            if($type->has_relationships) {
                if(count($relationshipsSearch)) {
                    foreach ($relationshipsSearch as $key => $fields) {
                        if(in_array($key, $relationships)) {
                            unset($relationships[array_search($key, $relationships)]);
                        }

                        $relationships[$key] = function($query) use ($fields, $search) {
                            foreach ($fields as $index => $field) {
                                $s = $field->search_comparison === 'like' ? "%{$search}%" : $search;
                                $key = $field->key;//preg_replace("/([^\.]*)(\.)(\S*)/", "roles.$3", $field->key);

                                if($index === 0) {
                                    $query->where($key, $field->search_comparison, $s);
                                } else {
                                    $query->orWhere($key, $field->search_comparison, $s);
                                }
                            }
                        };
                    }
                }
            }
        }


        if($type->has_relationships) {
            $query->with((array) $relationships);
        }

        $query->orderBy($orderBy ?: $primaryKey, $orderDirection);

        try {
            $results = $query
                ->paginate($type->records_per_page);
        } catch (\Exception $exception) {
            dd($exception->getMessage());
        }

        $primaryKey = $model->getKeyName();

        $hasPrimaryKeyField = (bool) $type->browse_columns
            ->where('key', $primaryKey)
            ->count();

        $results->getCollection()
            ->transform(function ($model) use ($type, $hasPrimaryKeyField, $primaryKey) {
                $return = [];

                foreach ($type->browse_columns as $column) {
                    $return[$column->key] = $column->getBrowseValue($model);
                }

                if(! $hasPrimaryKeyField) {
                    $return[$primaryKey] = $model->getKey();
                }

                return $return;
            });


        return $this->response($results->toArray());
    }

    public function createEditResponse($id = null, Request $request)
    {
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

        app('laradmin')->publishManyJs($jsObject);

        return view($view, compact('type', 'model', 'form', 'data'));
    }

    /**
     * @param Type $type
     * @param Request $request
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
        $createRepository = app(CreateUpdateRepository::class)
            ->createOrUpdate(
                $request->only($fields->pluck('key')->toArray()),
                $model,
                $type
            );

        if($createRepository->failed()) {
            return $this->responseBadRequest(
                $createRepository->errors()->first()
            );
        }

        $warnings = $createRepository->warnings();

        $redirect = route("laradmin.{$type->slug}.edit", [
            'id' => $model->getKey()
        ]);

        return $this->response(compact('model', 'redirect', 'warnings'));
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
     *
     *
     * @param Type $type
     * @param Request $request
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

        /** @var CreateUpdateRepository $updateRepository */
        $updateRepository = app(CreateUpdateRepository::class)
            ->createOrUpdate(
                $request->only($fields->pluck('key')->toArray()),
                $model,
                $type
            );

        if($updateRepository->failed()) {
            return $this->responseBadRequest(
                $updateRepository->errors()->first()
            );
        }

        $warnings = $updateRepository->warnings();

        $model = app(TransformTypeModelDataRepository::class)
            ->transform($type, $model->refresh());

        $redirect = false;

        return $this->response(
            compact('model', 'redirect', 'warnings')
        );
    }

    /**
     *
     *
     * @param Type $type
     * @param Request $request
     */
    protected function userCanDelete(Type $type, Request $request)
    {
        return $this->user()->can("delete {$type->slug}");
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

        $model = app($type->model)->findOrFail($id);

        $model->delete();

        return $this->response([
            'redirect' => route("laradmin.{$type->slug}.index")
        ]);
    }

}
