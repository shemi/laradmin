@php
    $fieldsType = $field->has_relationship_type ? $field->relationship_type : $type;
    $fieldsData = $field->has_relationship_type ? $data[$field->key] : $data;
@endphp

@component('laradmin::formFields.field', ['field' => $field])
    <div class="la-fields-group">
        @foreach($field->getSubFields() as $repeaterField)
            {{ $repeaterField->render($fieldsType, $fieldsData) }}
        @endforeach
    </div>
@endcomponent
