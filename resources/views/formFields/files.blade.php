@component('laradmin::formFields.field', ['field' => $field])

    <la-files-upload v-model="{{ $field->form_prefix.$field->key }}"
                     form-key="{{ $field->key }}"
                     :form="form">

    </la-files-upload>

@endcomponent
