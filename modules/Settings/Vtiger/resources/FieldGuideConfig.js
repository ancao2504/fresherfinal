/*
    File: FieldGuideConfig.js
    Author: Hieu Nguyen
    Date: 2020-01-20
    Purpose: handle logic on the UI for Field Guide Config
*/

CustomView_BaseController_Js('Settings_Vtiger_FieldGuideConfig_Js', {}, {
    registerEvents: function() {
        this._super();
        this.registerEventFormInit();
    },
    registerEventFormInit: function() {
        var form = $('form#config');
        var targetModuleInput = form.find('[name="target_module"]');

        // Reload page when the targe module is changed
        targetModuleInput.on('change', function () {
            var currentValue = targetModuleInput.data('value');
            if ($(this).val() == currentValue) return;

            app.helper.showConfirmationBox({ message: app.vtranslate('JS_FIELD_GUIDE_CONFIG_CHANGE_MODULE_CONFIRM_MSG') })
            .then(
                function () {   // OK
                    var urlParams = app.convertUrlToDataParams(location.href);
                    urlParams['target_module'] = targetModuleInput.val();
                    
                    var redirectUrl = 'index.php?' + $.param(urlParams);
                    location.href = redirectUrl;
                },
                function () {   // CANCEL
                    targetModuleInput.val(currentValue).trigger('change');
                }
            );
        });

        // Handle submit form
        form.vtValidate({
            submitHandler : function() {
                app.helper.showProgress();

                var formData = form.serializeFormData();
                formData['module'] = 'Vtiger';
                formData['parent'] = 'Settings';
                formData['action'] = 'FieldGuideConfigAjax';
                formData['mode'] = 'saveConfig';

                app.request.post({ data: formData })
                .then((err, res) => {
                    app.helper.hideProgress();

                    if (err) {
                        app.helper.showErrorNotification({ message: app.vtranslate('JS_FIELD_GUIDE_CONFIG_SAVE_ERROR_MSG') });
                        return;
                    }

                    app.helper.showSuccessNotification({ message: app.vtranslate('JS_FIELD_GUIDE_CONFIG_SAVE_SUCCESS_MSG') });
                    location.reload();
                });
            }
        });
    }
});