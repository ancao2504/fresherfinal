/*
    GeographyReportDetail.js
    Author: Phuc Lu
    Date: 2020.06.30
*/

jQuery(function ($) {
    var container = $('#custom-report-detail'); 

    initReportButtons(container);
    initReportFilters(container);
    initResultContent(container);
    
    $('#period').trigger('change');
    container.find('#form-filter').vtValidate();
});

function initReportFilters (container) {
    container.find('select.filter').select2(); 
    CustomReportHelper.initPeriodFilter(container);

    container.find('#report_module').on('change', function () {
        if ($(this).val() == 'Accounts') {
            $(this).closest('.filter-group').next().removeClass('hide');
        }
        else {
            $(this).closest('.filter-group').next().addClass('hide');
        }
    })
    
    container.find('#chx_is_real_customer').on('change', function () {
        if ($(this).is(':checked')) {
            $(this).next().val('1');
        }
        else {            
            $(this).next().val('0');
        }
    })
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
                report_module: $('select#report_module').val(),
                is_real_customer: $('input#is_real_customer').val(),
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

function initResultContent (container) {
    container.find('#result-content').freezeTable({
        freezeColumn: false,
        scrollable: true,
    });
}
