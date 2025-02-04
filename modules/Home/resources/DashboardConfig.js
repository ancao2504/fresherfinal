/**
 * Name: DashboardConfig.js
 * Author: Phu Vo
 * Date: 2020.10.12
 */

jQuery(function($) {
    window.DashboardConfig = new class {
        constructor () {
            this.reloadOnAddDashboardWidgetModalClose = false;
            this._registerEvents();
        }

        _dataTableLanguages = {
            emptyTable: app.vtranslate('JS_DATATABLES_NO_DATA_AVAILABLE'),
            info: app.vtranslate('JS_DATATABLES_FOOTER_INFO'),
            infoEmpty: app.vtranslate('JS_DATATABLES_FOOTER_INFO_NO_ENTRY'),
            lengthMenu: app.vtranslate('JS_DATATABLES_LENGTH_MENU'),
            loadingRecords: app.vtranslate('JS_DATATABLES_LOADING_RECORD'),
            processing: app.vtranslate('JS_DATATABLES_PROCESSING'),
            search: app.vtranslate('JS_DATATABLES_SEARCH'),
            zeroRecords: app.vtranslate('JS_DATATABLES_NO_RECORD'),
            sInfoFiltered: app.vtranslate('JS_DATATABLES_INFO_FILTERED'),
            paginate: {
                first: app.vtranslate('JS_DATATABLES_FIRST'),
                last: app.vtranslate('JS_DATATABLES_LAST'),
                next: app.vtranslate('JS_DATATABLES_PAGINATE_NEXT_PAGE'),
                previous: app.vtranslate('JS_DATATABLES_PAGINATE_PREVIOUS_PAGE')
            }
        }

        confirmButtons = {
            cancel: {
                label: app.vtranslate('JS_CANCEL'),
                className : 'btn-default confirm-box-btn-pad pull-right'
            },
            confirm: {
                label: app.vtranslate('JS_CONFIRM'),
                className : 'confirm-box-ok confirm-box-btn-pad btn-primary'
            },
        }

        _handleAjaxError (err, res) {
            if (err) {
                app.helper.showAlertBox({
                    message: `<div class="homepage-config-confirmation">${err.message}</div>`,
                });

                return false;
            }

            if (!res) {
                let message = app.vtranslate('JS_THERE_WAS_SOMETHING_ERROR');

                app.helper.showAlertBox({
                    message: `<div class="homepage-config-confirmation">${message}</div>`,
                });

                return false;
            }

            return true;
        }

        _showAjaxModal (name, handler = null, requestParams = {}, params = {}) {
            const self = this;

            requestParams = Object.assign({
                module: 'Home',
                view: 'DashboardAjax',
                mode: name,
            }, requestParams);

            app.helper.showProgress();

            return app.request.post({data: requestParams}).then((err, res) => {
                app.helper.hideProgress();

                if (!self._handleAjaxError(err, res)) return;

                params = Object.assign({ cb: null }, params);

                if (typeof handler == 'function') params.cb = handler;

                app.helper.showModal(res, params)
            });
        }

        _showAjaxPopup (name, handler = null, requestParams = {}, params = {}) {
            const self = this;

            requestParams = Object.assign({
                module: 'Home',
                view: 'DashboardAjax',
                mode: name,
            }, requestParams);

            app.helper.showProgress();

            return app.request.post({data: requestParams}).then((err, res) => {
                app.helper.hideProgress();

                if (!self._handleAjaxError(err, res)) return;

                params = Object.assign({ cb: null }, params);

                if (typeof handler == 'function') params.cb = handler;

                app.helper.showPopup(res, params)
            });
        }

        _registerEvents () {
            const self = this;

            $('.moreSettings .configDashboard').on('click', () => {
                this.showDashboardConfigModal();
            });

            $('.exitEditLayoutMode').on('click', () => {
                this.exitDashboardEditMode();
            });

            $('.applyLayoutToUsers').on('click', () => {
                // Call request to check template validity
                const requestParams = {
                    module: 'Home',
                    action: 'DashboardAjax',
                    mode: 'checkTemplateVadility',
                };

                app.helper.showProgress();

                app.request.post({ data: requestParams }).then((err, res) => {
                    app.helper.hideProgress();

                    if (!self._handleAjaxError(err, res)) return;

                    this.showApplyLayoutToUserConfirmation().then(() =>{
                        this.applyLayoutToUser();
                    });
                });
            });

            $('.showAddWidgetModal').on('click', () => {
                this.showAddWidgetToDashboardModal();
            });

            $('.removeAllWidget').on('click', function () {
                self.removeAllWidgetFromTab($(this));
            });

            // Disable drag and drop
            if (!window._CAN_EDIT_DASHBOARD) {
                Vtiger_DashBoard_Js?.gridster?.disable();
            }
        }

        _getSelectOptions (select) {
            const options = [];
            select.find('option').each((index, target) => {
                options.push({
                    id: $(target).attr('value'),
                    text: $(target).text(),
                });
            });

            return options;
        }

        _getSearchableString (str) {
            if (!str) return '';
            str = '' + str;
            str = str.unUnicode();
            return str.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
        }

        showDeleteHomepageComfirmation (name) {
            const replaceParams = { name };
            let message = app.vtranslate('Home.JS_DASHBOARD_DELETE_DASHBOARD_CONFIRMATION', replaceParams);

            return app.helper.showConfirmationBox({
                title: app.vtranslate('Home.JS_DASHBOARD_DELETE_DASHBOARD'),
                buttons: this.confirmButtons,
                message: `<div class="homepage-config-confirmation">${message}</div>`,
            });
        }

        showRemoveWidgetConfirmation (name, category) {
            const replaceParams = { name, category };
            let message = app.vtranslate('Home.JS_DASHBOARD_REMOVE_WIDGET_CONFIRMATION', replaceParams);

            return app.helper.showConfirmationBox({
                title: app.vtranslate('Home.JS_DASHBOARD_REMOVE_WIDGET'),
                buttons: this.confirmButtons,
                message: `<div class="homepage-config-confirmation">${message}</div>`,
            });
        }

        showDeleteCategoryConfirmation (name) {
            const replaceParams = { name };
            let message = app.vtranslate('Home.JS_DASHBOARD_DELETE_CATEGORY_CONFIRMATION', replaceParams);

            return app.helper.showConfirmationBox({
                title: app.vtranslate('Home.JS_DASHBOARD_DELETE_CATEGORY'),
                buttons: this.confirmButtons,
                message: `<div class="homepage-config-confirmation">${message}</div>`,
            });
        }

        showOverrideHomepageRoleErrorNotification (role, cb) {
            const replaceParams = { role };
            let message = app.vtranslate('Home.JS_DASHBOARD_DUPLICATE_ROLE_WARNING', replaceParams);

            return app.helper.showAlertBox({ message: `<div class="homepage-config-confirmation">${message}</div>` }, cb);
        }

        showExitEditHomepageModeConfirmation () {
            let message = app.vtranslate('Home.JS_DASHBOARD_EXIT_EDIT_HOMEPAGE_MODE_CONFIRMATION');

            return app.helper.showConfirmationBox({
                title: app.vtranslate('Home.JS_DASHBOARD_WARNING'),
                buttons: this.confirmButtons,
                message: `<div class="homepage-config-confirmation">${message}</div>`,
            });
        }

        showApplyLayoutToUserConfirmation () {
            let message = app.vtranslate('Home.JS_DASHBOARD_APPLY_LAYOUT_CONFIRMATION');

            return app.helper.showConfirmationBox({
                title: app.vtranslate('Home.JS_DASHBOARD_WARNING'),
                buttons: this.confirmButtons,
                message: `<div class="homepage-config-confirmation">${message}</div>`,
            });
        }

        showRemoveAllWidgetFromTab (name) {
            const replaceParams = { name };
            let message = app.vtranslate('Home.JS_DASHBOARD_REMOVE_ALL_WIDGET_FROM_TAB_CONFIRMATION', replaceParams);

            return app.helper.showConfirmationBox({
                title: app.vtranslate('Home.JS_DASHBOARD_WARNING'),
                buttons: this.confirmButtons,
                message: `<div class="homepage-config-confirmation">${message}</div>`,
            });
        }

        showRemoveAllWidgetFromCategoryConfirmatiom (name) {
            const replaceParams = { name };
            let message = app.vtranslate('Home.JS_DASHBOARD_REMOVE_ALL_WIDGET_FROM_CATEGORY_CONFIRMATION', replaceParams);

            return app.helper.showConfirmationBox({
                title: app.vtranslate('Home.JS_DASHBOARD_WARNING'),
                buttons: this.confirmButtons,
                message: `<div class="homepage-config-confirmation">${message}</div>`,
            });
        }

        enterDashboardEditMode (dashboardId) {
            const requestParams = {
                module: 'Home',
                action: 'DashboardAjax',
                mode: 'enterDashboardEditMode',
                id: dashboardId,
            }

            let href = 'index.php?' + $.param(requestParams);
            window.location = href;
        }

        exitDashboardEditMode () {
            const requestParams = {
                module: 'Home',
                action: 'DashboardAjax',
                mode: 'exitDashboardEditMode',
            }

            let href = 'index.php?' + $.param(requestParams);
            window.location = href;
        }

        showEditDashboardPopup (homepageId = null, isDuplicate = false) {
            return this._showAjaxPopup(
                'getEditDashboardModal',
                this.handleEditHomepagePopup.bind(this),
                {
                    id: homepageId ,
                    is_duplicate: isDuplicate ? 1 : 0
                }
            );
        }

        showSelectWidgetPopup (categoryId = null) {
            return this._showAjaxPopup(
                'getSelectWidgetModal',
                this.handleSelectWidgetPopup.bind(this),
                { category_id: categoryId }
            );
        }

        showEditCategoryPopup (categoryId = null) {
            return this._showAjaxPopup(
                'getEditCategoryModal',
                this.handleEditCategoryModal.bind(this),
                { category_id: categoryId }
            );
        }

        showAddDashletPopup () {
            return this._showAjaxPopup('addDashletModal', this.handleAddDashletPoup.bind(this));
        }

        showDashboardConfigModal () {
            return this._showAjaxModal('getDashboardConfigModal', this.handleConfigHomepageModel.bind(this));
        }

        deleteDashboardTemplate(templateId) {
            const requestParams = {
                module: 'Home',
                action: 'DashboardAjax',
                mode: 'deleteDashboardTemplate',
                id: templateId,
            }

            return app.request.post({ data: requestParams });
        }

        removeWidgetFromCategory(widgetId, categoryId) {
            const requestParams = {
                module: 'Home',
                action: 'DashboardAjax',
                mode: 'removeWidgetFromCategory',
                id: widgetId,
                category_id: categoryId,
            }

            return app.request.post({ data: requestParams });
        }

        handleConfigHomepageModel (modal) {
            const self = this;
            this.configModal = modal;

            // Handler events for homepage management
            this.HomepageListTable = modal.find('#homepage-list').DataTable({
                scrollY: '300px',
                scrollCollapse: true,
                responsive: true,
                ordering: false,
                paging: false,
                bInfo: false,
                processing: true,
                language: self._dataTableLanguages,
                ajax: {
                    url: 'index.php',
                    type: 'POST',
                    dataType: 'JSON',
                    data: function (data) {
                        return $.extend({}, data, {
                            module: 'Home',
                            action: 'DashboardAjax',
                            mode: 'getDashboardTemplates',
                        });
                    }
                },
                columns: [
                    { data: 'name', name: 'name' },
                    { data: 'status', name: 'status'},
                    {
                        data: 'roles',
                        render: function (data, type, row) {
                            return `<div class="roles data-row" title="${data}">${data}`;
                        }
                    },
                    { data: 'permission', name: 'permission' },
                    {
                        data: 'actions',
                        name: 'actions',
                        render: function (data, type, row) {
                            let rowString = JSON.stringify(row);
                            let htmlString = `
                                <div class="actions-wrapper" data-row='${rowString}'>
                                    <i class="far fa-trash-alt deleteDashboard" aria-hidden="true" data-toggle="dropdown" title="${app.vtranslate('Home.JS_DASHBOARD_DELETE_DASHBOARD')}"></i>
                                    <i class="far fa-copy duplicateDashboard" aria-hidden="true" data-toggle="dropdown" title="${app.vtranslate('Home.JS_DASHBOARD_DUPLICATE_DASHBOARD')}"></i>
                                    <i class="far fa-paint-brush editDashboardLayout" aria-hidden="true" data-toggle="dropdown" title="${app.vtranslate('Home.JS_DASHBOARD_EDIT_LAYOUT')}"></i>
                                    <i class="far fa-pen editDashboard" aria-hidden="true" data-toggle="dropdown" title="${app.vtranslate('Home.JS_DASHBOARD_EDIT_DASHBOARD_INFORMATION')}"></i>
                            `;

                            if (row.raw.status == 'Active') {
                                htmlString += `
                                    <i class="far fa-check applyDashboard" aria-hidden="true" data-toggle="dropdown" title="${app.vtranslate('Home.JS_DASHBOARD_APPLY_DASHBOARD_LAYOUT_TO_USERS')}"></i>
                                `;
                            }

                            htmlString += `
                                </div>
                            `;
                            return htmlString;
                        }
                    },
                    {
                        data: 'payload',
                        name: 'payload',
                        visible: false,
                        render: function (data, type, row) {
                            return self._getSearchableString(JSON.stringify(row));
                        }
                    },
                ],
            });

            $(this.HomepageListTable.table().container()).find('.dataTables_filter').hide();

            modal.find('[name="category_keyword"]').on('keyup', (event) => {
                const searchKeyword = this._getSearchableString(modal.find('[name="category_keyword"]').val());
                this.HomepageListTable.search(searchKeyword).draw();
            });

            this.HomepageListTable.on('draw.dt', () => {
                
                // Delete dashboard template
                $(this.HomepageListTable.table().container()).find('.deleteDashboard').off('click').on('click', function(event) {
                    let wrapper = $(this).closest('.actions-wrapper');
                    let rowData = wrapper.data('row');

                    self.showDeleteHomepageComfirmation(rowData.name).then(() => {
                        app.helper.showProgress();

                        self.deleteDashboardTemplate(rowData.id).then((err, res) => {
                            app.helper.hideProgress();

                            if (!self._handleAjaxError(err, res)) return;

                            self.HomepageListTable.ajax.reload();
                        });
                    });
                });

                // Delete dashboard template
                $(this.HomepageListTable.table().container()).find('.editDashboardLayout').off('click').on('click', function(event) {
                    let wrapper = $(this).closest('.actions-wrapper');
                    let rowData = wrapper.data('row');

                    self.enterDashboardEditMode(rowData.id);
                });

                // Delete dashboard template
                $(this.HomepageListTable.table().container()).find('.editDashboard').off('click').on('click', function(event) {
                    let wrapper = $(this).closest('.actions-wrapper');
                    let rowData = wrapper.data('row');

                    self.showEditDashboardPopup(rowData.id);
                });

                $(this.HomepageListTable.table().container()).find('.duplicateDashboard').off('click').on('click', function(event) {
                    let wrapper = $(this).closest('.actions-wrapper');
                    let rowData = wrapper.data('row');

                    self.showEditDashboardPopup(rowData.id, true);
                });

                $(this.HomepageListTable.table().container()).find('.applyDashboard').off('click').on('click', function(event) {
                    let wrapper = $(this).closest('.actions-wrapper');
                    let rowData = wrapper.data('row');

                    // Call request to check template validity
                    const requestParams = {
                        module: 'Home',
                        action: 'DashboardAjax',
                        mode: 'checkTemplateVadility',
                        template_id: rowData.id,
                    };

                    app.helper.showProgress();

                    app.request.post({ data: requestParams }).then((err, res) => {
                        app.helper.hideProgress();

                        if (!self._handleAjaxError(err, res)) return;
                        
                        self.showApplyLayoutToUserConfirmation().then(() =>{
                            const requestParams = {
                                module: 'Home',
                                action: 'DashboardAjax',
                                mode: 'applyCurrentDashboardTemplateToUsers',
                                template_id: rowData.id,
                            }
    
                            app.helper.showProgress();
    
                            app.request.post({ data: requestParams }).then((err, res) => {
                                app.helper.hideProgress();
    
                                if (!self._handleAjaxError(err, res)) return;
    
                                app.helper.showSuccessNotification({ message: app.vtranslate('Home.JS_DASHBOARD_APPLY_DASHBOARD_LAYOUT_TO_USERS_SUCCESS_MSG') });
                            });
                        });
                    });
                });
            });

            this.WidgetListTable = modal.find('#dashlet-list').DataTable({
               scrollY: '220px',
               scrollCollapse: true,
               responsive: true,
               ordering: false,
               processing: true,
               language: self._dataTableLanguages,
               ajax: {
                    url: 'index.php',
                    type: 'POST',
                    dataType: 'JSON',
                    data: function (data) {
                        return $.extend({}, data, {
                            module: 'Home',
                            action: 'DashboardAjax',
                            mode: 'getWidgetsByCategory',
                            category_id: modal.find('[name="widget_category"]').val(),
                        });
                    }
                },
                columns: [
                    { data: 'name', name: 'name' },
                    { data: 'type', name: 'type' },
                    { data: 'primary_module', name: 'primary_module' },
                    {
                        data: 'actions',
                        name: 'actions',
                        render: function (data, type, row) {
                            let rowString = JSON.stringify(row);
                            let htmlString = `
                                <div class="actions-wrapper" data-row='${rowString}'>
                                    <i class="far fa-trash-alt removeWidget" aria-hidden="true" title="${app.vtranslate('Home.JS_DASHBOARD_REMOVE_WIDGET')}"></i>
                                </div>
                            `;

                            return htmlString;
                        }
                    },
                    {
                        data: 'payload',
                        name: 'payload',
                        visible: false,
                        render: function (data, type, row) {
                            return self._getSearchableString(JSON.stringify(row));
                        }
                    },
                ],
            });

            $(this.WidgetListTable.table().container()).find('.dataTables_filter, .dataTables_length').hide();

            modal.find('[name="widget_keyword"]').on('keyup', (event) => {
                const searchKeyword = this._getSearchableString(modal.find('[name="widget_keyword"]').val());
                this.WidgetListTable.search(searchKeyword).draw();
            });

            modal.find('[data-toggle="tab"]').on('shown.bs.tab', event => {
                if (event.target.hash == '#homepage-management') {
                    this.HomepageListTable.columns.adjust().draw();
                }
                if (event.target.hash == '#dashlet-category-management') {
                    this.WidgetListTable.columns.adjust().draw();
                }
            });

            this.WidgetListTable.on('draw.dt', (event) => {
                // Disable button delete all on empty
                if ($(event.target).find('.dataTables_empty')[0] != undefined) {
                    modal.find('.removeAllWidget').attr('disabled', true);
                }
                else {
                    modal.find('.removeAllWidget').attr('disabled', false);
                }

                $(this.WidgetListTable.table().container()).find('.removeWidget').off('click').on('click', function (event) {
                    let wrapper = $(this).closest('.actions-wrapper');
                    let rowData = wrapper.data('row');
                    let categoryId = modal.find('[name="widget_category"]').val();
                    let categoryName = modal.find('[name="widget_category"]').find('option:selected').text();

                    self.showRemoveWidgetConfirmation(rowData.name, categoryName).then(() => {
                        app.helper.showProgress();

                        self.removeWidgetFromCategory(rowData.id, categoryId).then((err, res) => {
                            app.helper.hideProgress();

                            if (!self._handleAjaxError(err, res)) return;

                            app.helper.showSuccessNotification({ message: app.vtranslate('JS_DELETE_SUCCESSFULLY')});
                            self.WidgetListTable.ajax.reload();
                        });
                    });
                });
            });

            modal.find('.addHomepage').on('click', () => {
                this.showEditDashboardPopup();
            });

            modal.find('.createCategory').on('click', () => {
                self.showEditCategoryPopup();
            });

            modal.find('.editCategory').on('click', () => {
                let categoryData = modal.find('[name="widget_category"]').select2('data');
                if (!categoryData) return;

                let categoryId = categoryData.id;
                self.showEditCategoryPopup(categoryId);
            });

            modal.find('.deleteCategory').on('click', () => {
                let categoryData = modal.find('[name="widget_category"]').select2('data');
                if (!categoryData) return;

                self.showDeleteCategoryConfirmation(categoryData.text).then(() => {
                    const requestParams = {
                        module: 'Home',
                        action: 'DashboardAjax',
                        mode: 'deleteCategoryAndRelatedWidgets',
                        id: categoryData.id,
                    }

                    app.helper.showProgress();

                    app.request.post({ data: requestParams }).then((err, res) => {
                        app.helper.hideProgress();

                        if (!self._handleAjaxError(err, res)) return;

                        app.helper.showSuccessNotification({ message: app.vtranslate('JS_DELETE_SUCCESSFULLY')});

                        this.configModal.find('[name="widget_category"]').find(`option[value="${categoryData.id}"]`).remove();
                        this.configModal.find('[name="widget_category"]').trigger('change');
                    })
                });
            });

            modal.find('[name="widget_category"]').on('change', event => {
                const target = $(event.target);

                if (target.val()) {
                    modal.find('.deleteCategory').attr('disabled', false);
                    modal.find('.editCategory').attr('disabled', false);
                    modal.find('.selectWidget').attr('disabled', false);
                } else {
                    modal.find('.deleteCategory').attr('disabled', true);
                    modal.find('.editCategory').attr('disabled', true);
                    modal.find('.selectWidget').attr('disabled', true);
                }

                this.WidgetListTable.ajax.reload();
            }).trigger('change');

            modal.find('.selectWidget').on('click', () => {
                let categoryId = modal.find('[name="widget_category"]').val();
                this.showSelectWidgetPopup(categoryId);
            });

            modal.find('.removeAllWidget').on('click', () => {
                let categoryId = modal.find('[name="widget_category"]').val();
                let categoryName = modal.find('[name="widget_category"] option:selected').text();
                this.showRemoveAllWidgetFromCategoryConfirmatiom(categoryName).then(() => {
                    app.helper.showProgress();

                    self.removeWidgetFromCategory('all', categoryId).then((err, res) => {
                        app.helper.hideProgress();

                        if (!self._handleAjaxError(err, res)) return;

                        app.helper.showSuccessNotification({ message: app.vtranslate('JS_DELETE_SUCCESSFULLY')});
                        self.WidgetListTable.ajax.reload();
                    });
                });
            });
        }

        handleAddDashletPoup (modal) {
            const self = this;

            // Handler events for widget management
            this.SelectWidgetListTable = modal.find('#dashlet-list').DataTable({
                scrollY: '300px',
                scrollCollapse: true,
                responsive: true,
                ordering: false,
                paging: false,
                bInfo: false,
                language: self._dataTableLanguages,
            });

            $(this.SelectWidgetListTable.table().container()).find('.dataTables_filter').hide();

            modal.find('[name="widget_keyword"]').on('keyup', (event) => {
                event.preventDefault();
                const searchKeyword = this._getSearchableString(modal.find('[name="widget_keyword"]').val());
                this.SelectWidgetListTable.search(searchKeyword).draw();
            });
        }

        submitEditHomepage (formData) {
            const self = this;
            let saveMode = formData['save_mode'];

            app.helper.showProgress();

            return app.request.post({ data: formData }).then((err, res) => {
                app.helper.hideProgress();

                if (!self._handleAjaxError(err, res)) return;

                if (saveMode == 'save_and_edit_layout') {
                    self.enterDashboardEditMode(res.id);
                    return;
                }

                app.helper.hidePopup();
                self.HomepageListTable.ajax.reload();
            });
        }

        handleEditHomepagePopup (modal) {
            const self = this;

            modal.find('form[name="edit_homepage"]').vtValidate({
                submitHandler: form => {
                    const formData = $(form).serializeFormData();

                    // Validate role
                    const requestParams = {
                        module: 'Home',
                        action: 'DashboardAjax',
                        mode: 'checkDuplicateRolesInDashboardTemplate',
                        roles: formData['template_data[roles]'],
                        exclude: formData['id'],
                        is_duplicate: formData['is_duplicate'],
                    }

                    app.helper.showProgress();

                    app.request.post({ data: requestParams }).then((err, res) => {
                        app.helper.hideProgress();

                        if (!self._handleAjaxError(err, res)) return;

                        if (res.duplicate_roles) {
                            self.showOverrideHomepageRoleErrorNotification(res.duplicate_roles, function () {
                                let duplicateRoles = res.duplicate_roles.split(',').map(single => single.trim());
                                let rolesSelect = $(form).find('[name="template_data[roles]"]');
                                let selectedRoles = rolesSelect.val();

                                selectedRoles = selectedRoles.filter(single => {
                                    let optionValue = rolesSelect.find(`option[value="${single}"]`).text();
                                    return !duplicateRoles.includes(optionValue);
                                });

                                rolesSelect.val(selectedRoles).trigger('change');
                            });
                            return;
                        }
                        self.submitEditHomepage(formData);
                    });

                    return false;
                }
            })
        }

        handleEditCategoryModal (modal) {
            const self = this;

            modal.find('form[name="edit_category"]').vtValidate({
                submitHandler: form => {
                    const formData = $(form).serializeFormData();

                    app.helper.showProgress();

                    app.request.post({ data: formData }).then((err, res) => {
                        app.helper.hideProgress();

                        if (!self._handleAjaxError(err, res)) return;

                        app.helper.hidePopup();

                        let newOption = new Option(res.name, res.id, false, false);
                        let select = this.configModal.find('[name="widget_category"]');

                        if (select.find(`option[value=${res.id}]`)[0] == null) {
                            select.append(newOption);
                        }
                        else {
                            select.find(`option[value=${res.id}]`).replaceWith(newOption);
                        }

                        select.select2('destroy');
                        select.select2();
                        select.val(res.id).trigger('change');
                    });

                    return false;
                },
            });
        }

        handleSelectWidgetPopup (modal) {
            const self = this;

            this.SelectWidgetTable = modal.find('#widget-list').DataTable({
                scrollY: '300px',
                scrollCollapse: true,
                responsive: true,
                ordering: false,
                paging: false,
                bInfo: false,
            });

            $(this.SelectWidgetTable.table().container()).find('.dataTables_filter').hide();

            modal.find('[name="widget_keyword"]').on('keyup', (event) => {
                const searchKeyword = this._getSearchableString(modal.find('[name="widget_keyword"]').val());
                this.SelectWidgetTable.search(searchKeyword).draw();
            });

            modal.find('form[name="select_widget"] :input').keydown(function (e) {
                if (e.keyCode == 13) {
                    e.preventDefault();
                    $(e.target).trigger('change');

                    return false;
                }
            });

            modal.find('.unselectAll').on('click', function(event) {
                event.preventDefault();
                modal.find('#widget-list_wrapper input[type="checkbox"]:visible').prop('checked', false);
            });

            modal.find('.selectAll').on('click', function(event) {
                event.preventDefault();
                modal.find('#widget-list_wrapper input[type="checkbox"]:visible').prop('checked', true);
            });

            modal.find('form[name="select_widget"]').vtValidate({
                submitHandler: form => {
                    this.SelectWidgetTable.search('').draw();

                    setTimeout(() => {
                        const formData = $(form).serializeFormData();

                        app.helper.showProgress();

                        app.request.post({ data: formData }).then((err, res) => {
                            app.helper.hideProgress();

                            self._handleAjaxError(err, res);

                            app.helper.hidePopup();
                            self.WidgetListTable.ajax.reload();
                        });
                    }, 100);

                    return false;
                },
            });
        }

        applyLayoutToUser () {
            const self = this;

            const requestParams = {
                module: 'Home',
                action: 'DashboardAjax',
                mode: 'applyCurrentDashboardTemplateToUsers',
            }

            app.helper.showProgress();

            app.request.post({ data: requestParams }).then((err, res) => {
                app.helper.hideProgress();

                if (!self._handleAjaxError(err, res)) return;

                app.helper.showSuccessNotification({ message: app.vtranslate('Home.JS_DASHBOARD_APPLY_DASHBOARD_LAYOUT_TO_USERS_SUCCESS_MSG')});
            });
        }

        showAddWidgetToDashboardModal () {
            return this._showAjaxModal(
                'getAddWidgetModal',
                this.handleAddDashletToDashboardModal.bind(this),
                { tabid: $('.active.dashboardTab').data('tabid') },
                { backdrop: 'static' },
            );
        }

        removeAllWidgetFromTab (target) {
            const self = this;

            this.showRemoveAllWidgetFromTab(target.data('tabname')).then(() => {
                const requestParams = {
                    module: 'Home',
                    action: 'DashboardAjax',
                    mode: 'removeAllWidgetFromTab',
                    tab_id: target.data('tabid'),
                    template_id: target.data('templateid'),
                }

                app.helper.showProgress();

                app.request.post({ data: requestParams }).then((err, res) => {
                    app.helper.hideProgress();

                    if (!self._handleAjaxError(err, res)) return;

                    app.helper.showSuccessNotification({ message: app.vtranslate('JS_DELETE_SUCCESSFULLY')});
                    window.location.reload();
                });
            });
        }

        handleAddDashletToDashboardModal (modal) {
            const self = this;
            let searchInterval = null;
            this.reloadOnAddDashboardWidgetModalClose = false;

            modal.find('[name="keyword"]').focus();

            modal.find('[name="keyword"]').on('keyup', event => {
                if (searchInterval) clearInterval(searchInterval);
                searchInterval = setTimeout(() => {
                    let searchKeyword = self._getSearchableString($(event.target).val());

                    if (!searchKeyword) {
                        modal.find('ul.widgetsList li').show();
                    }
                    else {
                        modal.find('ul.widgetsList li').hide();

                        modal.find('ul.widgetsList li').each((index, target) => {
                            let targetKeyword = self._getSearchableString($(target).data('keyword'));

                            if (targetKeyword.search(searchKeyword) > -1) {
                                $(target).show();
                            }
                        });
                    }
                }, 100);
            });

            modal.one('hidden.bs.modal', () => {
                if (this.reloadOnAddDashboardWidgetModalClose) window.location.reload();
            });
        }
    }
});
