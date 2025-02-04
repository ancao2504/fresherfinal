/*
	Name: ZaloOAConfig.js
	Author: Vu Mai
	Date: 2022-08-02
	Purpose: ZaloOA config UI handle 
*/

CustomView_BaseController_Js('CPSocialIntegration_ZaloOAConfig_Js', {}, {

	registerEvents: function () {
		this._super();
		this.registerEventFormInit();
	},

	registerEventFormInit: function () {
		let self = this;
		let form = this.getForm();

		// Added by Vu Mai on 2022-08-22 to add click event for icon active in modules menu to reload page
		$('#modules-menu').find('ul li.active').on('click', function () { 
			location.reload();
		});
		// End Vu Mai

		if (form.data('tab') == 'GeneralConfig') {
			this.initGeneralConfigForm(form);
		}
		
		if (form.data('tab') == 'Connection') {
			this.initConnectionForm(form);
		}
	},

	getForm: function () {
		return $('form#config');
	},

	// Begin General Config logic
	initGeneralConfigForm: function (form) {
		let self = this;

		// Init chat distribution users field
		let chatDistributionUsersInput = form.find('input#chat-distribution-users');
		CustomOwnerField.initCustomOwnerFields(chatDistributionUsersInput);

		// Do saving general config
		form.vtValidate({
			submitHandler: function () {
				self.saveConfig(form);
				return;
			}
		});
	},

	// Implemented by Hieu Nguyen on 2022-08-15
	saveConfig: function (form) {
		app.helper.showProgress();
		let formData = form.serializeObject();

		let params = {
			module: 'CPSocialIntegration',
			action: 'ZaloOAAjax',
			mode: 'saveConfig',
			config: formData
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

	// Begin Connection logic
	initConnectionForm: function (form) {
		let self = this;

		// Init oa toggle button
		form.find('.bootstrap-switch').bootstrapSwitch();

		// Handle click event for edit zalo credentials button
		form.find('#edit-zalo-credentials').on('click', function() {
			let currentCredentials = form.find('input#credentials').data('credentials');
			
			self.showCredentialsModal(currentCredentials, function () {
				app.helper.showSuccessNotification({ message: app.vtranslate('JS_SAVE_SETTINGS_SUCCESS_MSG') });
				window.location.reload();
			});
		});

		// Handle click event for connect zalo oa button
		form.find('#connect-zalo-oa, #add-zalo-oa').on('click', function() {
			self.triggerConnectZaloOAProcess(form);
		});

		// Handle toggle zalo shop
		form.find('.is-zalo-shop').on('change', function () {
			let selectedOA = $(this).closest('.oa-container');
			let oaInfo = self.getOAInfo(selectedOA);
			let isZaloShop = $(this).is(':checked');
			self.toggleZaloShop(oaInfo.id, isZaloShop);
		});

		// Handle toggle enable/disable oa
		form.find('.toggle-enable-oa').on('switchChange.bootstrapSwitch', function () {
			let selectedOA = $(this).closest('.oa-container');
			let oaInfo = self.getOAInfo(selectedOA);
			let enable = $(this).is(':checked');

			if (!enable) {
				let replaceParams = { oa_name: oaInfo.name };
				let confirmMsg = app.vtranslate('CPSocialIntegration.JS_ZALO_OA_CONFIG_TOGGLE_ENABLE_ZALO_OA_CONFIRM_MSG', replaceParams);

				app.helper.showConfirmationBox({ message: confirmMsg })
				.then(
					function () {
						self.toggleEnableOA(oaInfo.id, enable);
					},
					function () {
						form.find('.toggle-enable-oa').attr('checked', true).trigger('change');
					}
				);
			}
			else {
				self.toggleEnableOA(oaInfo.id, enable);
			}
		});
	},

	// Implemented by Hieu Nguyen on 2022-08-15
	getOAInfo: function (oaContainer) {
		let oaInfo = oaContainer.data('oaInfo');
		return oaInfo;
	},

	// Implemented by Hieu Nguyen on 2022-08-15
	triggerConnectZaloOAProcess(form) {
		let self = this;
		let currentCredentials = form.find('input#credentials').data('credentials');

		// This is the first time connect, show credentials modal first then show connect zalo oa modal
		if (!currentCredentials) {
			self.showCredentialsModal(null, function (newCredentials) {
				self.openZaloAuthPopup(newCredentials.app_id);
			});
		}
		// This is when the credentials are saved, show connect zalo oa modal directly
		else {
			self.openZaloAuthPopup(currentCredentials.app_id);
		}
	},

	// Implemented by Hieu Nguyen on 2022-08-15
	showCredentialsModal: function (currentCredentials, saveSuccessCallback) {
		let self = this;
		let mainForm = self.getForm();
		let modal = $('.modal-credentials').clone(true, true);

		app.helper.showModal(modal, {
			preShowCb: function () {
				modal.removeClass('hide');
				let form = modal.find('form#credentials');
				let appIdInput = form.find('[name="zalo_app_id"]');
				let secretKeyInput = form.find('[name="secret_key"]');

				// In Edit mode
				if (currentCredentials) {
					// Update modal title and button name
					modal.find('.modal-header').find('h4').text(app.vtranslate('CPSocialIntegration.JS_MODAL_EDIT_CREDENTIALS_TITLE'));
					modal.find('.modal-footer').find('button[type="submit"]').text(app.vtranslate('CPSocialIntegration.JS_MODAL_EDIT_CREDENTIALS_SAVE_BTN_TITLE'));

					// Fill current credentails
					appIdInput.val(currentCredentials.app_id);
					secretKeyInput.val(currentCredentials.secret_key);
				}

				// Init modal form
				let controller = Vtiger_Edit_Js.getInstance();
				controller.registerBasicEvents(form);
				vtUtils.applyFieldElementsView(form);

				// Form validation
				let params = {
					submitHandler: function () {
						app.helper.showProgress();
						let appId = appIdInput.val().trim();
						let secretKey = secretKeyInput.val().trim();

						let params = {
							module: 'CPSocialIntegration',
							action: 'ZaloOAAjax',
							mode: 'saveCredentials',
							app_id: appId,
							secret_key: secretKey,
						};

						// Call ajax to save credentials
						app.request.post({ data: params })
						.then((err, res) => {
							app.helper.hideProgress();
							
							// Handle error
							if (err) {
								app.helper.showErrorNotification({ message: err.message });
								return;
							}

							// Dismiss modal
							form.find('.cancelLink').trigger('click');
							
							// Update hidden input value
							let newCredentials = {
								app_id: appId,
								secret_key: secretKey,
							};

							mainForm.find('input#credentials').data('credentials', newCredentials);

							// Trigger callback function
							if (typeof saveSuccessCallback == 'function') {
								saveSuccessCallback(newCredentials);
							}
						});

						return;
					}
				};

				form.vtValidate(params);
			}
		});
	},

	// Implemented by Hieu Nguyen on 2022-08-15
	openZaloAuthPopup: function (appId) {
		let url = 'index.php?module=CPSocialIntegration&action=ZaloOAAjax&mode=makeAuthRequest&app_id=' + appId;
		let popup = UIUtils.popupCenter(url, 'ConnectZaloOA', 800, 780);	// Open connect url in new popup
	},

	// Implemented by Hieu Nguyen on 2022-08-15
	toggleZaloShop: function (oaId, isZaloShop) {
		app.helper.showProgress();
		let params = {
			module: 'CPSocialIntegration',
			action: 'ZaloOAAjax',
			mode: 'toggleZaloShop',
			oa_id: oaId,
			is_zalo_shop: isZaloShop,
		}

		app.request.post({ data: params })
		.then((err, res) => {
			app.helper.hideProgress();

			if (err) {
				app.helper.showErrorNotification({ message: err.message });
				window.location.reload();
			}
		});
	},

	// Implemented by Hieu Nguyen on 2022-08-15
	toggleEnableOA: function (oaId, enable) {
		app.helper.showProgress();
		let params = {
			module: 'CPSocialIntegration',
			action: 'ZaloOAAjax',
			mode: 'toggleEnableOA',
			oa_id: oaId,
			enable: enable,
		}

		app.request.post({ data: params })
		.then((err, res) => {
			app.helper.hideProgress();

			if (err) {
				app.helper.showErrorNotification({ message: err.message });
				window.location.reload();
			}
		});
	},

	// Modified by Hieu Nguyen on 2022-08-15
	removeZaloOA: function (targetBtn) {
		let self = this;
		let selectedOA = $(targetBtn).closest('.oa-container');
		let oaInfo = self.getOAInfo(selectedOA);
		
		let replaceParams = { oa_name: oaInfo.name };
		let confirmMsg = app.vtranslate('CPSocialIntegration.JS_ZALO_OA_CONFIG_DISCONNECT_ZALO_OA_CONFIRM_MSG', replaceParams);

		app.helper.showConfirmationBox({ message: confirmMsg })
		.then(function () {
			app.helper.showProgress();

			let params = {
				module: 'CPSocialIntegration',
				action: 'ZaloOAAjax',
				mode: 'removeOA',
				oa_id: oaInfo.id
			};

			// Call ajax to remove the selected OA
			app.request.post({ data: params })
			.then((err, res) => {
				app.helper.hideProgress();
				
				// Handle error
				if (err) {
					app.helper.showErrorNotification({ message: err.message });
					return;
				}

				let message = app.vtranslate('CPSocialIntegration.JS_ZALO_OA_CONFIG_DISCONNECT_ZALO_OA_SUCCESS_MSG');
				app.helper.showSuccessNotification({ message: message });
				window.location.reload();
			});

			return;
		});
	},

	// Implemented by Hieu Nguyen on 2022-08-15
	syncZaloFollowerIds: function (targetBtn) {
		let self = this;
		let selectedOA = $(targetBtn).closest('.oa-container');
		let oaInfo = self.getOAInfo(selectedOA);

		// Check if OA is valid to sync or not
		if (oaInfo.token_status == 'expired') {
			app.helper.showErrorNotification({ message: app.vtranslate('CPSocialIntegration.JS_SOCIAL_CONFIG_ZALO_OA_EXPIRED_ERROR_MSG') });
			return;
		}

		if (!selectedOA.find('.toggle-enable-oa').is(':checked')) {
			app.helper.showErrorNotification({ message: app.vtranslate('CPSocialIntegration.JS_SOCIAL_CONFIG_ZALO_OA_DISABLED_ERROR_MSG') });
			return;
		}
		
		// Call ajax to trigger sync queue
		app.helper.showProgress();
		let params = {
			module: 'CPSocialIntegration',
			action: 'SyncAjax',
			mode: 'triggerSyncZaloOAFollowersIds',
			oa_id: oaInfo.id,
		};

		app.request.post({ data: params })
		.then((err, res) => {
			app.helper.hideProgress();

			// Handle error
			if (err) {
				app.helper.showErrorNotification({ message: err.message });
				return;
			}
			
			if (res !== true && !res.success) {
				app.helper.showErrorNotification({ message: app.vtranslate('CPSocialIntegration.JS_SOCIAL_SYNC_ZALO_FOLLOWER_IDS_ERROR_MSG') });
				return;
			}

			app.helper.showSuccessNotification({ message: app.vtranslate('CPSocialIntegration.JS_SOCIAL_SYNC_ZALO_FOLLOWER_IDS_SUCCESS_MSG') });
			window.location.reload();
		});
	},

	// Modified by Hieu Nguyen on 2022-08-15
	showConfigRequestInfoMessageModal: function (targetBtn) {
		let self = this;
		let modal = $('.modal-config-request-info-message').clone(true, true);
		let selectedOA = $(targetBtn).closest('.oa-container');
		let oaInfo = self.getOAInfo(selectedOA);
		let requestInfoMsg = selectedOA.data('requestInfoMsg');

		modal.removeClass('hide');

		let replaceParams = { oa_name: oaInfo.name };
		let hintText = app.vtranslate('CPSocialIntegration.JS_ZALO_OA_CONFIG_MODAL_CONFIG_REQUEST_INFO_MESSAGE_HINT_TEXT', replaceParams);
		modal.find('.modal-hint-text').html(hintText);

		// Display modal
		app.helper.showModal(modal, {
			preShowCb: function (modal) {
				const modalForm = modal.find('form#config-request-info-message');
				let titleInput = modalForm.find('[name="title"]');
				let messageInput = modalForm.find('[name="message"]');
				let imageUrlInput = modalForm.find('[name="image_url"]');

				// Fill info
				if (requestInfoMsg) {
					titleInput.val(requestInfoMsg.title);
					messageInput.val(requestInfoMsg.message);
					imageUrlInput.val(requestInfoMsg.image_url);
				}

				// Init form
				modalForm.vtValidate({
					submitHandler: function () {
						app.helper.showProgress();
						let title = titleInput.val().trim();
						let message = messageInput.val().trim();
						let imageUrl = imageUrlInput.val().trim();

						let params = {
							module: 'CPSocialIntegration',
							action: 'ZaloOAAjax',
							mode: 'saveRequestInfoMessageConfig',
							oa_id: oaInfo.id,
							title: title,
							message: message,
							image_url: imageUrl,
						};

						// Call ajax to save info
						app.request.post({ data: params })
						.then((err, res) => {
							app.helper.hideProgress();
							
							// Handle error
							if (err) {
								app.helper.showErrorNotification({ message: err.message });
								return;
							}

							modalForm.find('.cancelLink').trigger('click');  // Dismiss modal
							app.helper.showSuccessNotification({ message: app.vtranslate('JS_SAVE_SETTINGS_SUCCESS_MSG') });
							window.location.reload();
						});

						return;
					}
				});
			}
		});
	},
});

function handleConnectZaloOAResult(popup, success) {
    popup.close();

    setTimeout(() => {
        if (success) {
            bootbox.alert({
                message: app.vtranslate('CPSocialIntegration.JS_SOCIAL_CONFIG_CONNECT_ZALO_OA_SUCCESS_MSG'),
                callback: () => {
                    window.location.reload();
                }
            });
        }
        else {
            bootbox.alert(app.vtranslate('CPSocialIntegration.JS_SOCIAL_CONFIG_CONNECT_ZALO_OA_ERROR_MSG'));
        }
    }, 100);
}