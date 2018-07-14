import Vue from 'vue';
import MixinsLoader from '../../Helpers/MixinsLoader';
import LaForm from '../../Forms/LaForm';
import deleteMixin from '../../Mixins/Delete';
import ServerError from '../../Mixins/ServerError';
import {LaRepeater} from '../Repeater/index';
import {LaRelationship, LaRelationshipRow} from '../Relationship/index';
import LaFilesUpload from '../FilesUpload/FilesUpload.vue';
import LaImageUpload from '../ImageUpload/ImageUpload.vue';
import LaEditor from '../Editor/Editor';
import LaFileUpload from '../FileUpload/FileUpload.vue';
import TagsField from '../TagsField/TagsField.vue';
import laEditable from '../Editable/Editable';
import laDateTime from '../DateTime/DateTime';

export default {

    name: 'CrudCreateEdit',

    props: ['type', 'model'],

    mixins: MixinsLoader.load('crudCreateEdit', [deleteMixin, ServerError]),

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

                        this.form.rebuild(res.data.model);
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
        LaRepeater,
        LaFilesUpload,
        LaRelationship,
        LaRelationshipRow,
        LaImageUpload,
        LaFileUpload,
        TagsField,
        LaEditor,
        laEditable,
        laDateTime
    }

}