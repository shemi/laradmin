@component('laradmin::formFields.field', ['field' => $field])

    @php
        $properties = [
            "type=".$field->field_type,
            "v-model=form.".$field->key,
            "expanded",
            "multiple",
            "size=8"
        ];

        if($field->icon) {
            $properties[] = "icon=".$field->icon;
        }

        if($field->placeholder) {
            $properties[] = "placeholder='".$field->placeholder."'";
        }

    @endphp

    <div class="select is-multiple">
        <select @foreach($properties as $property){!!  ' '.$property  !!}@endforeach>
            @foreach($field->options as $option)
                <option value="{{ $option['key'] }}">{{ $option['value'] }}</option>
            @endforeach
        </select>
    </div>

@endcomponent