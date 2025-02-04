/*
    SalesResultReportDetail.js
    Author: Phuc Lu
    Date: 2020.05.25
*/

jQuery(function ($) {
    var container = $('#custom-report-detail');  
    initReportButtons(container);
    initReportFilters(container);
    
    container.find('#form-filter').vtValidate();
});

function initReportFilters (container) {
    container.find('select.filter').select2();

    container.find('#displayed_by').on('change', function () {
        if (jQuery(this).val() == 'three_latest_years') {
            jQuery('#year').addClass('hide');
            jQuery(this).prev().removeClass('width-220').addClass('width-340');
        }
        else {
            jQuery('#year').removeClass('hide');
            jQuery(this).prev().removeClass('width-340').addClass('width-220');
        }
    })

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
