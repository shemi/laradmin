<template>
    <div>
        <textarea :id="id">{{ content }}</textarea>
    </div>
</template>

<script>
    // Import TinyMCE
    import tinymce from 'tinymce/tinymce';

    tinymce.baseURL = window.laradmin.public_path+'/tinymce';

    export default {
        name: 'tinymce',

        props: {
            id: {
                type: String,
                required: true
            },
            htmlClass: {
                default: '',
                type: String
            },
            value: {
                default: ''
            },
            plugins: {
                type: Array,
                default: function () {
                    return [
                        'advlist autolink lists link image charmap print preview hr anchor pagebreak',
                        'searchreplace wordcount visualblocks visualchars code fullscreen',
                        'insertdatetime media nonbreaking save table contextmenu directionality',
                        'template paste textcolor colorpicker textpattern imagetools toc help emoticons hr codesample'
                    ];
                }
            },
            toolbar1: {
                type: String,
                default: 'formatselect | bold italic  strikethrough  forecolor backcolor | link | alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent  | removeformat'
            },
            toolbar2: {
                type: String,
                default: ''
            },
            other_options: {
                type: Object,
                default: function () {
                    return {};
                }
            },
            readonly: {
                default: false,
                type: Boolean
            }
        },

        data() {
            return {
                content: '',
                editor: null,
                cTinyMce: null,
                checkerTimeout: null,
                isTyping: false
            };
        },

        mounted() {
            this.content = this.value;
            this.init();
        },

        beforeDestroy() {
            this.editor.destroy();
        },

        watch: {
            value: function (newValue) {
                if (!this.isTyping) {
                    if (this.editor !== null) {
                        this.editor.setContent(newValue);
                    } else {
                        this.content = newValue;
                    }
                }
            },

            readonly(value) {
                if (value) {
                    this.editor.setMode('readonly');
                } else {
                    this.editor.setMode('design');
                }
            }
        },

        methods: {
            init() {
                let options = {
                    selector: '#' + this.id,
                    skin: true,
                    toolbar1: this.toolbar1,
                    themes: "modern",
                    toolbar2: this.toolbar2,
                    plugins: this.plugins,
                    init_instance_callback: this.initEditor,
                    skin_url:  (window.laradmin.public_path+'/tinymce/skins/lightgray').replace("\/\/", "\/"),
                    branding: false,
                    file_picker_types: 'image',
                    file_picker_callback(cb, value, meta) {
                        let input = document.createElement('input');
                        input.setAttribute('type', 'file');
                        input.setAttribute('accept', 'image/*');

                        input.onchange = function() {
                            let file = this.files[0];
                            let reader = new FileReader();

                            reader.onload = () => {
                                let id = 'blobid' + (new Date()).getTime(),
                                    blobCache =  tinymce.activeEditor.editorUpload.blobCache,
                                    base64 = reader.result.split(',')[1],
                                    blobInfo = blobCache.create(id, file, base64);

                                blobCache.add(blobInfo);
                                cb(blobInfo.blobUri(), { title: file.name });
                            };

                            reader.readAsDataURL(file);
                        };

                        input.click();
                    }
                };

                console.log(this.concatAssciativeArrays(options, this.other_options));

                tinymce.init(this.concatAssciativeArrays(options, this.other_options));
            },

            initEditor(editor) {
                this.editor = editor;

                editor.on('KeyUp', (e) => {
                    this.submitNewContent();
                });

                editor.on('Change', (e) => {
                    if (this.editor.getContent() !== this.value) {
                        this.submitNewContent();
                    }

                    this.$emit('editorChange', e);
                });

                editor.on('init', (e) => {
                    editor.setContent(this.content);
                    this.$emit('input', this.content);
                });

                if (this.readonly) {
                    this.editor.setMode('readonly');
                } else {
                    this.editor.setMode('design');
                }

                this.$emit('editorInit', editor);
            },

            concatAssciativeArrays(array1, array2) {
                if (array2.length === 0) {
                    return array1;
                }

                if (array1.length === 0) {
                    return array2;
                }

                let dest = [];

                for (let key in array1) {
                    dest[key] = array1[key];
                }

                for (let key in array2){
                    dest[key] = array2[key];
                }

                return dest;
            },

            submitNewContent() {
                this.isTyping = true;

                if (this.checkerTimeout !== null) {
                    clearTimeout(this.checkerTimeout);
                }

                this.checkerTimeout = setTimeout(() => {
                    this.isTyping = false;
                }, 300);

                this.$emit('input', this.editor.getContent());
            }
        }
    }
</script>