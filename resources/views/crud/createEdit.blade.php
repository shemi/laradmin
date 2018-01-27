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

                                @if($panel->has_container)
                                    <b-panel :collapsible="true">
                                        <span slot="header">{{ $panel->title }}</span>
                                        <div class="content">
                                            @foreach($panel->fieldsFor($viewType) as $field)
                                                {{ $field->render($type, $model, $data) }}
                                            @endforeach
                                        </div>
                                    </b-panel>

                                    @continue
                                @endif

                                <div :style="{{ $panel->style }}">
                                    @foreach($panel->fieldsFor($viewType) as $field)
                                        {{ $field->render($type, $model, $data) }}
                                    @endforeach
                                </div>

                            @endforeach

                        </section>
                    </div>

                    <div class="column">
                        <section class="section">

                            @foreach($type->side_panels as $panel)

                                @if($panel->fieldsFor($viewType)->isEmpty())
                                    @continue
                                @endif

                                @if($panel->is_main_meta)
                                    @component('laradmin::components.meta-box', ['model' => $model])

                                        @foreach($panel->fieldsFor($viewType) as $field)
                                            @component('laradmin::components.meta-line', [
                                                'langKey' => $field->label,
                                                'filter' => $field->getVueFilter(),
                                                'hr' => ! $loop->last
                                            ])
                                                form.{{ $field->key }}
                                            @endcomponent
                                        @endforeach

                                    @endcomponent

                                    @continue

                                @elseif(! $panel->has_container)
                                    <div class="panel" :style="{{ $panel->style }}">
                                        @foreach($panel->fieldsFor($viewType) as $field)
                                            {{ $field->render($type, $model, $data) }}
                                        @endforeach
                                    </div>

                                    @continue
                                @endif

                                <b-panel :collapsible="true">
                                    <span slot="header">{{ $panel->title }}</span>
                                    <div class="content">
                                        @foreach($panel->fieldsFor($viewType) as $field)
                                            {{ $field->render($type, $model, $data) }}
                                        @endforeach
                                    </div>
                                </b-panel>

                            @endforeach

                        </section>
                    </div>
                </div>
            </form>
        </div>
    </crud-create-edit>

@endsection