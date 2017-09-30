@section('page-header-actions-before')
    <li class="{{ route("laradmin.types.index") === url()->current() ? 'is-active' : '' }}">
        <a href="{{ route("laradmin.types.index") }}">
            <b-icon icon="list"></b-icon>
            <span>
                @lang('laradmin::crud.actions.all', ['name' => 'types'])
            </span>
        </a>
    </li>
    <li class="{{ route("laradmin.types.create") === url()->current() ? 'is-active' : '' }}">
        <a href="{{ route("laradmin.types.create") }}">
            <b-icon icon="plus"></b-icon>
            <span>
                @lang('laradmin::crud.actions.new', ['name' => 'type'])
            </span>
        </a>
    </li>
@endsection