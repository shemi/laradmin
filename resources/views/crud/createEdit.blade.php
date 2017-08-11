@php
    $viewType = $model->exists ? 'edit' : 'create';
@endphp

@extends('laradmin::layouts.page', [
    'bodyClass' => 'crud-create-edit',
    'pageTitle' => trans('laradmin::crud.page_title.'. $viewType, ['name' => str_singular($type->name)]),
    'mainComponent' => 'crud'
])

@section('scripts')
    <script>
        window.laradmin.model = {{ $form }}
    </script>
@endsection

@section('content')

    <crud-create-edit inline-template>
        <div>
            <form v-on:submit.prevent="save()" novalidate>
                <div class="columns">

                    <div class="column is-three-quarters">
                        <section class="section">
                            @include('laradmin::components.forms.globalFormErrors', ['key' => 'form'])

                            @foreach($type->main_panels as $panel)

                                @foreach($panel->fields as $field)
                                    @if($field->isVisibleOn($model->exists ? 'edit' : 'create'))
                                        {{ $field->render($type, $model, $data) }}
                                    @endif
                                @endforeach

                            @endforeach

                        </section>
                    </div>

                    <div class="column">
                        <section class="section">

                            @foreach($type->side_panels as $panel)

                                @if($panel->is_main_meta)
                                    @component('laradmin::components.meta-box', ['model' => $model])

                                        @foreach($panel->fields as $field)
                                            @component('laradmin::components.meta-line', [
                                                'langKey' => $field->label,
                                                'filter' => $field->getVueFilter(),
                                                'hr' => ! $loop->last
                                            ])
                                                form.{{ $field->key }}
                                            @endcomponent
                                        @endforeach

                                    @endcomponent
                                @endif

                            @endforeach

                        </section>
                    </div>
                </div>
            </form>
        </div>
    </crud-create-edit>

@endsection