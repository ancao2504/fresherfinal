/*
    Planned Calls Widget
    Author: Hieu Nguyen
    Date: 2019-12-23
    Purpose: to handle logic in the UI for planned calls widget on the dashboard
*/

var PlannedCallsWidget = {
    init: function () {
        var thisInstance = this;

        // Init DataTable on widget load
        this.initDataTable();

        // Init DataTable on widget refresh
        $('.planned-calls-widget:visible').closest('.dashboardWidget').on(Vtiger_Widget_Js.widgetPostRefereshEvent, function(e) { 
			thisInstance.initDataTable();
		});
    },
    initDataTable: function () {
        $('.tbl-planned-calls:visible').DataTable({
            ordering: false,
            searching: false,
            pageLength: 5,
            lengthChange: false,

            // Modified by Phu Vo on 2022.01.21
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
            // End Phu Vo
        });
    }
};

jQuery(function ($) {
    PlannedCallsWidget.init();
});