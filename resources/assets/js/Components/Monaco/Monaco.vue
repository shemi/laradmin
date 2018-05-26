<script>
    import Loader from './Loader';
    import Helpers from '../../Helpers/Helpers';

    let monacoSchemasLoaded = false;

    export default {
        name: 'MonacoEditor',

        props: {
            value: String,
            theme: {
                type: String,
                default: 'vs-dark'
            },
            instId: {
                type: String,
                default: () => Helpers.makeId()
            },
            fileName: String,
            language: String,
            options: Object,
            jsonSchema: {
                type: Object,
                default: () => {}
            },
            placeholder: null
        },

        data() {
            return {
                editorLoaded: false,
                filePath: `${this.instId}/${this.fileName}`,
                model: null,
                editor: null
            }
        },

        watch: {
            options: {
                deep: true,
                handler(options) {
                    if (this.editor) {
                        this.editor.updateOptions(options)
                    }
                }
            },

            jsonSchema: {
                deep: true,
                handler() {
                    if (this.editor) {
                        this.setJsonSchema();
                    }
                }
            },

            value(newValue) {
                if (this.editor) {
                    if (newValue !== this.editor.getValue()) {
                        this.editor.setValue(newValue)
                    }
                }
            },

            language(newVal) {
                if (this.editor) {
                    window.monaco.editor.setModelLanguage(this.editor.getModel(), newVal)
                }
            },

            theme(newVal) {
                if (this.editor) {
                    window.monaco.editor.setTheme(newVal)
                }
            },

            fileName(newVale) {
                this.model && this.model.dispose();
                this.editor && this.editor.dispose();
                this.filePath = `${this.instId}/${this.fileName}`;

                this.$nextTick(() => {
                    this.init();
                });
            }

        },

        mounted() {
            Loader.load().then(() => {
                this.loadJsonSchemas();
                this.init();
            });
        },

        beforeDestroy() {
            this.model && this.model.dispose();
            this.editor && this.editor.dispose();
        },

        methods: {
            init() {
                const options = {
                    automaticLayout: true,
                    value: this.value,
                    theme: this.theme,
                    language: this.language,
                    ...this.options
                };

                if(this.instId && this.fileName) {
                    try {
                        options.model = window.monaco.editor.createModel(this.value, this.language, this.filePath);
                    } catch (err) {

                    }
                }

                this.editorLoaded = true;

                this.editor = window.monaco.editor.create(this.$el, options);
                this.$emit('editorMount', this.editor);

                this.model = this.editor.getModel();

                this.editor.onContextMenu(event => this.$emit('contextMenu', event));
                this.editor.onDidBlurEditor(() => this.$emit('blur'));
                this.editor.onDidBlurEditorText(() => this.$emit('blurText'));

                this.editor.onDidChangeConfiguration(event =>
                    this.$emit('configuration', event)
                );

                this.editor.onDidChangeCursorPosition(event =>
                    this.$emit('position', event)
                );

                this.editor.onDidChangeCursorSelection(event =>
                    this.$emit('selection', event)
                );

                this.editor.onDidChangeModel(event => this.$emit('model', event));

                this.editor.onDidChangeModelContent(event => {
                    const value = this.editor.getValue();

                    if (this.value !== value) {
                        this.$nextTick(() => {
                            this.$emit('input', value);
                        });
                    }
                });

                this.editor.onDidChangeModelDecorations(event => {
                    this.$emit('modelDecorations', event);

                    this.$nextTick(() => {
                        this.checkForErrors();
                    });
                });

                this.editor.onDidChangeModelLanguage(event =>
                    this.$emit('modelLanguage', event)
                );

                this.editor.onDidChangeModelOptions(event =>
                    this.$emit('modelOptions', event)
                );

                this.editor.onDidDispose(event => this.$emit('afterDispose', event));
                this.editor.onDidFocusEditor(() => this.$emit('focus'));
                this.editor.onDidFocusEditorText(() => this.$emit('focusText'));
                this.editor.onDidLayoutChange(event => this.$emit('layout', event));
                this.editor.onDidScrollChange(event => this.$emit('scroll', event));
                this.editor.onKeyDown(event => this.$emit('keydown', event));
                this.editor.onKeyUp(event => this.$emit('keyup', event));
                this.editor.onMouseDown(event => this.$emit('mouseDown', event));
                this.editor.onMouseLeave(event => this.$emit('mouseLeave', event));
                this.editor.onMouseMove(event => this.$emit('mouseMove', event));
                this.editor.onMouseUp(event => this.$emit('mouseUp', event));
            },

            checkForErrors() {
                let model = this.model,
                    errors = window.monaco.editor.getModelMarkers({model}),
                    set = [];

                for(let error of errors) {
                    if(error.resource === this.filePath) {
                        set.push(error);
                    }
                }

                this.$emit('has-errors', set.length > 0);

                return set.length > 0;
            },

            loadJsonSchemas() {
                let data = window.laradmin.builderData,
                    schemas = [],
                    type;

                if(! data || monacoSchemasLoaded) {
                    return;
                }

                for(type in data) {
                    this.getJsonSchemas(type, data[type], schemas);
                }


                window.monaco.languages.json.jsonDefaults.setDiagnosticsOptions({
                    validate: true,
                    schemas: schemas
                });

                monacoSchemasLoaded = true;
            },

            getJsonSchemas(type, data, bucket = []) {
                let schemaKey;

                if(! type || ! data) {
                    return;
                }

                for (schemaKey in data) {
                    if(! data[schemaKey]['schema']) {
                        continue;
                    }

                    bucket.push({
                        uri: `http://myserver/${type}-${schemaKey}`,
                        fileMatch: [`*/${type}-${schemaKey}`],
                        schema: data[schemaKey]['schema']
                    });
                }

            },

            getMonaco() {
                return this.editor
            },

            focus() {
                this.editor.focus()
            }
        },

        render(h) {
            return h('div', null, [this.editorLoaded ? null : this.placeholder])
        }
    }
</script>