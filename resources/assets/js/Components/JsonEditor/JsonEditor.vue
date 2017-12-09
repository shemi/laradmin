<template>

    <div class="la-json-editor" style="height: 600px">

    </div>

</template>

<script>
    import JSONEditor from 'jsoneditor';

    export default {

        props: {
            'value': [Array, Object],
            'schema': {
                type: Object,
                default: {}
            }
        },

        data() {
            return {
                newValue: this.value,
                selfChange: false,
                editor: null
            }
        },

        watch: {
            value: {
                handler: function (value) {
                    this.setJson(value);
                },
                deep: true,
                immediate: false
            }
        },

        mounted() {
            this.initEditor();
        },

        methods: {
            initEditor() {
                let options = {
                    modes: [],
                    mode: 'tree',
                    name: 'field',
                    onChange: this.onEditorChange.bind(this),
                    schema: window.laradmin.schemas.input.coolSchema
                };

                this.editor = new JSONEditor(this.$el, options, {});
                this.setJson(this.newValue);
            },

            setJson(json) {
                if (!this.selfChange) {
                    this.editor.set(json);
                }

                this.selfChange = false;
            },

            onEditorChange() {
                this.newValue = this.editor.get();
                this.$emit('input', this.newValue);
                this.selfChange = true;
            }

        }

    }

</script>