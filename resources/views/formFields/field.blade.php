@php

    $properties = [
        ":type=\"form.errors.has('{$field->validation_key}') ? 'is-danger' : ''\"",
        ":message=\"form.errors.has('{$field->validation_key}') ? form.errors.get('{$field->validation_key}') : ''\"",
        "position={$field->template_position}"
    ];

    if($field->show_label) {
        $properties[] = 'label="'. $field->label .'"';
    }

    if($field->is_grouped) {
        $properties[] = 'grouped';
    }

    if($field->is_group_multiline) {
        $properties[] = 'class=is-grouped-multiline';
    }

@endphp

<b-field @foreach($properties as $property){!!  ' '.$property  !!}@endforeach>

    {{ $slot }}

</b-field>