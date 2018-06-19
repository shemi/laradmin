@php

@endphp
<div class="card meta-box">
    <header class="card-header">
        <p class="card-header-title">
            {{ trans('laradmin::template.publish') }}
        </p>
    </header>
    <div class="card-content">
        <div class="content">
            @foreach($panel->fieldsFor($viewType) as $field)
                <la-editable :form="form"
                             type="{{ $field->type }}"
                             empty-string="---"
                             label="{{ $field->label }}"
                             :template-options="{{ attr_json_encode($field->template_options) }}"
                             :browse-settings="{{ attr_json_encode($field->browse_settings) }}"
                             :disabled="{{ $field->read_only ? 'true' : 'false' }}"
                             form-key="{{ $field->key }}">
                    {{ $field->render($type, $data) }}
                </la-editable>
            @endforeach
        </div>
    </div>
    <footer class="card-footer">
        <button type="submit"
                :class="{'is-loading': form.busy}"
                class="button is-primary">
            @lang('laradmin::template.save')
        </button>

        @if(isset($model) && $model->exists && Laradmin::user()->can('delete ' . $type->slug))
            <a class="button is-link is-small is-danger is-outlined"
               @click.prevent="deleteModel"
            >
                @lang('laradmin::template.delete')
            </a>
        @endif
    </footer>
</div>