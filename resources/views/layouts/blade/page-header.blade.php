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

        <nav class="tabs is-boxed">
            <div class="container is-fluid">
                <ul>
                    @if(isset($type))
                        <li class="{{ route("laradmin.{$type->slug}.index") === url()->current() ? 'is-active' : '' }}">
                            <a href="{{ route("laradmin.{$type->slug}.index") }}">
                                <b-icon icon="list"></b-icon>
                                <span>
                                    @lang('laradmin::crud.actions.all', ['name' => str_plural($type->name)])
                                </span>
                            </a>
                        </li>

                        <li class="{{ route("laradmin.{$type->slug}.create") === url()->current() ? 'is-active' : '' }}">
                            <a href="{{ route("laradmin.{$type->slug}.create") }}">
                                <b-icon icon="plus"></b-icon>
                                <span>
                                    @lang('laradmin::crud.actions.new', ['name' => str_singular($type->name)])
                                </span>
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        </nav>

    </div>

</section>