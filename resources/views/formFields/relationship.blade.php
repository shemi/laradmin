@component('laradmin::formFields.field', ['field' => $field])

    @php
        $properties = [
            "v-model=".$field->form_prefix.$field->key,
            "label='".$field->getTemplateOption('items_label', $field->label)."'",
            "label-singular='".str_singular($field->getTemplateOption('item_label', $field->label))."'",
            "query-uri=".route('laradmin.relationship.query', ['typeSlug' => $type->slug, 'fieldKey' => $field->key]),
            ":form.sync=".trim($field->form_prefix, '.')
        ];


        if($field->has_relationship_type) {
            $relationType = $field->relationship_type;

            if($user->can("update {$relationType->slug}")) {
                $properties[] = "show-edit-button";
            }

            if($user->can("create {$relationType->slug}")) {
                $properties[] = "show-create-button";
            }

            $properties[] = "create-button-link=".route("laradmin.{$relationType->slug}.create");
        }
    @endphp

    <la-relationship {!! implode(' ', $properties) !!}>

    </la-relationship>
@endcomponent
