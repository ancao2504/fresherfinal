/*
	VTZaloOAMessageTask.js
	Author: Hieu Nguyen
	Date: 2021-10-28
	Purpose: handle logic on the UI for VTZaloOAMessageTask workflow task settings
*/

jQuery(function ($) {
	let formModelContent = $('.editTaskBody');
	let senderIdInput = formModelContent.find('select[name="sender_id"]');
	let sendFromAllOAInput = formModelContent.find('#send_from_all_oa');
	let textToSendInput = formModelContent.find('[name="text_to_send"]');

	// Tick send from all OAs
	sendFromAllOAInput.on('change', function () {
		if ($(this).is(':checked')) {
			senderIdInput.attr('disabled', true);
			removeRequiredRule(senderIdInput);
		}
		else {
			senderIdInput.attr('disabled', false);
			senderIdInput.attr('data-rule-required', 'true');
		}
	});

	// Insert variable
	formModelContent.find('#btnInsertVariable').on('click', function () {
		var variable = formModelContent.find('#variable').val();
		if (variable == '') return;

		UIUtils.insertAtCursor(variable, textToSendInput);
	});

	function removeRequiredRule(inputElement) {
		vtUtils.hideValidationMessage(inputElement);
		inputElement.removeAttr('data-rule-required').removeClass('input-error');
		inputElement.prev('.select2-container').find('.input-error').removeClass('input-error');
	}
});