@php
$onSubmit = isset($onSubmit) ? $onSubmit : 'submitted($event)';
$form = isset($form) ? $form : 'form';
@endphp

<form @submit.prevent="{{ $onSubmit }}">

    <b-notification v-if="{{ $form }}.errors.has('form')" type="is-danger" has-icon>
        <?php echo $form . '.errors.get("form")' ?>
    </b-notification>

    {{ $slot }}

</form>