/*
*	PopupDatatable.js
*	Author: Tin Bui
*	Date: 2022.03.16
*   Purpose: js script for emailtemplate datatable popup
*/

let PopupDatatable = {
    dataTableLanguages: {
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
        },
    },
    initDatatable: function () {
        let language = this.dataTableLanguages;
        let dataTableDefaultOptions = {
            bSort: false,
            processing: true,
            serverSide: true,
            pageLength: 10,
            lengthChange: false,
            scrollY: 380,
            language: language
        };

        let columns = [
            { data: 'stt', name: 'stt', className: 'text-center', width: 40 },
            { data: 'templatename', name: 'templatename', className: 'text-left', width: 200 },
            { data: 'subject', name: 'subject', className: 'text-left', width: 200 },
            { data: 'description', name: 'description', className: 'text-left', width: 200 },
            { data: 'module', name: 'module', className: 'text-center', width: 200 }
        ];

        let tableContainer = $('#emailTemplateTable');

        let dtEmailTemplates = tableContainer.DataTable($.extend(dataTableDefaultOptions, {
            aoColumns: columns,
            columns: columns,
            ajax: {
                url: 'index.php',
                type: 'POST',
                dataType: 'JSON',
                data: function (data) {
                    data = $.extend({}, data,
                        {
                            module: 'EmailTemplates',
                            action: 'PopupDatatable'
                        },
                    );
                    return data;
                },
                dataSrc: function (response) {
                    return response.data;
                },
            }
        }));

        $(dtEmailTemplates.table().container()).find('.dataTables_scrollBody').addClass('fancyScrollbar');
        dtEmailTemplates.ajax.reload();
    }
}

$(function () {
    PopupDatatable.initDatatable();
});