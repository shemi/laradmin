@component('laradmin::formFields.field', ['field' => $field])
    <la-repeater v-model="form.{{ $field->key }}" :form.sync="form">

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
