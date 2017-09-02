<template>
    <div class="image-card" :class="'is-' + file.status">
        <div class="image-holder">


            <div class="image is-square ">
                <img :src="src" :alt="alt" v-if="src">
                <b-loading :active="! src" class="image-loader"></b-loading>
            </div>


            <div class="image-progress" v-if="showProgress">
                <progress class="progress" :value="file.progress" max="100">
                    {{ file.progress }}%
                </progress>
            </div>


            <div class="image-overlay" v-if="showOverlay">
                <div class="file-original-name">
                    <span class="name">{{ file.name }}</span>
                </div>

                <div class="file-info-block has-text-centered">
                    <span class="tag is-primary">
                        {{ size }}
                    </span>
                </div>

                <div class="file-actions">
                    <button type="button" class="button" @click.stop="deleteFile">
                        <b-icon icon="trash"></b-icon>
                    </button>
                </div>
            </div>

        </div>

        <div class="errors" v-if="hasErrors">

            <p class="server-error" v-if="file.xhrResponse.responseText">
                {{ serverResponseErrorMessage }}
            </p>
            <p class="client-error" v-else>
                {{ file.errorMessage }}
            </p>

        </div>

    </div>
</template>

<script>

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

            showOverlay() {
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
            },

            src() {
                return this.file.customAttributes.uri;
            },

            alt() {
                return this.file.customAttributes.alt;
            }
        }

    }

</script>