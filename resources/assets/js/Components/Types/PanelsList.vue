<template>

    <div class="la-panel-list">
        <div>

            <vddl-list class="la-panels-list-container"
                       :list="panels"
                       :allowed-types="['panels']"
                       :drop="handleDrop"
                       :horizontal="false">

                <vddl-draggable v-for="(panel, index) in panels"
                                :key="panel.id"
                                :draggable="panel"
                                :index="index"
                                :wrapper="panels"
                                type="panels"
                                :moved="handleMoved"
                                class="la-panels-list-item"
                                effect-allowed="move">

                    <la-panel :form-key="formKey + '.' + index"
                              @clone="clonePanel(index, panel)"
                              @delete="deletePanel(index, panel)"
                              v-model="panels[index]">
                    </la-panel>

                </vddl-draggable>
            </vddl-list>

            <vddl-placeholder>
                <div class="placeholder-panel">
                    Drop Here
                </div>
            </vddl-placeholder>

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
            createPanel(type, name = null) {
                let panelStructure = cloneDeep(this.builderData.panels[type]['structure']);
                panelStructure.id = Helpers.makeId();

                if(name) {
                    panelStructure.name = name;
                }

                this.form[this.formKey]['push'](panelStructure);
            },

            input(value) {
                this.panels = value;
                this.$emit('input', value);
            },

            clonePanel(index, panel) {
                let clone = cloneDeep(panel);

                clone.id = Helpers.makeId();
                clone.title = `${clone.title} (CLONE)`;

                for(let field of clone.fields) {
                    field.id = Helpers.makeId();
                }

                this.panels.splice(index + 1, 0, clone);
                this.input(this.panels);
            },

            deletePanel(index, panel) {
                this.$delete(this.panels, index);
                this.input(this.panels);
            },

            handleDrop(data) {
                const { index, list, item } = data;

                this.$set(item, 'id', Helpers.makeId());
                list.splice(index, 0, item);
            },

            handleMoved(item) {
                const { index, list } = item;
                list.splice(index, 1);
            }
        },

        components: {
            LaPanel
        }

    }

</script>