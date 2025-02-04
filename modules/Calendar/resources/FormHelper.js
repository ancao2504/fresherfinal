/*
	FormHelper.js
	Author: Hieu Nguyen
	Date: 2022-01-06
	Purpose: to handle common logic in the UI for both EditView and QuickCreate
*/

jQuery(function ($) {
	// Fill customer address into activity location field
	function relatedCustomerChangedHandler (e, res) {
		let form = $(this).closest('form');
		let selectedType = $(this).closest('.fieldValue').find('[name="popupReferenceModule"]').val();
		let selectedId = $(this).val();

		if (selectedId != '' && (selectedType == 'Accounts' || selectedType == 'Contacts' || selectedType == 'Leads')) {
			// Get data from selected customer
			let editViewController = Vtiger_Edit_Js.getInstance();
			let params = { 'source_module': selectedType, 'record': selectedId };
			
			editViewController.getRecordDetails(params)
			.then(function (res) {
				let address = '';

				if (selectedType == 'Accounts') {
					address = res.data.bill_street;
				}

				if (selectedType == 'Contacts') {
					address = res.data.mailingstreet;

					// Auto fill related account
					if (res.data.account_id > 0) {
						let params = { 'source_module': 'Accounts', 'record': res.data.account_id };
				
						editViewController.getRecordDetails(params)
						.then(function (res) {
							if (res.data) {
								form.find('[name="related_account"]').val(res.data.id);
								form.find('[name="related_account_display"]').val(res.data.accountname).attr('readonly', true);
								form.find('[name="related_account"]').closest('.referencefield-wrapper').find('.clearReferenceSelection').removeClass('hide');
							}
						});
					}
					else {
						form.find('[name="related_account"]').val('');
						form.find('[name="related_account_display"]').val('').attr('readonly', false);
						form.find('[name="related_account"]').closest('.referencefield-wrapper').find('.clearReferenceSelection').addClass('hide');
					}
				}
				
				if (selectedType == 'Leads') {
					address = res.data.lane;
				}

				if (address.trim() != '') {
					// Auto fill
					if (_CALENDAR_USER_SETTINGS.auto_fill_customer_address_into_activity_location === '1') {
						form.find('[name="location"]').val(address);
					}
					// Ask to confirm
					else {
						let message = app.vtranslate('Calendar.JS_FILL_CUSTOMER_ADDRESS_INTO_EVENT_LOCATION_CONFIRM_MSG');
					
						if (form.find('[name="module"]').val() == 'Calendar') {
							message = app.vtranslate('Calendar.JS_FILL_CUSTOMER_ADDRESS_INTO_TASK_LOCATION_CONFIRM_MSG');
						}

						app.helper.showConfirmationBox({ message: message })
						.then(function (e) {
							form.find('[name="location"]').val(address);
						});
					}
				}
			});
		}
	}

	$('.fieldValue.related_account').find('[name="related_account"]').on('Vtiger.PostReference.Selection Vtiger.PostReference.QuickCreateSave', relatedCustomerChangedHandler);
	$('.fieldValue.contact_id').find('[name="contact_id"]').on('Vtiger.PostReference.Selection Vtiger.PostReference.QuickCreateSave', relatedCustomerChangedHandler);
	$('.fieldValue.related_lead').find('[name="related_lead"]').on('Vtiger.PostReference.Selection Vtiger.PostReference.QuickCreateSave', relatedCustomerChangedHandler);

	// Fill relate contact into contact invitee list in Event edit form
	if ($('[name="contact_invitees"]')[0] != null) {
		$('.fieldValue.contact_id').find('[name="contact_id"]').on('Vtiger.PostReference.Selection Vtiger.PostReference.QuickCreateSave', function (e, res) {
			let selectedId = $(this).val();

			if (selectedId != '') {
				app.helper.showConfirmationBox({ message: app.vtranslate('Calendar.JS_FILL_SELECTED_CONTACT_AS_INVITEE_CONFIRM_MSG') })
				.then(function (e) {
					let selectedContactName = $('.fieldValue.contact_id').find('[name="contact_id_display"]').val();
					let contactInviteesInput = $('[name="contact_invitees"]');
					let currentContactInvitees = contactInviteesInput.select2('data');

					contactInviteesInput.select2('data', currentContactInvitees.concat({ id: selectedId, text: selectedContactName }));
					$(document).scrollTop($(document).height());    // Scroll down to show the changes
				});
			}
		});
	}
});