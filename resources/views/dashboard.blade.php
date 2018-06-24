@extends('laradmin::layouts.page', ['bodyClass' => 'dashboard', 'pageTitle' => trans('laradmin::dashboard.page_title')])

@section('content')

    <la-dashboard class="section" inline-template>
        <div>
            @foreach(app('laradmin')->widgets()->rows() as $widgets)
                <div class="columns">
                    @foreach($widgets as $key => $widget)
                        <div class="column {{ $widget->getCssClasses() }}">
                            {!! $widget->render() !!}
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </la-dashboard>

@endsection