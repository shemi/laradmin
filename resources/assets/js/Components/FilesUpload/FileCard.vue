<template>
    <div class="file-card" :class="'is-' + file.status">
        <div class="file-card-icon">
            <b-icon :icon="icon" size="is-large"></b-icon>
        </div>

        <div class="file-progress">
            <progress class="progress" :value="file.progress" max="100">
                {{ file.progress }}%
            </progress>
        </div>

        <div class="file-original-name">
            <span class="name">{{ file.name }}</span>

        </div>

        <div class="file-actions" v-if="showActions">
            <button class="button">
                <b-icon icon="trash"></b-icon>
            </button>

            <button class="button">
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

            }
        },

        methods: {

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
                const ext = this.file.name.split('.').pop();

                return extensions[ext] || icons.file;
            },

            showActions() {
                return this.file.status === 'success' || this.file.hashName;
            },

            hasErrors() {
                console.log(this.file.xhrResponse);

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
            }
        }

    }

</script>