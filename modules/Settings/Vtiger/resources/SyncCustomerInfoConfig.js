/**
 * Name: SyncCustomerInfoConfig.js
 * Author: Phu Vo
 * Date: 2020.06.24
 */

CustomView_BaseController_Js('Settings_Vtiger_SyncCustomerInfoConfig_Js', {}, {
    registerEvents: function () {
        this._super();
        this.registerFormSubmit();
        this.initTooltipContent();
        this.initCustomOwnerField();
        this.initBootstrapToggle();
    },

    getForm: function () {
        return $('form[name="configs"]');
    },

    /**
     * Method to process form data for submission
     * @param {*} form 
     * @author Phu Vo (2019.07.12) 
     */
    getFormSerialize: function (form) {
        if (!(form instanceof jQuery)) form = $(form);
        let data = form.serializeFormData();

        for (let name in data) {
            let selector = `[name="${name}"]`;

            // Process checkbox case to save 1 value for on
            if (
                this.getForm().find(selector).attr('type') == 'checkbox'
                && data[name] == 'on'
            ) {
                data[name] = '1';
            }

            // Walk around in case select multiple save with only one value

            if (this.getForm().find(selector).is('select')) {
                data[name] = this.getForm().find(selector).val();
            }
        }

        return data;
    },

    registerFormSubmit: function () {
        this.getForm().on('submit', (event) => {
            event.preventDefault();

            const target = $(event.target);
            const formData = this.getFormSerialize(target);

            let params = Object.assign({
                module: 'Vtiger',
                parent: 'Settings',
                action: 'SaveSyncCustomerInfoConfig',
            }, formData);

            app.helper.showProgress();

            app.request.post({ data: params }).then((err, res) => {
                app.helper.hideProgress();

                if(err) {
                    app.helper.showErrorNotification({message: app.vtranslate('JS_NOTIFICATIONS_SAVE_ERROR_MSG', 'CPNotifications')});
                    return;
                }

                app.helper.showSuccessNotification({message: app.vtranslate('JS_NOTIFICATIONS_SAVE_SUCCESS_MSG', 'CPNotifications')});
                return;
            });

            return;
        });
    },

    initTooltipContent: function () {
        $('.custom-popover-wrapper').customPopover({
            trigger: 'hover',
        });
    },

    initCustomOwnerField: function () {
        const form = this.getForm();
        const ownerFields = form.find(':input.assigned-users');
        CustomOwnerField.initCustomOwnerFields(ownerFields);
    },
    initBootstrapToggle: function () {
        $('.bootstrap-switch').bootstrapSwitch();
    }
});