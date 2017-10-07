@component('laradmin::formFields.field', ['field' => $field])

    <la-image-upload v-model="{{ $field->form_prefix.$field->key }}"
                     form-key="{{ $field->key }}"
                     :form="form">

    </la-image-upload>

@endcomponent
