/**
 * CustomerHaveNoSOInPeriodWidget
 * Author: Phu Vo
 * Date: 2020.08.24
 */

window.CustomerHaveNoSOInPeriodWidget = {
    init: function () {
        var thisInstance = this;

        this.updatePeriodDay();

        $('.widgetTable.CustomerHaveNoSOInPeriodWidget').closest('.dashboardWidget').on(Vtiger_Widget_Js.widgetPostRefereshEvent, function (e) { 
            thisInstance.updatePeriodDay();
        });
    },

    updatePeriodDay: function () {
        const widget = $('.widgetTable.CustomerHaveNoSOInPeriodWidget').closest('.dashboardWidget');
        const periodDays = widget.find(':input[name="params[period_days]"]').val() || 'X';
        
        widget.find('.period-days').text(periodDays);
    }
}