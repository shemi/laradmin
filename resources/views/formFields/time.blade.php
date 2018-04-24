@component('laradmin::formFields.field', ['field' => $field])

    @php
        $properties = [
            "v-model=".$field->form_prefix.$field->key,
            "expanded"
        ];

        if($field->field_size && $field->field_size !== 'default') {
            $properties[] = "size=".$field->field_size;
        }

        if($field->icon) {
            $properties[] = "icon=".($field->icon ?: 'calendar-o');
        }

        if($field->getTemplateOption('format')) {
            $properties[] = "hour-format=".($field->getTemplateOption('format') ?: '24');
        }

        if($field->placeholder) {
            $properties[] = "placeholder='".$field->placeholder."'";
        }

    @endphp

    <b-timepicker {!!  implode(' ', $properties)  !!}></b-timepicker>


@endcomponent