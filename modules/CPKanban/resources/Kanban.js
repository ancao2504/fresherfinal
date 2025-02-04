/*
*   Kanban.js
*   Author: Phu Vo
*   Version: 1.0.1
*   Date: 2019-02-21 (ver 1.0.0)
*   Purpose: Kanban App View Controller
*/

// Pass window object to vue prototype
Vue.prototype.global = window;

// Kanban common variables
var KanbanVars = {
    module: 'CPKanban',
    dateFormat: {
        en_us: 'MMMM DD',
        vn_vn: 'DD MMMM',
    },
    defaultAvatar: 'resources/images/default-user-avatar.png',
    draggableOptions: {
        group: 'item',
        scroll: false,
        animation: 200,
        ghostClass: 'ghost',
    },
};

// init window.related_uimeta
window.related_uimeta = {};

Vue.component('chosen-select', {
    props: {
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
        value (val, pre) {
            $(this.$el).val(val).trigger('chosen:updated');
        }
    },
    mounted () {
        $(this.$el)
            .val(this.value)
            .chosen()
            .on('change', e => this.$emit('input', $(this.$el).val()));
    },
    updated () {
        KanbanHelper.resetChosen($(this.$el));
    },
    destroyed () {
        $(this.$el).chosen('destroy');
    }
});

Vue.component('multiselect', {
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
        value (val, pre) {
            $(this.$el).val(val).multiselect('refresh');
        }
    },
    mounted () {
        $(this.$el).val(this.value).multiselect({
            enableFiltering: Boolean(this.filterable),
            disableIfEmpty: true,
            includeSelectAllOption: false,
            maxHeight: 200,
            numberDisplayed: 1,
            buttonText: function (options, select) {
                let length = options.length;

                if (length == 0) {
                    return app.vtranslate('JS_KANBAN_COLUMN');
                }
                else {
                    let label = app.vtranslate('JS_CHOOSED');
                    return `${length} ${label}`;
                }
            },
        }).on('change', e => this.$emit('input', $(this.$el).val()));
    },
    updated () {
        $(this.$el).multiselect('rebuild').hide();
        $(this.$el).val(this.value).multiselect('refresh');
    },
    destroyed () {
        $(this.$el).multiselect('destroy');
    },
});

window.kanban = new Vue({
    el: 'div#kanbanApp',
    data: {
        meta: { ..._KANBAN_META },
        options: {
            'module_name': '',
            'picklist_name': '',
            'kanban_columns': [],
            'item_fields': [],
            'sorting_field': '',
            'filter': '',
        },
        ui: {
            'modules': {},
            'picklist_fields': {},
            'module_fields': {},
            'picklist_values': {},
        },
        data: {},
        lists: {},
        cache: {},
    },
    watch: {
        data (val, pre) {
            // When data change => mapping to display list
            if (this.cache.updateItemSingle) {
                this.cache.updateItemSingle = false;
            }
            else {
                this.updateItems(val);
            }
        },
        lists (val, pre) {
            // Check has scroll bar for new column
            $(this.$el).find('div.items.fancyScrollbar').each(function() {
                KanbanHelper.hasScrollBar($(this));
            });
        },
        'options.module_name' (val, pre) {
            this.cache.emptyData = false;

            if (val) {
                KanbanHelper.loadRelatedUimeta(val);
            }
        }
    },
    methods: {
        // UI get display data function goes here
        getColColor (col) {
            let meta = this.ui.picklist_values[col];
            let outCludes = ['#ffffff', '#000000'];
            
            if (meta) { // return color not include white and black;
                if (!outCludes.includes(meta.color)) return meta.color;
            }
            
            return;
        },
        getFieldLabel (field) {
            let meta  = this.ui.module_fields[field];
            return meta && meta.label;
        },
        getPicklistItemLabel (field) {
            let meta = this.ui.picklist_values[field];

            if (meta) return meta.label;
            return field;
        },
        getPicklistItemName (field) {
            let meta = this.ui.picklist_values[field];
            return meta && meta.name;
        },
        getColumnItemsCount (colName) {
            if (this.lists[colName]) return this.lists[colName].length;
            return 0;
        },
        getUitype (field) {
            let moduleMeta = this.meta[this.options.module_name];

            if (moduleMeta && moduleMeta.all_fields[field]) return moduleMeta.all_fields[field].uitype;
            return '';
        },
        // UI get display data function end here
        // Quick edit item 
        quickEdit (id, column) {
            if (id && column) {
                KanbanHelper.openQuickEditModal({
                    data: {
                        module: this.options.module_name,
                        record: id,
                    },
                    postSaveCb: () => {
                        // Modidied by Phu Vo on 2019.07.30 to update item new position
                        let newColumn = column;
                        let picklistName = this.options.picklist_name;
                        let preSaveCache = this.cache.itemQuickEditFormData;

                        if (preSaveCache[picklistName] != column) newColumn = preSaveCache[picklistName];

                        this.loadItem(id, newColumn, column);
                        // End update item new position
                    }
                });
            }
        },
        optionHandler (sender) {
            if (sender == 'module_name') {
                let picklistFields = {};
                let val = this.options.module_name;

                if (val) {
                    // Update pick list option
                    let picklistFieldsMeta = this.meta[val].picklist_fields;
    
                    Object.keys(picklistFieldsMeta).forEach(fieldName => {
                        picklistFields[fieldName] = {
                            name: fieldName,
                            label: this.meta[val].all_fields[fieldName].label,
                            selected: true
                        }
                    });

                    // Chose first option when it have value
                    this.options.sorting_field = KanbanHelper.getSelectDefault($(this.$el), 'sorting_field');
                    this.options.filter = KanbanHelper.getSelectDefault($(this.$el), 'filter');
                }
                else {
                    this.options.sorting_field = '';
                    this.options.filter = '';
                }
    
                // Update UI
                this.ui.picklist_fields = picklistFields;
                this.ui.module_fields = this.meta[val] ? this.meta[val].all_fields : {};
                this.ui.picklist_values = {}
                
                // Save setting preventDefault();
                this.cache.saveSettingDefault = true;

                // reset defaul modal
                this.options.picklist_name = '';
                this.options.kanban_columns = [];
                this.options.item_fields = [];
            }
            if (sender == 'picklist_name') {
                let picklistValues = {};
                let val = this.options.picklist_name;

                if (val) {
                    // Update picklist value option
                    let picklistFieldsMeta = this.meta[this.options.module_name].picklist_fields;
                    let picklistValueMeta = picklistFieldsMeta[val];
    
                    Object.keys(picklistValueMeta).forEach(fieldName => {
                        picklistValues[fieldName] = {
                            name: fieldName,
                            label: picklistValueMeta[fieldName].label,
                            color: picklistValueMeta[fieldName].color,
                            selected: true
                        }
                    });
                }
    
                // Update Ui
                this.ui.picklist_values = picklistValues;

                // Default select all multi field option
                this.options.kanban_columns = KanbanHelper.getMultiselectDefault(Object.keys(picklistValues));

                // set flag saveSettingDefault
                this.cache.saveSettingDefault = true;

                // Call to Update data
                if (this.isEnabled('saveSetting')) {
                    this.saveSetting();
                }
            }
            if (sender == 'kanban_columns') {
                // Call to Update data
                if (this.options.kanban_columns instanceof Array && this.options.kanban_columns.length > 5) {
                    // Revert option from cache
                    KanbanHelper.notifyError(app.vtranslate('JS_MAX_KANBAN_COLUMNS_ERROR'));
                    this.options.kanban_columns = this.cache.kanban_columns;
                    return;
                }

                if (this.isEnabled('saveSetting') && !this.cache.saveSettingDefault && !this.kanbanColumnLock) {
                    // reset data
                    this.data = {};
                    this.saveSetting();
                }
            }
            if (sender == 'item_fields') {
                // Call to Update data
                if (this.isEnabled('saveSetting')) {
                    this.saveSetting();
                }
            }
            if (sender == 'sorting_field') {
                // Call to Update data
                if (this.isEnabled('saveSetting')) {
                    this.saveSetting();
                }
            }
            if (sender == 'filter') {
                // Call to Update data
                if (this.isEnabled('saveSetting')) {
                    this.saveSetting();
                }
            }
        },
        searchHandler (e) {
            if (this.cache.seachTimeout) {
                clearTimeout(this.cache.seachTimeout);
                delete this.cache.seachTimeout;
            }

            this.cache.seachTimeout = setTimeout(() => {
                let self = this;
                
                let target = $(e.target);
                let searchString = KanbanHelper.getSearchString(target.val());
                let forCol = target.data('for');
                let colItemsDom = $(`div.items[data-col="${forCol}"]`).find('div.item');

                if (!searchString) {
                    colItemsDom.show();
                }
                else {
                    if (this.data[forCol] && this.data[forCol]['items']) {
                        colItemsDom.each(function() {
                            let searchFrom = KanbanHelper.getSearchString(JSON.stringify(self.data[forCol]['items'][$(this).data('key')]));

                            if (searchFrom.indexOf(searchString) > -1) {
                                $(this).show();
                            }
                            else {
                                $(this).hide();
                            }
                        });
                    }
                }

                // Check has scroll bar for new column
                $(this.$el).find('div.items.fancyScrollbar').each(function() {
                    KanbanHelper.hasScrollBar($(this));
                });
            }, 500);
        },
        saveSetting () {
            let params = { ...this.options };
            let itemFields = null;

            // assign item fields
            if (this.options.item_fields && this.options.item_fields instanceof Array && this.options.item_fields.length > 0) {
                itemFields = this.options.item_fields;
            }

            params.mode = 'saveSettings';

            params.params = {
                'kanban_columns': this.options.kanban_columns,
                'item_fields': itemFields,
                'sorting_field': this.options.sorting_field,
                'filter': this.options.filter,
            }

            if (this.cache.saveSettingDefault) params.params.is_default = true;

            this.cache.emptyData = false;
            
            let callback = (error, res) => {
                if (res) {
                    if (res.settings && res.settings.is_default) delete res.settings.is_default;

                    this.data = res.data;
                    this.options = { ...this.options, ...res.settings };
                    if (this.cache.saveSettingDefault) this.cache.saveSettingDefault = false;

                    // Save cache with valid kanban_columns
                    if (this.options.kanban_columns) this.cache.kanban_columns = this.options.kanban_columns;

                    this.cache.emptyData = (res.data instanceof Array && res.data.length == 0);
                }
            }

            KanbanHelper.request(params, callback);
        },
        reloadData () {
            let params = {
                'mode': 'reload',
                'module_name': this.options.module_name,
                'picklist_name': this.options.picklist_name
            }

            let callback = (err, res) => {
                if (res) {
                    if (res.result) res = res.result;
                    
                    if (res.data) this.data = res.data;
                    if (res.meta) this.meta = res.meta;

                    this.cache.emptyData = res.data instanceof Array && res.data.length == 0; // Added by Phu Vo on 2019.09.19 to fix empty data block could not hide when refresh
                }
            }
            
            KanbanHelper.request(params, callback);
        },
        openItemFieldsModal () {
            let self = this;

            return KanbanHelper.showModal({
                ui: 'div#KanbanDisplayFieldSelector',
                show: modal => {
                    let form = modal.find('form[name="kanban_display_form"]');
                    let select = modal.find('select[name="item_fields"]');
                    // Update item fields select
                    select.val(self.options.item_fields);
                    select.trigger("liszt:updated");

                    let oldOrder = self.options.item_fields || [];
                    let newOrder = oldOrder.map((field) => {
                        return Object.keys(self.ui.module_fields).indexOf(field);
                    });
                    
                    KanbanHelper.reOrder(select.next('.chzn-container').find('.chzn-choices'), newOrder);

                    form.vtValidate({
                        submitHandler: () => {
                            // Validate max option
                            let value = select.val();

                            if (value instanceof Array && value.length > 5) {
                                KanbanHelper.notifyError(app.vtranslate('JS_MAX_ITEM_FIELDS_ERROR'));
                                return false;
                            }
                            
                            let selectedItems = [];
                            let selectedItemsDom = form.find('div.sortable-select a.search-choice-close');

                            selectedItemsDom.each(function() {
                                let index = $(this).attr('rel');
                                selectedItems.push(Object.keys(self.ui.module_fields)[index]);
                            });

                            self.options.item_fields = selectedItems; // Save to model
                            self.optionHandler('item_fields'); // Trigger handler
                            KanbanHelper.hideModal();
                        }
                    });
                }
            });
        },
        openQuickCreateActionSheet (item) {
            return KanbanHelper.showModal({
                ui: 'div#CalendarQuickCreate',
                show: modal => {
                    modal.find('.parent-name').text(item.meta.name);

                    modal.data({
                        id: item.id,
                        name: item.meta.name,
                        col: item.column,
                        assigned_user_id: item.meta.assigned_user_id
                    });
                }
            });
        },
        isEnabled (sender) {
            // switch and nothing more, it will return at each case
            switch (sender) {
                case 'openItemFieldsModal':
                    return Boolean(this.options.module_name);
                case 'saveSetting':
                    return Boolean(this.options.module_name && this.options.picklist_name && this.options.sorting_field && this.options.filter);
                case 'setDefault':
                    return Boolean((this.options.module_name && this.options.picklist_name) && !this.options.sorting_field || !this.options.filter);
                default: return;
            }
        },
        dragOnEnd (e) {
            if (e.from != e.to) {
                this.moveItem(e);
            }
        },
        moveItem (e) {
            // Get item old & new columname using jquery attr => avoid jquery data caching
            let oldColName = $(e.from).attr('data-col');
            let newColName = $(e.to).attr('data-col');

            // Generate new item ids of each column
            let newOrder = {
                [oldColName]: this.lists[oldColName].map(item => {
                    return item.id;
                }),
                [newColName]: this.lists[newColName].map(item => {
                    return item.id;
                }),
            };

            let itemId = this.lists[newColName][e.newIndex].id;
            
            let params = {
                'mode': 'move',
                'module_name': this.options.module_name,
                'picklist_name': this.options.picklist_name,
                'source_column': oldColName,
                'dest_column': newColName,
                'new_order': newOrder,
                'item_id': itemId,
            }

            let callback = (err, res) => {
                // Error handling
                if (res && !res.success) {
                    this.reloadData();
                }
                // Success
                else {
                    if (res.item) this.updateItem(itemId, res.item, newColName, oldColName);
                }
            }

            KanbanHelper.request(params, callback);
        },
        quickCreate (target, jButton) {
            let item = jButton.closest('div.kb-modal');
            let id = item.data('id');
            let column = item.data('col');

            // map activity name with module name
            let mapping = { Call: 'Events', Task: 'Calendar', Meeting: 'Events' };
            let moduleName = this.options.module_name;

            // Hide modal, cus we are going to open vtiger modal
            KanbanHelper.hideModal();

            let quickCreateParams = {
                data: { assigned_user_id: item.data('assigned_user_id') },
                parentModule: moduleName,
                parentId: id,
                preShowCb: container => {
                    // For now hide button go to full form
                    container.find('#goToFullForm').remove();
                },
                postShowCb: form => {
                    // [fix me] not work
                    app.helper.registerLeavePageWithoutSubmit(form);
                    app.helper.registerModalDismissWithoutSubmit(form);
                },
                postSaveCb: () => {
                    this.loadItem(id, column);
                }
            }

            // Update activity type
            if (mapping[target] == 'Events') quickCreateParams.data.activitytype = target;

            if (mapping[target]) vtUtils.openQuickCreateModal(mapping[target], quickCreateParams);
        },
        loadItem (id, column, preColumn) {
            let params = {
                mode: 'loadItem',
                module_name: this.options.module_name,
                picklist_name: this.options.picklist_name,
                item_id: id
            }

            let callback = (err, res) => {
                if (res) {
                    if (res.result) res = res.result;

                    if (res.item) {
                        this.updateItem(id, res.item, column, preColumn);
                    }
                }
            }

            KanbanHelper.request(params, callback, true);
        },
        updateItems (data) {
            let lists = {};

            // Map data and lists
            if (this.options.kanban_columns && this.options.kanban_columns instanceof Array) {
                this.options.kanban_columns.forEach(col => {
                    lists[col] = [];
                    
                    if (data[col]&& data[col]['items']) {
                        data[col]['items'].forEach(item => {
                            let assignParams = { // format item to map lists
                                column: col
                            };

                            lists[col].push({ ...assignParams, ...item });
                        });
                    }
                });

                this.lists = lists;
            }
        },
        updateItem (id, item, column, preColumn) {
            // Todo: find preCol if it not passed to function
            let data = { ...this.data }

            // Initial data if it empty
            if (!data[column]) {
                data[column] = {
                    meta: {},
                    items: [],
                }
            }

            // Find new item index from lists
            let itemIndex = this.lists[column].findIndex(item => item.id == id);
            let oldIndex = this.data[column]['items'].findIndex(item => item.id == id);

            // Insert item to new column or update index in data if it has changed
            if (preColumn && preColumn != column || itemIndex != oldIndex) {
                data[preColumn].items.splice(oldIndex, 1);
                data[column].items.splice(itemIndex, 0, item);
            }

            // Assign data and trigger render dom without render whole lists
            this.cache.updateItemSingle = true;
            this.data = data;

            // Render Item in list only
            let lists = { ...this.lists };

            // Update column data for new item
            item.column = column;
            
            lists[column][itemIndex] = item; // Add to new column

            // Update List
            this.lists = lists;
        },
    },
    created () {
        // Show app dom after vue object is ready
        $('div#kanbanApp').css({ display: 'flex' });

        // Init vtiger events, added by Phu Vo on 2019.07.30
        app.event.on(Vtiger_Edit_Js.recordPresaveEvent, (e, data) => {
            this.cache.itemQuickEditFormData = data;
        });
        // End Init vtiger events
    },
    mounted () {
        // Init ui data
        this.ui.modules  = Object.keys(this.meta);

        // Prevent related popup click
        app.event.on('pre.popupSelect.click', event => {
            // Only prevent click with popup open from telesales item
            if ($('#popupPageContainer').closest('.modal').hasClass('preventClick')) event.preventDefault();
        });

        // Prevent click from overlay preview
        jQuery('#helpPageOverlay').on('shown.bs.modal', event => {
            let modal = $(event.target);
            let previewPopup = modal.find('.quickPreview');
            let module = previewPopup.find('#sourceModuleName').val();
            let record = previewPopup.find('#recordId').val();

            modal.addClass('tele-modal preventClick');
            previewPopup.find('a').attr('target', '_blank');
            previewPopup.find('.quickPreviewActions .pull-right').remove();

            previewPopup.find('.quickPreviewActions .pull-left button.btn-success')
                .attr('onclick', '')
                .on('click', () => {
                    let link = `index.php?module=${module}&view=Detail&record=${record}`;
                    window.open(link, '_blank');
                });
            
            let moreRecentUpdates = previewPopup.find('.moreRecentUpdates').clone().attr('class', 'btn btn-success moreRecentUpdatesNewTab');

            moreRecentUpdates.off('click').on('click', () => {
                let link = `index.php?module=${module}&view=Detail&record=${record}&mode=showRecentActivities&page=1&tab_label=LBL_UPDATES`;
                window.open(link, '_blank');
            });

            previewPopup.find('.moreRecentUpdates').replaceWith(moreRecentUpdates);
        });
    },
});

// Added by Hieu Nguyen on 2020-03-10 to show default kanban view
jQuery(function ($) {
    if (_SELECTED_MODULE) {
        $('[name="module"]').val(_SELECTED_MODULE).trigger('change');

        setTimeout(() => {
            if (_SELECTED_PICKLIST) {
                $('[name="picklist_field"]').val(_SELECTED_PICKLIST).trigger('change');
            }
        }, 100);
    }
});
// End Hieu Nguyen