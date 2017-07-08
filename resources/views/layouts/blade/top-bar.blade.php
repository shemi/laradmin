<top-bar class="nav has-shadow" inline-template>
    <nav>
        <div class="nav-left">
            <a class="nav-item logo" href="{{ route('laradmin.dashboard') }}">
                @lang('laradmin::template.name')
            </a>
        </div>

        <!-- This "nav-toggle" hamburger menu is only visible on mobile -->
        <!-- You need JavaScript to toggle the "is-active" class on "nav-menu" -->
        <span class="nav-toggle"
              @click.prevent="toggleMobileMenu()"
              :class="{'is-active': isMobileMenuOpen}">
            <span></span>
            <span></span>
            <span></span>
        </span>

        <!-- This "nav-menu" is hidden on mobile -->
        <!-- Add the modifier "is-active" to display it on mobile -->
        <div class="nav-right nav-menu" :class="{'is-active': isMobileMenuOpen}">
            <a class="nav-item is-tab" href="#">
                <figure class="image is-16x16" style="margin-right: 8px;">
                    <img src="http://bulma.io/images/jgthms.png">
                </figure>
                @lang('laradmin::template.profile')
            </a>
            <a @click.prevent="logout()" class="nav-item is-tab" href="{{ route('laradmin.logout') }}">
                @lang('laradmin::template.logout')
            </a>
            <form id="logout-form"
                  style="display: none"
                  method="POST"
                  action="{{ route('laradmin.logout') }}">
                {{ csrf_field() }}
            </form>
        </div>
    </nav>
</top-bar>
