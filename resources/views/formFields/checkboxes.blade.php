@component('laradmin::formFields.field', ['field' => $field])

    @foreach($field->options as $option)
        <div class="control is-expanded">
            <b-checkbox v-model="form.{{ $field->key }}"
                        name="{{ $field->key }}"
                        :native-value="{{ $option['key'] }}">

                {{ $option['label'] }}

            </b-checkbox>
        </div>
    @endforeach

@endcomponent