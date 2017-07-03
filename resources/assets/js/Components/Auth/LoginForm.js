import MixinsLoader from '../../Helpers/MixinsLoader';
import LaForm from '../../Forms/LaForm';
import LaHttp from '../../Forms/LaHttp';

export default {
    mixins: MixinsLoader.load('login'),

    data() {
      return {
          loginForm: new LaForm({
              'email': '',
              'password': '',
              'remember': false
          }),
      }
    },

    mounted() {

    },

    methods: {
        login() {
            LaHttp.post('/login', this.loginForm)
                .then(res => {
                    console.log(res);
                    window.location.href = res.data.redirect;
                })
                .catch(err => {
                    console.log(err);
                });
        }
    }

}