@php
    $bodyClass = isset($bodyClass) ? ' '.$bodyClass : '';
    $pageTitle = isset($pageTitle) ? ' - '.$pageTitle : '';
    $hasNavbar = isset($hasNavbar) ? $hasNavbar : true;
@endphp

<!doctype html>
<html lang="en" class="{{ $hasNavbar ? 'has-navbar-fixed-top' : '' }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }}{{ $pageTitle }} - @lang('laradmin::template.name')</title>

    {{ app('laradmin')->jsVars()->render() }}

    <link rel="stylesheet" href="{{ laradmin_asset('/css/app.css') }}">
    @yield('styles')

</head>

<body class="laradmin{{ $bodyClass }}">

    <div id="app">

        @yield('main-content')

        <div class="la-page-loading" v-cloak :class="{'is-active': isLoading}">
            <div class="la-logo">
                @include('laradmin::layouts.blade.logo')
            </div>
            <div class="lds-ring"><div></div><div></div><div></div><div></div></div>
        </div>

    </div>

    @yield('scripts')

    @if(view()->exists('laradmin::before-scripts'))
        @include('laradmin::before-scripts')
    @endif

    <script src="{{ laradmin_asset('/js/app.js') }}"></script>
</body>

</html>