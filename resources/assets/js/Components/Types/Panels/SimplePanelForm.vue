<template>

    <div :class="classList">

        <b-field :type="hasErrors('title') ? 'is-danger' : ''"
                 :messag="errorMessage('title')"
                 label="Title">
            <b-input v-model="panel.title"
                     @input="input($event, 'title')"
                     type="text"></b-input>
        </b-field>

        <b-field :type="hasErrors('has_container') ? 'is-danger' : ''"
                 :messag="errorMessage('has_container')">
            <b-switch v-model="panel.has_container"
                      @input="input($event, 'has_container')">
                {{ panel.has_container ? 'Has Container' : 'No Container' }}
            </b-switch>
        </b-field>

        <b-field :type="hasErrors('fields') ? 'is-danger' : ''"
                 :messag="errorMessage('fields')"
                 class="field-la-fields-list"
                 label="fields">

            <la-fields-list v-model="panel.fields"
                            @input="input($event, 'fields')"
                            @has-errors="$emit('has-errors', $event)"
                            :form-key="fieldKey('fields')">

            </la-fields-list>

        </b-field>

    </div>

</template>

<script>
    import PanelFormMixin from '../../../Mixins/PanelFormMixin';

    export default {

        name: 'la-simple-panel-form',

        mixins: [PanelFormMixin]

    }

</script>