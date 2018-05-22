<template>
    <div :class="classList">

        <b-field :type="hasErrors('title') ? 'is-danger' : ''"
                 :messag="errorMessage('title')"
                 label="Title">
            <b-input v-model="panel.title"
                     @input="input($event, 'title')"
                     type="text"></b-input>
        </b-field>

        <b-field :type="hasErrors('tabs') ? 'is-danger' : ''"
                 :messag="errorMessage('tabs')"
                 label="Tabs">
            <la-tabs-builder v-model="panel.tabs"
                             @tab-selected="onTabSelected"
                             :errors="tabsErrors"
                             @input="input($event, 'tabs')">
            </la-tabs-builder>
        </b-field>

        <b-field :type="hasErrors('fields') ? 'is-danger' : ''"
                 :messag="errorMessage('fields')"
                 class="field-la-fields-list"
                 label="fields">

            <la-fields-list v-model="panel.fields"
                            @input="input($event, 'fields')"
                            :set-structure-before-create="setFieldTabId"
                            :is-hidden="isHidden"
                            @has-errors="$emit('has-errors', $event)"
                            @field-has-errors="onErrors($event)"
                            :form-key="fieldKey('fields')">
            </la-fields-list>

        </b-field>

    </div>
</template>

<script>
    import PanelFormMixin from '../../../Mixins/PanelFormMixin';
    import LaTabsBuilder from '../TabsBuilder';

    export default {

        name: 'la-tabs-panel-form',

        mixins: [PanelFormMixin],

        data() {
            return {
                selectedTab: null,
                tabsErrors: {}
            }
        },

        methods: {
            onErrors({field, hasErrors}) {
                if(! field.tab_id) {
                    return;
                }

                this.$set(this.tabsErrors, field.tab_id, hasErrors);
            },

            onTabSelected(tabId) {
                this.selectedTab = tabId;
            },

            isHidden(field) {
                return this.selectedTab !== field.tab_id;
            },

            setFieldTabId(field) {
                if(! this.selectedTab) {
                    return;
                }

                field.tab_id = this.selectedTab;
            }
        },

        components: {
            LaTabsBuilder
        }

    }

</script>