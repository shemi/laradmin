@php
    $fieldsType = $field->has_relationship_type ? $field->relationship_type : $type;
    $fieldsData = $field->has_relationship_type ? $data[$field->key] : $data;
    $columns = $field->getSubFields()->pluck('label', 'key');
@endphp

@component('laradmin::formFields.field', ['field' => $field])
    <la-repeater v-model="{{ $field->form_prefix.$field->key }}"
                 form-key="{{ $field->validation_key }}"
                 :init-columns='{{ json_encode($columns, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) }}'
                 label="{{ $field->getTemplateOption('repeater_items_label', $field->label) }}"
                 label-singular="{{ $field->getTemplateOption('repeater_item_label', str_singular($field->label)) }}"
                 add-button-text="{{ $field->getTemplateOption('repeater_add_text', 'Add ' . str_singular($field->label)) }}"
                 collapse-field-key="{{ $field->getSubFields()->first()->key }}"
                 :form.sync="{{ trim($field->form_prefix, '.') }}">

        <template slot-scope="props">
            @foreach($field->getSubFields() as $repeaterField)
                {{ $repeaterField->render($fieldsType, $fieldsData) }}
            @endforeach
        </template>

    </la-repeater>
@endcomponent
