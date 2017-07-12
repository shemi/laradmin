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
                'id': 0,
                'name': '',
                'slug': '',
                'created_at': '',
                'updated_at': '',
                'items': []
            }),
            isNewEditItemModalActive: false,
            currentItemLocation: '',
            itemForm: new LaForm({
                'id': 0,
                'title': '',
                'type': 'route',
                'route_name': '',
                'url': '',
                'icon': '',
                'css_class': '',
                'in_new_window': false,
                'route_url': ''
            }),
            'isIconSelectModalActive': false,
            'items': []
        }
    },

    watch: {
        items: {
            handler: function(val, oldVal) {
                this.$set(this.form, 'items', val);
            },
            deep: true
        }
    },

    created() {
        let dragula = this.$dragula;

        dragula.createService({
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

        this.syncFormWithMenu();
    },

    methods: {

        syncFormWithMenu() {
            let rebuildObject = {};


            for(let key in this.menu) {
                if (! this.menu.hasOwnProperty(key)) {
                    continue;
                }

                rebuildObject[key] = Helpers.value(
                    this.menu[key],
                    this.form[key]
                );
            }

            this.form.rebuild(rebuildObject);
            this.items = this.form.items;
        },

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
                        this.itemForm.rebuild(res.data);
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
        },

        save() {
            let method = this.form.id ? 'put' : 'post';

            this.form.rebuild({
                'items': this.items
            });

            this.form[method]('/menus/' + (this.form.id || ''))
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