@component('laradmin::formPanels.panel', compact('panel', 'type', 'model', 'viewType', 'data'))

    @component('laradmin::components.meta-box', ['model' => $model, 'typeSlug' => $type->slug])

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

@endcomponent