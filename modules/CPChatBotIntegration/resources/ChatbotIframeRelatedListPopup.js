/**
 * ChatbotRelatedListIfame.js
 * Author: Phu Vo
 * Date: 2020.09.11
 * Description: Handle create or link new customer popup
 */

(() => {
    const initData = {
        overlay: false,
        form_data: {},
        fields: [],
    }

    window.App = new Vue({
        el: '#app',

        // App Data
        data: Object.assign(initData, window._IFRAME_DATA),

        methods: {
            goToPage(pageNumber) {
                const preCurrentPage = this.table_data.current_page;
                const self = this;
                const params = Object.assign({}, this.url_params, {
                    name: this.url_params.name,
                    bot_name: window._IFRAME_DATA.bot_name,
                    access_token: this.url_params.access_token,
                    action: 'ChatbotIframeAjax',
                    mode: 'getRelatedList',
                    source_record: url_params.source_record,
                    source_module: url_params.source_module,
                    related_module: url_params.related_module,
                    current_page: pageNumber,
                    limit: this.table_data.limit,
                    search: this.table_data.search,
                });

                this.overlay = true;

                app.request.post({ url: 'entrypoint.php', data: params}).then((err, res) => {
                    self.overlay = false;

                    if (err) {
                        this.$bvToast.toast(err.message, {
                            title: app.vtranslate('JS_ERROR'),
                            variant: 'danger',
                        });
                        this.table_data.current_page = preCurrentPage;
                        return false;
                    }

                    if (!res) {
                        this.$bvToast.toast(app.vtranslate('JS_THERE_WAS_SOMETHING_ERROR'), {
                            title: app.vtranslate('JS_ERROR'),
                            variant: 'danger',
                        });
                        this.table_data.current_page = preCurrentPage;
                        return false;
                    }

                    self.table_data = res.table_data;
                    return true;
                });
            },

            handleSearchKeydown(event) {
                if (event.code === 'Enter') {
                    this.goToPage(1);
                }
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

                const columns = this.fields.map((field) => {
                    return {
                        data: field.key,
                        name: field.key,
                        className: field.class,
                    };
                });

                window.RelatedDataTable = $('#related-list').DataTable({
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
                            return $.extend({}, self.url_params, data,
                                {
                                    name: self.url_params.name,
                                    bot_name: window._IFRAME_DATA.bot_name,
                                    access_token: self.url_params.access_token,
                                    action: 'ChatbotIframeAjax',
                                    mode: 'getRelatedList',
                                    source_record: self.url_params.source_record,
                                    source_module: self.url_params.source_module,
                                    related_module: self.url_params.related_module,
                                    filters: $('form[name="filters"]').serializeFormData(),
                                },
                            );
                        },
                    },
                    columns: columns,
                    language: language,
                });
            },

            clearFilter() {
                this.form_data = {};
                setTimeout(() => this.reloadDataTable(), 0);
            },

            reloadDataTable(e = '') {
                if (e) e.preventDefault();
                window.RelatedDataTable.ajax.reload();
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
            $(this.$el).show();
            this.initDataTable();
        }
    });
})();