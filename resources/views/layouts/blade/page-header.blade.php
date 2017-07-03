@php
    $pageTitle = isset($pageTitle) ? $pageTitle : "";
@endphp

<section class="hero is-twitter is-bold">
    <div class="hero-body">
        <div class="container is-fluid">

            <h1 class="title">
                {{ $pageTitle }}
            </h1>

        </div>
    </div>

    <div class="hero-foot">

        <nav class="tabs is-small">
            <div class="container is-fluid">
                <ul>
                    <li><a>Modifiers</a></li>
                    <li><a>Grid</a></li>
                    <li><a>Elements</a></li>
                    <li><a>Components</a></li>
                    <li><a>Layout</a></li>
                </ul>
            </div>
        </nav>

    </div>

</section>