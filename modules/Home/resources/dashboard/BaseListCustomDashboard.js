/**
 * BaseListCustomDashboard
 * Author: Phu Vo
 * Date: 2020.08.26
 */

window.BaseListCustomDashboard = {
    init: function (ui) {
        const thisInstance = this;
        const widgetName = ui.data('widget-name');

        this.initDataTable(ui);

        ui.closest('.dashboardWidget').on(Vtiger_Widget_Js.widgetPostRefereshEvent, function (e) {
            thisInstance.initDataTable(ui);
        });
    },

    initDataTable: function (ui) {
        const thisInstance = this;
        const widget = ui.closest('.dashboardWidget');
        const widgetName = ui.data('widget-name');
        const tableColumns = thisInstance.getDataTableColumns(ui);
        const widgetTable = ui.find('table.widgetTable');

        const widgetDataTable = $(widgetTable).DataTable({
            ordering: false,
            searching: false,
            processing: true,
            serverSide: true,
            pageLength: 5,
            lengthChange: false,
            scrollX: true,
            language: {
                emptyTable: app.vtranslate('JS_DATATABLES_NO_DATA_AVAILABLE'),
                info: app.vtranslate('Home.JS_DASHBOARD_DATATABLES_FOOTER_INFO'),
                infoEmpty: '',
                zeroRecords: app.vtranslate('JS_DATATABLES_NO_RECORD'),
                paginate: {
                    next: app.vtranslate('JS_DATATABLES_PAGINATE_NEXT_PAGE'),
                    previous: app.vtranslate('JS_DATATABLES_PAGINATE_PREVIOUS_PAGE')
                },
            },
            ajax: {
                url: 'index.php',
                type: 'POST',
                dataType: 'JSON',
                data: function(data) {
                    const filterParams = widget.find(':input.widgetFilter').serializeFormData();

                    return $.extend({}, data,
                        {
                            module: 'Home',
                            view: 'ShowWidget',
                            name: widgetName,
                            data: true,
                        },
                        filterParams,
                    );
                },
            },
            columns: tableColumns,
        });

        // Support catching table render event from widget handler file
        widgetDataTable.on('draw.dt', function () {
            if (typeof window[widgetName] != 'undefined' && typeof window[widgetName]['handleTableRenderEvent'] == 'function') {
                window[widgetName]['handleTableRenderEvent'](widget);
            }
        });

        // Hide error alert
        $.fn.DataTable.ext.errMode = 'none';

        widgetDataTable.on( 'error.dt', function( e, settings, techNote, message ) {
            console.log( message );
        } );

        $(widgetDataTable.table().container()).find('.dataTables_scrollBody').addClass('fancyScrollbar');

        // Auto adjust after render
        setTimeout(() => widgetDataTable.columns.adjust().draw(), 0);

        // Auto adject after resize
        widget.on(Vtiger_Widget_Js.widgetPostResizeEvent, function (e) {
            widgetDataTable.columns.adjust().draw();
        });
    },

    getDataTableColumns: function (ui) {
        const thisInstance = this;
        const widgetName = ui.data('widget-name');
        const meta = window._CUSTOM_WIDGET_META[widgetName];
        const widgetHeaders = meta['widget_headers'];
        const tableColumns = [];

        widgetHeaders.forEach((headerData) => {
            const column = {};
            const header = headerData['name'];
            let className = header;

            if (headerData['type']) className += ' ' + headerData['type'];

            column['data'] = header;
            column['name'] = header;
            column['className'] = className;

            // Create link for record name
            if (header == 'record_name') {
                column['render'] = function (data, type, row) {
                    let link = thisInstance.getDetailViewLink(row);
                    return `<a target="_BLANK" href="${link}" title="${row.record_name}">${row.record_name}</a>`;
                }
            }

            // Support fetch data for custom action button
            if (header == 'action') {
                column['render'] = function (data, type, row) {
                    let rowData = JSON.stringify(row);
                    return `<div class="row-action" data-row='${rowData}'>${data}</div>`;
                }
            }

            if (typeof window[widgetName] != 'undefined' && typeof window[widgetName]['renderTableColumn'] == 'function') {
                column['render'] = window[widgetName]['renderTableColumn'](header);
            }

            tableColumns.push(column);
        });

        return tableColumns;
    },

    getDetailViewLink: function (row) {
        return `index.php?module=${row.record_module}&view=Detail&record=${row.record_id}&mode=showDetailViewByMode&requestMode=full`;
    },
}
