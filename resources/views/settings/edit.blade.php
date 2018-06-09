@extends('laradmin::layouts.page', [
    'bodyClass' => 'settings-page-edit',
    'pageTitle' => $page->name,
    'mainComponent' => 'settings'
])

@section('content')

    <settings-edit inline-template>
        <div>
            <form v-on:submit.prevent="save()" novalidate>
                <div class="columns">

                    <div class="column is-three-quarters">
                        <section class="section">
                            @include('laradmin::components.forms.globalFormErrors', ['key' => 'form'])

                            @foreach($page->main_panels as $panel)

                                @if($panel->fieldsFor($viewType)->isEmpty())
                                    @continue
                                @endif

                                {{ $panel->render($page, $viewType, $data) }}

                            @endforeach
                        </section>
                    </div>

                    <div class="column">
                        <section class="section">
                            @foreach($page->side_panels as $panel)
                                @if($panel->type !== 'main_meta' && $panel->fieldsFor($viewType)->isEmpty())
                                    @continue
                                @endif

                                {{ $panel->render($page, $viewType, $data) }}

                            @endforeach
                        </section>
                    </div>
                </div>
            </form>
        </div>
    </settings-edit>

@endsection