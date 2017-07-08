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

            <div class="credit">
                Photo by
                <a target="_blank" href="https://unsplash.com/@andy_brunner">Andy Brunner</a>
                on
                <a target="_blank" href="https://unsplash.com">Unsplash</a>
            </div>
        </div>

    </div>

@endsection