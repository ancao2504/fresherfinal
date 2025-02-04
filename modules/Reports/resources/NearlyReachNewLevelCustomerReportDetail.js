
/*
    NearlyReachNewLevelCustomerReportDetail.js
    Author: Phuc Lu
    Date: 2020.05.20
*/

jQuery(function ($) {
    var container = $('#custom-report-detail');    

    initReportButtons(container);
    initResultContent(container);

    container.find('#form-filter').vtValidate();
});

function initReportButtons (container) {
    CustomReportHelper.initCustomButtons(container);
}

function initResultContent (container) {
    container.find('#result-content').freezeTable({
        freezeColumn: false,
        scrollable: true,
    });
}