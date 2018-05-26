<template>
    <div class="tabs is-toggle">
        <vddl-list :list="newValue"
                   :tag="'ul'"
                   :allowed-types="['tabs']"
                   :drop="handleDrop"
                   :horizontal="false">

            <vddl-draggable v-for="(tab, index) in newValue"
                            :class="{'is-active': tab.id === selectedTab}"
                            :key="tab.id"
                            :draggable="tab"
                            :index="index"
                            :tag="'li'"
                            :wrapper="newValue"
                            :dragstart="dragStart"
                            :dragend="dragEnd"
                            type="tabs"
                            :moved="handleMoved"
                            effect-allowed="move">

                <a @click="selectTab(tab)"
                   :class="{'has-text-white-bis has-background-danger': errors[tab.id]}"
                   @dragenter="onDragOver($event, tab)">
                    <b-icon v-if="tab.icon" :icon="tab.icon"></b-icon>
                    <span>{{ tab.title }}</span>
                </a>
            </vddl-draggable>

            <vddl-placeholder :tag="'li'">
                <a>Drop here</a>
            </vddl-placeholder>

            <li>
                <a @click="make">
                    <span>Add Tab</span>
                    <b-icon icon="plus"></b-icon>
                </a>
            </li>
        </vddl-list>
    </div>
</template>

<script>
    import ParentFormMixin from '../../Mixins/ParentForm';
    import {cloneDeep, find} from 'lodash';
    import Helpers from '../../Helpers/Helpers';
    import TabFormModal from './TabFormModal';

    export default {

        name: 'la-tabs-builder',

        mixins: [ParentFormMixin],

        props: {
            value: Array,
            errors: Object
        },

        data() {
            return {
                newValue: this.value,
                selectedTab: null,
                draggingTab: false
            }
        },

        watch: {
            value(value) {
                this.newValue = value;
            }
        },

        mounted() {
            if(this.newValue[0]) {
                this.selectTab(this.newValue[0]);
            }
        },

        methods: {
            make() {
                this.createUpdateModal({title: '', icon: null, id: null});
            },

            onDragOver($event, tab) {
                if(tab.id === this.selectedTab || this.draggingTab) {
                    return;
                }

                console.log($event);

                this.selectTab(tab);
            },

            dragStart() {
                this.draggingTab = true;
            },

            dragEnd() {
                this.draggingTab = false;
            },

            createUpdateModal(tab) {
                const self = this;

                return this.$modal.open({
                    parent: this,
                    component: TabFormModal,
                    hasModalCard: true,
                    props: {tab},
                    events: {
                        save(newTab) {
                            if(newTab.id) {
                                tab = find(self.newValue, {id: tab.id});

                                if(tab) {
                                    self.$set(tab, 'title', newTab.title);
                                    self.$set(tab, 'icon', newTab.icon);
                                }

                                return;
                            }

                            self.createTab(newTab.title, newTab.icon);
                        }
                    },
                    canCancel: false
                });
            },

            createTab(title, icon = null) {
                const id = Helpers.makeId();

                this.newValue.push({id, title, icon});
                this.$emit('input', this.newValue);
                this.selectTab(this.newValue[this.newValue.length - 1]);
            },

            selectTab(tab) {
                if(! tab || ! tab.id) {
                    return;
                }

                if(this.selectedTab === tab.id) {
                    this.createUpdateModal(cloneDeep(tab));

                    return;
                }

                this.selectedTab = tab.id;
                this.$emit('tab-selected', this.selectedTab);
            },

            handleDrop(data) {
                const { index, list, item } = data;

                // item.id = Helpers.makeId();
                list.splice(index, 0, item);
            },

            handleMoved(item) {
                const { index, list } = item;
                list.splice(index, 1);
            },
        }

    }

</script>