<template>
    <div class="file-card" :class="'is-' + file.status">
        <div class="file-card-icon">
            <b-icon :icon="icon" size="is-large"></b-icon>
        </div>

        <div class="file-progress" v-if="showProgress">
            <progress class="progress" :value="file.progress" max="100">
                {{ file.progress }}%
            </progress>
        </div>

        <div class="file-info-block has-text-centered" v-else>
            <span class="tag is-primary">
                {{ size }}
            </span>
        </div>

        <div class="file-original-name">
            <span class="name">{{ file.name }}</span>
        </div>

        <div class="file-actions" v-if="showActions">
            <button type="button" class="button" @click.stop="deleteFile">
                <b-icon icon="trash"></b-icon>
            </button>

            <button type="button" class="button" @click.stop="isEditModalOpen = true">
                <b-icon icon="edit"></b-icon>
            </button>
        </div>

        <div class="errors" v-if="hasErrors">

            <p class="server-error" v-if="file.xhrResponse.responseText">
                {{ serverResponseErrorMessage }}
            </p>
            <p class="client-error" v-else>
                {{ file.errorMessage }}
            </p>

        </div>

        <vddl-nodrag class="nodrag">
            <b-modal :active.sync="isEditModalOpen" has-modal-card>
                <div class="modal-card">
                    <header class="modal-card-head">
                        <p class="modal-card-title">Edit File</p>
                    </header>
                    <section class="modal-card-body">
                        <b-field label="Name">
                            <b-input type="text"
                                     v-model="file.name"
                                     placeholder="File Display Name">
                            </b-input>
                        </b-field>

                        <b-field label="ALT">
                            <b-input type="text"
                                     v-model="file.customAttributes.alt"
                                     placeholder="Alternative Text">
                            </b-input>
                        </b-field>

                        <b-field label="Caption">
                            <b-input type="textarea"
                                    v-model="file.customAttributes.caption">
                            </b-input>
                        </b-field>
                    </section>

                    <footer class="modal-card-foot">
                        <button class="button" type="button" @click="isEditModalOpen = false">
                            Close and save
                        </button>
                    </footer>
                </div>
            </b-modal>
        </vddl-nodrag>

    </div>
</template>

<script>

    import {icons, extensions} from './icons';

    export default {

        props: {
            file: {
                type: Object,
                required: true
            }
        },

        data() {
            return {
                isEditModalOpen: false,
            }
        },

        methods: {

            deleteFile() {
                this.$emit('on-delete', this.file);
            }

        },

        computed: {
            size() {
                const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'],
                      bytes = this.file.size;
                let i;

                if (bytes === 0) {
                    return 'n/a';
                }

                i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));

                if (i === 0) {
                    return bytes + ' ' + sizes[i];
                }

                return (bytes / Math.pow(1024, i)).toFixed(2) + ' ' + sizes[i];
            },

            icon() {
                const ext = this.file.customAttributes.ext || this.file.name.split('.').pop();

                return extensions[ext] || icons.file;
            },

            showActions() {
                return this.file.status === 'success' ||
                       this.file.customAttributes.id;
            },

            hasErrors() {
                return this.file.status === 'error';
            },

            serverResponseErrorMessage() {
                let res = this.file.xhrResponse;
                let message = `Server respond with ${res.statusCode} status code.`;

                try {
                    let messages = JSON.parse(res.responseText);
                    message = messages.file[0] || message;
                } catch (e) {

                }

                return message;
            },

            showProgress() {
                return this.file.status === 'queued' || this.file.status === 'added';
            }
        }

    }

</script>