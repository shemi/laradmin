@component('laradmin::formFields.field', ['field' => $field])
    <la-relationship v-model="{{ $field->form_prefix.$field->key }}"
                 label="{{ $field->getTemplateOption('items_label', $field->label) }}"
                 label-singular="{{ str_singular($field->getTemplateOption('item_label', $field->label)) }}"
                 query-uri="{{ route('laradmin.relationship.query', ['typeSlug' => $type->slug, 'fieldKey' => $field->key]) }}"
                 :form.sync="{{ trim($field->form_prefix, '.') }}">

    </la-relationship>
@endcomponent
