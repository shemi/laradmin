@component('laradmin::formFields.field', ['field' => $field])

    <la-file-upload v-model="{{ $field->form_prefix.$field->key }}"
                     form-key="{{ $field->key }}"
                     :form="form">

    </la-file-upload>

@endcomponent
