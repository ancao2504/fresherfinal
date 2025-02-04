/**
 * CustomerUnfollowedInPeriodWidget
 * Author: Phu Vo
 * Date: 2020.08.24
 */

window.CustomerUnfollowedInPeriodWidget = {
    init: function () {
        var thisInstance = this;

        this.updatePeriodDay();

        $('.widgetTable.CustomerUnfollowedInPeriodWidget').closest('.dashboardWidget').on(Vtiger_Widget_Js.widgetPostRefereshEvent, function (e) { 
            thisInstance.updatePeriodDay();
        });
    },

    updatePeriodDay: function () {
        const widget = $('.widgetTable.CustomerUnfollowedInPeriodWidget').closest('.dashboardWidget');
        const periodDays = widget.find(':input[name="params[period_days]"]').val() || 'X';
        
        widget.find('.period-days').text(periodDays);
    },
}