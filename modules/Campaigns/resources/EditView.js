/*
	EditView.js
	Author: Hieu Nguyen
	Date: 2019-08-05
	Purpose: to handle custom logic on Campaign's DetailView
*/

jQuery(function ($) {
	$.validator.setDefaults({
		ignore: ":hidden, :disabled"
	});

	$('[name="campaigntype"]').ready(function () {
		// Display Campaign Purpose when page load
		displayCampaignPurpose();
	});

	// Display Campaign Purpose when Campaign Type change
	$('[name="campaigntype"]').on('change', function () {
		displayCampaignPurpose();
	});
});

function displayCampaignPurpose() {
	var campaignTypeInput = $('[name="campaigntype"]');
	var campaignPurposeInput = $('[name="campaigns_purpose"]');
	var messageCampaign = ['Social', 'SMS Message', 'Zalo Message'];
	var campaignType = campaignTypeInput.val();
	
	// Hide campaign purpose field when campaign is not for sending messages
	if ($.inArray(campaignType, messageCampaign) < 0 && campaignType != 'Telesales') {	// Modified By Vu Mai on 2022-08-12 to show campaign purpose when campaign type is telesales
		$('.campaigns_purpose').css({ 'visibility': 'hidden' });
		campaignPurposeInput.addClass('ignore-validation');
		vtUtils.hideValidationMessage(campaignPurposeInput);
	}
	else {
		$('.campaigns_purpose').css({ 'visibility': 'none' });
		campaignPurposeInput.attr('data-rule-required', 'true');
		campaignPurposeInput.removeClass('ignore-validation');

		// Hide campaign purpose Promotion when campaign type = Zalo Message
		if (campaignType == 'Zalo Message') {
			if (campaignPurposeInput.val() == 'promotion') {
				campaignPurposeInput.val('').trigger('change');
			}

			campaignPurposeInput.find('[value="promotion"]').attr('disabled', true);
			campaignPurposeInput.select2('destroy').select2();
		}
		else {
			campaignPurposeInput.find('[value="promotion"]').attr('disabled', false);
			campaignPurposeInput.select2('destroy').select2();
		}
	}
}