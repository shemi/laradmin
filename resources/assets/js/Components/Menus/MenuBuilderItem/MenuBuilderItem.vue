<template>
    <div class="menu-builder-item" :class="{'not-empty': item.items.length}">
        <div class="level">
            <div class="level-left">
                <div class="level-item">
                    <b-icon :icon="item.icon" class="is-twitter" :class="{'is-dim': ! item.icon}"></b-icon>
                    <p class="title is-5">
                        <b>{{ item.title }}</b>
                        <small>{{ item.url }}</small>
                    </p>
                </div>
            </div>
            <div class="level-right">
                <div class="level-item">
                    <a class="button is-small is-inverted" @click.prevent="edit()">
                        <b-icon icon="pencil"></b-icon>
                    </a>
                </div>

                <div class="level-item">
                    <a class="button is-small is-danger is-inverted" @click.prevent="deleteItem()">
                        <b-icon icon="trash-o"></b-icon>
                    </a>
                </div>
            </div>
        </div>

        <draggable v-model="item.items" :options="{group:'menu'}" class="sub-items">
            <menu-builder-item v-for="(childItem, index) in item.items"
                               :key="childItem.id"
                               :position="position+'.items.'+ index"
                               @edit="onEdit($event)"
                               @delete="onDelete($event)"
                               :item.sync="childItem">
            </menu-builder-item>
        </draggable>
    </div>
</template>
<script>
    import draggable from 'vuedraggable';

    export default {
        name: 'menu-builder-item',

        props: ['item', 'position'],

        data() {
            return {

            }
        },

        methods: {
            edit() {

                this.$emit('edit', {
                    item: this.item,
                    position: this.position
                });
            },

            onEdit(event) {
                this.$emit('edit', {
                    item: event.item,
                    position: event.position
                });
            },

            deleteItem() {
                this.$emit('delete', this.position);
            },

            onDelete(event) {
                this.$emit('delete', event);
            }
        },

        components: {
            draggable
        }

    }
</script>