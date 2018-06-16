@component('laradmin::formFields.field', ['field' => $field])

    @php
        $properties = [
            "v-model=".$field->form_prefix.$field->key,
            "id=".$field->id,
            ":readonly=". ($field->read_only ? 'true' : 'false')
        ];

        $plugins = $field->getTemplateOption('mce.editor');

        if($plugins && is_array($plugins)) {
            $properties[] = ':plugins=\''.attr_json_encode($plugins).'\'';
        }

        $toolbar1 = $field->getTemplateOption('mce.toolbar1');

        if($toolbar1 && is_array($toolbar1)) {
            $properties[] = ':toolbar1="'.implode(" | ", $toolbar1).'"';
        }

        $toolbar2 = $field->getTemplateOption('mce.toolbar2');

        if($toolbar2 && is_array($toolbar2)) {
            $properties[] = ':toolbar2="'.implode(" | ", $toolbar2).'"';
        }

        $otherOptions = $field->getTemplateOption('mce.otherOptions');

        if($otherOptions && is_array($otherOptions)) {
            $properties[] = ':otherOptions=\''.attr_json_encode($otherOptions).'\'';
        }

    @endphp

    <la-editor {!! implode(' ', $properties) !!}></la-editor>

@endcomponent