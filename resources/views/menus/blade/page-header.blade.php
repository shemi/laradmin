@section('page-header-actions-before')
    <li class="{{ route("laradmin.menus.menus.index") === url()->current() ? 'is-active' : '' }}">
        <a href="{{ route("laradmin.menus.menus.index") }}">
            <b-icon icon="list"></b-icon>
            <span>
                @lang('laradmin::crud.actions.all', ['name' => 'menus'])
            </span>
        </a>
    </li>
    <li class="{{ route("laradmin.menus.menus.create") === url()->current() ? 'is-active' : '' }}">
        <a href="{{ route("laradmin.menus.menus.create") }}">
            <b-icon icon="plus"></b-icon>
            <span>
                @lang('laradmin::crud.actions.new', ['name' => 'menu'])
            </span>
        </a>
    </li>
@endsection