@extends('laradmin::layouts.page', ['bodyClass' => 'dashboard', 'pageTitle' => trans('laradmin::dashboard.page_title')])

@section('content')

    <section class="section">

        @foreach(\Laradmin::widgetsRows() as $widgets)
            <div class="columns">
                @foreach($widgets as $key => $widget)
                    <div class="column {{ $widget->getCssClasses() }}">
                        {!! $widget->render() !!}
                    </div>
                @endforeach
            </div>
        @endforeach

    </section>

@endsection