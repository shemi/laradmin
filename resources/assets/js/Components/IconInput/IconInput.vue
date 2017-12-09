<template>

    <b-field>
        <p class="control icon-only-addon">
            <b-icon :icon="newValue"></b-icon>
        </p>
        <b-input type="text"
                 expanded
                 v-model="newValue">
        </b-input>
        <p class="control">
            <button type="button"
                    @click="openIconSelectModal"
                    class="button is-primary">
                <b-icon icon="smile-o"></b-icon>
            </button>
        </p>

        <icon-select-modal :active.sync="isIconSelectModalActive"
                           :selected-icon.sync="newValue">
        </icon-select-modal>
    </b-field>

</template>

<script>
    import IconSelectModal from '../IconSelectModal/IconSelectModal.vue';

    export default {
        name: 'la-icon-input',

        props: [
            'value'
        ],

        data() {
            return {
                isIconSelectModalActive: false,
                newValue: this.value
            }
        },

        watch: {

            value(value) {
                this.newValue = value;
            },

            newValue(value) {
                this.$emit('input', value);
            }
        },

        methods: {
            openIconSelectModal() {
                this.isIconSelectModalActive = true;
            }
        },

        components: {
            IconSelectModal
        }

    }

</script>