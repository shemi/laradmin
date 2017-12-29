<template>

    <div class="la-panel-list">
        <div>

            <la-panel v-for="(panel, index) in panels"
                      :form-key="formKey + '.' + index"
                      :panel="panel"
                      :key="panel.id">
            </la-panel>

        </div>
    </div>

</template>

<script>
    import { cloneDeep } from 'lodash';
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
                builderData: window.laradmin.builderData
            }
        },

        methods: {
            createPanel() {
                let panelStructure = cloneDeep(this.builderData.panels.panel.structure);
                panelStructure.id = Helpers.makeId();

                this.form[this.formKey]['push'](panelStructure);
            }
        },

        components: {
            LaPanel
        }

    }

</script>