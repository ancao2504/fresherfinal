/*
	LicenseActivate.js
	Author: Hieu Nguyen
	Date: 2021-09-20
	Purpose: handle logic on the UI of License Activate page
*/

jQuery(function ($) {
	$('html').off('click');	// Disable event html click to prevent JS error

	var form = $('#form-activate-license');

	form.find('#btn-submit').on('click', function () {
		$.ajax({
			url: 'entrypoint.php?name=License&mode=activateLicense',
			type: 'POST',
			dataType: 'JSON',
			data: {
				license_code: form.find('#license-code').val().trim()
			},
			success: function (res) {
				if (res && res.success == true) {
					bootbox.alert(app.vtranslate('Vtiger.JS_LICENSE_ACTIVATE_SUCCESS_MSG'), function () {
						location.href = 'entrypoint.php?name=License&mode=showLicense';
					});
				}
			},
			error: function (xhr, ajaxOptions, thrownError) {
				var error = JSON.parse(xhr.responseText);
				bootbox.alert(error.message);
			},
		});

		return false;
	});
});