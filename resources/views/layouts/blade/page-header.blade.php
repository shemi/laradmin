@php
    $pageTitle = isset($pageTitle) ? $pageTitle : null;
@endphp

@if($pageTitle)
    <div class="page-title container is-fluid">
        <h1 class="title">
            {{ $pageTitle }}
        </h1>
        @isset($type)
            <div class="title-actions">
                @if(! laradmin()->links()->isCreate() && laradmin()->user()->can('create ' . $type->slug))
                    <a href="{{ laradmin()->links()->create($type) }}"
                       class="action button is-small">
                        <span>@lang('New ' . str_singular($type->name))</span>
                    </a>
                @endif
            </div>
        @endisset
    </div>
@endif