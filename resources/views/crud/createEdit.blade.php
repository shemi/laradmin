@php
    $viewType = $model->exists ? 'edit' : 'create';
@endphp

@extends('laradmin::layouts.page', [
    'bodyClass' => 'crud-create-edit',
    'pageTitle' => trans('laradmin::crud.page_title.'. $viewType, ['name' => str_singular($type->name)]),
    'mainComponent' => 'crud'
])

@section('content')

    <crud-create-edit inline-template>
        <div>
            <form v-on:submit.prevent="save()" novalidate>
                <div class="columns">

                    <div class="column is-three-quarters">
                        <section class="section">
                            @include('laradmin::components.forms.globalFormErrors', ['key' => 'form'])

                            @foreach($type->main_panels as $panel)

                                @if($panel->fieldsFor($viewType)->isEmpty())
                                    @continue
                                @endif

                                {{ $panel->render($type, $model, $viewType, $data) }}

                            @endforeach
                        </section>
                    </div>

                    <div class="column">
                        <section class="section">
                            @foreach($type->side_panels as $panel)

                                @if($panel->fieldsFor($viewType)->isEmpty())
                                    @continue
                                @endif

                                {{ $panel->render($type, $model, $viewType, $data) }}

                            @endforeach
                        </section>
                    </div>
                </div>
            </form>
        </div>
    </crud-create-edit>

@endsection