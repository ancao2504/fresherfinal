/**
 * Name: BaseSummaryCustomDashboard.js
 * Author: Phu Vo
 * Date: 2020.11.20
 */

window.BaseSummaryCustomDashboard = {
    init: function (ui) {
        const thisInstance = this;
        const widgetName = ui.data('widget-name');

        this.initTooltip(ui);
    },

    initTooltip: function (ui) {
        vtUtils.enableTooltips();
    }
}