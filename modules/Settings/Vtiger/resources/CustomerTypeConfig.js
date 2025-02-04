/**
 * Name: CustomerTypeConfig.js
 * Author: Phu Vo
 * Date: 2021.03.19
 * Description: Js handler for customer type config
 */

 CustomView_BaseController_Js('Settings_Vtiger_CustomerTypeConfig_Js', {}, {

    getForm: function () {
        if (!this.form) this.form = $('form[name="config"]');
        return this.form;
    },

    registerFormSubmit: function () {
        this.getForm().vtValidate({
            submitHandler: form => {
                const formData = $(form).serializeFormData();

                app.helper.showProgress();

                app.request.post({ data: formData }).then((err, res) => {
                    app.helper.hideProgress();
        
                    if (err || !res) {
                        var message = app.vtranslate('JS_THERE_WAS_SOMETHING_ERROR');
                        app.helper.showErrorNotification({ message: message });
                        return;
                    }
                    
                    app.helper.showSuccessNotification({message: app.vtranslate('JS_CUSTOMER_TYPE_CONFIG_SAVE_SETTINGS_SUCCESS_MSG')});
                });
            },
        });
    },

    registerFormEvents: function () {
        let self = this;
        
        this.getForm().find('[name="config[customer_type]"]').on('change', function (event) {
            self.getForm().find('.customer-type-container').removeClass('checked');
            self.getForm().find('[name="config[customer_type]"]:checked').closest('.customer-type-container').addClass('checked');
        });
    },
    
    registerEvents: function () {
        this._super();
        this.registerFormSubmit();
        this.registerFormEvents();
    }
});