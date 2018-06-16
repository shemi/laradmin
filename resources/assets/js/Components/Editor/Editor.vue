<template>

    <div class="la-editor">

        <tinymce v-model="newValue"
                 :id="id"
                 :plugins="plugins"
                 :toolbar1="toolbar1"
                 :toolbar2="toolbar2"
                 :other_options="otherOptions"
                 :readonly="readonly"
        >
        </tinymce>

    </div>

</template>

<script>
    import tinymce from './Tinymce';

    export default {

        name: 'la-editor',

        props: {
            value: {
                type: [String],
                required: true
            },
            id: {
                type: String,
                required: true
            },
            plugins: {
                type: Array,
                default: function() {
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
            otherOptions: {
                type: Object,
                default: function () {
                    return {};
                }
            },
            readonly: {default: false, type: Boolean}
        },

        data() {
            return {
                newValue: this.value
            }
        },

        watch: {
            newValue(newValue) {
                this.$emit('value', newValue);
            },
        },

        components: {
            tinymce
        }

    }

</script>