import Vue from 'vue';
import LaForm from '../../Forms/LaForm';

export default {

    name: 'CrudCreateEdit',

    props: ['type', 'model'],

    data() {
        return {
            form: new LaForm(window.laradmin.model),
        }
    },

    watch: {

    },

    created() {
        console.log(this.form);
    },

    methods: {

    },

    computed: {

    },

    components: {

    }

}