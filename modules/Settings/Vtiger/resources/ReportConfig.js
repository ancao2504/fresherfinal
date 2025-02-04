/*
    ReportConfig.js
    Author: Phuc Lu
    Date: 2020.03.30
    Purpose: handle ui and saving report config
*/

CustomView_BaseController_Js('Settings_Vtiger_ReportConfig_Js', {}, {
    registerEvents: function () {
        this._super();
        this.registerEventFormInit();
    }, 
    registerEventFormInit: function () {
        var form = jQuery('form[name="settings"]');

        jQuery('.to-value').each(function (){
            if (!jQuery(this).is(':disabled')) {
                jQuery(this).attr('data-rule-required', true);
            }
        })

        form.on('keyup', 'input[name="min_successful_percentage"]' , function() {
            formatNumber(this, 'int');

            if (jQuery(this).val().length >= 3) {
                jQuery(this).val('100');
            }

            jQuery('#spn_percent').html(jQuery(this).val());
        })

        jQuery('input[name="min_successful_percentage"]').trigger('keyup');

        form.on('click', '.delete-button', function (e) {
            var group = jQuery(this).closest('.customer-group');
            app.helper.showConfirmationBox({message: app.vtranslate('JS_REPORT_CONFIG_REMOVE_GROUP_CONFIRM_MSG')}).then(function (){
                group.next().remove();
                group.remove();

                jQuery('.to-value').removeAttr('disabled');                
                jQuery('.to-value').trigger('change');
                jQuery('.to-value:last').attr('disabled', true);                
                jQuery('.to-value:last').data('rule-required', '');
                jQuery('.to-value:last').removeAttr('data-rule-required');       
                jQuery('.to-value:last').removeClass('input-error');
                jQuery('.to-value:last').val('');
            });
        });

        form.on('change', '.chx-alert-group', function (e) {
            if (jQuery(this).is(':checked')) {
                jQuery(this).closest('.form-group').find('.alert-group').find('.alert-value').val('');
                jQuery(this).closest('.form-group').find('.alert-group').removeClass('hide');
            }
            else {
                jQuery(this).closest('.form-group').find('.alert-group').addClass('hide');
            }
        });
        
        form.find('#add-group').on('click', function (e) {
            var cloneElement = jQuery('#customerGroup').find('.customer-group:first').clone();
            var d = new Date();
            var milisecond = d.getTime();

            jQuery(cloneElement).removeClass('hide');
            jQuery(cloneElement).find('.delete-button').removeClass('hide');
            jQuery(cloneElement).find('input[type="checkbox"]').attr('checked', false);    
            jQuery(cloneElement).find('input[type="text"]').val(''); 

            jQuery(cloneElement).find('input.group-name').attr('data-rule-required', true);
            jQuery(cloneElement).find('input.to-value').attr('data-rule-required', true);

            jQuery(cloneElement).find('input.group-name').attr('name', 'group-name-' + milisecond);
            jQuery(cloneElement).find('input.from-value').attr('name', 'from-value-' + milisecond);
            jQuery(cloneElement).find('input.to-value').attr('name', 'to-value-' + milisecond);
        
            if (!jQuery(cloneElement).find('input.alert-value').parent().parent().hasClass('hide')) {
                jQuery(cloneElement).find('input.alert-value').parent().parent().addClass('hide');
            }
            
            jQuery('#customerGroup').find('.customer-group:last').find('.to-value').removeAttr('disabled');
            jQuery('#customerGroup').find('.customer-group:last').find('.to-value').attr('data-rule-required', true);

            var maxValue = jQuery('#customerGroup').find('.customer-group:last').find('.to-value').val();
            maxValue = app.unformatCurrencyToUser(maxValue);

            if (isNaN(maxValue)) {
                maxValue = 2;
            }
            else {
                if (maxValue !== '') {
                    maxValue += 1;
                }
                else {
                    maxValue = 0;
                }
            }

            jQuery(cloneElement).find('.from-value').val(maxValue);
            jQuery(cloneElement).find('.from-value').trigger('keyup');

            jQuery(cloneElement).insertBefore(jQuery('.add-action-group'));
            jQuery('<hr>').insertBefore(jQuery('.add-action-group'));            
            jQuery('#customerGroup').find('.customer-group:last').find('.to-value').attr('disabled', 'disabled');
        });

        form.on('keyup change', '.to-value', function (e) {
            if (e.type == 'keyup') {
                formatNumber(this, 'int');
            }
            else {
                jQuery(this).val(app.formatNumberToUserFromNumber(jQuery(this).val()));
            }

            var thisValue = jQuery(this).val();
            var prevValue = jQuery(this).closest('.customer-group').find('.from-value').val();
            thisValue = app.unformatCurrencyToUser(thisValue);
            prevValue = app.unformatCurrencyToUser(prevValue);

            if (isNaN(thisValue) || isNaN(prevValue)) {
                return;
            }
            else {
                if (thisValue < prevValue && e.type == 'change') {
                    thisValue = prevValue + 1;
                    jQuery(this).val(thisValue);
                    jQuery(this).trigger('change');
                    return;
                }

                thisValue += 1;
            }

            nextFromElement = jQuery(this).closest('.customer-group').next().next('.customer-group').find('.from-value');
            jQuery(nextFromElement).val(thisValue);
            jQuery(nextFromElement).trigger('change');
        });
        
        form.on('keyup change', '.from-value', function (e) {
            if (e.type == 'keyup') {
                formatNumber(this, 'int');
            }
            else {
                jQuery(this).val(app.formatNumberToUserFromNumber(jQuery(this).val()));
            }

            var toElement = jQuery(this).closest('.customer-group').find('.to-value');
            if (jQuery(toElement).is(':disabled')) return;

            var thisValue = jQuery(this).val();
            var toValue = jQuery(toElement).val();

            thisValue = app.unformatCurrencyToUser(thisValue);
            toValue = app.unformatCurrencyToUser(toValue);

            if (toValue > thisValue) return;

            toValue = thisValue + 1;
            jQuery(toElement).val(toValue);
            jQuery(toElement).trigger('change');
        });

        form.on('keyup change', '.alert-value', function (e) {
            formatNumber(this, 'int');

            var fromElement = jQuery(this).closest('.customer-group').find('.from-value');
            var fromValue = jQuery(fromElement).val();
            var thisValue = jQuery(this).val();

            thisValue = app.unformatCurrencyToUser(thisValue);
            fromValue = app.unformatCurrencyToUser(fromValue);

            if (fromValue - 1 >= thisValue) return;

            thisValue = fromValue - 1;
            jQuery(this).val(thisValue);
            formatNumber (this, 'int');
        });

        form.vtValidate({
            submitHandler: (form) => {
                app.helper.showProgress();

                let customerGroups = [];

                jQuery('.customer-group:not(.hide)').each(function () {
                    let customerGroup = [];

                    customerGroup = {
                        group_id : jQuery(this).find('.group-id').val(), 
                        group_name : jQuery(this).find('.group-name').val(), 
                        from_value : jQuery(this).find('.from-value').val(), 
                        to_value : jQuery(this).find('.to-value').val(), 
                        alert_group : jQuery(this).find('.chx-alert-group').is(':checked') ? 1 : 0, 
                        alert_value : jQuery(this).find('.alert-value').val(), 
                    };

                    customerGroups.push(customerGroup);
                })

                let data = {
                    module: 'Vtiger', 
                    parent: 'Settings', 
                    action: 'SaveReportConfig', 
                    min_successful_percentage: jQuery('[name="min_successful_percentage"]').val(), 
                    customer_group_calculate_by: jQuery('[name="customer_group_calculate_by"]:checked').val(), 
                    customer_groups: customerGroups
                };

                // Need to peform form data procession here
                app.request.post({ data })
                .then((err, res) => {
                    app.helper.hideProgress();
                    
                    // handle error
                    if (err) {
                        app.helper.showErrorNotification ({ message: err.message });
                        return;
                    }
                    
                    // handle saving error
                    if (res !== true && !res.result) {
                        app.helper.showErrorNotification ({ message: app.vtranslate('JS_REPORT_CONFIG_ERROR_MSG') });
                        return;
                    }
                    
                    // Process res here
                    app.helper.showSuccessNotification ({ message: app.vtranslate('JS_REPORT_CONFIG_SUCCESS_MSG') });
                });

                return;
            }
        });
    }
});