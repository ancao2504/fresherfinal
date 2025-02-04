/*
	LicenseWarning.js
	Author: Hieu Nguyen
	Date: 2022-10-21
	Purpose: to control displaying the License warning from JS
*/

jQuery(function ($) {
	let wrapper = $('#license-warning');
	let btnClose = wrapper.find('#btn-close-license-warning');

	// Handle onload event
	wrapper.ready(function () {
		let statusJsonStr = localStorage.getItem('licenseWarningStatus');
		
		// Always show the warning when no status is saved
		if (!statusJsonStr) {
			toggleLicenseWarning('show');
			return;
		}

		let statusInfo = JSON.parse(statusJsonStr);

		// Today is the same day with saved status
		if (statusInfo.date == new Date().toLocaleDateString()) {
			// Hide warning if the status is hide
			if (statusInfo.status == 'hide') {
				toggleLicenseWarning('hide');
			}
			// Othewise, show the warning back
			else {
				toggleLicenseWarning('show');
			}
		}
		// Today is the next day
		else {
			// Show the warning as normal
			toggleLicenseWarning('show');
		}
	});

	// Handle button close warning
	btnClose.on('click', function () {
		let data = { status: 'hide', date: new Date().toLocaleDateString() };	// Hide warning for 1 day, will display again in next day
		localStorage.setItem('licenseWarningStatus', JSON.stringify(data));
		toggleLicenseWarning('hide');
	});

	// Util function to show or hide the warning
	function toggleLicenseWarning(status = 'show') {
		if (status == 'show') {
			$('body').attr('data-has-header-warning', 'true');
		}
		else if (status == 'hide') {
			$('body').removeAttr('data-has-header-warning');
		}
	}
});