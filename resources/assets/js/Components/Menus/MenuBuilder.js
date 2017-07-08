import LaForm from '../../Forms/LaForm';
import Helpers from '../../Helpers/Helpers';
import IconSelectModal from '../IconSelectModal/IconSelectModal.vue';

export default {

    props: ['menu', 'routes'],

    data() {
        return {
            form: new LaForm({
                'name': '',
                'items': []
            }),
            isNewEditItemModalActive: false,
            itemForm: new LaForm({
                'id': null,
                'title': '',
                'type': 'route',
                'route_name': '',
                'url': '',
                'icon': '',
                'css_class': '',
                'in_new_window': false,
            }),
            'isIconSelectModalActive': false
        }
    },

    mounted() {
        this.form.rebuild({
            'name': Helpers.value(this.menu.name, ''),
            'items': Helpers.value(this.menu.items, [])
        });
    },

    methods: {

        openNewEditModal(item = {}) {
            this.isNewEditItemModalActive = true;
            this.itemForm.rebuild(item);
        },

        openIconSelectModal() {
            this.isIconSelectModalActive = true;
        },

        closeNewEditModal() {
            this.isNewEditItemModalActive = false;
            this.itemForm.reset();
        },

        createOrUpdateMenuItem() {

            this.closeNewEditModal();
        }

    },

    computed: {
        filteredRoutesArray() {
            return this.routes.filter((option) => {
                return option.name
                        .toString()
                        .toLowerCase()
                        .indexOf(this.itemForm.route_name.toLowerCase()) >= 0;
            });
        }
    },

    components: {
        IconSelectModal
    }

}