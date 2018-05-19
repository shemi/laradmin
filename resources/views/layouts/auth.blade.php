@php
$bodyClass = isset($bodyClass) ? ' '.$bodyClass : '';
@endphp

@extends('laradmin::layouts.app', ['bodyClass' => 'auth-template' . $bodyClass, 'hasNavbar' => false])

@section('main-content')

    <div id="page" class="page">

        <div class="main-form">
            @yield('form')
        </div>

        <div class="auth-content"
             @if(config('laradmin.style.login_bg'))
             style="background-image: url({{ config('laradmin.style.login_bg') }})"
             @endif>

            @yield('content')

            @if(! config('laradmin.style.login_bg'))
                <div class="credit">
                    Photo by
                    <a target="_blank" href="https://unsplash.com/@andy_brunner">Andy Brunner</a>
                    on
                    <a target="_blank" href="https://unsplash.com">Unsplash</a>
                </div>
            @endif
        </div>

    </div>

@endsection