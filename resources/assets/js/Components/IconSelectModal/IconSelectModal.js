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
            isActive: false,
            search: ""
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
            this.$emit('update:selectedIcon', icon.ligature);
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
                    this.icons = res.data.data.icons;
                });

        }

    },

    computed: {
        filteredIconsArray() {
            let search = this.search.toLowerCase();

            if(! search) {
                return this.icons;
            }

            return this.icons.filter((icon) => {
                for(let i in icon.keywords) {
                    let word = icon.keywords[i].toString().toLowerCase();

                    if(word.indexOf(search) >= 0) {
                        return true;
                    }
                }

                return false;
            });
        }
    }

}