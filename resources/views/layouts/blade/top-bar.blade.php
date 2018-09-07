<top-bar class="navbar has-shadow" inline-template>
    <nav>

        <div class="navbar-menu is-active" :class="{'is-active': isMobileMenuOpen}">
            <div class="navbar-end">
                <b-dropdown position="is-bottom-left">
                    <a class="navbar-item" slot="trigger">
                        <figure class="image is-16x16" style="margin-right: 8px;">
                            <img src="http://bulma.io/images/jgthms.png">
                        </figure>
                        <span>{{ Laradmin::user()->name }}</span>
                        <b-icon icon="angle-down"></b-icon>
                    </a>

                    <b-dropdown-item custom>
                        Logged as <b>{{ Laradmin::user()->name }}</b>
                    </b-dropdown-item>

                    <hr class="dropdown-divider">

                    @if(Laradmin::getUserType())
                        <b-dropdown-item has-link>
                            <a href="{{ Laradmin::manager('links')->edit(Laradmin::getUserType(), Laradmin::user()) }}">
                                <b-icon icon="user"></b-icon>
                                Profile
                            </a>
                        </b-dropdown-item>
                    @endif

                    <b-dropdown-item @click="logout()">
                        <b-icon icon="sign-out"></b-icon>
                        @lang('laradmin::template.logout')
                    </b-dropdown-item>

                    <hr class="dropdown-divider">

                    <b-dropdown-item custom>
                        Laradmin <b>v{{ Laradmin::version() }}</b>
                    </b-dropdown-item>
                </b-dropdown>

                <form id="logout-form"
                      style="display: none"
                      method="POST"
                      action="{{ route('laradmin.logout') }}">
                    {{ csrf_field() }}
                </form>
            </div>
        </div>
    </nav>
</top-bar>
