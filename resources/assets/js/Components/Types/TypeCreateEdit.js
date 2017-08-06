import LaForm from '../../Forms/LaForm';

export default {

    name: 'type-create-edit',

    props: ['type', 'tables'],

    data() {
        return {
            form: new LaForm({
                id: 0,
                name: '',
                table: null,
                model: '',
                slug: '',
                public: true,
                controller: '',
                fields: []
            })
        }
    },

    mounted() {
        console.log(this.tables);
    },

    methods: {

        save() {

        }

    },

    components: {

    }

}