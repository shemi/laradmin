<template>

    <div class="la-panel-list">
        <div>

            <la-panel v-for="(panel, index) in panels"
                      :form-key="formKey+'.'+index"
                      :panel="panel"
                      :key="panel.id">
            </la-panel>

        </div>
    </div>

</template>

<script>
    import LaPanel from './Panel.vue';
    import ParentFormMixin from '../../Mixins/ParentForm';
    import Helpers from '../../Helpers/Helpers';

    export default {
        name: 'la-panels-list',

        props: {
            'panels': Array,
            'formKey': String
        },

        mixins: [ParentFormMixin],

        data() {
            return {

            }
        },

        methods: {
            createPanel() {
                let panelSchema = JSON.parse(JSON.stringify(window.laradmin.schemas.panel.schema));
                panelSchema.id = Helpers.makeId();

                this.form[this.formKey]['push'](panelSchema);
            }
        },

        components: {
            LaPanel
        }

    }

</script>