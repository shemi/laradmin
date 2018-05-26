import LaForm from '../../Forms/LaForm';
import IconSelectModal from '../IconSelectModal/IconSelectModal.vue';
import LaPanelList from './PanelsList.vue';
import LaColumnsEditor from './ColumnsEditor';
import MixinsLoader from '../../Helpers/MixinsLoader';
import deleteMixin from '../../Mixins/Delete';
import ServerError from '../../Mixins/ServerError';
import {sortBy} from 'lodash';
import MonacoLoader from '../Monaco/Loader';

export default {

    name: 'type-create-edit',

    props: [],

    mixins: MixinsLoader.load('typeCreateEdit', [deleteMixin, ServerError]),

    data() {
        return {
            isLoading: true,
            form: new LaForm(window.laradmin.model),
            panels: window.laradmin.builderData.panels,
            isIconSelectModalActive: false
        }
    },

    mounted() {
        MonacoLoader.load().then(() => {this.isLoading = false});

        if(! this.form.exists) {
            this.addPanel('main_meta', 'Publish');
        }
    },

    methods: {

        save() {
            let method = window.laradmin.model.exists ? 'put' : 'post';

            this.form[method](window.laradmin.routs.save)
                .then((res) => {
                    if (res.data.redirect) {
                        window.location.href = res.data.redirect;
                    } else {
                        this.$toast.open({
                            message: 'All Changes Saved!',
                            type: 'is-success'
                        });

                        //POC
                        if (this.form.updated_at) {
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

                    if (code !== 422) {
                        this.alertServerError(err);
                    }
                });
        },

        addPanel(type, name = null) {
            this.$refs.panels.createPanel(type, name);
        },

        openIconSelectModal(toUpdate) {
            this.isIconSelectModalActive = true;
        },

        flatFields(items, fields = []) {
            let item;

            for (item of items) {
                if (item.fields) {
                    if (item.fields.length > 0) {
                        this.flatFields(item.fields, fields);
                    }

                    continue;
                }

                fields.push(item);
            }

            return fields;
        },

        addColumn(column) {
            if(! column) {
                return;
            }

            if(! column.visibility) {
                this.$set(column, 'visibility', []);
            }

            column.visibility.push('browse');
        }

    },

    computed: {

        allFields() {
            return this.flatFields(this.form.panels);
        },

        browseColumns() {
            const columns = this.allFields
                .filter((column) => {
                    return !column.visibility || column.visibility.indexOf('browse') >= 0;
                });

            return sortBy(columns, function (column) {
                return column.browse_settings.order;
            });
        },

        notBrowseColumns() {
            const columns = this.allFields
                .filter((column) => {
                    return !column.visibility || column.visibility.indexOf('browse') < 0;
                });

            return sortBy(columns, function (column) {
                return column.browse_settings.order;
            });
        }

    },

    components: {
        IconSelectModal,
        LaPanelList,
        LaColumnsEditor
    }

}