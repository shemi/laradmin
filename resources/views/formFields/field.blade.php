@php
    $grouped = $field->is_grouped ? ' grouped' : '';
    $position = ' '.$field->template_position;
@endphp

<b-field {{ $field->show_label ? 'label='. $field->label : '' }}
         :type="form.errors.has('{{ $field->key }}') ? 'is-danger' : ''"
         :message="form.errors.has('{{ $field->key }}') ? form.errors.get('{{ $field->key }}') : ''"
        {{ $grouped }}{{ $position }}>

    {{ $slot }}

</b-field>