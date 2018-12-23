<template>
    <li class="menu-item"
        :class="cssClasses">

        <a @click="linkClicked($event)"
           :href="item.url"
           :target="item.in_new_window ? '_blank' : '_self'"
           :class="{'is-active': isActive}">
            <b-icon v-if="item.icon" :icon="item.icon"></b-icon>
            <span class="item-label">{{ item.title }}</span>
            <b-icon v-if="hasItems" class="collapsible-icon" icon="angle-down"></b-icon>
        </a>

        <collapsible-menu-group ref="group"
                                v-if="hasItems"
                                v-show="isActive" :items="items"></collapsible-menu-group>
    </li>
</template>

<script>
    export default {

        name: 'collapsible-menu-item',

        props: ['item'],

        data() {
            return {
                isActive: false
            }
        },

        mounted() {
            this.isActive = this.item.is_active;

            if(this.isActive) {
                this.$emit('is-active');
            }

            if(this.$refs.group && ! this.isActive) {
                this.isActive = this.$refs.group.hasActive;
            }
        },

        methods: {
            linkClicked($event) {
                if(! this.hasItems) {
                    return true;
                }

                $event.preventDefault();
                this.isActive = ! this.isActive;
            }

        },

        computed: {
            hasItems() {
                return this.items && this.items.length > 0;
            },

            items() {
                return this.item.items;
            },

            cssClasses() {
                let classes = [];

                if(this.item.css_class) {
                    classes.push(this.item.css_class);
                }

                if(this.hasItems) {
                    classes.push('has-items');
                }

                return classes;
            }
        }

    }
</script>