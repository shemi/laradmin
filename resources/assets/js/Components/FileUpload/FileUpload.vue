<template>

    <div class="la-file-upload">

        <vue-clip :options="options"
                  :on-complete="complete"
                  :on-queue-complete="queueComplete"
                  ref="vc"
                  v-show="! newFile && ! value"
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

        <la-file-card v-if="newFile || value"
                       @on-delete="deleteFile"
                       :file="newFile || value">
        </la-file-card>

    </div>

</template>

<script>

    import File from 'vue-clip/src/File';
    import Helpers from '../../Helpers/Helpers';
    import LaFileCard from './FileCard.vue';

    const token = document.head.querySelector('meta[name="csrf-token"]');

    export default {

        props: {
            value: {
                type: [Object],
                required: false
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
                newFile: null,

                options: {
                    'url': window.laradmin.routs.upload,
                    'maxFiles': 1,
                    'headers': {
                        'X-CSRF-TOKEN': token.content
                    },
                    'params': {
                        'field_form_key': this.formKey
                    }
                }
            }
        },

        created() {

        },

        methods: {
            addedFile (file) {
                this.newFile = null;
                file.customAttributes.temp_id = Helpers.makeId(20);

                this.$nextTick(function() {
                    this.newFile = file;
                });
            },

            complete (file, status, xhr) {
                if(status === 'error') {
                    setTimeout(function() {
                        this.deleteFile(file);
                    }.bind(this), 1500);

                    return;
                }
                
                let data = JSON.parse(xhr.response);
                delete file.dataUrl;
                this.newFile.customAttributes = Object.assign(this.newFile.customAttributes, data.data);

                this.$nextTick(function() {
                    this.$emit('input', this.newFile);
                    this.newFile = null;
                });
            },

            queueComplete() {
                this.newFile = null;
            },

            deleteFile(file) {
                try {
                    this.$refs.vc.removeFile(file);
                } catch (err) {}

                this.newFile = null;
                this.$emit('input', null);
            }

        },

        computed: {

        },

        components: {
            LaFileCard
        }

    }

</script>