import Vue from 'vue';
import MenuGroup from './MenuGroup/MenuGroup.vue';
import MenuItem from './MenuItem/MenuItem.vue';

Vue.component('CollapsibleMenuGroup', MenuGroup);
Vue.component('CollapsibleMenuItem', MenuItem);

export default {

    props: [],

    data() {
        return {
            items: [
                {
                    'id': 1,
                    'label': 'Dashboard',
                    'icon': 'dashboard',
                    'href': '/admin'
                },
                {
                    'id': 2,
                    'label': 'Customers',
                    'href': '/customers',
                    'icon': 'person',
                    'items': [
                        {
                            'id': 3,
                            'label': 'All customers',
                            'href': '/customers'
                        },
                        {
                            'id': 4,
                            'label': 'New Customer',
                            'href': '/customers/new'
                        }
                    ]
                }
            ]
        }
    }

}