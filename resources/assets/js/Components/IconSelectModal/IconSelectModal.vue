<template>
    <b-modal :active.sync="isActive" :width="900" @close="onModalClosed" class="icon-select-modal">
        <div class="modal-card" ref="element">

            <header class="modal-card-head">
                <p class="modal-card-title">Select Icon</p>
            </header>

            <div class="modal-card-search">
                <b-field>
                    <b-input placeholder="Search..."
                             type="search"
                             icon="search"
                             v-model="search">
                    </b-input>
                </b-field>
            </div>

            <div class="modal-card-body">

                <div class="columns is-multiline">

                    <div class="column is-12" v-for="group in filteredIconsArray">

                        <p class="title is-3">
                            {{ group.name }}
                        </p>

                        <hr>

                        <div class="columns is-multiline">
                            <div class="column is-4" v-for="icon in group.icons">
                                <div class="select-box"
                                     :class="{'is-active': selectedIcon == icon.name}"
                                     @click="select(icon)">

                                    <b-icon :icon="icon.name" size="is-large"></b-icon>

                                    <span class="title is-6">
                                    {{ icon.title }}
                                </span>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

            </div>
        </div>
    </b-modal>
</template>

<script>
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
                    this.$nextTick(function() {
                        this.fetchIcons();
                    });
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

                const loadingComponent = this.$loading.open({
                    container: this.$refs.element
                });

                LaHttp.get('/icons')
                    .then(({ data }) => {
                        this.icons = data.data.icons;
                        icons = data.data.icons;
                        loadingComponent.close();
                    })
                    .catch(err => {
                        loadingComponent.close();
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
</script>