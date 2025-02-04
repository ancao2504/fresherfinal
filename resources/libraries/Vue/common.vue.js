/**
 * This file includes some common vue element using Cloudpro Production
 * Author: Phu Vo
 * Date: 2020.04.06
 */

(() => {
    // Pass window object to vue prototype
    Vue.prototype.global = window;

    // Init global plugin components
    Vue.component('date-picker', VueBootstrapDatetimePicker);

    Vue.component("chosen-select", {
        props:{
            value: [String, Array],
            placeholder: [String],
            multiple: Boolean,
        },
        template:`
            <select :multiple="multiple" :data-placeholder="placeholder">
                <slot></slot>
            </select>
        `,
        watch: {
            value(val, pre){
                $(this.$el).val(val).trigger('chosen:updated');
            }
        },
        mounted(){
            $(this.$el)
            .val(this.value)
            .chosen()
            .on("change", e => this.$emit('input', $(this.$el).val()))
        },
        updated() {
            KanbanHelper.resetChosen($(this.$el));
        },
        destroyed() {
            $(this.$el).chosen('destroy');
        }
    });

    Vue.component("multiselect", {
        props: {
            value: [String, Array],
            filterable: [Boolean, String]
        },
        template: `
            <select multiple>
                <slot></slot>
            </select>
        `,
        watch: {
            value(val, pre) {
                $(this.$el).val(val).multiselect('refresh');
            }
        },
        mounted() {
            $(this.$el).val(this.value).multiselect({
                enableFiltering: Boolean(this.filterable),
                disableIfEmpty: true,
                includeSelectAllOption: false,
                maxHeight: 200,
                numberDisplayed: 1,
                buttonText: function(options, select) {
                    let length = options.length;
                    if (length === 0) {
                        return app.vtranslate('JS_KANBAN_COLUMN');
                    }
                    else {
                        let label = app.vtranslate('JS_CHOOSED');
                        return `${length} ${label}`;
                    }
                }
            }).on("change", e => this.$emit('input', $(this.$el).val()));
        },
        updated() {
            $(this.$el).multiselect('rebuild').hide();
            $(this.$el).val(this.value).multiselect('refresh');
        },
        destroyed() {
            $(this.$el).multiselect('destroy');
        }
    });

    Vue.component('multiple-owner', {
        props: ['selectedTags', 'mainOwnerId', 'name', 'endpoint'],
        template: `<b-form-input multiple :name='name'></b-form-input>`,
        watch: {
            selectedTags(val, pre) {
                $(this.$el).select2('data', val).trigger('change');
            }
        },
        mounted: function() {
            const self = this;

            $(self.$el).select2({
                placeholder: app.vtranslate('JS_CUSTOM_OWNER_FIELD_SELECT2_PLACEHOLDER'),
                minimumInputLength: _VALIDATION_CONFIG ? _VALIDATION_CONFIG.autocomplete_min_length : 2,
                closeOnSelect: false,
                tags: [],
                tokenSeparators: [','],
                ajax: {
                    type: 'POST',
                    url: self.endpoint || 'index.php?module=Vtiger&action=HandleOwnerFieldAjax&mode=loadOwnerList',
                    dataType: 'json',
                    data: function(term, page) {
                        term = term.trim();

                        // Skip ajax request when user enter only spaces
                        if (term.length == 0) {
                            $(self.$el).select2('close');
                            $(self.$el).select2('open');
                            return null;
                        }

                        var data = {
                            keyword: term,
                            user_only: false,
                            assignable_users_only: true,
                            skip_current_user: false
                        };

                        return data;
                    },
                    results: function(data) {
                        return {results: data.result};
                    },
                    transport: function(params) {
                        return jQuery.ajax(params);
                    }
                },
                formatSelection: function(object, container) {
                    if (object.id) {
                        let params = object.id.split(':');

                        // Change from div to span on 2020.02.20 by Phu Vo to prevent css rule from docs.min.css
                        let template =  `<div title="${object.text}">${object.text}</div>`;

                        // Process item type
                        container
                            .closest('.select2-search-choice')
                            .attr('data-type', params[0])

                        return template;
                    }

                    return object.text;
                }
            });

            // Init sortable
            $(self.$el).select2('container').find('ul.select2-choices').sortable({
                containment: 'parent',
                start: () => {
                    $(self.$el).select2('onSortStart');
                },
                update: () => {
                    $(self.$el).select2('onSortEnd');
                }
            });

            // Init event
            $(self.$el).on('change.select2', (e) => {
                self.$emit('input', $(e.target).val());
            });

            if (self.selectedTags) setTimeout(() => $(self.$el).select2('data', self.selectedTags).trigger('change'), 0);
        },
        destroyed: function() {
            $(this.$el).off().select2("destroy");
        }
    });

    Vue.component('parent-record', {
        props: ['parentId', 'parentModule', 'parentName', 'module', 'endpoint', 'submitUrl'],
        template: `
            <div class="parent-record" style="display: flex">
                <b-form-input role="select2"></b-form-input>
                <b-button @click="create" class="inline-button ml-2" style="margin-top: 1px"><i class="far fa-plus" aria-hidden="true"></i></b-button>
                <b-modal ref="quick-create-accounts" v-bind:title="getTitle()" :cancel-title="global.app.vtranslate('JS_LBL_CANCEL')" :ok-title="global.app.vtranslate('JS_LBL_SAVE')" @hide="createModalHide" @ok="submitCreateModal">
                    <b-overlay :show="overlay" rounded="sm">
                        <b-form name="quick_create">
                            <b-form-row>
                                <b-col cols="4">
                                    <label class="mt-3"><strong><span v-html="getFieldName('Accounts', 'accountname')"></span>:<span v-show="isRequired('Accounts', 'accountname')" class="text-danger"> *</span></strong></label>
                                </b-col>
                                <b-col cols="8">
                                    <b-form-input name="accountname" :data-rule-required="isRequired('Accounts', 'accountname')" v-model="form_data.accountname" class="mt-2"></b-form-input>
                                </b-col>
                            </b-form-row>
                            <b-form-row>
                                <b-col cols="4">
                                    <label class="mt-3"><strong><span v-html="getFieldName('Accounts', 'website')"></span>:<span v-show="isRequired('Accounts', 'website')" class="text-danger"> *</span></strong></label>
                                </b-col>
                                <b-col cols="8">
                                    <b-form-input name="website" :data-rule-required="isRequired('Accounts', 'website')" v-model="form_data.website" class="mt-2"></b-form-input>
                                </b-col>
                            </b-form-row>
                            <b-form-row>
                                <b-col cols="4">
                                    <label class="mt-3"><strong><span v-html="getFieldName('Accounts', 'phone')"></span>:<span v-show="isRequired('Accounts', 'phone')" class="text-danger"> *</span></strong></label>
                                </b-col>
                                <b-col cols="8">
                                    <b-form-input name="phone" :data-rule-required="isRequired('Accounts', 'phone')" v-model="form_data.phone" class="mt-2"></b-form-input>
                                </b-col>
                            </b-form-row>
                        </b-form>
                    </b-overlay>
                </b-modal>
            </div>
        `,
        data() {
            return {
                form_data: {},
                overlay: false,
            }
        },
        methods: {
            create () {
                if (this.parentModule) {
                    const ref = 'quick-create-' + this.parentModule.toLowerCase();
                    if (this.$refs[ref]) this.$refs[ref].show();
                }
            },
            createModalHide () {
                this.form_data = {};
            },
            submitCreateModal (event) {
                event.preventDefault();
                // Support validation
                const form = $(event.target).find('form');
                if (!form.valid()) return;

                const self = this;
                const params = Object.assign(this.form_data, {
                    module: self.parentModule,
                    action: 'SaveAjax',
                    assigned_user_id: _CURRENT_USER_META.id,
                    main_owner_id: _CURRENT_USER_META.id,
                });

                self.overlay = true;

                app.request.post({
                        url: self.submitUrl || 'index.php',
                        data: params
                    }).then((err, res) => {
                    self.overlay = false;

                    // Process error with message
                    if (err) {
                        let tmp = document.createElement("DIV");
                        tmp.innerHTML = err.message;
                        let message = tmp.textContent || tmp.innerText || "";
                        
                        return self.$bvToast.toast(message, {
                            title: err.title,
                            variant: 'danger',
                        });
                    }

                    if (err || !res) {
                        return self.$bvToast.toast('Đã có lỗi xảy ra', {
                            title: 'Lỗi',
                            variant: 'danger',
                        });
                    }

                    const selectInput = $(self.$el).find('[role="select2"]');
                    selectInput.select2('data', { id: res._recordId, text: res._recordLabel }).trigger('change');

                    self.$bvToast.toast('Tạo mới thành công', {
                        title: 'Thành công',
                        variant: 'success',
                    });

                    const ref = 'quick-create-' + this.parentModule.toLowerCase();
                    this.$refs[ref].hide();
                });
            },
            getTitle () {
                const singleModuleName = window._IFRAME_DATA.meta_data[this.parentModule].single;
                return app.vtranslate('CPChatBotIntegration.JS_CREATE_NEW_TITLE', { module_name: singleModuleName});
            },
            isRequired (moduleName, fieldName) {
                const metaData = window._IFRAME_DATA.meta_data;

                if (metaData[moduleName] && metaData[moduleName].all_fields && metaData[moduleName].all_fields[fieldName]) {
                    return metaData[moduleName].all_fields[fieldName].required === true;
                }

                return false;
            },
            getFieldName (moduleName, fieldName) {
                const metaData = window._IFRAME_DATA.meta_data;

                if (metaData[moduleName] && metaData[moduleName].all_fields && metaData[moduleName].all_fields[fieldName]) {
                    return metaData[moduleName].all_fields[fieldName].label;
                }

                return '';
            }
        },
        mounted: function() {
            const self = this;
            const module = self.module || 'Vtiger';
            const selectInput = $(self.$el).find('[role="select2"]');

            selectInput.select2({
                placeholder: app.vtranslate('JS_TYPE_TO_SEARCH'),
                minimumInputLength: _VALIDATION_CONFIG ? _VALIDATION_CONFIG.autocomplete_min_length : 2,
                ajax: {
                    type: 'POST',
                    url: self.endpoint || `index.php?module=${module}&search_module=${self.parentModule}&action=BasicAjax`,
                    dataType: 'JSON',
                    data: function(term, page) {
                        // Skip ajax request when user enter only spaces
                        if (term.length == 0) {
                            $(self.$el).select2('close');
                            $(self.$el).select2('open');
                            return null;
                        }

                        const data = {
                            search_value: term,
                        };

                        return data;
                    },
                    results: function(data) {
                        return {results: data.result.map((single) => ({ id: single.id, text: single.label }))};
                    },
                    transport: function(params) {
                        return jQuery.ajax(params);
                    }
                }
            });

            // Init events
            selectInput.on('change.select2', (e) => {
                self.$emit('input', selectInput.val());
            });

            // Init value
            if (self.parentId && self.parentName) selectInput.select2('data', { id: self.parentId, text: self.parentName });
        }
    });

    Vue.component("select-ajax", {
        props: ['placeholder', 'module', 'displayValue', 'url'],
        template: `
            <b-form-input></b-form-input>
        `,
        data() {
            return {

            }
        },
        mounted() {
            const self = this;

            $(this.$el).select2({
                placeholder: self.placeholder || 'Nhập để tìm kiếm',
                minimumInputLength: _VALIDATION_CONFIG ? _VALIDATION_CONFIG.autocomplete_min_length : 2,
                ajax: {
                    url: self.url || 'index.php',
                    type: 'POST',
                    dataType: 'JSON',
                    data: function(term, page) {
                        // Skip ajax request when user enter only spaces
                        if (term.length == 0) {
                            $(self.$el).select2('close');
                            $(self.$el).select2('open');
                            return null;
                        }

                        const data = {
                            module: 'Vtiger',
                            action: 'BasicAjax',
                            search_module: self.module,
                            search_value: term,
                        }

                        return data;
                    },
                    results: function(data) {
                        return {results: data.result.map((single) => ({ id: single.id, text: single.label }))};
                    },
                    transport: function(params) {
                        return jQuery.ajax(params);
                    }
                }
            });

            // Init events
            $(this.$el).on('change.select2', (e) => {
                self.$emit('input', $(self.$el).val());
            });

            // Init value
            if ($(this.$el).val()) {
                $(this.$el).select2('data', { id: $(this.$el).val(), text: this.displayValue });
            }
        },
        destroyed: function() {
            $(this.$el).off().select2("destroy");
        }
    });

    Vue.component("select-2", {
        props: ["options", "value"],
        template: `<select><slot></slot></select>`,
        mounted: function() {
          var vm = this;
          $(this.$el)
            // init select2
            .select2({ data: this.options })
            .val(this.value)
            .trigger("change")
            // emit event on change.
            .on("change", function() {
              vm.$emit("input", this.value);
            });
        },
        watch: {
          value: function(value) {
            // update value
            $(this.$el)
              .val(value)
              .trigger("change");
          },
          options: function(options) {
            // update options
            $(this.$el)
              .empty()
              .select2({ data: options });
          }
        },
        destroyed: function() {
          $(this.$el)
            .off()
            .select2("destroy");
        }
    });

    window.VueUtils = new class {
        hideBootstrapTooltip () {
            $('.b-tooltip').css({
                display: 'none',
                zIndex: '-10',
            });
        }
    }
})();

// Temp solution to lessThanToday validation issue
jQuery.validator.addMethod("less-than-today", function(value, element, params) {
    try {
        if(value) {
            var fieldDateInstance = app.helper.getDateInstance(value, app.getDateFormat());
            fieldDateInstance.setHours(0,0,0,0);
            var todayDateInstance = new Date();
            todayDateInstance.setHours(0,0,0,0);
            var comparedDateVal = todayDateInstance - fieldDateInstance;
            if(comparedDateVal <= 0){
                return false;
            }
        }
        return true;
    } catch(err) {
        return false;
    }
}, function(params, element) {
        return app.vtranslate('JS_SHOULD_BE_LESS_THAN_CURRENT_DATE');
    }
);