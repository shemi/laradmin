@component('laradmin::formFields.field', ['field' => $field])

    @php
        $options = json_encode($field->options, JSON_UNESCAPED_UNICODE);

        $closeOnSelect = $field->getTemplateOption('multiselect.closeOnSelect', 'true');
        $closeOnSelect = is_string($closeOnSelect) ? $closeOnSelect : ($closeOnSelect ? 'true' : 'false');

        $clearOnSelect = $field->getTemplateOption('multiselect.clearOnSelect', 'true');
        $clearOnSelect = is_string($clearOnSelect) ? $clearOnSelect : ($clearOnSelect ? 'true' : 'false');

        $hideSelected = $field->getTemplateOption('multiselect.hideSelected', 'true');
        $hideSelected = is_string($hideSelected) ? $hideSelected : ($hideSelected ? 'true' : 'false');

        $preserveSearch = $field->getTemplateOption('multiselect.preserveSearch', 'true');
        $preserveSearch = is_string($preserveSearch) ? $preserveSearch : ($preserveSearch ? 'true' : 'false');

        $properties = [
            "v-model=".$field->form_prefix.$field->key,
            ":multiple=true",
            ":options='".$options."'",
            ":close-on-select=".$closeOnSelect,
            ":clear-on-select=".$clearOnSelect,
            ":hide-selected=".$hideSelected,
            ":preserve-search=".$preserveSearch,
            "label=label",
            "track-by=key"
        ];

        if($field->icon) {
            $properties[] = "icon=".$field->icon;
        }

        if($field->placeholder) {
            $properties[] = "placeholder='".$field->placeholder."'";
        }


    @endphp

    <multiselect {!! implode(' ', $properties) !!}>
        <template slot="tag" scope="props">
            <span class="tag is-primary">
                <span>@{{ props.option.label }}</span>
                <button class="delete is-small"
                        type="button"
                        @click="props.remove(props.option)"></button>
            </span>
        </template>
    </multiselect>

@endcomponent