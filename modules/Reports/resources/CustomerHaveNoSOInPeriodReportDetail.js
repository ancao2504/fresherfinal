
/*
    CustomerHaveNoSOInPeriodReportDetail.js
    Author: Phuc Lu
    Date: 2020.06.02
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
    CustomReportHelper.initCustomButtons(container);
}

function initResultContent (container) {
    container.find('#result-content').freezeTable({
        freezeColumn: false,
        scrollable: true,
    });
}
