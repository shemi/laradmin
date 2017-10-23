<template>

    <div>

        <b-field class="search-field">
            <b-autocomplete
                    v-model="search"
                    :data="data"
                    :placeholder="placeholder"
                    field="label"
                    expanded
                    :keep-first="true"
                    :loading="loading"
                    @input="getAsyncData"
                    @select="onTagSelect">
            </b-autocomplete>
            <p class="control">
                <button class="button is-primary"
                        type="button"
                        :class="{'is-loading': crating}"
                        @click.prevent="createTag">
                    <b-icon icon="plus"></b-icon>
                </button>
            </p>
        </b-field>

        <b-field grouped group-multiline>

            <div class="control"
                 v-for="tag in newValue"
                 :key="tag.key">

                <b-tag type="is-primary"
                       attached
                       closable
                       @close="deleteTag(tag)">
                    {{ tag.label }}
                </b-tag>

            </div>

        </b-field>

    </div>

</template>

<script>

    import LaHttp from '../../Forms/LaHttp';
    import LaForm from '../../Forms/LaForm';
    import debounce from 'lodash/debounce';

    export default {

        props: {
            value: {
                type: Array,
                required: true
            },
            form: {
                type: Object,
                required: true
            },
            queryUri: {
                type: String,
                required: true
            },
            createUri: {
                type: String,
                required: true
            },
            createKey: {
                type: String,
                required: true
            },
            placeholder: {
                type: String,
                default: ""
            }
        },

        data() {
            return {
                data: [],
                search: "",
                loading: false,
                crating: false,
                newValue: this.value
            }
        },

        watch: {

            value(value) {
                this.newValue = value;
            }

        },

        methods: {
            getAsyncData: debounce(function() {
                this.loading = true;
                this.data = [];

                LaHttp.get(this.queryUri, {
                    search: this.search,
                    page: 1
                })
                .then(res => {
                    let data = res.data.data;

                    data.data.forEach((item) => this.data.push(item));
                    this.loading = false;
                })
                .catch(err => {
                    this.loading = false;
                });
            }, 250),

            onTagSelect(tag) {
                if(tag) {
                    this.addTag(tag);
                }
            },

            createTag() {
                this.crating = true;
                let body = {};

                body[this.createKey] = this.search;

                LaHttp.post(this.createUri, new LaForm(body))
                    .then(res => {
                        this.crating = false;
                        this.addTag(res.data);
                    })
                    .catch(err => {
                        this.crating = false;

                        this.$nextTick(function() {
                            this.search = null;
                        });
                    });
            },

            addTag(tag) {
                for (let index in this.newValue) {
                    if(this.newValue[index].key === tag.key) {
                        this.$nextTick(function() {
                            this.search = null;
                        });

                        return;
                    }
                }

                this.newValue.push(tag);

                this.$emit('input', this.newValue);

                this.$nextTick(function() {
                    this.search = null;
                });
            },

            deleteTag(tag) {
                for (let index in this.newValue) {
                    if(this.newValue[index] === tag) {
                        this.$delete(this.newValue, index);
                    }
                }

                this.$emit('input', this.newValue);
            }

        }

    }

</script>