@php
$bodyClass = isset($bodyClass) ? ' '.$bodyClass : '';
@endphp

@extends('laradmin::layouts.app', ['bodyClass' => 'auth-template' . $bodyClass])

@section('main-content')

    <div id="page" class="page">

        <div class="main-form">
            @yield('form')
        </div>

        <div class="auth-content">
            @yield('content')
        </div>

    </div>

@endsection