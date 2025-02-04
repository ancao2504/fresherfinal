jQuery(function ($) {
    var container = $('#custom-report-detail');

    // Handle add to dashboard event
    var addChartToDashboardBtn = container.find('#add-chart-to-dashboard');

    addChartToDashboardBtn.find('.dashboard-tab').on('click', function() {
        var dashboardTabId = $(this).data('tabId');
        var customParams = { 
            dashBoardTabId: dashboardTabId, 
            data: { 
                chart_title: 'Leads by Source Chart', 
                start_date: '2020-03-01', 
                end_date: '2020-03-31'
            }
        };
        
        CustomReportHelper.addChartToDashboard(customParams, addChartToDashboardBtn);
    });
});