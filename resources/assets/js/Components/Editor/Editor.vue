<template>

    <div class="la-editor">

        <tinymce v-model="newValue"
                 :id="id"
                 @input="$emit('input', newValue)"
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
                        'autoresize advlist autolink lists link image preview hr anchor pagebreak',
                        'searchreplace wordcount code fullscreen',
                        'insertdatetime media nonbreaking table contextmenu directionality',
                        'template paste textcolor colorpicker textpattern toc emoticons hr codesample'
                    ];
                }
            },
            toolbar1: {
                type: String,
                default: 'formatselect  | bold italic  strikethrough  forecolor backcolor | link | image media | numlist bullist outdent indent | codesample code | searchreplace'
            },

            toolbar2: {
                type: String,
                default: 'undo redo | ltr rtl | alignleft aligncenter alignright alignjustify | insertdatetime | removeformat | fullscreen | template'
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
            console.log(this.otherOptions);
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