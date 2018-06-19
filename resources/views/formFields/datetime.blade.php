@component('laradmin::formFields.field', ['field' => $field])

    @php
        /** @var \Shemi\Laradmin\Models\Field $field */

        $properties = [
            "v-model=".$field->form_prefix.$field->key,
            "form-key=".$field->form_prefix.$field->key,
            "type=\"".$field->type."\"",
            "timezone=\"".$field->getTemplateOption('datetime.timezone', 'local')."\"",
            ":config='".attr_json_encode($field->getTemplateOption('datetime', (object) []))."'",
            "expanded"
        ];

        if($field->field_size && $field->field_size !== 'default') {
            $properties[] = "size=".$field->field_size;
        }

        if($field->icon) {
            $properties[] = "icon=".$field->icon;
        }

        if($field->placeholder) {
            $properties[] = "placeholder='".$field->placeholder."'";
        }

    @endphp

    <la-date-time {!!  implode(' ', $properties)  !!}></la-date-time>


@endcomponent