/*
	SMSAndOTTMessagePopup.js
	Author: Hieu Nguyen
	Date: 2020-11-13
	Purpose: to handle logic for SMS and OTT message popup
*/

// Implemented by Hieu Nguyen on 2020-11-13 to send SMS and OTT message
function triggerComposeSMSAndOTTMessage(channel, targetButton) {
	// Load metadata
	app.helper.showProgress();
	var campaignId = app.getRecordId();

	let params = {
		module: 'Campaigns',
		action: 'CampaignAjax',
		mode: 'loadMetadata',
		campaign_id: campaignId,
		channel: channel
	};

	app.request.post({ data: params }).then(function (error, res) {
		app.helper.hideProgress();

		if (error || res.metadata == null) {
			var message = app.vtranslate('Campaigns.JS_SEND_SMS_AND_OTT_MESSAGE_UNKNOWN_ERROR_MSG');
			app.helper.showErrorNotification({ message: message });
			return false;
		}

		// Campaign should be active
		if (res.metadata.campaign_info.status != 'Active') {
			var message = app.vtranslate('Campaigns.JS_SEND_SMS_AND_OTT_MESSAGE_CAMPAIGN_NOT_ACTIVE_ERROR_MSG', { 'channel': channel });
			app.helper.showErrorNotification({ message: message });
			return false;
		}

		// Campaign should not be ended
		if (new Date() > new Date(res.metadata.campaign_info.end_date)) {
			var message = app.vtranslate('Campaigns.JS_SEND_SMS_AND_OTT_MESSAGE_CAMPAIGN_ENDED_ERROR_MSG', { 'channel': channel });
			app.helper.showErrorNotification({ message: message });
			return false;
		}

		// Campaign should have at least 1 target list that match the channel
		if (res.metadata.target_lists.length == 0) {
			var message = app.vtranslate('Campaigns.JS_SEND_SMS_AND_OTT_MESSAGE_NO_TARGET_LIST_SPECIFIED_ERROR_MSG');
			app.helper.showErrorNotification({ message: message }, { delay: 5000 });
			return false;
		}

		// Show broadcast modal
		var popupTitle = $(targetButton).text().trim();
		showSMSAndOTTMessagePopup(popupTitle, campaignId, channel, res.metadata);
	});
}

// Implemented by Hieu Nguyen on 2020-11-13 to show compose SMS and OTT message popup
function showSMSAndOTTMessagePopup(popupTitle, campaignId, channel, metadata) {
	var modalTemplate = $('#smsAndOTTMessageModal').clone(true, true);
	modalTemplate.removeClass('hide');

	// Set title
	modalTemplate.find('.modal-header .pull-left').text(popupTitle);

	// Setup inital values
	var form = modalTemplate.find('form');
	form.find('[name="campaign_id"]').val(campaignId);
	form.find('[name="channel"]').val(channel);

	// Setup modal size
	if (metadata.campaign_info.purpose == 'promotion' && !metadata.promotion_api_supported) {
		modalTemplate.addClass('modal-lg');
	}

	var callBackFunction = function (data) {
		var form = data.find('form');
		var phoneFieldsInput = form.find('[name="phone_fields"]');
		var templateInput = form.find('[name="template"]');
		var messageInput = form.find('[name="message"]');
		var targetListsContainer = form.find('.targetListsContainer');
		var sendPlanInput = form.find('[name="send_plan"]');
		var scheduleField = form.find('.schedule');
		var emailToInput = form.find('[name="email_to"]');
		var emailTemplateInput = form.find('[name="email_template"]');

		// Init select2
		phoneFieldsInput.select2().addClass('select2');
		templateInput.select2().addClass('select2');
		emailToInput.select2().addClass('select2');
		emailTemplateInput.select2().addClass('select2');

		// Fill templates
		Object.keys(metadata.templates).forEach(function (key) {
			var item = metadata.templates[key];
			templateInput.append('<option value="'+ item.id +'"> '+ item.name + '</option>');
			templateInput.find('option:last').data('info', item);
		});

		// Handle event select template
		templateInput.on('change', function () {
			if ($(this).val() == '') {
				messageInput.val('');
			}
			else {
				var templateInfo = $(this).find('option:selected').data('info');
				messageInput.val(templateInfo.message);
			}

			messageInput.trigger('change');
		});

		// Fill target lists
		Object.keys(metadata.target_lists).forEach(function (key) {
			var item = metadata.target_lists[key];
			var disabled = item.customers_count == 0 ? 'disabled' : '';

			targetListsContainer.append('<label><input type="checkbox" name="target_lists" value="'+ item.id +'" '+ disabled +'/> '+ item.name + ' ('+ item.customers_count +')' + '</label><br/>');
		});

		targetListsContainer.find('input:not(:disabled)').attr('checked', true); // Modified by Phu Vo to select all Marketing List by default
		
		// Show hide the schedule when the send plan change
		sendPlanInput.on('change', function () {
			if ($(this).val() == 'schedule') {
				scheduleField.removeClass('hide');
			}
			else {
				scheduleField.addClass('hide');
				vtUtils.hideValidationMessage(scheduleField.find('[name="schedule_date"]'));
				vtUtils.hideValidationMessage(scheduleField.find('[name="schedule_time"]'));
			}
		});

		// Bind validator to schedule date field
		var todayDateStr = MomentHelper.getDisplayDate(new Date());
		var campaignEndDateStr = MomentHelper.getDisplayDate(metadata.campaign_info.end_date);
		scheduleField.find('[name="schedule_date"]').attr('data-rule-between-date-range', JSON.stringify([todayDateStr, campaignEndDateStr]));

		// SMS Promotion request email form
		if (metadata.campaign_info.purpose == 'promotion' && !metadata.promotion_api_supported) {
			messageInput.attr('readonly', false);   // Allow to edit promotion message
			form.find('.row.send_plan').addClass('hide');
			form.find('.row.email').removeClass('hide');
			var emailSubjectInput = form.find('[name="email_subject"]');
			var emailContentInput = form.find('[name="email_content"]');

			// Fill partner contacts
			Object.keys(metadata.partner_contacts).forEach(function (key) {
				var item = metadata.partner_contacts[key];
				emailToInput.append('<option value="'+ item.id +'"> '+ item.name + ' ('+ item.email +')' + '</option>');
				emailToInput.find('option:last').data('info', item);
			});

			if (metadata.email_templates) {
				// Email templates
				Object.keys(metadata.email_templates).forEach(function (key) {
					var item = metadata.email_templates[key];
					emailTemplateInput.append('<option value="'+ item.id +'"> '+ item.name + '</option>');
					emailTemplateInput.find('option:last').data('info', item);
				});

				// Fill subject and body from selected email template
				emailTemplateInput.on('change', function () {
					if ($(this).val() == '') return;
					var templateInfo = $(this).find('option:selected').data('info');
					emailSubjectInput.val(templateInfo.subject).trigger('change');
					CKEDITOR.instances.email_content.insertHtml(templateInfo.body);
					emailContentInput.trigger('change');
				});
			}

			// Init CKEDITOR
			var ckeConfig = {
				height: '100px',
				toolbar: [
					{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup','align','list', 'indent','colors' ,'links'], items: [ 'Bold', 'Italic', 'Underline', '-','TextColor', 'BGColor' , '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', '-', 'NumberedList', 'BulletedList', '-', 'Link', 'Unlink','Image', '-', 'RemoveFormat'] },
					{ name: 'styles', items: ['Font', 'FontSize'] }
				]
			};

			var ckeInstance = new Vtiger_CkEditor_Js();
			ckeInstance.loadCkEditor(emailContentInput.attr('id', 'email_content'), ckeConfig);
		}

		// Init modal form
		var controller = Vtiger_Edit_Js.getInstance();
		controller.registerBasicEvents(form);
		vtUtils.applyFieldElementsView(form);
		vtUtils.initDatePickerFields(form);

		// Form validation
		var params = {
			submitHandler: function (form) {
				form = $(form);

				if (form.find('[name="target_lists"]:checked').length == 0) {
					var message = app.vtranslate('Campaigns.JS_SEND_SMS_AND_OTT_MESSAGE_NO_TARGET_LIST_SELECTED_ERROR_MSG');
					app.helper.showErrorNotification({ message: message });
					return;
				}

				bootbox.confirm({
					message: app.vtranslate('Campaigns.JS_SEND_SMS_AND_OTT_MESSAGE_SEND_CONFIRM_MSG'),
					callback: function (result) {
						if (result) {
							sendSMSAndOTTMessage(form);
						}
					}
				});
			}
		};

		form.vtValidate(params);
	};

	var modalParams = {
		backdrop: 'static',
		keyboard: false,
		cb: callBackFunction
	};

	app.helper.showModal(modalTemplate, modalParams);
	return false;
}

// Implemented by Hieu Nguyen on 2020-11-13 to send SMS and OTT message
function sendSMSAndOTTMessage(form) {
	app.helper.showProgress();
	var channel = form.find('[name="channel"]').val();
	let params = form.serializeFormData();

	// Convert selected phone fields to array
	if (typeof params.phone_fields == 'string') {
		params.phone_fields = [params.phone_fields];
	}

	// Get message from selected template
	params.message = form.find('[name="message"]').val();

	// Quick hack to fix bug serializeFormData can not get multiple values
	params.target_lists = $.map(form.find('[name="target_lists"]:checked'), (item) => { return $(item).val() });

	// Get selected telco contact
	if (params.email_to) {
		params.email_to = $.map(form.find('[name="email_to"]').find('option:selected'), (item) => { return $(item).data('info') });
	}

	app.request.post({ data: params }).then(function (error, res) {
		app.helper.hideProgress();
		var message = '';

		if (error || !res || !res.success) {
			if (params.email_to) { // Modified by Phu Vo on 2020.12.04 to fix email_to undefined cause wrong logic flow
				message = app.vtranslate('Campaigns.JS_SEND_SMS_AND_OTT_MESSAGE_SEND_EMAIL_ERROR_MSG', { 'channel': channel });
			}
			else {
				message = app.vtranslate('Campaigns.JS_SEND_SMS_AND_OTT_MESSAGE_SEND_ERROR_MSG', { 'channel': channel });
			}
			
			app.helper.showErrorNotification({ message: message }, { delay: 5000 });
			return false;
		}

		if (params.email_to) { // Modified by Phu Vo on 2020.12.04 to fix email_to undefined cause wrong logic flow
			message = app.vtranslate('Campaigns.JS_SEND_SMS_AND_OTT_MESSAGE_SEND_EMAIL_SUCCESS_MSG', { 'channel': channel });
		}
		else {
			message = app.vtranslate('Campaigns.JS_SEND_SMS_AND_OTT_MESSAGE_QUEUED_SUCCESS_MSG', { 'channel': channel });
		}

		app.helper.showSuccessNotification({ message: message }, { delay: 5000 });
		form.find('.cancelLink').trigger('click');  // Dismiss modal
	});
}