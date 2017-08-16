<template>

    <div class="la-files-upload">

        <vddl-list class="columns is-multiline is-mobile"
                   :list="value"
                   :horizontal="true">

            <vddl-draggable class="column is-one-quarter"
                 v-for="(file, index) in value"
                 :key="file.customAttributes.id || file.customAttributes.temp_id"
                 :draggable="file"
                 :index="index"
                 :wrapper="value"
                 effect-allowed="move">

                <la-file-card :file="file" @on-delete="deleteFile"></la-file-card>

            </vddl-draggable>

            <div class="column is-one-quarter">
                <vue-clip :options="options"
                          :on-complete="complete"
                          :on-queue-complete="queueComplete"
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

            <vddl-placeholder class="column is-one-quarter drag-placeholder">
                <div>
                    <span>Move Hear</span>
                </div>
            </vddl-placeholder>

        </vddl-list>

    </div>

</template>

<script>

    import LaFileCard from './FileCard.vue';
    import File from 'vue-clip/src/File';
    import Helpers from '../../Helpers/Helpers';

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
                }
            }
        },

        created() {
            for(let fileIndex in this.value) {
                let file = this.value[fileIndex],
                    fileModel = new File({
                        status: 'exists',
                        name: file.name,
                        upload: {},
                        type: '',
                        size: file.size
                    });

                fileModel.customAttributes = {
                    alt: file.alt,
                    caption: file.caption,
                    ext: file.ext,
                    id: file.id
                };

                this.value[fileIndex] = fileModel;
            }
        },

        methods: {
            addedFile (file) {
                file.customAttributes.temp_id = Helpers.makeId(20);

                this.value.push(file);
            },

            complete (file, status, xhr) {
                if(status === 'error') {
                    setTimeout(function() {
                        this.deleteFile(file);
                    }.bind(this), 1500);

                    return;
                }
                
                let data = JSON.parse(xhr.response);
                file.customAttributes = data.data;
                delete file.dataUrl;
            },

            queueComplete() {

            },

            deleteFile(file, sync = true) {
                for (let fileIndex in this.value) {
                    if(this.value[fileIndex] === file) {
                        this.$delete(this.value, fileIndex);
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