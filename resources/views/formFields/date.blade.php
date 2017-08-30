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

        if($field->max_length > 0) {
            $properties[] = "maxlength=".$field->max_length;
        }

        $properties[] = "icon='calendar-o'";

    @endphp

    <b-datepicker {!!  implode(' ', $properties)  !!}></b-datepicker>


@endcomponent