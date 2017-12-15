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
            },
            'onEditable': {
                type: Function,
                default: node => true
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
            },
            schema: {
                handler: function (value) {
                    this.setSchema(value);
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
                    onEditable: this.onEditable,
                    schema: this.schema
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

            setSchema(schema) {
                this.editor.setSchema(schema);
            },

            onEditorChange() {
                try {
                    this.newValue = this.editor.get();
                } catch (e) {
                    return false;
                }

                this.$emit('input', this.newValue);
                this.selfChange = true;
            }

        }

    }

</script>