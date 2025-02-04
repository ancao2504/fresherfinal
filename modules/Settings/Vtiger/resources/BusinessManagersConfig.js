/*
	File: BusinessManagersConfig.js
	Author: Vu Mai
	Date: 2022-08-01
	Purpose: Business Managers config UI handle
*/

CustomView_BaseController_Js('Settings_Vtiger_BusinessManagersConfig_Js', {}, {

	registerEvents: function () {
		this._super();
		this.registerEventFormInit();
	},

	registerEventFormInit: function () {
		let self = this;
		let form = this.getForm();
		let facebookIntegrationManagersInput = form.find('input[name="facebook_integration_managers"]');
		let zaloIntegrationManagersInput = form.find('input[name="zalo_integration_managers"]');
		let telesalesCampaignManagersInput = form.find('input[name="telesales_campaign_managers"]');
		let leadsDistributionManagersInput = form.find('input[name="leads_distribution_managers"]');
		let ecommerceShopIntegrationInput = form.find('input[name="ecommerce_shop_integration_managers"]'); // Added by Tung Nguyen on 2022-09-07

		// Init input in form
		CustomOwnerField.initCustomOwnerFields(facebookIntegrationManagersInput);
		CustomOwnerField.initCustomOwnerFields(zaloIntegrationManagersInput);
		CustomOwnerField.initCustomOwnerFields(telesalesCampaignManagersInput);
		CustomOwnerField.initCustomOwnerFields(leadsDistributionManagersInput);
		CustomOwnerField.initCustomOwnerFields(ecommerceShopIntegrationInput); // Added by Tung Nguyen on 2022-09-07

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

	saveConfig: function (form) {
		app.helper.showProgress();
		let config = form.serializeFormData();

		// Do saving config
		let params = {
			module: 'Vtiger',
			parent: 'Settings',
			action: 'SaveBusinessManagersConfig',
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
		})
	},
});