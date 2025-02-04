/* 
    CustomReportWidgetHandler.js
    Author: Phuc Lu 
    Date: 2020.04.13
    Purpose: to provide util functions to handle custom report widget
*/

var CustomReportWidget = {
    initFilters: function () {
        // For time type filter
        jQuery('.widgetFilter[name="filter[period]"').on('change LoadFilter', function () {
            var period = jQuery(this).val();
            var container = jQuery(this).closest('.filterContainer');

            if (period == 'custom') {
                container.find('.month-field').addClass('hide'); 
                container.find('.quarter-field').addClass('hide');
                container.find('.year-field').addClass('hide');
                container.find('.custom-field').removeClass('hide');
            }

            if (period == 'month') {
                container.find('.month-field').removeClass('hide'); 
                container.find('.quarter-field').addClass('hide');
                container.find('.year-field').removeClass('hide');
                container.find('.custom-field').addClass('hide');
            }
            
            if (period == 'quarter') {
                container.find('.month-field').addClass('hide'); 
                container.find('.quarter-field').removeClass('hide');
                container.find('.year-field').removeClass('hide');
                container.find('.custom-field').addClass('hide');
            }
            
            if (period == 'year') {            
                container.find('.month-field').addClass('hide'); 
                container.find('.quarter-field').addClass('hide');
                container.find('.year-field').removeClass('hide');
                container.find('.custom-field').addClass('hide');
            }
        });

        jQuery('.widgetFilter[name="filter[period]"').trigger('LoadFilter');

        // For department
        jQuery('.widgetFilter[name="filter[department]"]').on('change', function () {
            var container = jQuery(this).closest('.filterContainer');
            var employeeElement = container.find('.widgetFilter[name="filter[employee]"]');

            if (employeeElement.length) {       
                var params = {
                    module: 'Reports',
                    action: 'DetailAjax',
                    mode: 'getUsersByDepartment',
                    department: jQuery(this).val()
                };
        
                app.request.post({ data: params }).then(function (error, data) {
                    if (error || !data) {
                        var message = app.vtranslate('Vtiger.JS_THERE_WAS_SOMETHING_ERROR');
                        app.helper.showErrorNotification({ message: message });
                        return;
                    }
        
                    employeeElement.select2('destroy');
                    employeeElement.find('option').remove();
        
                    jQuery.each(data, function (k, v) {
                        employeeElement.append('<option value="' + k  + '">' + v + '</option>');
                    })
        
                    employeeElement.select2();
                });
            }
        });
    }
};

jQuery(function ($) {
    $('li.dashboardWidget').on(Vtiger_Widget_Js.widgetPostLoadEvent, CustomReportWidget.initFilters);
});