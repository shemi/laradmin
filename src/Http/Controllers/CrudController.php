<?php

namespace Shemi\Laradmin\Http\Controllers;

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

        if($search && ! empty($search)) {
            foreach ($type->searchable_fields as $index => $field) {
                $s = $field->search_comparison === 'like' ? "%{$search}%" : $search;

                if($index === 0) {
                    $query->where($field->key, $field->search_comparison, $s);
                } else {
                    $query->orWhere($field->key, $field->search_comparison, $s);
                }
            }
        }

        $query->orderBy($orderBy ?: $primaryKey, $orderDirection);

        $results = $query
            ->paginate($type->records_per_page);

        return $this->response($results);
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

        $form = $type->getModelArray($model);
        $form = new HtmlString(json_encode($form, JSON_UNESCAPED_UNICODE));

        return view('laradmin::crud.createEdit', compact('type', 'model', 'form'));
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
        $form = $type->getModelArray($model);
        $form = new HtmlString(json_encode($form, JSON_UNESCAPED_UNICODE));

        return view('laradmin::crud.createEdit', compact('type', 'model', 'form'));
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
