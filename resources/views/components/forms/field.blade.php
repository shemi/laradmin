@php
    $type = isset($type) ? $type : 'text';
    $form = isset($form) ? $form : 'form';
    $properties = isset($properties) ? $properties : [];
    $properties = is_array($properties) ? $properties : [$properties];
    $label = isset($label) ? trans("laradmin::{$group}.{$label}") : null;
    $label = $label ?: (trans()->has("laradmin::{$group}.{$model}") ? trans("laradmin::{$group}.{$model}") : null);
    $grouped = isset($grouped) ? ' grouped' : '';
    $position = isset($position) ? ' '.$position : ' is-left';

    if ($type == 'password'){
        $properties[] = 'password-reveal';
    }
@endphp

<b-field {{ $label ? 'label="'. $label .'"' : '' }}
         :type="{{ $form }}.errors.has('{{ $model }}') ? 'is-danger' : ''"
         :message="{{ $form }}.errors.has('{{ $model }}') ? {{ $form }}.errors.get('{{ $model }}') : ''"
         {{ $grouped }}
         {{ $position }}>

    <b-input type="{{ $type }}"
             v-model="{{ $form }}.{{ $model }}"
             @foreach($properties as $property)
                 {{ ' '.$property }}
             @endforeach
    >
    </b-input>

</b-field>