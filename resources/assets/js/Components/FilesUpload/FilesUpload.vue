<template>

    <div class="la-files-upload">

        <div class="columns is-multiline is-mobile">

            <div class="column is-one-quarter" v-for="file in files">
                <la-file-card :file="file"></la-file-card>
            </div>

            <div class="column is-one-quarter">
                <vue-clip :options="options"
                          :on-complete="complete"
                          :on-added-file="addedFile">
                    <template slot="clip-uploader-action">
                        <div class="file is-boxed">
                            <div class="file-label dz-message">
                                <span class="file-cta">
                                  <span class="file-icon">
                                    <b-icon icon="upload"></b-icon>
                                  </span>
                                  <span class="file-label">
                                    Choose a file…
                                  </span>
                                </span>
                            </div>
                        </div>
                    </template>
                </vue-clip>
            </div>

        </div>

    </div>

</template>

<script>

    import LaFileCard from './FileCard.vue';

    const token = document.head.querySelector('meta[name="csrf-token"]');

    export default {

        props: {
            value: {
                type: Array,
                required: true
            },
            form: {
                type: Object,
                required: true
            },
            formKey: {
                type: String,
                required: true
            }
        },

        data() {
            return {
                options: {
                    'url': window.laradmin.routs.upload,
                    'headers': {
                        'X-CSRF-TOKEN': token.content
                    },
                    'params': {
                        'field_form_key': this.formKey
                    }
                },
                files: []
            }
        },

        created() {

        },

        watch: {
            files(newVal, oldVal) {
                console.log(newVal);
            }
        },

        methods: {

            addedFile (file) {
                this.files.push(file);
            },

            complete (file, status, xhr) {
                let self = this;

                if(status === 'error') {
                    setTimeout(function() {
                        self.deleteFile(file);
                    }, 5000);

                    return;
                }
                
                let data = JSON.parse(xhr.response);
                file.customAttributes = data.data;
                delete file.dataUrl;
                this.value.push(file);
            },

            deleteFile(file) {
                for (let fileIndex in this.files) {
                    if(this.files[fileIndex] === file) {
                        this.$delete(this.files, fileIndex);
                    }
                }
            }

        },

        computed: {

        },

        components: {
            LaFileCard
        }

    }

</script>