@component('laradmin::formPanels.panel', compact('panel', 'type', 'viewType', 'data'))
    <b-tabs type="is-boxed">
        @foreach($panel->tabs as $tab)
            <b-tab-item label="{{ $tab['title'] }}"{{ $tab['icon'] ? ' :icon="'.$tab['icon'].'"' : '' }}>
                @foreach($panel->fieldsFor($viewType)->where('tab_id', $tab['id']) as $field)
                    <div class="content">
                        {{ $field->render($type, $data) }}
                    </div>
                @endforeach
            </b-tab-item>
        @endforeach
    </b-tabs>
@endcomponent