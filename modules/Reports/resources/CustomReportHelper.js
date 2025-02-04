/*
    Custom Report Helper
    Author: Hieu Nguyen
    Date: 2020-03-30
    Purpose: provide util functions for custom reports
*/

// Added by Phuc on 2020.06.15 to remove confirm popup in fix report
window.onunload=function () { return true; }

var CustomReportHelper = {

    addChartToDashboard: function (customParams, addToDashboardBtn) {
        var recordId = app.getRecordId();
        var widgetTitle = 'CustomReport_ChartWidget_' + recordId;

        var params = {
            module: 'Reports',
            action: 'ChartActions',
            mode: 'addChartToDashboard',
            reportId: recordId,
            title: widgetTitle
        };

        params = $.extend(params, customParams);

        app.request.post({ data: params })
        .then(function (error, data) {
            if (error) {
                var message = app.vtranslate('JS_ADD_CHART_TO_DASHBOARD_ERROR_MSG', 'Reports');
                app.helper.showErrorNotification({ message: message });
                return;
            }

            var message = app.vtranslate('JS_ADD_CHART_TO_DASHBOARD_SUCCESS_MSG', 'Reports');
            app.helper.showSuccessNotification({ message: message });

            // Auto hide the dropdown menu button
            if (addToDashboardBtn) {
                addToDashboardBtn.removeClass('dropdown-toggle').removeAttr('data-toggle');
            }
        });
    },

    // Added by Phuc on 2020.04.14
    initCustomButtons: function (container) {
        container.find('.customReportAction').on('click', function () {
            var href = $(this).data('href');

            // Get current filter
            var filterData = container.find('#form-filter').serializeArray();

            container.find('#form-filter').find('select[multiple]').each(function() {
                let name = $(this).attr('name');
                name = name.replace('[]', '');

                filterData.push({name: name, value: $(this).val()});
            })

            var headerContainer = $('div.reportsDetailHeader');
            var newEle = '<form action=' + href + ' method="POST" target="_blank">\n\
            <input type = "hidden" name ="' + csrfMagicName + '"  value=\''+csrfMagicToken+'\'>\n\
            <input type="hidden" value="" name="advanced_filter" id="advanced_filter" /></form>';

            var ele = jQuery(newEle); 
            var form = ele.appendTo(headerContainer);
            form.find('#advanced_filter').val(JSON.stringify(filterData));
            form.submit();
        });

        container.find('.addToMarketingList').on('click', function() {
            if ($('#result-module').val() == 'Account') {
                app.helper.showAlertNotification({ message: app.vtranslate('Reports.JS_REPORT_CAN_NOT_ADD_ACCOUNT_TO_MARKETING_LIST') });
                return;
            }
    
            var params = {
                'module' : 'CPTargetList',
                'src_module' : 'Reports',
                'src_field' : '',
                'src_record' : '',
                'multi_select' : true,
            }
    
            Vtiger_Popup_Js("CPTargetList_Popup_Js", {}, {
                done: function (result, eventToTrigger){
                    var label = 'Reports.JS_REPORT_SELECT_TARGET_LIST_CONFIRM';
        
                    if (Object.keys(result).length > 1) {
                        label = 'Reports.JS_REPORT_SELECT_TARGET_LISTS_CONFIRM';
                    }
        
                    app.helper.showConfirmationBox({ message: app.vtranslate(label) }).then(function (){
                        Vtiger_Popup_Js.prototype.done.call(this, result, eventToTrigger);
                    });
                }
            });
            
            popupInstance = CPTargetList_Popup_Js.getInstance();
            popupInstance.show(params, function (data) {
                app.helper.showProgress();
                var contact_ids = [];
                var responseData = JSON.parse(data);
    
                $('#result-content').find('tbody').find('tr').find('a[data-module="Contacts"]').each(function() {
                    contact_ids.push($(this).data('record-id'));
                });
    
                var params = {
                    'module': 'CPTargetList',
                    'action': 'RelationAjax',
                    'mode': 'addRelationsForContacts',
                    'cptargetlist_ids': Object.keys(responseData),
                    'contact_ids' : contact_ids
                };
    
                app.request.post({ data: params }).then(function (error, data) {
                    app.helper.hideProgress();

                    if (error || !data) {
                        var message = app.vtranslate('Vtiger.JS_THERE_WAS_SOMETHING_ERROR');
                        app.helper.showErrorNotification({ message: message });
                        return;
                    }

                    var message = app.vtranslate('Reports.JS_REPORT_ADD_SUCCESSFULLY');
                    app.helper.showSuccessNotification({ message: message });
                    return;
                });
            });        
        });
    },

    initPeriodFilter: function (container, cbFunction = null, args = null) {
        container.find('#period').on('change', function () {
            var displayedBy = jQuery(this).val();
    
            if (displayedBy == 'custom') {
                container.find('#month').addClass('hide');
                container.find('#quarter').addClass('hide');
                container.find('#year').addClass('hide');
                container.find('.date-time-field').removeClass('hide');
            }
    
            if (displayedBy == 'month') {
                container.find('#month').removeClass('hide'); 
                container.find('#quarter').addClass('hide');
                container.find('#year').removeClass('hide');
                container.find('.date-time-field').addClass('hide');
            }
            
            if (displayedBy == 'quarter') {
                container.find('#month').addClass('hide');
                container.find('#quarter').removeClass('hide');
                container.find('#year').removeClass('hide');
                container.find('#year').select2();
                container.find('.date-time-field ').addClass('hide');
            }
            
            if (displayedBy == 'year') {            
                container.find('#month').addClass('hide');
                container.find('#quarter').addClass('hide');
                container.find('#year').removeClass('hide');
                container.find('.date-time-field').addClass('hide');
            }

            // Check if load any actions
            if (cbFunction != null) {
                if (args == null) {
                    eval(cbFunction + '()');
                }
                else {
                    eval(cbFunction + '(' + args + ')');
                }
            }
        });
    },

    initDepartmentFilter: function (container, addEmpty = false, addAll = true) {
        container.find('#department').on('change', function () {
            app.helper.showProgress();
    
            var params = {
                module: 'Reports',
                action: 'DetailAjax',
                mode: 'getUsersByDepartment',
                department: jQuery(this).val(),
                add_all: addAll,
                add_empty: addEmpty
            };
    
            app.request.post({ data: params }).then(function (error, data) {
                app.helper.hideProgress();
    
                if (error || !data) {
                    var message = app.vtranslate('Vtiger.JS_THERE_WAS_SOMETHING_ERROR');
                    app.helper.showErrorNotification({ message: message });
                    return;
                }
    
                jQuery('#employee').select2('destroy');
                jQuery('#employee').find('option').remove();
    
                jQuery.each(data, function (k, v) {
                    jQuery('#employee').append('<option value="' + k  + '">' + v + '</option>');
                })
    
                if (addEmpty) jQuery('#employee').val('');
                if (addAll) jQuery('#employee').val('0');

                jQuery('#employee').select2();
            });
        })
    },

    initDeparmentsFilter: function (container, addEmpty = false, addAll = true) {
        if (jQuery('#employees').length && jQuery('#employees').data('reference') == 'deparments') {
            container.find('#departments').on('change', function () {
                app.helper.showProgress();
        
                var params = {
                    module: 'Reports',
                    action: 'DetailAjax',
                    mode: 'getUsersByDepartment',
                    add_empty: addEmpty,
                    add_add: addAll,
                    department: jQuery(this).val()
                };
        
                app.request.post({ data: params }).then(function (error, data) {
                    app.helper.hideProgress();
        
                    if (error || !data) {
                        var message = app.vtranslate('Vtiger.JS_THERE_WAS_SOMETHING_ERROR');
                        app.helper.showErrorNotification({ message: message });
                        return;
                    }
        
                    jQuery('#employees').select2('destroy');
                    jQuery('#employees').find('option').remove();
        
                    jQuery.each(data, function (k, v) {
                        jQuery('#employees').append('<option value="' + k  + '">' + v + '</option>');
                    })
        
                    jQuery('#employees').select2();
                });
            })
        }
    },
}