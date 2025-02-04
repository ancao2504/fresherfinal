
/*
    AverageDaysWonPotentialByEmployeeReportDetail.js
    Author: Phuc Lu
    Date: 2020.05.20
*/

jQuery(function ($) {
    var container = $('#custom-report-detail');    

    initReportFilters(container);
    initReportButtons(container);
    initResultContent(container);

    $('#period').trigger('change');
    container.find('#form-filter').vtValidate();
});

function initReportFilters (container) {
    container.find('select.filter').select2();

    CustomReportHelper.initPeriodFilter(container);
    CustomReportHelper.initDeparmentsFilter(container);
}

function initReportButtons (container) {

    CustomReportHelper.initCustomButtons(container);
}

function initResultContent (container) {
    container.find('#result-content').freezeTable({
        freezeColumn: false,
        scrollable: true,
    });
}