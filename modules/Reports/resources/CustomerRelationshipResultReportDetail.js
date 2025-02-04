
/*
    CustomerRelationshipResultReportDetail.js
    Author: Phuc Lu
    Date: 2020.05.05
*/

jQuery(function ($) {
    var container = $('#custom-report-detail');    

    initReportFilters(container);
    initReportButtons(container);

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