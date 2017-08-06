
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