import Vue from 'vue';
import LaForm from '../../Forms/LaForm';
import Helpers from '../../Helpers/Helpers';
import IconSelectModal from '../IconSelectModal/IconSelectModal.vue';
import MenuBuilderItem from './MenuBuilderItem/MenuBuilderItem.vue';

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
            'isIconSelectModalActive': false,
            'items': []
        }
    },

    created() {
        let dragula = this.$dragula;

        let service = dragula.createService({
            name: 'menus',
            drakes: {
                menus: {
                    copy: false,
                    revertOnSpill: false,
                    removeOnSpill: false,
                    accepts: (el, target, source, sibling) => {
                        // console.log(target);
                        //
                        // if(! target) {
                        //     return false;
                        // }
                        //
                        // if(! el.contains || el === target || el.contains(target)) {
                        //     return false;
                        // }
                        //
                        // return true;

                        return ! function (a, b) {
                            return a.contains ?
                                a != b && a.contains(b) :
                                !!(a.compareDocumentPosition(b) & 16);
                        }(el, target);
                    }
                }
            }
        });
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
            this.itemForm.post('menus/item/validation')
                .then(res => {
                    this.items.push(res.data);
                    this.closeNewEditModal();
                });
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
        IconSelectModal,
        MenuBuilderItem
    }

}