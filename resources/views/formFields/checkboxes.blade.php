@component('laradmin::formFields.field', ['field' => $field])

    @foreach($field->options as $index => $option)
        <div class="control is-expanded">
            <b-checkbox v-model="{{ $field->form_prefix.$field->key }}"
                        name="{{ $field->key }}"
                        :native-value="{{ $option['key'] }}">

                {{ $option['label'] }}

            </b-checkbox>
        </div>
    @endforeach

@endcomponent