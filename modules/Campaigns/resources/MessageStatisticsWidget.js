/**
 * Name: MessageStatisticsWidget.js
 * Author: Phu Vo
 * Date: 2020.11.17
 */

$(() => {
    window.MessageStatisticsWidget = new class {
        constructor() {
            this.initDataTable();
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

        initDataTable() {
            const self = this;
            const table = $('#message-statistics-detail');
            const tableColumns = this.getDataTableColumns();

            this.MessageStatisticsDataTable = table.DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                pageLength: 10,
                scrollY: '300px',
                scrollCollapse: true,
                columns: tableColumns,
                language: self._dataTableLanguages,
                ajax: {
                    url: 'index.php',
                    type: 'POST',
                    dataType: 'JSON',
                    data: function(data) {
                        data =  $.extend({}, data,
                            {
                                module: 'Campaigns',
                                action: 'DetailAjax',
                                mode: 'getMessageStatisticsDetail',
                                record: app.getRecordId(),
                            },
                        );

                        return data;
                    },
                },
            });

            // Auto adjust after render
            // setTimeout(() => this.MessageStatisticsDataTable.columns.adjust().draw(), 0);
        }

        getDataTableColumns () {
            let widgetHeaders = _MESSAGE_STATISTICS_DATA_TABLE_HEADERS;
            let tableColumns = [];

            widgetHeaders.forEach((headerData) => {
                let column = {};
                let header= headerData['name'];
                let className = 'td ' + header;

                if (headerData['type']) className += ' ' + headerData['type'];

                column['data'] = header;
                column['name'] = header;

                column['render'] = function(data, type, row) {
                    return `<div class="${className}">${data}</div>`;
                }

                tableColumns.push(column);
            });

            return tableColumns;
        }
    }
});