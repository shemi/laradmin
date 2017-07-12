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
    }

}