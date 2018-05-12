@php
    $pageTitle = isset($pageTitle) ? $pageTitle : null;
@endphp

@if($pageTitle)
    <section class="hero is-dark is-bold">
        <div class="hero-body">
            <div class="container is-fluid">

                <h1 class="title">
                    {{ $pageTitle }}
                </h1>

            </div>
        </div>

    </section>
@endif