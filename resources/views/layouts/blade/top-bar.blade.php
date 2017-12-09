<top-bar class="navbar has-shadow" inline-template>
    <nav>
        <div class="navbar-brand">
            <a class="nav-item logo" href="{{ route('laradmin.dashboard') }}">
                @lang('laradmin::template.name')
            </a>

            <button class="button navbar-burger">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>

        <div class="navbar-menu" :class="{'is-active': isMobileMenuOpen}">
            <div class="navbar-end">
                <a class="navbar-item is-tab" href="#">
                    <figure class="image is-16x16" style="margin-right: 8px;">
                        <img src="http://bulma.io/images/jgthms.png">
                    </figure>
                    @lang('laradmin::template.profile')
                </a>
                <a @click.prevent="logout()" class="navbar-item is-tab" href="{{ route('laradmin.logout') }}">
                    @lang('laradmin::template.logout')
                </a>

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
