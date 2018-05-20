<template>

    <form action="" @submit.prevent="save">
        <div class="modal-card" style="width: auto">
            <header class="modal-card-head">
                <p class="modal-card-title">Tab</p>
            </header>
            <section class="modal-card-body">
                <b-field label="Title">
                    <b-input type="text" v-model="newTab.title" placeholder="The Tab Title" required></b-input>
                </b-field>

                <b-field label="Icon">
                    <la-icon-input v-model="newTab.icon"></la-icon-input>
                </b-field>

            </section>
            <footer class="modal-card-foot">
                <button class="button" type="button" @click="$emit('close')">Close</button>
                <button class="button is-primary">Save</button>
            </footer>
        </div>
    </form>

</template>

<script>

    export default {

        props: ['tab'],

        data() {
            return {
                newTab: this.tab
            }
        },

        methods: {
            save() {
                this.$emit('save', this.newTab);
                this.$emit('close');
            }
        }

    }

</script>