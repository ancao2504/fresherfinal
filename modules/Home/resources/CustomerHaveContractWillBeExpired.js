/**
 * CustomerHaveContractWillBeExpiredWidget
 * Author: Phu Vo
 * Date: 2020.08.24
 */

window.CustomerHaveContractWillBeExpiredWidget = {
    init: function () {
        var thisInstance = this;

        this.initDataTable();
        this.updatePeriodDay();

        $('.customerHaveContractWillBeExpiredWidget').closest('.dashboardWidget').on(Vtiger_Widget_Js.widgetPostRefereshEvent, function (e) { 
            thisInstance.initDataTable();
            thisInstance.updatePeriodDay();
        });
    },

    initDataTable: function () {
        const thisInstance = this;
        const widget = $('.customerHaveContractWillBeExpiredWidget').closest('.dashboardWidget');

        $('.customerHaveContractWillBeExpiredWidget:visible').dataTable({
            ordering: false,
            searching: false,
            processing: true,
            serverSide: true,
            pageLength: 5,
            lengthChange: false,
            language: {
                emptyTable: app.vtranslate('JS_DATATABLES_NO_DATA_AVAILABLE'),
                info: app.vtranslate('JS_DATATABLES_FOOTER_INFO'),
                infoEmpty: app.vtranslate('JS_DATATABLES_FOOTER_INFO_NO_ENTRY'),
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
                    return $.extend({}, data,
                        {
                            module: 'Home',
                            view: 'ShowWidget',
                            name: 'CustomerHaveContractWillBeExpiredWidget',
                            data: true,
                            period: widget.find(':input[name="period"]').val(),
                        },
                    );
                },
            },
            columns: [
                {
                    data: 'record_name',
                    name: 'record_name',
                    render: function (data, type, row) {
                        link = thisInstance.getDetailViewLink(row);
                        return `<a target="_BLANK" href="${link}" title="${row.record_name}">${row.record_name}</a>`;
                    }
                },
                {data: 'email', name: 'email'},
                {data: 'address', name: 'address'},
                {data: 'contract_no', name: 'contract_no'},
                {data: 'expire_date', name: 'expire_date'},
                {data: 'active_day_left', name: 'active_day_left'},
            ],
        });
    },

    getDetailViewLink: function (row) {
        return `index.php?module=Accounts&view=Detail&record=${row.record_id}&mode=showDetailViewByMode&requestMode=full`;
    },

    updatePeriodDay: function () {
        const widget = $('.customerHaveContractWillBeExpiredWidget').closest('.dashboardWidget');
        const periodDays = widget.find(':input[name="period"]').val();
        
        widget.find('span.period').text(periodDays);
    }
}

jQuery(function($) {
    CustomerHaveContractWillBeExpiredWidget.init();
});