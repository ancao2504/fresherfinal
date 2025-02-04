/*
	DetailView.js
	Author: Hieu Nguyen
	Date: 2019-08-05
	Purpose: to handle custom logic on Campaign's DetailView
*/

jQuery(function ($) {
	displayMessageType();

	// Handle click event for button Send Article Broadcast
	$('.details').on('click', '.send-broadcast', function () {
		var campaignId = app.getRecordId();
		var articleId = $(this).closest('.listViewEntries').data('id');
		var articleName = $(this).closest('.listViewEntries').find('a[href*="module=CPSocialArticle"]').text().trim();
		
		triggerSocialArticleBroadcastPopup(campaignId, articleId, articleName);
		return false;
	});

	// Added by Phu Vo on 2019.08.14 to remove log relation delete button
	removeRelationDeleteButtons();

	app.event.on('post.relatedListLoad.click', (event, data) => {
		removeRelationDeleteButtons();
	});
	// End remove log relation delete button
});

function displayMessageType() {
	var messageCampaign = ['Social', 'SMS Message', 'Zalo Message'];
	var campaignType = $('.fieldValue.campaigntype').find('.value').data('value');
	
	// // Hide message type field when campaign is not for sending messages
	if ($.inArray(campaignType, messageCampaign) < 0) {
		$('.campaigns_message_type').css({ 'visibility': 'hidden' });
	}
	else {
		$('.campaigns_message_type').css({ 'visibility': 'none' });
	}
}

// Implemented by Phu Vo on 2019-08.14
function removeRelationDeleteButtons() {
	let removeRelationDeleteButtonModules = ['CPSocialArticleLog', 'CPSocialMessageLog', 'CPSocialFeedback'];
	let relatedModuleName = $('.relatedContainer').find('.relatedModuleName').val();

	// Perform remove action
	if (removeRelationDeleteButtonModules.includes(relatedModuleName)) {
		$('.relatedContainer').find('.relationDelete').remove();
	}
}

// Implemented by Hieu Nguyen on 2019-07-23
function triggerComposeSocialMessage(channel) {
	app.helper.showProgress();

	var params = {
		'module': 'CPSocialIntegration',
		'action': 'SocialMessageAjax',
		'mode': 'checkCampaign',
		'campaign_id': app.getRecordId()
	};

	app.request.post({ data: params }).then(function (error, res) {
		app.helper.hideProgress();

		if (error || !res) {
			var message = app.vtranslate('JS_SOCIAL_INTEGRATION_UNKNOWN_ERROR_MSG');
			app.helper.showErrorNotification({ message: message });
			return;
		}

		// Campaign should be active
		if (res.campaign_info.status != 'Active') {
			var message = app.vtranslate('JS_SOCIAL_INTEGRATION_SEND_SOCIAL_MESSAGE_CAMPAIGN_NOT_ACTIVE_ERROR_MSG');
			app.helper.showErrorNotification({ message: message });
			return false;
		}

		// Campaign should not be ended
		if (new Date() > new Date(res.campaign_info.end_date)) {
			var message = app.vtranslate('JS_SOCIAL_INTEGRATION_SEND_SOCIAL_MESSAGE_CAMPAIGN_ENDED_ERROR_MSG');
			app.helper.showErrorNotification({ message: message });
			return false;
		}

		// Campaign should have at least 1 target list that contains customers
		var valid = false;

		for (var i = 0; i < res.target_lists.length; i++) {
			if (res.target_lists[i].customers_count > 0) {
				valid = true;
			}
		}

		if (!valid) {
			app.helper.showErrorNotification({ message: app.vtranslate('CPSocialIntegration.JS_SOCIAL_MESSAGE_EMPTY_TARGET_LIST_ERROR_MSG') });
			return;
		}

		SocialHandler.composeSocialMessage(channel);
	});
}

function triggerSocialArticleBroadcastPopup(campaignId, articleId, articleName) {
	// Load metadata
	app.helper.showProgress();

	let params = {
		module: 'CPSocialIntegration',
		action: 'SocialArticleAjax',
		mode: 'loadMetadata',
		campaign_id: campaignId,
		article_id: articleId
	};

	app.request.post({ data: params }).then(function (error, res) {
		app.helper.hideProgress();

		if (error || res.metadata == null) {
			var message = app.vtranslate('JS_SOCIAL_INTEGRATION_UNKNOWN_ERROR_MSG');
			app.helper.showErrorNotification({ message: message });
			return false;
		}

		let channel = res.metadata.channel;
		let senderName = res.metadata.sender_name;

		// The system should connect to the corresponding sender first
		if (res.metadata.sender_not_connected) {
			var message = '';
			
			if (channel == 'Facebook') {
				message = app.vtranslate('JS_SOCIAL_INTEGRATION_FACEBOOK_PAGE_NOT_CONNECTED_ERROR_MSG', senderName);
			}

			if (channel == 'Zalo') {
				message = app.vtranslate('JS_SOCIAL_INTEGRATION_ZALO_OA_NOT_CONNECTED_ERROR_MSG', senderName);
			}
			
			app.helper.showErrorNotification({ message: message }, { delay: 3000 });
			return false;
		}

		// Campaign should be active
		if (res.metadata.campaign_info.status != 'Active') {
			var message = app.vtranslate('JS_SOCIAL_INTEGRATION_SEND_ARTICLE_BROADCAST_CAMPAIGN_NOT_ACTIVE_ERROR_MSG');
			app.helper.showErrorNotification({ message: message });
			return false;
		}

		// Campaign should not be ended
		if (new Date() > new Date(res.metadata.campaign_info.end_date)) {
			var message = app.vtranslate('JS_SOCIAL_INTEGRATION_SEND_ARTICLE_BROADCAST_CAMPAIGN_ENDED_ERROR_MSG');
			app.helper.showErrorNotification({ message: message });
			return false;
		}

		// Campaign should have at least 1 target list that match the channel
		if (res.metadata.target_lists.length == 0) {
			var message = app.vtranslate('JS_SOCIAL_INTEGRATION_SEND_ARTICLE_BROADCAST_NO_TARGET_LIST_SPECIFIED_ERROR_MSG', channel);
			app.helper.showErrorNotification({ message: message }, { delay: 5000 });
			return false;
		}

		// Show broadcast modal
		showSocialArticleBroadcastPopup(campaignId, articleId, articleName, res.metadata);
	});
}

function showSocialArticleBroadcastPopup(campaignId, articleId, articleName, metadata) {
	var modalTemplate = $('#socialArticleBroadcastModal').clone(true, true);
	modalTemplate.removeClass('hide');

	// Setup inital values
	var form = modalTemplate.find('form');
	form.find('[name="campaign_id"]').val(campaignId);
	form.find('[name="article_id"]').val(articleId);
	form.find('[name="channel"]').val(metadata.channel);

	var callBackFunction = function (data) {
		var form = data.find('form');
		var targetListsContainer = form.find('.targetListsContainer');
		var sendPlanInput = form.find('[name="send_plan"]');
		var scheduleField = form.find('.schedule');

		// Fill article name
		form.find('.articleName').text(articleName);

		// Fill senders
		form.find('[name="sender_id"]').val(metadata.sender_id);
		form.find('.senderName').text(metadata.sender_name);

		// Fill target lists
		Object.keys(metadata.target_lists).forEach(function (key) {
			var item = metadata.target_lists[key];
			var disabled = item.customers_count == 0 ? 'disabled' : '';

			targetListsContainer.append('<label><input type="checkbox" name="target_lists" value="'+ item.id +'" '+ disabled +'/> '+ item.name + '</label><br/>');
		});

		targetListsContainer.find('input:first:not(:disabled)').attr('checked', true); // Set first target list as selected by default
		
		// Show hide the schedule when the send plan change
		sendPlanInput.on('change', function () {
			if ($(this).val() == 'schedule') {
				scheduleField.removeClass('hide');
			}
			else {
				scheduleField.addClass('hide');
			}
		});

		// Bind validator to schedule date field
		var todayDateStr = MomentHelper.getDisplayDate(new Date());
		var campaignEndDateStr = MomentHelper.getDisplayDate(metadata.campaign_info.end_date);
		scheduleField.find('[name="schedule_date"]').attr('data-rule-between-date-range', JSON.stringify([todayDateStr, campaignEndDateStr]));

		// Init modal form
		var controller = Vtiger_Edit_Js.getInstance();
		controller.registerBasicEvents(form);
		vtUtils.applyFieldElementsView(form);
		vtUtils.initDatePickerFields(form);

		// Form validation
		var params = {
			submitHandler: function (form) {
				if ($(form).find('[name="target_lists"]:checked').length == 0) {
					var message = app.vtranslate('JS_SOCIAL_INTEGRATION_SEND_ARTICLE_BROADCAST_MODAL_NO_TARGET_LIST_SELECTED_ERROR_MSG');
					app.helper.showErrorNotification({ message: message });
					return;
				}

				bootbox.confirm({
					message: app.vtranslate('JS_SOCIAL_INTEGRATION_SEND_ARTICLE_BROADCAST_MODAL_SEND_CONFIRM_MSG'),
					callback: function (result) {
						if (result) {
							SocialHandler.broadcastSocialArticle($(form));
						}
					}
				});
				
			}
		};

		form.vtValidate(params);
	};

	var modalParams = {
		cb: callBackFunction
	};

	app.helper.showModal(modalTemplate, modalParams);

	return false;
}

// Added by phuc on 2019.08.08 to display social report
function viewSocialReport() {

}