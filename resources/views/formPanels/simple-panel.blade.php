@component('laradmin::formPanels.panel', compact('panel', 'type', 'model', 'viewType', 'data'))
    @if($panel->has_container)
        <b-collapse class="panel">
            <div slot="trigger"
                 slot-scope="props"
                 class="panel-heading is-collapsible">
                <span>{{ $panel->title }}</span>
                <span class="icon is-pulled-right">
                    <b-icon :icon="props.open ? 'caret-down' : 'caret-up'"></b-icon>
                </span>
            </div>
            <div class="panel-content panel-block">
                <div class="content">
                    @foreach($panel->fieldsFor($viewType) as $field)
                        {{ $field->render($type, $model, $data) }}
                    @endforeach
                </div>
            </div>
        </b-collapse>
    @else
        <b-panel :collapsible="true">
            <span slot="header">{{ $panel->title }}</span>
            <div class="content">
                @foreach($panel->fieldsFor($viewType) as $field)
                    {{ $field->render($type, $model, $data) }}
                @endforeach

            </div>
        </b-panel>
    @endif
@endcomponent