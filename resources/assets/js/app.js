import './bootstrap';
import Vue from 'vue';
import Buefy from 'buefy';
import Login from './Components/Auth/LoginForm';
import TopBar from './Components/TopBar/TopBar';
import CollapsibleMenu from './Components/CollapsibleMenu/CollapsibleMenu.vue';
import BrowseMenus from './Components/Menus/BrowseMenus';
import MenuBuilder from './Components/Menus/MenuBuilder';
import BrowseTypes from './Components/Types/BrowseTypes';
import TypeCreateEdit from './Components/Types/TypeCreateEdit';
import Crud from './Components/Crud/Crud';
import { Vue2Dragula } from 'vue2-dragula';

import './Filters/Slugify';


Vue.use(Buefy);

Vue.use(Vue2Dragula, {});

const app = new Vue({
    el: '#app',

    components: {
        Login,
        TopBar,
        CollapsibleMenu,
        BrowseMenus,
        MenuBuilder,
        BrowseTypes,
        TypeCreateEdit,
        Crud
    }
});