@component('laradmin::formFields.field', ['field' => $field])

    @php
        $options = json_encode($field->options, JSON_UNESCAPED_UNICODE);

        $properties = [
            "v-model=form.".$field->key,
            ":multiple=true",
            ":options='".$options."'",
            ":close-on-select=".$field->getTemplateOption('multiselect.clearOnSelect', 'true'),
            ":clear-on-select=".$field->getTemplateOption('multiselect.clearOnSelect', 'false'),
            ":hide-selected=".$field->getTemplateOption('multiselect.hideSelected', 'true'),
            ":preserve-search=".$field->getTemplateOption('multiselect.preserveSearch', 'true'),
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

    <multiselect @foreach($properties as $property){!!  ' '.$property  !!}@endforeach>
        <template slot="tag" scope="props">
            <span class="tag is-primary">
                <span>@{{ props.option.label }}</span>
                <button class="delete is-small" @click="props.remove(props.option)"></button>
            </span>
        </template>
    </multiselect>

@endcomponent