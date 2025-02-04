/*
    SalesOrderByCustomerTypeReportDetail.js
    Author: Phuc Lu
    Date: 2020.04.21
*/

jQuery(function ($) {
    var container = $('#custom-report-detail');  
    initReportButtons(container);
    initReportFilters(container);

    container.find('#form-filter').vtValidate();
});

function initReportFilters (container) {
    container.find('select.filter').select2();
}

function initReportButtons (container) {
    // Handle add to dashboard event
    var addChartToDashboardBtn = container.find('#add-chart-to-dashboard');

    addChartToDashboardBtn.find('.dashboard-tab').on('click', function () {
        var dashboardTabId = $(this).data('tabId');
        var customParams = { 
            dashBoardTabId: dashboardTabId, 
            data: { 
                chart_title: '', 
                displayed_by: $('select#displayed_by').val(),
                year: $('select#year').val(),
                size: {
                    sizex : 2,
                    sizey : 2
                }
            }
        };
        
        CustomReportHelper.addChartToDashboard(customParams, addChartToDashboardBtn);
    });

    CustomReportHelper.initCustomButtons(container);
}