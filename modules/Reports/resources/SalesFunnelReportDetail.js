/*
    SalesResultReportDetail.js
    Author: Phuc Lu
    Date: 2020.05.25
*/

jQuery(function ($) {
    var container = $('#custom-report-detail');  

    initReportFilters(container);
    initReportButtons(container);
    
    container.find('#form-filter').vtValidate();
});

function initReportFilters (container) {
    container.find('select.filter').select2();

    container.find('#displayed_by').on('change', function () {
        if ($(this).val() == 'all') {
            $('#department').closest('.filter-group').addClass('hide');
            $('#employee').closest('.filter-group').addClass('hide');
            $('#campaign').closest('.filter-group').addClass('hide');
        }
        else if ($(this).val() == 'employee') {            
            $('#department').closest('.filter-group').removeClass('hide');
            $('#employee').closest('.filter-group').removeClass('hide');
            $('#campaign').closest('.filter-group').addClass('hide');
        }
        else {            
            $('#department').closest('.filter-group').addClass('hide');
            $('#employee').closest('.filter-group').addClass('hide');
            $('#campaign').closest('.filter-group').removeClass('hide');
        }
    })

    CustomReportHelper.initPeriodFilter(container); 
    CustomReportHelper.initDepartmentFilter(container, false, true);

    container.find('#period').trigger('change');
    container.find('#displayed_by').trigger('change');
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
                from_date: $('input#from-date').val(),
                to_date: $('input#to-date').val(),
                top: $('select#top').val(),
                period: $('select#period').val(),
                month: $('select#month').val(),
                quarter: $('select#quarter').val(),
                year: $('select#year').val(),
                displayed_by: $('select#displayed_by').val(),
                department: $('select#department').val(),
                employee: $('select#employee').val(),
                campaign: $('select#campaign').val(),
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
