
/*
    CustomerConversionRateByEmployeeReportDetail.js
    Author: Phuc Lu
    Date: 2020.05.12
*/

jQuery(function ($) {
    var container = $('#custom-report-detail');    

    initReportFilters(container);
    initReportButtons(container);
    initResultContent(container);
    initHoverTooltip(container);

    $('#period').trigger('change');
    container.find('#form-filter').vtValidate();
});

function initReportFilters (container) {
    container.find('select.filter').select2();

    CustomReportHelper.initPeriodFilter(container);
    CustomReportHelper.initDeparmentsFilter(container);
}

function initReportButtons (container) {
    var addChartToDashboardBtn = container.find('#add-chart-to-dashboard');

    addChartToDashboardBtn.find('.dashboard-tab').on('click', function () {
        var dashboardTabId = $(this).data('tabId');
        var customParams = { 
            dashBoardTabId: dashboardTabId, 
            data: { 
                chart_title: '',
                departments: $('select#departments').val(),
                employees: $('select#employees').val(), 
                industries: $('select#industries').val(),
                sources: $('select#sources').val(),
                from_date: $('input#from-date').val(),
                to_date: $('input#to-date').val(),
                period: $('select#period').val(),
                month: $('select#month').val(),
                quarter: $('select#quarter').val(),
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

function initHoverTooltip(container) {
    container.find('.spn-compare').on('mousemove', function () {
        if (typeof $(this).attr('title') == 'undefined') {
            var val = $(this).html();
            var label;

            if ($(this).hasClass('spn-positive')) {
                label = app.vtranslate('Reports.JS_REPORT_INCREASE_P_COMPARE_TO_PREVIOUS_PERIOD', {'n':val});
            } else
                if ($(this).hasClass('spn-negative')) {
                    label = app.vtranslate('Reports.JS_REPORT_DECREASE_P_COMPARE_TO_PREVIOUS_PERIOD', {'n':val});
                }
                else {
                    label = app.vtranslate('Reports.JS_REPORT_NO_CHANGE');
                }
        }

        $(this).attr('title', label);
    })
}

function initResultContent (container) {
    container.find('#result-content').freezeTable({
        scrollable: true,
    });
}