@component('laradmin::formFields.field', ['field' => $field])

    @php
        $properties = [
            "v-model=".$field->form_prefix.$field->key,
            "expanded"
        ];

        if($field->field_size !== 'default') {
            $properties[] = "size=".$field->field_size;
        }

        if($field->icon) {
            $properties[] = "icon=".$field->icon;
        }

        if($field->placeholder) {
            $properties[] = "placeholder='".$field->placeholder."'";
        }
    @endphp

    <b-select {!! implode(' ', $properties) !!}>

        @if($field->nullable)
            <option :value="null">
                {{ $field->placeholder ?: 'Select' }}
            </option>
        @endif

        @foreach($field->options as $option)

            <option value="{{ $option['key'] }}">
                {{ $option['label'] }}
            </option>

        @endforeach

    </b-select>


@endcomponent