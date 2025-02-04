/*
	File: CentralizedChatboxConfig.js
	Author: Vu Mai
	Date: 2022-07-29
	Purpose: Centralized ChatBox config UI handle
*/

CustomView_BaseController_Js('Settings_Vtiger_CentralizedChatboxConfig_Js', {}, {
	registerEvents: function () {
		this._super();
		this.registerEventFormInit();
	},

	registerEventFormInit: function () {
		let self = this;
		let form = this.getForm();
		let switchButton = form.find('[name="switch_button"]');
		let chatAdmin = form.find('input[name="chat_admins"]');

		// Init toggle button
		switchButton.bootstrapSwitch();

		// Init chat admin field
		CustomOwnerField.initCustomOwnerFields(chatAdmin);

		// Handle toggle button
		switchButton.on('switchChange.bootstrapSwitch', function () {
			let enable = $(this).is(':checked');
			self.toggleConfig(enable);
		});

		// Validate form
		form.vtValidate({
			submitHandler: function () {
				self.saveConfig(form);
			}
		});
	},

	getForm: function () {
		return $('form#config');
	},

	toggleConfig: function (enable) {
		app.helper.showProgress();
		let form = this.getForm();

		// Do saving config 
		let params = {
			module: 'Vtiger',
			parent: 'Settings',
			action: 'SaveCentralizedChatboxConfig',
			mode: 'toggleConfig',
			enable: enable,
		};

		app.request.post ({ data: params })
		.then((err, res) => {
			app.helper.hideProgress();

			if (err) {
				app.helper.showErrorNotification({ message: err.message });
				return;
			}

			if (enable) {
				form.find('#config-container').removeClass('hide');
				form.find('#inactive-config-hint-text').addClass('hide');
				form.find('#config-footer').removeClass('hide');
				app.helper.showSuccessNotification({ message: app.vtranslate('JS_ENABLE_CONFIG_SUCCESS_MSG') });
			}
			else {
				form.find('#config-container').addClass('hide');
				form.find('#inactive-config-hint-text').removeClass('hide');
				form.find('#config-footer').addClass('hide');
				app.helper.showSuccessNotification({ message: app.vtranslate('JS_DISABLE_CONFIG_SUCCESS_MSG') });
			}
		});
	},

	saveConfig: function (form) {
		app.helper.showProgress();
		let config = form.deepSerializeFormData();

		// Do saving config
		let params = {
			module: 'Vtiger',
			parent: 'Settings',
			action: 'SaveCentralizedChatboxConfig',
			mode: 'saveConfig',
			config: config,
		};

		app.request.post({ data: params })
		.then((err, res) => {
			app.helper.hideProgress();

			if (err) {
				app.helper.showErrorNotification({ message: err.message });
				return;
			}

			if (res !== true && !res.result) {
				app.helper.showErrorNotification({ message: app.vtranslate('JS_SAVE_SETTINGS_ERROR_MSG') });
				return;
			}

			app.helper.showSuccessNotification({ message: app.vtranslate('JS_SAVE_SETTINGS_SUCCESS_MSG') });
		});
	},
});