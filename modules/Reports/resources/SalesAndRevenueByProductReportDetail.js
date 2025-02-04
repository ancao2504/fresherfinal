/*
    SalesAndRevenueByProductReportDetail.js
    Author: Phuc Lu
    Date: 2020.05.27
*/

jQuery(function ($) {
    var container = $('#custom-report-detail');  
    
    initReportButtons(container);
    initReportFilters(container);

    container.find('#form-filter').vtValidate();
});

function initReportFilters (container) {
    container.find('select.filter').select2();

    CustomReportHelper.initPeriodFilter(container);
}

function initReportButtons (container) {
    CustomReportHelper.initCustomButtons(container);
}