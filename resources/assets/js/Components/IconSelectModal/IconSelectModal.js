import LaHttp from '../../Forms/LaHttp';

export default {
    props: {
        active: Boolean,
        selectedIcon: {
            type: String,
            default: '',
            required: false
        }
    },

    data() {
        return {
            icons: [],
            selected: null,
            isActive: false
        }
    },

    watch: {
        active(value) {
            this.isActive = value;

            if(value) {
                this.fetchIcons();
            }
        }
    },

    mounted() {

    },

    methods: {

        select(icon) {
            this.$emit('update:selectedIcon', icon);
            this.close();
        },

        close() {
            this.$emit('update:active', false);
        },

        onModalClosed() {
            this.close();
        },

        fetchIcons() {
            if(this.icons.length > 0) {
                return;
            }

            LaHttp.get('/icons')
                .then(res => {
                    let icons = res.data.data.icons;

                    console.log(icons);
                });

        }

    }

}