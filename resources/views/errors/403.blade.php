@extends('laradmin::layouts.page', [
    'bodyClass' => 'errors-page error-403',
    'pageTitle' => 'YOU SHALL NOT PASS'
])

@section('content')

    <section class="section">
        <b-notification type="is-danger" has-icon :closable="false">
            <p>You cannot access this view.</p>
        </b-notification>
    </section>

@endsection