@php
$key = isset($key) ? $key : 'form';
@endphp

<b-notification v-if="{{ $key }}.errors.has('form')" type="is-danger">
    @php
    echo "{{ {$key}.errors.get(\"form\") }}";
    @endphp
</b-notification>