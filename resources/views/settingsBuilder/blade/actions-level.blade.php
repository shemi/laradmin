<div class="level">

    <div class="level-left">
        <div class="level-item">
            <b>@lang('laradmin::template.created_at'):</b>
            <span>@{{ form.created_at | date }}</span>
        </div>

        <div class="level-item">
            <b>@lang('laradmin::template.updated_at'):</b>
            <span>@{{ form.updated_at | date }}</span>
        </div>

        <div class="level-item">
            <b>@lang('laradmin::template.slug'):</b>
            <span>{{ form.name || '<?php echo trans('laradmin::template.not_available') ?>' | slugify }}</span>
        </div>
    </div>

    <div class="level-right">

        <div class="level-item">
            <button type="submit"
                    :class="{'is-loading': form.busy}"
                    class="button is-primary">
                @lang('laradmin::template.save')
            </button>
        </div>

        @if($model->exists)
            <div class="level-item">
                <a class="button is-link is-small is-danger is-outlined">
                    @lang('laradmin::template.delete')
                </a>
            </div>
        @endif

    </div>

</div>