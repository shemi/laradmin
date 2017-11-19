<script>
    import Vue from 'vue';
    import ParentFormMixin from '../../Mixins/ParentForm';

    import {get} from 'lodash';

    export default {
        props: [
            'template',
            'formKey'
        ],

        mixins: [ParentFormMixin],

        data() {
            return {
                templateRender: null,
            };
        },

        render(h) {
            if (! this.templateRender) {
                return h('span', '...');
            } else {
                return this.templateRender();
            }
        },

        watch: {
            template: {
                immediate: true,
                handler() {
                    let res = Vue.compile(this.template);

                    this.templateRender = res.render;

                    this.$options.staticRenderFns = [];

                    this._staticTrees = [];

                    for (let i in res.staticRenderFns) {
                        this.$options.staticRenderFns.push(res.staticRenderFns[i]);
                    }
                }
            }
        },

        computed: {
            field() {
                return get(this.form, this.formKey.split('.'));
            }
        }

    }


</script>