/**
 * Name: PlannedPaymentsWidget.js
 * Author: Phu Vo
 * Date: 2020.11.13
 */

window.PlannedPaymentsWidget = {
    init: function (ui) {
        // Init dependencies picklist
        Vtiger_Edit_Js.getInstanceByModuleName('Vtiger').registerEventForPicklistDependencySetup(ui.closest('.dashboardWidget').find('.filterContainer'));
    }
}