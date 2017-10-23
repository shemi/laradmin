@component('laradmin::formFields.field', ['field' => $field])

    @php
        $properties = [
            "v-model=".$field->form_prefix.$field->key,
            "label='".$field->getTemplateOption('items_label', $field->label)."'",
            "label-singular='".str_singular($field->getTemplateOption('item_label', $field->label))."'",
            "query-uri=".route('laradmin.relationship.query', ['typeSlug' => $type->slug, 'fieldKey' => $field->key]),
            "create-uri=".route('laradmin.relationship.create', ['typeSlug' => $type->slug, 'fieldKey' => $field->key]),
            "create-key=".$field->relation_labels[0],
            ":form.sync=".trim($field->form_prefix, '.')
        ];

        if($field->icon) {
            $properties[] = "icon=".$field->icon;
        }

        if($field->placeholder) {
            $properties[] = "placeholder='".$field->placeholder."'";
        }


    @endphp

    <tags-field @foreach($properties as $property){!!  ' '.$property  !!}@endforeach>

    </tags-field>

@endcomponent