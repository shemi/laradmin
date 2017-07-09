import './bootstrap';
import Vue from 'vue';
import Buefy from 'buefy';
import Login from './Components/Auth/LoginForm';
import TopBar from './Components/TopBar/TopBar';
import CollapsibleMenu from './Components/CollapsibleMenu/CollapsibleMenu.vue';
import BrowseMenus from './Components/Menus/BrowseMenus';
import MenuBuilder from './Components/Menus/MenuBuilder';
import { Vue2Dragula } from 'vue2-dragula'


Vue.use(Buefy);

Vue.use(Vue2Dragula, {
    // logging: {
    //     service: true
    // }
});

const app = new Vue({
    el: '#app',

    components: {
        Login,
        TopBar,
        CollapsibleMenu,
        BrowseMenus,
        MenuBuilder
    }
});