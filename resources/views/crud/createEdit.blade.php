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
                        <section class="page-section">
                            @include('laradmin::components.forms.globalFormErrors', ['key' => 'form'])

                            @foreach($type->main_panels as $panel)

                                @if($panel->fieldsFor($viewType)->isEmpty())
                                    @continue
                                @endif

                                {{ $panel->render($type, $viewType, $data) }}

                            @endforeach
                        </section>
                    </div>

                    <div class="column">
                        <section class="page-section">
                            @foreach($type->side_panels as $panel)

                                @if($panel->fieldsFor($viewType)->isEmpty())
                                    @continue
                                @endif

                                {{ $panel->render($type, $viewType, $data) }}

                            @endforeach
                        </section>
                    </div>
                </div>
            </form>
        </div>
    </crud-create-edit>

@endsection