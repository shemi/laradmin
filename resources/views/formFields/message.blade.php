@php
    $properties = [
        "title='".$field->label."'",
        "type=".$field->field_type
    ];

    if($field->field_size !== 'default') {
        $properties[] = "size=".$field->field_size;
    }

    if($field->getTemplateOption('has_icon')) {
        $properties[] = "has-icon";
    }

    if(! $field->getTemplateOption('closable', true)) {
        $properties[] = ":closable=false";
    }

    $viewName = $field->getTemplateOption('view');
@endphp

@if($viewName && view()->exists($viewName))
    <b-message {!! implode(' ', $properties) !!}>
        @include($viewName, compact('field', 'type', 'model', 'data'))
    </b-message>

@else
    <b-message type="is-danger">
        THE VIEW "{{ $viewName }}" DOES NOT EXIST
    </b-message>
@endif

