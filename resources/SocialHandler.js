/*
	SocialHandler.js
	Author: Hieu Nguyen
	Date: 2019-07-16
	Purpose: To handle logic for social integration on the UI
*/

var SocialHandler = {

	// Handle button request update Zalo profile
	triggerRequestShareZaloContactInfo: function() {
		var listParams = '';

		if (app.getViewName() == 'List') {
			var listParams = this.getListViewParams();
			if (!listParams) return;
		}

		app.helper.showProgress();

		let params = {
			module: 'CPSocialIntegration',
			action: 'SyncAjax',
			mode: 'loadZaloOAList'
		};

		app.request.post({ data: params }).then(function (error, res) {
			app.helper.hideProgress();

			if (error || res.status == false) {
				var message = app.vtranslate('CPSocialIntegration.JS_SOCIAL_INTEGRATION_UNKNOWN_ERROR_MSG');
				app.helper.showErrorNotification({ message: message });
				return false;
			}

			if (res.oa_list.length == 0) {
				var message = app.vtranslate('CPSocialIntegration.JS_SOCIAL_INTEGRATION_NO_ZALO_OA_CONNECTED_ERROR_MSG');
				app.helper.showErrorNotification({ message: message });
				return false;
			}

			var title = app.vtranslate('CPSocialIntegration.JS_ZALO_REQUEST_SHARE_CONTACT_INFO_TITLE');
			var hint = app.vtranslate('CPSocialIntegration.JS_ZALO_REQUEST_SHARE_CONTACT_INFO_HINT');

			SocialHandler.showZaloOAListModal(title, hint, res.oa_list, 
				function (modal) {
					// Setup inital value after modal init
					modal.data('listParams', listParams);
				}, 
				function (data, modal) {
					// Handle submit form
					SocialHandler.requestShareZaloContactInfo(data.oa_id, modal);
				}
			);
		});
	},

	// Send request update Zalo profile
	requestShareZaloContactInfo: function (oaId, modal) {
		app.helper.showProgress();
		var currentView = app.getViewName();

		let params = {
			module: 'CPSocialIntegration',
			action: 'SocialMessageAjax',
			mode: 'send',
			target_module: app.getModuleName(),
			current_view: currentView,
			channel: 'Zalo',
			sender_id: oaId,
			message_type: 'request_info'
		};

		if (currentView == 'List') {
			params.list_params = modal.data('listParams');
		}

		if (currentView == 'Detail') {
			params.target_record = app.getRecordId();
		}

		app.request.post({ data: params }).then(function (error, res) {
			app.helper.hideProgress();
			var message = '';

			if (error || res.status == 'ERROR') {
				if (res.error_code == 'follower_id_not_synced_with_oa') { // Modified by Phu Vo on 2019.10.24 to map with response result
					message = app.vtranslate('CPSocialIntegration.JS_ZALO_REQUEST_SHARE_CONTACT_INFO_FOLLOWER_ID_NOT_SYNCED_WITH_ZALO_OA_ERROR_MSG');
				}
				// Error code: https://developers.zalo.me/docs/api/official-account-api/phu-luc/ma-loi-post-735
				else if (res.error_message == 'user_id is not valid' || res.error_code == '-213' || res.error_code == '-20109') { // Modified by Phu Vo on 2019.10.24 to map with response result
					message = app.vtranslate('CPSocialIntegration.JS_ZALO_REQUEST_SHARE_CONTACT_INFO_SOCIAL_ID_NOT_FOLLOWED_TO_ZALO_OA_ERROR_MSG');
				}
				else if (res.error_code == '-32') { // Modified by Phu Vo on 2019.10.24 to map with response result
					message = app.vtranslate('CPSocialIntegration.JS_ZALO_OA_API_OUT_OF_RATE_LIMIT_ERROR_MSG');
				}
				else {
					message = app.vtranslate('CPSocialIntegration.JS_ZALO_REQUEST_SHARE_CONTACT_INFO_UNKNOWN_ERROR_MSG');
				}

				app.helper.showErrorNotification({ message: message }, { delay: 5000 });
				return false;
			}

			if (error || res.status == 'ERROR') {
				message = app.vtranslate('CPSocialIntegration.JS_ZALO_REQUEST_SHARE_CONTACT_INFO_ERROR_MSG');
				app.helper.showErrorNotification({ message: message });
				return false;
			}

			if (res.status == 'SENT') {
				message = app.vtranslate('CPSocialIntegration.JS_ZALO_REQUEST_SHARE_CONTACT_INFO_SUCCESS_MSG');
			}
			else if (res.status == 'QUEUED') {
				message = app.vtranslate('CPSocialIntegration.JS_ZALO_REQUEST_SHARE_CONTACT_INFO_QUEUED_MSG');
			}

			app.helper.showSuccessNotification({ message: message }, { delay: 5000 });
			modal.find('.cancelLink').trigger('click');  // Dismiss modal
		});
	},

	// Show compose social message popup
	composeSocialMessage: function (channel) {
		var socialMessageModal = $('#socialMessageModal').clone(true, true);

		// Display modal title
		var title = app.vtranslate('CPSocialIntegration.JS_SOCIAL_MESSAGE_MODAL_TITLE', channel);
		socialMessageModal.find('.modal-header .pull-left').text(title);

		// Setup inital values
		var form = socialMessageModal.find('form');
		form.find('[name="channel"]').val(channel);
		
		var senderInput = form.find('[name="sender"]');
		senderInput.select2();

		if (app.getViewName() == 'List') {
			var listParams = this.getListViewParams();
			if (!listParams) return;

			form.find('[name="list_params"]').val(JSON.stringify(listParams));
		}

		var callBackFunction = function (container) {
			container.find('#socialMessageModal').removeClass('hide');
			var form = container.find('#socialMessageForm');
			var templateInput = form.find('[name="template"]');
			var variableInput = form.find('[name="variable"]');
			var messageInput = form.find('[name="message"]');

			// Init modal form
			var controller = Vtiger_Edit_Js.getInstance();
			controller.registerBasicEvents(form);
			vtUtils.applyFieldElementsView(form);
			templateInput.select2();
			variableInput.select2();

			// Insert message content from the selected template
			templateInput.on('change', function () {
				var templateId = $(this).val();

				if (SocialHandler.socialMessageTemplates[templateId]) {
					var template = SocialHandler.socialMessageTemplates[templateId];

					if (template.content_type == 'buttons' || template.content_type == 'list') {
						// Message template type = 'buttons' or 'list' does not have message 
						messageInput.val('');
						variableInput.closest('.form-group').hide();
						messageInput.closest('.form-group').hide();
					}
					else {
						messageInput.val(template.content);
						variableInput.closest('.form-group').show();
						messageInput.closest('.form-group').show();
					}
				}
				else if (templateId == '') {
					messageInput.val('');
					variableInput.closest('.form-group').show();
					messageInput.closest('.form-group').show();
				}
			});

			// Insert selected variable into the message
			form.find('#btnInsertVariable').on('click', function () {
				var variable = variableInput.val();
				var cursorPos = messageInput.prop('selectionStart');
				var text = messageInput.val();
				var head = text.substring(0, cursorPos);
				var tail = text.substring(cursorPos, text.length);

				messageInput.val(head + variable + tail);
			});

			// Load metadata
			SocialHandler.loadSocialMessageMetadata(form);

			// Form validation
			var params = {
				submitHandler: function (form) {
					var form = jQuery(form);

					bootbox.confirm({
						message: app.vtranslate('CPSocialIntegration.JS_SOCIAL_MESSAGE_MODAL_SEND_MESSAGE_CONFIRM_MSG'),
						callback: function (result) {
							if (result) {
								SocialHandler.sendSocialMessage(form);
							}
						}
					});
				}
			};

			form.vtValidate(params);

			// Trigger event when send social message modal is loaded
			app.event.trigger('post.sendMessageModal.load', container);
		};

		var modalParams = {
			cb: callBackFunction
		};

		app.helper.showModal(socialMessageModal, modalParams);

		return false;
	},

	getListViewParams: function () {
		var listViewController = window.app.controller();
		var listSelectParams = listViewController.getListSelectAllParams(true);

		if (listSelectParams) {
			var listParams = listViewController.getDefaultParams();
			var params = jQuery.extend(listParams, listSelectParams);
			return params;
		}
		else {
			listViewController.noRecordSelectedAlert();
			return null;
		}
	},

	// Load metadata for social message popup
	loadSocialMessageMetadata: function (form, callback) {
		app.helper.showProgress();

		let params = {
			module: 'CPSocialIntegration',
			action: 'SocialMessageAjax',
			mode: 'loadMetadata',
			target_module: app.getModuleName(),
			channel: form.find('[name="channel"]').val()
		};

		app.request.post({ data: params }).then(function (error, res) {
			app.helper.hideProgress();

			if (error) {
				bootbox.alert(app.vtranslate('CPSocialIntegration.JS_SOCIAL_MESSAGE_MODAL_LOAD_METADATA_ERROR_MSG'));
				return false;
			}

			SocialHandler.socialMessageTemplates = res.metadata.templates;

			var senderInput = form.find('[name="sender"]');
			var templateInput = form.find('[name="template"]');
			var variableInput = form.find('[name="variable"]');

			// Fill senders
			Object.keys(res.metadata.senders).forEach(function (key) {
				var item = res.metadata.senders[key];
				senderInput.append('<option value="'+ item.id +'">'+ item.name +'</option>');
			});

			// Fill templates
			Object.keys(res.metadata.templates).forEach(function (key) {
				var item = res.metadata.templates[key];
				templateInput.append('<option value="'+ item.id +'">'+ item.name +'</option>');
			});

			// Fill variables
			Object.keys(res.metadata.variables).forEach(function (key) {
				var item = res.metadata.variables[key];
				variableInput.append('<option value="'+ item.name +'">'+ item.label +'</option>');
			});
		});
	},

	// Send social message
	sendSocialMessage: function (form) {
		app.helper.showProgress();
		var currentView = app.getViewName();
		var channelName = form.find('[name="channel"]').val();

		let params = {
			module: 'CPSocialIntegration',
			action: 'SocialMessageAjax',
			mode: 'send',
			target_module: app.getModuleName(),
			current_view: currentView,
			channel: form.find('[name="channel"]').val(),
			sender_id: form.find('[name="sender"]').val(),
			message: form.find('[name="message"]').val(),
			template_id: form.find('[name="template"]').val()
		};

		if (currentView == 'List') {
			params.list_params = form.find('[name="list_params"]').val();
		}

		if (currentView == 'Detail') {
			params.target_record = app.getRecordId();
		}

		// Added by Phu Vo on 2020.08.26 to support send Zalo message within Dashboard View
		if (currentView == 'DashBoard') {
			params.target_record = form.find('[name="target_record"]:input').val();
		}
		// End Phu Vo

		app.request.post({ data: params }).then(function (error, res) {
			app.helper.hideProgress();
			var message = '';

			// Zalo error code ref: https://developers.zalo.me/docs/api/official-account-api/phu-luc/ma-loi-post-735
			if (error || res.status == 'ERROR') {
				message = app.vtranslate('CPSocialIntegration.JS_SOCIAL_MESSAGE_MODAL_SEND_MESSAGE_UNKNOWN_ERROR_MSG', channelName);

				// Out of rate limit
				if (res.error_code == '-32') {
					message = app.vtranslate('CPSocialIntegration.JS_ZALO_OA_API_OUT_OF_RATE_LIMIT_ERROR_MSG');
				}
				else {
					// Send to a specific customer
					if (currentView == 'Detail' && $.inArray(app.getModuleName(), ['Leads', 'Contacts', 'CPTarget']) != -1) {
						// Customer's social id not synced with selected OA
						if (res.error_code == 'follower_id_not_synced_with_oa') {
							message = app.vtranslate('CPSocialIntegration.JS_SOCIAL_MESSAGE_MODAL_SEND_MESSAGE_FOLLOWER_ID_NOT_SYNCED_WITH_ZALO_OA_ERROR_MSG');
						}
						// Serious error when sending to a specific customer
						else if (res.error_message == 'user_id is invalid' || res.error_code == '-213' || res.error_code == '-20109') {
							if (res.queued) {
								message = app.vtranslate('CPSocialIntegration.JS_SOCIAL_MESSAGE_MODAL_SEND_MESSAGE_ERROR_AND_QUEUED_MSG', channelName);
							}
							else {
								message = app.vtranslate('CPSocialIntegration.JS_SOCIAL_MESSAGE_MODAL_SEND_MESSAGE_SOCIAL_ID_NOT_FOLLOWED_TO_ZALO_OA_ERROR_MSG');
							}
						}
					}
					// Send to multiple customers
					else {
						if (res.queued) {
							message = app.vtranslate('CPSocialIntegration.JS_SOCIAL_MESSAGE_MODAL_SEND_MASS_MESSAGE_ERROR_AND_QUEUED_MSG', channelName);
						}
						else {
							message = app.vtranslate('CPSocialIntegration.JS_SOCIAL_MESSAGE_MODAL_SEND_MASS_MESSAGE_UNKNOWN_ERROR_MSG', channelName);
						}
					}
				}

				app.helper.showErrorNotification({ message: message }, { delay: 5000 });
				return false;
			}

			if (res.status == 'SENT') {
				message = app.vtranslate('CPSocialIntegration.JS_SOCIAL_MESSAGE_MODAL_SEND_MESSAGE_SUCCESS_MSG', channelName);
			}
			else if (res.status == 'QUEUED') {
				message = app.vtranslate('CPSocialIntegration.JS_SOCIAL_MESSAGE_MODAL_SEND_MESSAGE_QUEUED_MSG', channelName);
			}

			app.helper.showSuccessNotification({ message: message }, { delay: 5000 });
			form.find('.cancelLink').trigger('click');  // Dismiss modal
		});
	},

	broadcastSocialArticle: function(form) {
		app.helper.showProgress();
		var channelName = form.find('[name="channel"]').val();

		let params = form.serializeFormData();
		params.module = 'CPSocialIntegration';
		params.action = 'SocialArticleAjax';
		params.mode = 'send';

		// Quick hack to fix bug serializeFormData can not get multiple values
		params.target_lists = $.map(form.find('[name="target_lists"]:checked'), (item) => { return $(item).val() });

		app.request.post({ data: params }).then(function (error, res) {
			app.helper.hideProgress();
			var message = '';

			if (error || res.status == 'ERROR') {
				// Error code: https://developers.zalo.me/docs/api/official-account-api/phu-luc/ma-loi-post-735
				if (res.data.error_code == '-32') {
					message = app.vtranslate('CPSocialIntegration.JS_ZALO_OA_API_OUT_OF_RATE_LIMIT_ERROR_MSG');
				}
				else if (res.data.error_code == '-211') {
					message = app.vtranslate('CPSocialIntegration.JS_ZALO_ARTICLE_SEND_OUT_OF_QUOTA_ERROR_MSG');
				}
				else {
					message = app.vtranslate('CPSocialIntegration.JS_SOCIAL_ARTICLE_BROADCAST_MODAL_SEND_RESULT_UNKNOWN_ERROR_MSG', channelName);
				}

				app.helper.showErrorNotification({ message: message }, { delay: 5000 });
				return false;
			}

			if (res.status == 'SENT') {
				message = app.vtranslate('CPSocialIntegration.JS_SOCIAL_ARTICLE_BROADCAST_MODAL_SEND_RESULT_SUCCESS_MSG', channelName);
			}
			else if (res.status == 'QUEUED') {
				message = app.vtranslate('CPSocialIntegration.JS_SOCIAL_ARTICLE_BROADCAST_MODAL_SEND_RESULT_QUEUED_MSG', channelName);
			}

			app.helper.showSuccessNotification({ message: message }, { delay: 5000 });
			form.find('.cancelLink').trigger('click');  // Dismiss modal
		});
	},

	// Show Zalo OA List popup for selecting OA
	showZaloOAListModal: function (title, hint, oaList, initCallback, submitCallback) {
		var modalTemplate = $('div.modal-dialog.modal-template-md:first').clone(true, true);

		// Display modal title
		modalTemplate.find('.modal-header .pull-left').text(title);

		// Setup inital values
		var form = modalTemplate.find('form');
		form.data('channel', 'Zalo');

		var oaSelectBox = jQuery('<select name="oa_id" class="form-control select2 full-width" data-rule-required="true"></select>');

		oaList.forEach(function (item) {
			oaSelectBox.append('<option value='+ item.id +'>'+ item.name +'</option>');
		});

		var callBackFunction = function (data) {
			var form = data.find('form');

			// Populate form content
			form.find('.modal-body').append(hint);
			form.find('.modal-body').append('<br/><br/>');
			form.find('.modal-body').append(oaSelectBox);

			// Init modal form
			var controller = Vtiger_Edit_Js.getInstance();
			controller.registerBasicEvents(form);
			vtUtils.applyFieldElementsView(form);

			// Form validation
			var params = {
				submitHandler: function (form) {
					var oaId = $(form).find('[name="oa_id"]').val();
					
					if (typeof submitCallback == 'function') {
						submitCallback({ channel: 'Zalo', oa_id: oaId }, modalTemplate);
					}
				}
			};

			form.vtValidate(params);

			if (typeof initCallback == 'function') {
				initCallback(modalTemplate);
			}
		};

		var modalParams = {
			cb: callBackFunction
		};

		app.helper.showModal(modalTemplate, modalParams);

		return false;
	},

	// Handle button Sync Social Articles
	triggerSyncSocialArticles: function (button, channel) {
		app.helper.showProgress();
		var button = $(button);

		let modeMapping = {
			'Facebook': 'loadFbPageList',
			'Zalo': 'loadZaloOAList'
		};

		let hintMapping = {
			'Facebook': '',
			'Zalo': app.vtranslate('CPSocialIntegration.JS_SYNC_ZALO_ARTICLES_HINT')
		};

		let params = {
			module: 'CPSocialIntegration',
			action: 'SyncAjax',
			mode: modeMapping[channel]
		};

		app.request.post({ data: params }).then(function (error, res) {
			app.helper.hideProgress();

			if (error || res.status == false) {
				var message = app.vtranslate('CPSocialIntegration.JS_SOCIAL_INTEGRATION_UNKNOWN_ERROR_MSG');
				app.helper.showErrorNotification({ message: message });
				return false;
			}

			var title = button.text().trim();
			var hint = hintMapping[channel];

			if (channel == 'Facebook') {
				// TODO
				return;
			}

			if (channel == 'Zalo') {
				if (res.oa_list.length == 0) {
					var message = app.vtranslate('CPSocialIntegration.JS_SOCIAL_INTEGRATION_NO_ZALO_OA_CONNECTED_ERROR_MSG');
					app.helper.showErrorNotification({ message: message });
					return false;
				}

				SocialHandler.showZaloOAListModal(title, hint, res.oa_list, null, function (data, model) {
					var oaName = model.find('[name="oa_id"]').find('option:selected').text();

					SocialHandler.syncSocialArticles(data.channel, data.oa_id, oaName);
				});
			}
		});
	},

	// Sync social article from social channels into CRM
	syncSocialArticles: function (channel, oaId, oaName) {
		app.helper.showProgress();

		let params = {
			module: 'CPSocialIntegration',
			action: 'SyncAjax',
			mode: 'syncSocialArticles',
			channel: channel,
			oa_id: oaId,
			oa_name: oaName
		};

		app.request.post({ data: params }).then(function (error, res) {
			app.helper.hideProgress();

			if (error || !res || res.status == false) {
				var message = app.vtranslate('CPSocialIntegration.JS_SYNC_SOCIAL_ARTICLES_ERROR_MSG');
				app.helper.showErrorNotification({ message: message });
				return false;
			}
	
			var message = app.vtranslate('CPSocialIntegration.JS_SYNC_SOCIAL_ARTICLES_SUCCESS_MSG');
			app.helper.showSuccessNotification({ message: message });
		});
	},

	// Handle button Sync Products To Zalo Shop
	triggerSyncProductsToZalo: function(button) {
		app.helper.showProgress();
		var button = $(button);

		let params = {
			module: 'CPSocialIntegration',
			action: 'SyncAjax',
			mode: 'loadZaloOAList',
			shop_only: true
		};

		app.request.post({ data: params }).then(function (error, res) {
			app.helper.hideProgress();

			if (error || res.status == false) {
				var message = app.vtranslate('CPSocialIntegration.JS_SOCIAL_INTEGRATION_UNKNOWN_ERROR_MSG');
				app.helper.showErrorNotification({ message: message });
				return false;
			}

			var title = button.text().trim();
			var hint = app.vtranslate('CPSocialIntegration.JS_SYNC_PRODUCTS_TO_ZALO_SHOP_HINT');

			if (res.oa_list.length == 0) {
				var message = app.vtranslate('CPSocialIntegration.JS_SOCIAL_INTEGRATION_NO_ZALO_SHOP_OA_CONNECTED_ERROR_MSG');
				app.helper.showErrorNotification({ message: message });
				return false;
			}

			SocialHandler.showZaloOAListModal(title, hint, res.oa_list, 
				function (modal) {
					// Insert selected products count
					var listParams = SocialHandler.getListViewParams();
					var translatedModuleName = $('.module-title').text().trim();
					var selectedProductsCount = (listParams.selected_ids == 'all') ? $('#totalRecordsCount').text() : JSON.parse(listParams.selected_ids).length;
					var htmlSelectedProducstCountLabel = app.vtranslate('CPSocialIntegration.JS_SYNC_PRODUCTS_TO_ZALO_SHOP_SELECTED_PRODUCTS_COUNT');

					var htmlSelectedProductsCount = `<div style="margin-top: 10px">${htmlSelectedProducstCountLabel} <strong>${selectedProductsCount} ${translatedModuleName}</strong></div>`;
					modal.find('.modal-body').append(htmlSelectedProductsCount);
					
					// Insert selection options
					var htmlOptionCreateNewAndUpdateLabel = app.vtranslate('CPSocialIntegration.JS_SYNC_PRODUCTS_TO_ZALO_SHOP_CREATE_NEW_AND_UPDATE_EXISTING_PRODUCTS');
					var htmlOptionCreateNewOnlyLabel = app.vtranslate('CPSocialIntegration.JS_SYNC_PRODUCTS_TO_ZALO_SHOP_CREATE_NEW_PRODUCTS_ONLY');

					var htmlOptions = `<div style="margin-top: 10px">
							<label><input type="radio" name="sync_option" value="create_and_update" checked/> ${htmlOptionCreateNewAndUpdateLabel}</label>
							&nbsp;&nbsp;&nbsp;
							<label><input type="radio" name="sync_option" value="create_only"/> ${htmlOptionCreateNewOnlyLabel}</label>
						</div>
					`;
					
					modal.find('.modal-body').append(htmlOptions);
				}, 
				function (data, modal) {
					var listParams = SocialHandler.getListViewParams();
					var syncOption = modal.find('[name="sync_option"]:checked').val();

					SocialHandler.syncProductsToZaloShop(data.oa_id, listParams, syncOption);
				}
			);
		});
	},

	// Sync products from CRM to Zalo Shop
	syncProductsToZaloShop: function (oaId, listParams, syncOption) {
		app.helper.showProgress();

		let params = {
			module: 'CPSocialIntegration',
			action: 'SyncAjax',
			mode: 'syncProductsToZaloShop',
			oa_id: oaId,
			list_params: listParams,
			sync_option: syncOption
		};

		app.request.post({ data: params }).then(function (error, res) {
			app.helper.hideProgress();

			if (error || !res || !res.status) {
				var message = app.vtranslate('CPSocialIntegration.JS_SOCIAL_INTEGRATION_UNKNOWN_ERROR_MSG');
				app.helper.showErrorNotification({ message: message });
				return false;
			}

			if (res.status.failed && res.status.failed.length > 0) {
				var message = app.vtranslate('CPSocialIntegration.JS_SYNC_PRODUCTS_TO_ZALO_SHOP_ERROR_MSG', res.status.failed.join(', '));
				bootbox.alert(message);
				return false;
			}
	
			var message = app.vtranslate('CPSocialIntegration.JS_SYNC_PRODUCTS_TO_ZALO_SHOP_SUCCESS_MSG');
			app.helper.showSuccessNotification({ message: message });
		});
	}
}