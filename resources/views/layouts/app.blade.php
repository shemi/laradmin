@php
    $bodyClass = isset($bodyClass) ? ' '.$bodyClass : '';
    $pageTitle = isset($pageTitle) ? ' - '.$pageTitle : '';
@endphp

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }}{{ $pageTitle }} - @lang('laradmin::template.name')</title>

    <script>
        window.laradmin = {
            api_base: '/admin',
            routs: {
                icons: '{{ route('laradmin.icons') }}'
            },
            mixins: []
        }
    </script>

    <link rel="stylesheet" href="//fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="{{ laradmin_asset('app.css') }}">
    @yield('styles')

</head>

<body class="laradmin{{ $bodyClass }}">

    <div id="app">

        @yield('main-content')

    </div>

    @yield('scripts')
    <script src="{{ laradmin_asset('app.js') }}"></script>
</body>

</html>