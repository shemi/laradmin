import './bootstrap';
import Vue from 'vue';
import Buefy from 'buefy';
import Login from './Components/Auth/LoginForm';
import TopBar from './Components/TopBar/TopBar';
import CollapsibleMenu from './Components/CollapsibleMenu/CollapsibleMenu.vue';

Vue.use(Buefy);

const app = new Vue({
    el: '#app',

    components: {
        Login,
        TopBar,
        CollapsibleMenu
    }
});