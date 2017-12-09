import LaHttp from '../../Forms/LaHttp';

let icons = null;

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
            this.$emit('update:selectedIcon', icon.name);
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

            if(icons && icons.length > 0) {
                this.icons = icons;

                return;
            }

            LaHttp.get('/icons')
                .then(({ data }) => {
                    this.icons = data.data.icons;
                    icons = data.data.icons;
                });

        }

    },

    computed: {
        filteredIconsArray() {
            let search = this.search.toLowerCase();

            if(! search) {
                return this.icons;
            }

            return JSON.parse(JSON.stringify(this.icons)).filter((group) => {
                group.icons = group.icons.filter((icon) => {
                    return icon.title.toString().toLowerCase().indexOf(search) >= 0;
                });

                return group.icons.length > 0;
            });
        }
    }

}