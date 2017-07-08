<b-modal :active.sync="isNewEditItemModalActive"
         :can-cancel="false"
         @close="closeNewEditModal()">

    <form @submit.prevent="createOrUpdateMenuItem()">

        <div class="modal-card">

            <header class="modal-card-head">
                <p class="modal-card-title">
                    <span v-if="itemForm.id">
                        @lang('laradmin::menus.builder.edit_item')
                    </span>
                    <span v-else>
                        @lang('laradmin::menus.builder.new_item')
                    </span>
                </p>

                <button class="delete" @click.prevent="closeNewEditModal()"></button>
            </header>

            <section class="modal-card-body">

                <b-notification v-if="form.errors.has('form')" type="is-danger">
                    @{{ form.errors.get("form") }}
                </b-notification>

                <b-field :type="itemForm.errors.has('title') ? 'is-danger' : ''"
                         label="@lang('laradmin::menus.builder.item_title')"
                         :message="itemForm.errors.has('title') ? itemForm.errors.get('title') : ''">
                    <b-input type="text"
                             v-model="itemForm.title">
                    </b-input>
                </b-field>

                <b-field grouped>
                    <b-field label="@lang('laradmin::menus.builder.url_target')" expanded>
                        <b-field>
                            <b-select v-model="itemForm.type">
                                <option value="route">
                                    @lang('laradmin::menus.builder.route')
                                </option>
                                <option value="url">
                                    @lang('laradmin::menus.builder.url')
                                </option>
                            </b-select>

                            <b-input placeholder="@lang('laradmin::menus.builder.url_placeholder')"
                                     v-model="itemForm.url"
                                     v-if="itemForm.type == 'url'"
                                     expanded></b-input>

                            <b-autocomplete v-if="itemForm.type == 'route'"
                                            v-model="itemForm.route_name"
                                            placeholder="e.g. laradmin.dashboard"
                                            keep-first
                                            has-custom-template
                                            expanded
                                            :data="filteredRoutesArray"
                                            field="name">

                                <template scope="props">
                                    <div class="media">
                                        <div class="media-content">
                                            @{{ props.option.name }}
                                            <br>
                                            <small>
                                                URI: @{{ props.option.uri }}
                                            </small>
                                        </div>
                                    </div>
                                </template>

                            </b-autocomplete>
                        </b-field>
                    </b-field>
                </b-field>

                <div class="block">
                    <div class="field">
                        <b-switch v-model="itemForm.in_new_window">
                            <span v-if="itemForm.in_new_window">
                                @lang('laradmin::menus.builder.in_new_window')
                            </span>
                            <span v-else>
                                @lang('laradmin::menus.builder.not_in_new_window')
                            </span>
                        </b-switch>
                    </div>
                </div>

                <b-field :type="itemForm.errors.has('icon') ? 'is-danger' : ''"
                         label="@lang('laradmin::menus.builder.item_icon')"
                         :message="itemForm.errors.has('icon') ? itemForm.errors.get('icon') : ''">
                    <b-input type="text"
                             v-model="itemForm.icon">
                    </b-input>
                </b-field>

                <button type="button" @click="openIconSelectModal">Open Icon finder</button>
                
                <b-field :type="itemForm.errors.has('css_class') ? 'is-danger' : ''"
                         label="@lang('laradmin::menus.builder.item_css_class')"
                         :message="itemForm.errors.has('css_class') ? itemForm.errors.get('css_class') : ''">
                    <b-input type="text"
                             v-model="itemForm.css_class">
                    </b-input>
                </b-field>
                
            </section>

            <footer class="modal-card-foot">
                <button type="submit" class="button is-success">
                    <span v-if="itemForm.id">
                        @lang('laradmin::menus.builder.save_changes')
                    </span>
                    <span v-else>
                        @lang('laradmin::menus.builder.add_item')
                    </span>
                </button>

                <a class="button" @click.prevent="closeNewEditModal()">
                    @lang('laradmin::menus.builder.cancel')
                </a>
            </footer>

        </div>

    </form>

</b-modal>