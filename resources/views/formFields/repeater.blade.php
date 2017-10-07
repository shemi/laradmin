@component('laradmin::formFields.field', ['field' => $field])
    <la-repeater v-model="{{ $field->form_prefix.$field->key }}"
                 label="{{ $field->getTemplateOption('repeater_items_label', $field->label) }}"
                 label-singular="{{ $field->getTemplateOption('repeater_item_label', str_singular($field->label)) }}"
                 add-button-text="{{ $field->getTemplateOption('repeater_add_text', 'Add ' . str_singular($field->label)) }}"
                 :form.sync="{{ trim($field->form_prefix, '.') }}">

        <template scope="props">
            @foreach($field->fields as $repeaterField)
                <la-repeater-row field="{{ $repeaterField->key }}"
                                 label="{{ $repeaterField->label }}">
                    {{ $repeaterField->render($type, $model, $data) }}
                </la-repeater-row>
            @endforeach
        </template>

    </la-repeater>
@endcomponent
