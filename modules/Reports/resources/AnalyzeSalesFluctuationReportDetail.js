
/*
    AnalyzeSalesDataReportDetail.js
    Author: Phuc Lu
    Date: 2020.06.03
*/

jQuery(function ($) {
    var container = $('#custom-report-detail');    

    initReportFilters(container);
    initReportButtons(container);
    initResultContent(container);

    container.find('#form-filter').vtValidate();
});

function initReportFilters (container) {
    container.find('select.filter').select2();
}

function initReportButtons (container) {
    var addChartToDashboardBtn = container.find('#add-chart-to-dashboard');

    addChartToDashboardBtn.find('.dashboard-tab').on('click', function () {
        var dashboardTabId = $(this).data('tabId');
        var customParams = { 
            dashBoardTabId: dashboardTabId, 
            data: { 
                chart_title: '', 
                displayed_by: $('select#displayed_by').val(),
                size: {
                    sizex : 3,
                    sizey : 1
                }
            }
        };
        
        CustomReportHelper.addChartToDashboard(customParams, addChartToDashboardBtn);
    });
    
    CustomReportHelper.initCustomButtons(container);
}

function initResultContent (container) {
    container.find('#result-content').freezeTable({
        freezeColumn: false,
        scrollable: true,
    });
}
