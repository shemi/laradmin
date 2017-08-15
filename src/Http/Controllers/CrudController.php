<?php

namespace Shemi\Laradmin\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
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
        $model = null;
        $columns = $type->browse_columns;
        $primaryKey = 'id';

        if($type->hasModel()) {
            $model = app($type->model);
            $primaryKey = $model->getKeyName();
        }

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
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function query(Request $request)
    {
        $type = $this->getTypeBySlug($request);
        $primaryKey = 'id';
        $orderBy = $request->input('order_by');
        $orderDirection = $request->input('order', 'desc');
        $search = $request->input('search');

        if($type->hasModel()) {
            $model = app($type->model);
            $query = $model::select('*');
            $primaryKey = $model->getKeyName();

            if ($model->timestamps && ! $orderBy) {
                $orderBy = 'created_at';
            }

        } else {
            $query = DB::table($type->table_name);
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

        $results = $query
            ->paginate($type->records_per_page);

        $results->getCollection()
            ->transform(function ($model) use ($type) {
                $return = [];

                foreach ($type->browse_columns as $column) {
                    $return[$column->key] = $column->getBrowseValue($model);
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
            $model = $model->findOrFail($id);
        }

        $getFormMethod = camel_case("get_{$action}_form_data");

        if(method_exists($this, $getFormMethod)) {
            $form = call_user_func_array([$this, $getFormMethod], [$type, $model]);
        } else {
            $form = $type->getModelArray($model);
        }

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

        app('laradmin')->publishJs('model', $form);
        app('laradmin')->publishJs('routs.save', route($saveRouteKey, $saveRouteParameters));
        app('laradmin')->publishJs(
            'routs.upload',
            route("laradmin.upload", ['type' => $type->slug])
        );

        if($action === 'edit') {
            app('laradmin')->publishJs(
                'routs.delete',
                route("laradmin.{$type->slug}.destroy", $saveRouteParameters)
            );
        }

        app('laradmin')->publishJs('type', [
            'action' => $action,
            'name' => $type->name,
            'singular_name' => str_singular($type->name),
            'slug' => $type->slug,
            'id' => $type->id,
            'modelPrimaryKey' => $model->getKeyName()
        ]);

        return view($view, compact('type', 'model', 'form', 'data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
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
        $user = $request->user();

        $this->validate($request, [], [], []);

        $model = $this->insertCreateUpdateData($request, $model, $type);
        $redirect = route("laradmin.{$type->slug}.edit", [
            'id' => $model->getKey()
        ]);

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
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function edit($id, Request $request)
    {
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
        $model = app($type->model)->findOrFail($id);
        $user = $request->user();

        $this->validate($request, [], [], []);

        $model = $this->insertCreateUpdateData($request, $model, $type);
        $redirect = false;

        return $this->response(compact('model', 'redirect'));
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
        $model = app($type->model)->findOrFail($id);

        $model->delete();

        return $this->response([
            'redirect' => route("laradmin.{$type->slug}.index")
        ]);
    }
}
