<template>
    <ul class="menu-list">
        <collapsible-menu-item v-for="item in items" :item="item" :key="item.id"
                               @is-active="markActive()"></collapsible-menu-item>
    </ul>
</template>

<script>
    import MenuItem from '../MenuItem/MenuItem.vue';

    export default {

        props: ['items'],

        data() {
            return {
                hasActive: false
            }
        },

        methods: {
            markActive() {
                this.hasActive = true;
            }
        },

        components: {
            'LaMenuItem': MenuItem
        }

    }
</script>