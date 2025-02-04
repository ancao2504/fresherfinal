/*
	GoogleIntegrationConfig.js
	Author: Hieu Nguyen
	Date: 2022-06-16
	Purpose: handle logic on the UI of Google Integration Config form
*/

CustomView_BaseController_Js('Settings_Vtiger_GoogleIntegrationConfig_Js', {}, {
	registerEvents: function() {
		this._super();
		this.registerEventFormInit();
	},
	registerEventFormInit: function() {
		this.initSettingsForm()
	},
	initSettingsForm: function () {
		let self = this;
		let form = jQuery('form#settings');

		form.vtValidate({
			submitHandler: (form) => {
				form = $(form);
				self.saveConfig(form);
				return;
			}
		});
	},
	saveConfig: function (form) {
		app.helper.showProgress();
		let formData = form.deepSerializeFormData();
		let config = formData['config'];

		let params = {
			module: 'Vtiger',
			parent: 'Settings',
			action: 'SaveGoogleIntegrationConfig',
			mode: 'saveSettings',
			config: config
		}

		app.request.post({ data: params })
		.then((err, res) => {
			app.helper.hideProgress();

			if (err) {					
				app.helper.showErrorNotification({ message: app.vtranslate('JS_SAVE_SETTINGS_ERROR_MSG') });
				return;
			}

			app.helper.showSuccessNotification({ message: app.vtranslate('JS_SAVE_SETTINGS_SUCCESS_MSG') });
		});
	}
});