/**
 * Name: RelatedActivitiesConfig.js
 * Author: Phu Vo
 * Date: 2020.06.24
 */

CustomView_BaseController_Js('Settings_Vtiger_RelatedActivitiesConfig_Js', {}, {
    registerEvents: function () {
        this._super();
        this.registerSwitcher();
        this.registerFormSubmit();
    },

    getForm: function () {
        return $('form[name="configs"]');
    },

    registerSwitcher: function () {
        this.getForm().find('.bootstrap-switch').bootstrapSwitch();
    },

    registerFormSubmit: function () {
        this.getForm().on('submit', (event) => {
            event.preventDefault();

            const target = $(event.target);
            const formData = target.serializeFormData();

            let params = Object.assign({
                module: 'Vtiger',
                parent: 'Settings',
                action: 'SaveRelatedActivitiesConfig',
            }, formData);

            app.helper.showProgress();

            app.request.post({ data: params }).then((err, res) => {
                app.helper.hideProgress();

                if(err) {
                    app.helper.showErrorNotification({message: app.vtranslate('JS_RELATED_ACTIVITIES_SAVE_SUBPANEL_CONFIG_ERROR_MSG')});
                    return;
                }

                app.helper.showSuccessNotification({message: app.vtranslate('JS_RELATED_ACTIVITIES_SAVE_SUBPANEL_CONFIG_SUCCESS_MSG')});
                return;
            });

            return;
        });
    }
});