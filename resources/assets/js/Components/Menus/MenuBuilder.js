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
            currentItemLocation: '',
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
            'items': [{
                "id": 204,
                "title": "Dashboard",
                "type": "route",
                "route_name": "laradmin.dashboard",
                "url": null,
                "in_new_window": false,
                "icon": "dashboard",
                "css_class": "",
                "route_url": "http://laradmin.dev/admin",
                "items": []
            }, {
                "id": 5711,
                "title": "Test1",
                "type": "url",
                "route_name": null,
                "url": "dsdsd",
                "in_new_window": false,
                "icon": "view_list",
                "css_class": "",
                "route_url": "",
                "items": []
            }, {
                "id": 7865,
                "title": "Test2",
                "type": "url",
                "route_name": null,
                "url": "sdsdsdsd",
                "in_new_window": false,
                "icon": null,
                "css_class": "",
                "route_url": "",
                "items": []
            }, {
                "id": 5033,
                "title": "Test33",
                "type": "url",
                "route_name": null,
                "url": "sdsd",
                "in_new_window": false,
                "icon": null,
                "css_class": "",
                "route_url": "",
                "items": []
            }]
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
                        return !function (a, b) {
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

        openNewEditModal(item = {}, location = '') {
            this.isNewEditItemModalActive = true;
            this.currentItemLocation = location + '';
            this.itemForm.rebuild(item);
        },

        openIconSelectModal() {
            this.isIconSelectModalActive = true;
        },

        closeNewEditModal() {
            this.isNewEditItemModalActive = false;
            this.currentItemLocation = '';
            this.itemForm.reset();
        },

        getItemObjectByLocation(location) {
            let targetPath,
                target;

            if(! location) {
                return this.items;
            }

            targetPath = location.split('.');
            targetPath = Array.isArray(targetPath) ? targetPath : [targetPath];
            target = this.items;

            while (targetPath.length) {
                target = target[targetPath.shift()];
            }

            return target;
        },

        createOrUpdateMenuItem() {
            let isExists = !! this.itemForm.id,
                formInfo,
                formInfoKeys,
                formInfoKey,
                formInfoKeyIndex,
                target;

            this.itemForm.post('menus/item/validation')
                .then(res => {

                    if(isExists && this.currentItemLocation) {
                        formInfo = this.itemForm.toJson();
                        formInfoKeys = Object.keys(formInfo);
                        target = this.getItemObjectByLocation(this.currentItemLocation);

                        for (formInfoKeyIndex in formInfoKeys) {
                            formInfoKey = formInfoKeys[formInfoKeyIndex];

                            if(formInfoKey === 'items') {
                                continue;
                            }

                            this.$set(
                                target,
                                formInfoKey,
                                formInfo[formInfoKey]
                            );
                        }
                    } else if(! isExists) {
                        this.items.push(res.data);
                    }

                    this.closeNewEditModal();
                });
        },

        deleteMenuItem(location) {
            let target,
                key,
                locationPath = location.toString().split('.');

            if(locationPath.length === 1) {
                target = this.items;
                key = locationPath[0];
            } else {
                key = locationPath.pop();
                target = this.getItemObjectByLocation(locationPath.join('.'));
            }

            this.$delete(target, parseInt(key));
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