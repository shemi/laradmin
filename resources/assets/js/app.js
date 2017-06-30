window.Vue = require('vue');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

import Login from './Components/Auth/LoginForm';

const app = new Vue({
    el: '#app',

    components: {
        Login
    }
});