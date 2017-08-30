@component('laradmin::formFields.field', ['field' => $field])
    <la-relationship v-model="{{ $field->form_prefix.$field->key }}"
                 label="{{ $field->getTemplateOption('repeater_items_label', $field->label) }}"
                 label-singular="{{ $field->getTemplateOption('repeater_item_label', str_singular($field->label)) }}"
                 add-button-text="{{ $field->getTemplateOption('repeater_add_text', 'Add ' . str_singular($field->label)) }}"
                 :form.sync="{{ trim($field->form_prefix, '.') }}">



    </la-relationship>
@endcomponent
