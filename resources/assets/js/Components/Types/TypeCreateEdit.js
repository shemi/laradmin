import LaForm from '../../Forms/LaForm';
import IconSelectModal from '../IconSelectModal/IconSelectModal.vue';
import LaPanelList from './PanelsList.vue';
import MixinsLoader from '../../Helpers/MixinsLoader';
import deleteMixin from '../../Mixins/Delete';
import ServerError from '../../Mixins/ServerError';

export default {

    name: 'type-create-edit',

    props: [],

    mixins: MixinsLoader.load('typeCreateEdit', [deleteMixin, ServerError]),

    data() {
        return {
            form: new LaForm(window.laradmin.model),
            isIconSelectModalActive: false
        }
    },

    mounted() {

    },

    methods: {

        save() {
            let method = window.laradmin.model.exists ? 'put' : 'post';

            this.form[method](window.laradmin.routs.save)
                .then((res) => {
                    if(res.data.redirect) {
                        window.location.href = res.data.redirect;
                    } else {
                        this.$toast.open({
                            message: 'All Changes Saved!',
                            type: 'is-success'
                        });

                        //POC
                        if(this.form.updated_at) {
                            this.form.updated_at = new Date();
                        }
                    }
                })
                .catch(err => {
                    this.$toast.open({
                        message: 'Whoops.. Something went wrong!',
                        type: 'is-danger'
                    });

                    let code = err.status ? err.status : err.code;

                    if(code !== 422) {
                        this.alertServerError(err);
                    }
                });
        },

        addPanel(position) {
            this.$refs.panels.createPanel();
        },

        openIconSelectModal(toUpdate) {
            this.isIconSelectModalActive = true;
        }

    },

    components: {
        IconSelectModal,
        LaPanelList
    }

}