<template>
    <aside class="menu">

        <collapsible-menu-group :items="menu.items"></collapsible-menu-group>

    </aside>
</template>

<script>
    import Vue from 'vue';
    import MenuGroup from './MenuGroup/MenuGroup.vue';
    import MenuItem from './MenuItem/MenuItem.vue';

    Vue.component('CollapsibleMenuGroup', MenuGroup);
    Vue.component('CollapsibleMenuItem', MenuItem);

    export default {

        props: ['menu'],

        data() {
            return {

            }
        }

    }
</script>