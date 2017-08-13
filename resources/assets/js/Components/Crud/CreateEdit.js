import Vue from 'vue';
import MixinsLoader from '../../Helpers/MixinsLoader';
import LaForm from '../../Forms/LaForm';
import deleteMixin from '../../Mixins/Delete';

export default {

    name: 'CrudCreateEdit',

    props: ['type', 'model'],

    mixins: MixinsLoader.load('crudCreateEdit', [deleteMixin]),

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
        save() {
            let method = window.laradmin.type.action === 'edit' ? 'put' : 'post';

            this.form[method](window.laradmin.routs.save)
                .then((res) => {
                    if(res.data.redirect) {
                        window.location.href = res.data.redirect;
                    } else {
                        this.$toast.open({
                            message: 'All Changes Saved!',
                            type: 'is-success'
                        });
                    }
                })
                .catch((err) => {
                    this.$toast.open({
                        message: 'Whoops.. Something went wrong!',
                        type: 'is-danger'
                    });
                });
        },

        deleteModel() {
            this.onDelete(
                window.laradmin.routs.delete,
                window.laradmin.type.singular_name
            );
        }

    },

    computed: {

    },

    components: {

    }

}