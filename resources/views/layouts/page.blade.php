@php
    $bodyClass = isset($bodyClass) ? ' '.$bodyClass : '';
    $mainComponent = isset($mainComponent) ? " is={$mainComponent} inline-template" : '';
@endphp

@extends('laradmin::layouts.app', ['bodyClass' => 'page-template' . $bodyClass])

@section('main-content')

    <div id="app" class="page">

        <aside class="side-bar">
            <div class="side-menu-holder">
                <div class="navbar-brand">
                    <a class="navbar-item la-logo" href="{{ route('laradmin.dashboard') }}">
                        @include('laradmin::layouts.blade.logo')
                    </a>
                </div>

                @include('laradmin::layouts.blade.side-menu')
            </div>
        </aside>

        <div class="content-area">
            <header class="top-bar">
                @include('laradmin::layouts.blade.top-bar')
            </header>
            <main class="main-content">
                <div{{ $mainComponent }}>
                    <div>
                        <header class="page-header">
                            @include('laradmin::layouts.blade.page-header')
                        </header>

                        <div class="content-container">
                            <div class="container is-fluid">
                                @yield('content')
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>

    </div>

@endsection