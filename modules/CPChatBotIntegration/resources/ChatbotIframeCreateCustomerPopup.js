/**
 * ChatbotIframeCreateCustomerPopup
 * Author: Phu Vo
 * Date: 2020.09.11
 */

(() => {
    const initData = {
        customer_type_options: {
            'Leads': app.vtranslate('JS_LEAD'),
            'Contacts': app.vtranslate('JS_CONTACT'),
        },
        customer_salutationtype_options: {
            'Mr.': app.vtranslate('JS_MR'),
            'Ms.': app.vtranslate('JS_MS'),
        },
        customer: {},
        form_data: {},
        cache: {},
        mode: 'default',
        quick_create_form_data: {},
        overlay: false,
        filter_data: {},
        selected_tags: '',
        table: {
            fields: [
                {
                    key: 'lastname',
                    label: ChatbotHelper.getFieldLabel('Contacts', 'lastname'),
                },
                {
                    key: 'firstname',
                    label: ChatbotHelper.getFieldLabel('Contacts', 'firstname'),
                },
                {
                    key: 'mobile',
                    label: ChatbotHelper.getFieldLabel('Contacts', 'mobile'),
                },
                {
                    key: 'email',
                    label: ChatbotHelper.getFieldLabel('Contacts', 'email'),
                },
                {
                    key: 'accountid',
                    label: ChatbotHelper.getFieldLabel('Contacts', 'accountid'),
                },
            ],
            items: [],
            search: '',
            current_page: 1,
            total_count: 0,
            limit: 5,
        },
        datepicker_options: {
            format: window.vtUtils.getMomentDateFormat(),
        },
    };

    window.App = new Vue({
        el: ('#app'),

        data: $.extend(true, initData, window._IFRAME_DATA),

        methods: {
            getFormData () {
                const formData = Object.assign({ customer_type: 'Leads' }, this.customer);
                return formData;
            },

            isRequired (module, fieldName) {
                if (this.meta_data[module] && this.meta_data[module].all_fields && this.meta_data[module].all_fields[fieldName]) {
                    return this.meta_data[module].all_fields[fieldName].required === true;
                }

                return false;
            },

            submit() {
                if (!$('#customer').valid()) return;

                this.overlay = true;

                const params = {
                    name: this.url_params.name,
                    bot_name: window._IFRAME_DATA.bot_name,
                    access_token: this.url_params.access_token,
                    action: 'ChatbotIframeAjax',
                    mode: 'saveCustomer',
                    app_id: this.url_params.app_id,
                    chat_id: this.url_params.id,
                };

                formData = Object.assign(this.url_params, this.form_data);

                delete formData.module;
                delete formData.view;
                delete formData.action;
                delete formData.mode;

                app.request.post({ url: 'entrypoint.php', data: Object.assign(params, formData) }).then((err, res) => {
                    this.overlay = false;

                    if (err || !res) {
                        return this.$bvToast.toast(err.message, {
                            title: app.vtranslate('JS_ERROR'),
                            variant: 'danger',
                        });
                    }

                    window.iframeSubmitCallback(res);
                    window.close();
                });
            },

            goToPage(pageNumber) {
                const preCurrentPage = this.table.current_page;
                const self = this;
                const params = {
                    name: this.url_params.name,
                    bot_name: window._IFRAME_DATA.bot_name,
                    access_token: this.url_params.access_token,
                    action: 'ChatbotIframeAjax',
                    mode: 'searchCustomer',
                    current_page: pageNumber,
                    limit: this.table.limit,
                    search: this.table.search,
                };

                this.overlay = true;

                app.request.post({ url: 'entrypoint.php', data: params}).then((err, res) => {
                    self.overlay = false;

                    if (err) {
                        this.$bvToast.toast(err.message, {
                            title: app.vtranslate('JS_ERROR'),
                            variant: 'danger',
                        });
                        this.table.current_page = preCurrentPage;
                        return false;
                    }

                    if (!res) {
                        this.$bvToast.toast(app.vtranslate('JS_THERE_WAS_SOMETHING_ERROR'), {
                            title: app.vtranslate('JS_ERROR'),
                            variant: 'danger',
                        });
                        this.table.current_page = preCurrentPage;
                        return false;
                    }

                    tableData = res.table_data;
                    delete tableData.fields;

                    self.table = Object.assign(self.table, tableData);
                    return true;
                });
            },

            handleSearchKeydown(event) {
                if (event.code === 'Enter') {
                    this.goToPage(1);
                }
            },

            selectCustomer(customerInfo) {
                const self = this;
                const params = $.extend({}, this.url_params, {
                    name: this.url_params.name,
                    bot_name: window._IFRAME_DATA.bot_name,
                    access_token: this.url_params.access_token,
                    action: 'ChatbotIframeAjax',
                    mode: 'selectCustomer',
                    customer_id: customerInfo.record_id,
                    customer_type: customerInfo.record_module,
                });

                app.helper.showConfirmationBox({
                    message: app.vtranslate('CPChatBotIntegration.JS_SELECT_CUSTOMER_CONFIRM_MSG', {'customer_name': customerInfo.firstname + ' ' + customerInfo.lastname}),
                }).then(() => {
                    self.overlay = true;

                    return app.request.post({ url: 'entrypoint.php', data: params });
                }).then((err, res) => {
                    self.overlay = false;

                    if (err) {
                        this.$bvToast.toast(err.message, {
                            title: app.vtranslate('JS_ERROR'),
                            variant: 'danger',
                        });
                        return false;
                    }

                    if (!res) {
                        this.$bvToast.toast(app.vtranslate('JS_THERE_WAS_SOMETHING_ERROR'), {
                            title: app.vtranslate('JS_ERROR'),
                            variant: 'danger',
                        });

                        return false;
                    }

                    window.iframeSubmitCallback(res);
                    window.close();
                });
            },

            initDataTable() {
                const self = this;

                let language = {
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
                        next: '<i class="fa fa-caret-right"></i>',
                        previous: '<i class="fa fa-caret-left"></i>',
                    }
                };

                window.CustomerTable = $('#find-customer').DataTable({
                    ordering: false,
                    searching: false,
                    processing: true,
                    serverSide: true,
                    pageLength: 10,
                    ajax: {
                        url: 'entrypoint.php',
                        type: 'POST',
                        dataType: 'JSON',
                        data: function(data) {
                            return $.extend(
                                self.url_params,
                                data,
                                {
                                    name: self.url_params.name,
                                    bot_name: window._IFRAME_DATA.bot_name,
                                    access_token: self.url_params.access_token,
                                    action: 'ChatbotIframeAjax',
                                    mode: 'searchCustomer',
                                },
                                $('form[name="filters"]').serializeFormData()
                            );
                        },
                    },
                    columns: [
                        {
                            data: 'lastname',
                            name: 'lastname',
                        },
                        {
                            data: 'firstname',
                            name: 'firstname',
                        },
                        {
                            data: 'customer_type',
                            name: 'customer_type',
                        },
                        {
                            data: 'mobile',
                            name: 'mobile',
                        },
                        {
                            data: 'email',
                            name: 'email',
                        },
                        {
                            data: 'accountid',
                            name: 'accountid',
                        },
                    ],
                    language: language,
                });

                $('#find-customer tbody').on('click', 'tr', function() {
                    const rowData = CustomerTable.row(this).data();
                    self.selectCustomer(rowData);
                });
            },

            searchCustomer() {
                window.CustomerTable.ajax.reload();
            },

            clearFilter() {
                this.filter_data = {};
                setTimeout(() => window.CustomerTable.ajax.reload(), 0);
            },

            getEntryPointUrl (url) {
                let params = app.convertUrlToDataParams(url);
                delete params['index.php'];
                params.name = this.url_params.name;
                params.access_token = this.url_params.access_token;

                return 'entrypoint.php?' + $.param(params);
            },
        },

        // Event will be triggered when app ready
        mounted() {
            this.form_data = this.getFormData();
            $(this.$el).show();
            this.initDataTable();
        }
    });
})();