@component('laradmin::formFields.field', ['field' => $field])

    @php
        $properties = [
            "type=".$field->field_type,
            "v-model=".$field->form_prefix.$field->key,
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

        if($field->max_length > 0) {
            $properties[] = "maxlength=".$field->max_length;
        }

        if ($field->field_type === 'number') {

            if($field->getTemplateOption('step')) {
                $properties[] = 'step='.$field->getTemplateOption('step');
            }

            if($field->getTemplateOption('min')) {
                $properties[] = 'min='.$field->getTemplateOption('min');
            }

            if($field->getTemplateOption('max')) {
                $properties[] = 'max='.$field->getTemplateOption('max');
            }
        }

        if ($field->field_type === 'password'){
            $properties[] = 'password-reveal';
        }
    @endphp

    <b-input @foreach($properties as $property){!!  ' '.$property  !!}@endforeach></b-input>


@endcomponent