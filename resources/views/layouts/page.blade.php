@php
    $bodyClass = isset($bodyClass) ? ' '.$bodyClass : '';
@endphp

@extends('laradmin::layouts.app', ['bodyClass' => 'page-template' . $bodyClass])

@section('main-content')

    <div id="app" class="page">

        <header class="top-bar">
            @include('laradmin::layouts.blade.top-bar')
        </header>

        <div class="content-area">
            <div class="side-menu-holder">
                @include('laradmin::layouts.blade.side-menu')
            </div>

            <main class="main-content">
                <header class="page-header">
                    @include('laradmin::layouts.blade.page-header')
                </header>

                <div class="content-container">
                    <div class="container is-fluid">
                        @yield('content')
                    </div>
                </div>
            </main>
        </div>

    </div>

@endsection