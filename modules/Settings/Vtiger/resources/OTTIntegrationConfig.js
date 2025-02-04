/*
	OTTIntegrationConfig.js
	Author: Hieu Nguyen
	Date: 2022-06-16
	Purpose: handle logic on the UI of OTT Integration Config form
*/

CustomView_BaseController_Js('Settings_Vtiger_OTTIntegrationConfig_Js', {}, {
	registerEvents: function() {
		this._super();
		this.registerEventFormInit();
	},
	
	registerEventFormInit: function() {
		let form = this.getForm();

		if (form.data('mode') == 'ShowList') {
			this.initVendorListForm(form);
		}
		else {
			this.initVendorDetailForm(form);
		}
	},

	getForm: function () {
		return $('form#settings');
	},

	getBaseUrl: function (mode) {
		return 'index.php?module=Vtiger&parent=Settings&view=OTTIntegrationConfig&mode=' + mode;
	},

	initVendorListForm: function (form) {
		let self = this;
		let channelInput = form.find('[name="channel"]');
		let searchInput = form.find('[name="search_input"]');
		let vendorList = form.find('#vendor-list');

		// Handle channel input
		channelInput.on('change', function () {
			let channel = $(this).val();
			let url = self.getConfigListUrl(channel);
			window.location.href = url;
		});

		// Handle search input
		searchInput.on('input', function () {
			let keyword = $(this).val().toLowerCase();
			vendorList.find('.vendor-container').hide();

			vendorList.find('.vendor-container').each(function () {
				if ($(this).find('.vendor').data('displayName').toLowerCase().search(keyword) > -1) {
					$(this).show();
				}
			});
		});

		// Handle vendor item click
		vendorList.find('.vendor[connected]').on('click', function (e) {
			if ($(e.target).is('button, a')) return;
			e.preventDefault();

			let channel = channelInput.val();
			let gateway = $(this).data('name');
			let url = self.getConfigDetailUrl(channel, gateway);
			window.location.href = url;
		});

		// Handle connect button
		vendorList.find('.btn-connect:not(:disabled)').on('click', function (e) {
			e.preventDefault();

			let channel = channelInput.val();
			let gateway = $(this).closest('.vendor').data('name');
			let url = self.getConfigDetailUrl(channel, gateway);
			window.location.href = url;
		});

		// Handle disconnect button
		vendorList.find('.btn-disconnect').on('click', function () {
			let channel = channelInput.val();
			let channelName = channelInput.find('option[value="'+ channel +'"]').text();
			let gatewayName = $(this).closest('.vendor').data('displayName');
			self.disconnect(channel, channelName, gatewayName);
		});
	},

	getConfigListUrl: function (channel) {
		return this.getBaseUrl('ShowList') + '&channel='+ channel;
	},

	initVendorDetailForm: function (form) {
		let self = this;
		let vendorDetail = form.find('#vendor-detail');

		// Validate form
		form.vtValidate({
			submitHandler: function () {
				self.saveConfig(form);
				return;
			}
		});

		// Handle disconnect button
		form.find('#btn-disconnect').on('click', function () {
			let channel = form.find('[name="channel"]').val();
			let data = vendorDetail.data();
			self.disconnect(channel, data.channelName, data.gatewayName);
		});
	},

	getConfigDetailUrl: function (channel, gateway) {
		return this.getBaseUrl('ShowDetail') +'&channel='+ channel +'&gateway='+ gateway;
	},

	saveConfig: function (form) {
		app.helper.showProgress();
		let formData = form.deepSerializeFormData();
		let config = formData['config'];

		let params = {
			module: 'Vtiger',
			parent: 'Settings',
			action: 'SaveOTTIntegrationConfig',
			mode: 'saveConfig',
			channel: formData['channel'],
			gateway: formData['gateway'],
			config: config
		}

		app.request.post({ data: params })
		.then((err, res) => {
			app.helper.hideProgress();

			if (err) {					
				app.helper.showErrorNotification({ message: err.message });
				return;
			}

			app.helper.showSuccessNotification({ message: app.vtranslate('JS_SAVE_SETTINGS_SUCCESS_MSG') });
			window.location.reload();
		});
	},

	disconnect: function (channel, channelDisplayName, gatewayDisplayName) {
		let self = this;
		let replaceParams = { channel: channelDisplayName, gateway: gatewayDisplayName };
		let confirmMsg = app.vtranslate('JS_OTT_INTEGRATION_DISCONNECT_CONFIRMATION_MSG', replaceParams);

		app.helper.showConfirmationBox({ message: confirmMsg })
		.then(() => {
			app.helper.showProgress();

			let params = {
				module: 'Vtiger',
				parent: 'Settings',
				action: 'SaveOTTIntegrationConfig',
				mode: 'disconnect',
				channel: channel,
			};
	
			app.request.post({ data: params })
			.then((err, res) => {
				app.helper.hideProgress();
	
				if (err) {
					return app.helper.showErrorNotification({ message: err.message });
				}
	
				if (!res) {
					return app.helper.showErrorNotification({ message: app.vtranslate('JS_VENDOR_INTEGRATION_DISCONNECT_ERROR_MSG') });
				}

				app.helper.showSuccessNotification({ message: app.vtranslate('JS_VENDOR_INTEGRATION_DISCONNECT_SUCCESS_MSG') });
				window.location.href = self.getConfigListUrl(channel);
			});
		});
	}
});