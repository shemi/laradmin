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