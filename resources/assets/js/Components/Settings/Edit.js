import Vue from 'vue';
import MixinsLoader from '../../Helpers/MixinsLoader';
import LaForm from '../../Forms/LaForm';
import deleteMixin from '../../Mixins/Delete';
import ServerError from '../../Mixins/ServerError';
import {LaRepeater, LaRepeaterRow} from '../Repeater/index';
import {LaRelationship, LaRelationshipRow} from '../Relationship/index';
import LaFilesUpload from '../FilesUpload/FilesUpload.vue';
import LaImageUpload from '../ImageUpload/ImageUpload.vue';
import LaFileUpload from '../FileUpload/FileUpload.vue';
import TagsField from '../TagsField/TagsField.vue';

export default {

    name: 'SettingsEdit',

    props: ['type', 'model'],

    mixins: MixinsLoader.load('settingsEdit', [deleteMixin, ServerError]),

    data() {
        return {
            form: new LaForm(window.laradmin.model),
        }
    },

    watch: {

    },

    created() {

    },

    methods: {
        save() {
            this.form.put(window.laradmin.routs.save)
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
        }

    },

    computed: {

    },

    components: {
        LaRepeater,
        LaRepeaterRow,
        LaFilesUpload,
        LaRelationship,
        LaRelationshipRow,
        LaImageUpload,
        LaFileUpload,
        TagsField
    }

}