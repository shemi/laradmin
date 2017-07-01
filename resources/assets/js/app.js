import './bootstrap';
import Vue from 'vue';
import Buefy from 'buefy';
import Login from './Components/Auth/LoginForm';

Vue.use(Buefy);

const app = new Vue({
    el: '#app',

    components: {
        Login
    }
});