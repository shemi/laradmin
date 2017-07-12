import Vue from 'vue';
import MenuGroup from './MenuGroup/MenuGroup.vue';
import MenuItem from './MenuItem/MenuItem.vue';

Vue.component('CollapsibleMenuGroup', MenuGroup);
Vue.component('CollapsibleMenuItem', MenuItem);

export default {

    props: ['menu'],

    data() {
        return {

        }
    }

}