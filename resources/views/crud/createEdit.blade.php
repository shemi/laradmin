@php
    if($model->exist) {
        $pageTitle = trans('laradmin::crud.page_title.edit', ['name' => str_singular($type->name)]);
    } else {
        $pageTitle = trans('laradmin::crud.page_title.create', ['name' => str_singular($type->name)]);
    }
@endphp

@extends('laradmin::layouts.page', ['bodyClass' => 'crud-create-edit', 'pageTitle' => $pageTitle])

@section('content')



@endsection