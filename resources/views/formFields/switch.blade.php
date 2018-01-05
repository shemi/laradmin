@component('laradmin::formFields.field', ['field' => $field])

    @php
        $properties = [
            "v-model=".$field->form_prefix.$field->key,
        ];

        if($field->field_size && $field->field_size !== 'default') {
            $properties[] = "size=".$field->field_size;
        }

    @endphp

    <b-switch {!! implode(' ', $properties) !!}>
        {{ $field->label }}
    </b-switch>

@endcomponent