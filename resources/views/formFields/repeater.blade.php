@php
    $fieldsType = $field->has_relationship_type ? $field->relationship_type : $type;
    $fieldsData = $field->has_relationship_type ? $data[$field->key] : $data;
@endphp

@component('laradmin::formFields.field', ['field' => $field])
    <la-repeater v-model="{{ $field->form_prefix.$field->key }}"
                 label="{{ $field->getTemplateOption('repeater_items_label', $field->label) }}"
                 label-singular="{{ $field->getTemplateOption('repeater_item_label', str_singular($field->label)) }}"
                 add-button-text="{{ $field->getTemplateOption('repeater_add_text', 'Add ' . str_singular($field->label)) }}"
                 :form.sync="{{ trim($field->form_prefix, '.') }}">

        <template slot-scope="props">
            @foreach($field->getSubFields() as $repeaterField)
                <la-repeater-row field="{{ $repeaterField->key }}"
                                 label="{{ $repeaterField->label }}">
                    {{ $repeaterField->render($fieldsType, $fieldsData) }}
                </la-repeater-row>
            @endforeach
        </template>

    </la-repeater>
@endcomponent
