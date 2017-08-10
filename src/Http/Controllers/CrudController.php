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

        return view('laradmin::crud.browse', compact('type', 'model', 'columns', 'primaryKey', 'editRoute'));
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

        $results = $results->toArray();

        return $this->response($results);
    }

    protected function getCreateForm(Type $type, Model $model)
    {
        return $type->getModelArray($model);
    }

    protected function getCreateData(Type $type, Model $model)
    {
        return [];
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $type = $this->getTypeBySlug($request);
        $model = null;

        if($type->hasModel()) {
            $model = app($type->model);
        }

        $form = $this->getCreateForm($type, $model);
        $form = new HtmlString(json_encode($form, JSON_UNESCAPED_UNICODE));

        $data = $this->getCreateData($type, $model);

        $view = 'laradmin::crud.createEdit';

        if(view()->exists("laradmin::{$type->slug}.createEdit")) {
            $view = "laradmin::{$type->slug}.createEdit";
        }

        return view($view, compact('type', 'model', 'form', 'data'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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

    protected function getEditForm(Type $type, Model $model)
    {
        return $type->getModelArray($model);
    }

    protected function getEditData(Type $type, Model $model)
    {
        return [];
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
        $model = app($type->model)->findOrFail($id);
        $form = $this->getCreateForm($type, $model);
        $form = new HtmlString(json_encode($form, JSON_UNESCAPED_UNICODE));

        $data = $this->getEditData($type, $model);

        $view = 'laradmin::crud.createEdit';

        if(view()->exists("laradmin::{$type->slug}.createEdit")) {
            $view = "laradmin::{$type->slug}.createEdit";
        }

        return view($view, compact('type', 'model', 'form', 'data'));
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
