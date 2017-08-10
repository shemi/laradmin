<div class="card meta-box">
    <header class="card-header">
        <p class="card-header-title">
            {{ $title or trans('laradmin::template.publish') }}
        </p>
    </header>
    <div class="card-content">
        <div class="content">
            {{ $slot }}
        </div>
    </div>
    <footer class="card-footer">
        @if(isset($footer) && $footer)
            {{ $footer }}
        @else
            <button type="submit"
                    :class="{'is-loading': form.busy}"
                    class="button is-primary">
                @lang('laradmin::template.save')
            </button>
            @if($model->exists)
                <a class="button is-link is-small is-danger is-outlined">
                    @lang('laradmin::template.delete')
                </a>
            @endif
        @endif
    </footer>
</div>