/*
    Edit.js
    Author: Phuc Lu
    Date: 2019.08.16
    Purpose: to handle logic on the UI
*/

jQuery(function($) {

	$('[name="target_asset_account_id"], [name="source_asset_account_id"]').on(Vtiger_Edit_Js.postReferenceSelectionEvent, function(e, data) {
		var data = data[Object.keys(data)[0]];
		data = data[Object.keys(data)[0]];
		data = data['info'];
		$(this).attr('data-currency-id', data['cpassetaccount_currency']);
		$(this).attr('data-current-balance', data['current_balance']);

		if ($('[name="target_asset_account_id"]').val() == $('[name="source_asset_account_id"]').val()) {
			app.helper.showErrorNotification({message: app.vtranslate('JS_DO_NOT_ALLOW_SELECT_THE_SAME_ACCOUNT')});
			$(this).next().next('.clearReferenceSelection').trigger('click');
			return;
		}

		if ($('[name="target_asset_account_id"]').val() != '' && $('[name="source_asset_account_id"]').val() != '' && $('[name="target_asset_account_id"]').attr('data-currency-id') != $('[name="source_asset_account_id"]').attr('data-currency-id')) {
			app.helper.showErrorNotification({message: app.vtranslate('JS_DO_NOT_ALLOW_DIFFERENT_CURRENCY')});
			$(this).next().next('.clearReferenceSelection').trigger('click');
			return;
		}

		if (typeof currencies != 'undefined' && typeof currencies[data['cpassetaccount_currency']] != 'undefined') {
			currencySymbol = currencies[data['cpassetaccount_currency']]['currencysymbol'];
		}

		$('[name="amount"]').prev().html(currencySymbol);
	})

	// Custom code before submit
	$('#EditView').on('submit', function(e, confirm = false) {
        if (!confirm) {
			e.preventDefault();
            app.helper.showConfirmationBox({message: app.vtranslate('JS_CONFIRM_WHEN_SAVING')}).then(
				function() {
					$('#EditView').trigger('submit', true);
				},
				function() {
					setTimeout(function() {
						$('.btn.btn-success.saveButton').attr('disabled', false);
					}, 10);
				}
			);
		}
	});
})