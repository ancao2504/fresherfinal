/*
	File: ChatbotIntegrationConfig.js
	Author: Phu Vo
	Date: 2019.03.22
	Purpose: Chatbot Integration Config UI handler
	Refactored UI/UX by Vu Mai on 2022-07-15
*/

CustomView_BaseController_Js('Settings_Vtiger_ChatbotIntegrationConfig_Js', {}, {
	registerEvents: function () {
		this._super();
		this.registerEventFormInit();
	},

	registerEventFormInit: function () {
		const form = $('form#settings');

		// Init toggle button
		form.find('.bootstrap-switch').bootstrapSwitch();
		
		// Init form
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
		return 'index.php?module=Vtiger&parent=Settings&view=ChatbotIntegrationConfig&mode=' + mode;
	},

	// Begin Vendor List logic
	initVendorListForm: function (form) {
		let self = this;
		let switchButton = form.find('[name="switch_button"]')
		let searchInput = form.find('[name="search_input"]');
		let vendorList = form.find('#vendor-list');
	
		// Handle switch button
		switchButton.on('switchChange.bootstrapSwitch', function () {
			let enable = $(this).is(':checked');
			self.toggleConfig(enable);
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

			let provider = $(this).data('name');
			let url = self.getConfigDetailUrl(provider);
			window.location.href = url;
		});

		// Handle connect button
		vendorList.find('.btn-connect:not(:disabled)').on('click', function (e) {
			e.preventDefault();

			let provider = $(this).closest('.vendor').data('name');
			let url = self.getConfigDetailUrl(provider);
			window.location.href = url;
		});

		// Handle disconnect button
		vendorList.find('.btn-disconnect').on('click', function () {
			let providerDisplayName = $(this).closest('.vendor').data('displayName');
			self.disconnect(providerDisplayName);
		});
	},

	getConfigListUrl: function () {
		return this.getBaseUrl('ShowList');
	},

	toggleConfig: function (enable) {
		let form = this.getForm();
		app.helper.showProgress();
		
		let params = {
			module: 'Vtiger',
			parent: 'Settings',
			action: 'SaveChatbotIntegrationConfig',
			mode: 'toggleConfig',
			enable: enable,
		}

		app.request.post({ data: params })
		.then((err, res) => {
			app.helper.hideProgress();

			if (err) {
				app.helper.showErrorNotification({ message: err.message });
				return;
			}

			if (enable) {
				form.find('#vendor-list-container').removeClass('hide');
				form.find('#active-config-hint-text').removeClass('hide');
				form.find('#inactive-config-hint-text').addClass('hide');
				app.helper.showSuccessNotification({ message: app.vtranslate('JS_ENABLE_CONFIG_SUCCESS_MSG') });
			}
			else {
				form.find('#vendor-list-container').addClass('hide');
				form.find('#active-config-hint-text').addClass('hide');
				form.find('#inactive-config-hint-text').removeClass('hide');
				app.helper.showSuccessNotification({ message: app.vtranslate('JS_DISABLE_CONFIG_SUCCESS_MSG') });
			}
		});
	},

	// Begin Vendor Detail logic
	chatbotInfos: {},
	hasChatbots: false,

	initVendorDetailForm: function (form) {
		let self = this;
		let vendorDetail = form.find('#vendor-detail');

		// Load chatbots from hidden input if this vendor has chatbots
		if (form.find('[name="chatbot_infos"]')[0] != null) {
			this.hasChatbots = true;

			try {
				let chatbotInfos = JSON.parse(form.find('[name="chatbot_infos"]').val());
				this.chatbotInfos = chatbotInfos;

				// Convert empty array into an empty object to prevent error when adding new item with style key => value
				if (Array.isArray(this.chatbotInfos) && this.chatbotInfos.length == 0) {
					this.chatbotInfos = {};
				}
			}
			catch (error) {}
		}

		// Validate form
		form.vtValidate({
			submitHandler: function () {
				// Prevent saving config without any chatbot if this vendor requires to setup chatbot
				if (self.hasChatbots && Object.keys(self.chatbotInfos).length == 0) {
					app.helper.showErrorNotification({message: app.vtranslate('JS_CHATBOT_INTEGRATION_SAVE_CONFIG_BOT_EMPTY_ERROR_MSG')});
					return false;
				}

				// Do saving config
				self.saveConfig(form);
				return false;
			}
		});

		// Handle disconnect button
		$('#btn-disconnect').on('click', function () {
			let providerDisplayName = form.find('[name="provider_name"]').val();
			self.disconnect(providerDisplayName);
		});
	},

	getConfigDetailUrl: function (provider) {
		return this.getBaseUrl('ShowDetail') +'&provider='+ provider;
	},

	getChatbotList: function () {
		let form = this.getForm();
		let chatbotList = form.find('#chatbot-list');
		return chatbotList;
	},

	getProviderName: function () {
		let form = this.getForm();
		let providerName = form.find('[name="provider"]').val();
		return providerName;
	},

	showChatbotModal: function (targetBtn) {
		let self = this;
		let form = this.getForm();
		let provider = this.getProviderName();
		targetBtn = $(targetBtn);
		app.helper.showProgress();

		let params = {
			module: 'Vtiger',
			parent: 'Settings',
			view: 'ChatbotIntegrationConfig',
			mode: 'GetChatbotModal',
			provider: provider,
		}

		// User click edit button
		if (targetBtn.hasClass('btn-edit-chatbot')) {
			let chatbotId = targetBtn.closest('tr').attr('bot-id');	// Do not use .data() as it will return the old value
			params['chatbot_id'] = chatbotId;

			// Get chatbot info from local cache when chatbot list already changed by user
			if (form.find('[name="chatbots_updated"]').val() == 'true') {
				params['chatbot_info'] = self.chatbotInfos[chatbotId];
			}

			params['edit'] = true;	// To display the right modal title
		}

		// Call ajax to get modal content
		app.request.get({ data: params })
		.then((err, res) => {
			app.helper.hideProgress();

			if (err) {					
				app.helper.showErrorNotification({ message: err.message });
				return;
			}

			// Display modal
			app.helper.showModal(res, {
				preShowCb: function (modal) {
					const modalForm = modal.find('form[name="chatbot-info"]');

					modalForm.vtValidate({
						submitHandler: function () {
							let chatbotInfo = modalForm.deepSerializeFormData();
							
							if (targetBtn.hasClass('btn-edit-chatbot')) {
								let chatbotList = self.getChatbotList();
								let rowIndex = chatbotList.find('#tbl-chatbots tbody').find('tr').index(targetBtn.closest('tr'));
								self.saveChatbotInfo(chatbotInfo, rowIndex);
							}
							else {
								self.saveChatbotInfo(chatbotInfo);
							}

							modalForm.find('.cancelLink').trigger('click');
							return false;
						}
					});
				}
			});
		});
	},

	saveChatbotInfo: function (chatbotInfo, rowIndex = null) {
		if (!chatbotInfo.bot_id || !chatbotInfo.bot_name) return;
		let form = this.getForm();
		let chatbotList = this.getChatbotList();
		
		// When user update a chatbot
		if (rowIndex !== null) {
			let curRow = chatbotList.find('#tbl-chatbots tbody').find('tr:eq('+ rowIndex +')');
			let curBotId = curRow.attr('bot-id');	// Do not use .data() as it will return the old value

			// If bot id is changed then we should delete the old record
			if (chatbotInfo.bot_id != curBotId) {
				delete this.chatbotInfos[curBotId];
			}

			// Save updated chatbot info
			this.chatbotInfos[chatbotInfo.bot_id] = chatbotInfo;

			// Update row UI
			curRow.attr({ 'bot-id': chatbotInfo.bot_id, 'bot-name': chatbotInfo.bot_name });
			curRow.find('.bot-name').text(chatbotInfo.bot_name);
		}
		// When user add a new chatbot
		else {
			// Save new chatbot info
			this.chatbotInfos[chatbotInfo.bot_id] = chatbotInfo;

			// Insert new row
			let newRow = chatbotList.find('#tbl-chatbots tfoot#template').find('tr').clone(true, true);
			newRow.attr({ 'bot-id': chatbotInfo.bot_id, 'bot-name': chatbotInfo.bot_name });
			newRow.find('.bot-name').text(chatbotInfo.bot_name);
			chatbotList.find('#tbl-chatbots tbody').append(newRow);
		}

		// Update status
		form.find('[name="chatbots_updated"]').val('true');
	},

	removeChatbot: function (targetBtn) {
		let self = this;
		let form = this.getForm();
		let selectedRow = $(targetBtn).closest('tr');
		let botId = selectedRow.attr('bot-id');
		let botName = selectedRow.attr('bot-name');

		let replaceParams = { chatbot_name: botName };
		let confirmMsg = app.vtranslate('JS_CHATBOT_INTEGRATION_REMOVE_CHATBOT_CONFIRM', replaceParams);

		app.helper.showConfirmationBox({ message: confirmMsg })
		.then(function () {
			// Delete row
			selectedRow.remove();
			delete self.chatbotInfos[botId];

			// Update status
			form.find('[name="chatbots_updated"]').val('true');
		});
	},

	saveConfig: function (form) {
		app.helper.showProgress();

		// Write chatbots into hidden input before getting form data
		if (this.hasChatbots) {
			form.find('[name="chatbot_infos"]').val(JSON.stringify(this.chatbotInfos));
		}

		// Get form data for saving
		let data = form.deepSerializeFormData();

		let params = {
			module: 'Vtiger',
			parent: 'Settings',
			action: 'SaveChatbotIntegrationConfig',
			mode: 'saveConfig',
			config: data
		}

		app.request.post({ data: params })
		.then((err, res) => {
			app.helper.hideProgress();

			if (err) {					
				app.helper.showErrorNotification({ message: err.message });
				return;
			}

			app.helper.showSuccessNotification({ message: app.vtranslate('JS_SAVE_SETTINGS_SUCCESS_MSG') });
		});
	},

	// Begin common logic
	disconnect: function (providerDisplayName) {
		let self = this;
		let replaceParams = { provider_name: providerDisplayName };
		let confirmMsg = app.vtranslate('JS_CHATBOT_INTEGRATION_DISCONNECT_CONFIRM_MSG', replaceParams);

		app.helper.showConfirmationBox({ message: confirmMsg })
		.then(() => {
			app.helper.showProgress();

			let params = {
				module: 'Vtiger',
				parent: 'Settings',
				action: 'SaveChatbotIntegrationConfig',
				mode: 'disconnect',
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
				window.location.href = self.getConfigListUrl();
			});
		});
	}
});