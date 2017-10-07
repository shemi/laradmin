import LaForm from '../../Forms/LaForm';
import IconSelectModal from '../IconSelectModal/IconSelectModal.vue';
import LaPanelList from './PanelsList.vue';

export default {

    name: 'type-create-edit',

    props: [],

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