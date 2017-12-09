<script>
    import Vue from 'vue';
    import ParentFormMixin from '../../Mixins/ParentForm';
    import LaForm from '../../Forms/LaForm';

    import {get} from 'lodash';

    export default {

        props: {
            'template': String,
            'formKey': String,
            'form': Object,
            'field': {
                default: ''
            }
        },

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
                    let res = Vue.compile(this.template),
                        i;

                    this.templateRender = res.render;

                    this.$options.staticRenderFns = [];

                    this._staticTrees = [];

                    for (i in res.staticRenderFns) {
                        this.$options.staticRenderFns.push(res.staticRenderFns[i]);
                    }
                }
            }
        },

    }


</script>