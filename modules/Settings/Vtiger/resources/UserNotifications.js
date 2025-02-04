/*
    File: SystemNotification.js
    Author: Phu Vo
    Date: 2019.03.22
    Purpose: User notification ui handler
*/

CustomView_BaseController_Js('Settings_Vtiger_UserNotifications_Js', {}, {
    form: $('form[name="settings"]'),
    registerEvents: function() {
        this._super();
        this.registerEventFormInit();
        this.triggerProcess();
    },
    triggerProcess: function() {
        this.form.find('input[name="receive_notifications"]').trigger('change');
    },
    registerEventFormInit: function() {
        this.form.find('input').attr('autocomplete', 'off');

        this.form.find('.select2-input').on('focus', function() {
            let row = $(this).closest('.fieldValue');
            row.find('.select2-choices').removeClass('input-error');
        }) 

        this.form.on('change', 'input[name="receive_notifications"]', e => {
            let target = $(e.target);
            if(target.attr('checked')) {
                this.toggleShowOnAccept(true);
            }
            else {
                this.toggleShowOnAccept(false);
            }
        });
        
        this.form.on('submit', e => {
            e.preventDefault();
            
            let target = $(e.target);
            
            let formData = target.serializeFormData();

            target.find('input[type="checkbox"]').each( function(e) {
                let fieldName = $(this).attr('name')
                formData[fieldName] = $(this).is(':checked') ? 1 : 0;
            });

            // if user choose not receive notification, clear configs
            if(!formData.receive_notifications) {
                formData = {
                    receive_notifications: 0
                }

                this.clearForm();
            }

            // if user choose receiver notifications, validate notify_method
            if(formData.receive_notifications) {
                let notifyMethod = this.form.find('[name="receive_notifications_method"]');
                let notifyMethodValue = notifyMethod.val();
                let row = notifyMethod.closest('.fieldValue');

                if( !(notifyMethodValue  instanceof Array) || notifyMethodValue.length === 0) {
                    row.find('.select2-choices').addClass('input-error');
                    app.helper.showErrorNotification({message: app.vtranslate('JS_NOTIFICATIONS_NOTIFY_METHOD_REQUIRED_AT_LEAST_ONE_ERROR', 'CPNotifications')});
                    return false;
                }
            }

            let params = {
                module: 'Vtiger',
                parent: 'Settings',
                action: 'SaveUserNotificationConfig',
                config: formData
            }

            app.helper.showProgress();

            app.request.post({data: params}).then((err, res) => {
                app.helper.hideProgress();

                if(err) {
                    app.helper.showErrorNotification({message: app.vtranslate('JS_NOTIFICATIONS_SAVE_ERROR_MSG', 'CPNotifications')});
                    return;
                }

                app.helper.showSuccessNotification({message: app.vtranslate('JS_NOTIFICATIONS_SAVE_SUCCESS_MSG', 'CPNotifications')});
                return;
            });
        });
    },
    toggleShowOnAccept(status) {
        let target = $('.showOnAccept');
        if(status) {
            target.show();
            target.find("input, select, textarea").prop('disabled', false);
            return;
        }

        target.hide();
        target.find("input, select, textarea").prop('disabled', true);
    },
    clearForm(dom) {
        if( !(dom instanceof jQuery)) dom = jQuery(dom);
        if(!dom.is('form')) dom = this.form;
        if(!dom.is('form')) return false;

        this.form.find(':input').each(function() {
            switch(this.type) {
                case 'password':
                case 'select-multiple':
                case 'select-one':
                case 'text':
                case 'textarea':
                    $(this).val('');
                    break;
                case 'checkbox':
                case 'radio':
                    this.checked = false;
            }
        });
        
        this.form.find('select').trigger('change');
        return this.form;
    }
});