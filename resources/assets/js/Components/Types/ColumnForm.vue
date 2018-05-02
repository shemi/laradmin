<template>

    <div class="modal-card la-column-form" style="width: auto">
        <header class="modal-card-head">
            <p class="modal-card-title">Edit {{ label }} Column</p>
        </header>
        <section class="modal-card-body">

            <json-editor v-model="editorObject"
                         @input="updateValueFromEditorObject"
                         :schema="schema">
            </json-editor>

        </section>
        <footer class="modal-card-foot">
            <button class="button" type="button" @click="$parent.close()">Cancel</button>
            <button class="button is-primary" @click.prevent="save">Save</button>
        </footer>
    </div>

</template>

<script>
    import {cloneDeep} from 'lodash';
    import Helpers from '../../Helpers/Helpers';
    import JsonEditor from '../JsonEditor/JsonEditor.vue';

    export default {
        name: 'la-column-form',

        props: {
            value: Object
        },

        data() {
            return {
                newValue: cloneDeep(this.value),
                editorObject: {},
                schema: {}
            }
        },

        mounted() {
            this.updateEditorObject();
        },

        watch: {
            value(newValue) {
                this.newValue = newValue;
            }
        },

        methods: {
            updateEditorObject() {
                this.$nextTick(function () {
                    this.editorObject = cloneDeep(this.newValue.browse_settings);
                });
            },

            updateValueFromEditorObject(object) {
                this.$set(this.newValue, 'browse_settings', object);
            },

            save() {
                this.$emit('update', this.newValue.browse_settings);
                this.$parent.close();
            }
        },

        computed: {
            label() {
                return this.newValue.browse_settings.label || this.newValue.label;
            },
        },

        components: {
            JsonEditor
        }

    }

</script>