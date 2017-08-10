<?php

namespace Shemi\Laradmin\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Shemi\Laradmin\Models\Type;
use Spatie\Permission\Models\Role;

class UsersController extends CrudController
{

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

        $view = 'laradmin::crud.createEdit';

        if(view()->exists("laradmin::{$type->slug}.createEdit")) {
            $view = "laradmin::{$type->slug}.createEdit";
        }

        return view($view, compact('type', 'model', 'form'));
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

    protected function getCreateData(Type $type, Model $model)
    {
        return $this->getEditData($type, $model);
    }

    protected function getEditForm(Type $type, Model $model)
    {
        return [
            'name' => $model->name,
            'email' => $model->email,
            'role' => $model->role,
            'created_at' => $model->created_at,
            'updated_at' => $model->updated_at
        ];
    }

    protected function getEditData(Type $type, Model $model)
    {
        return [
            'roles' => Role::all()
        ];
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
}
