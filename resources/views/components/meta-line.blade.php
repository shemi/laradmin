@php
$hr = $hr ?? true;
$langKey = $langKey ?? '';
$filter = isset($filter) && $filter ? ' | ' . $filter : ' ';
@endphp

<div>
    @lang($langKey):
    <b>
        <?php echo '{{ (' . $slot . ' || \'' . __('laradmin::template.not_available') . '\')' . $filter . '}}'; ?>
    </b>
</div>

@if($hr)
    <hr>
@endif