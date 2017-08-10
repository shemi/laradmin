@php
    $form = isset($form) ? $form : 'form';
    $properties = isset($properties) ? $properties : [];
    $properties = is_array($properties) ? $properties : [$properties];
    $label = isset($label) ? $label : null;
    $grouped = isset($grouped) ? ' grouped' : '';
    $position = isset($position) ? ' '.$position : ' is-left';
@endphp

<b-field {{ $label ? 'label='. $label : '' }}
         :type="{{ $form }}.errors.has('{{ $model }}') ? 'is-danger' : ''"
         :message="{{ $form }}.errors.has('{{ $model }}') ? {{ $form }}.errors.get('{{ $model }}') : ''"
        {{ $grouped }}
        {{ $position }}>

    <b-select v-model="{{ $form }}.{{ $model }}"
            @foreach($properties as $property)
                {{ ' '.$property }}
            @endforeach
    >
        {{ $slot }}
    </b-select>

</b-field>