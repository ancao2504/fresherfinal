/*
    DetailView.js
    Author: Hieu Nguyen
    Date: 2020-05-27
 */

jQuery(function ($) {
    // Implemented by Phu Vo on 2020.06.10
    $('.detailview-content').on('click', '.check-in-manual', function() {
        var registrationRow = $(this).closest('.listViewEntries');

        const replaceParams = {
            'customer_name': registrationRow.find('[data-field-name="customer_name"]').find('.value').text().trim(),
        };

        app.helper.showConfirmationBox({ message: app.vtranslate('CPEventRegistration.JS_CHECK_IN_MANUAL_CONFIRM_MSG', replaceParams) })
        .then(function () {
			var params = {
				module: 'CPEventRegistration',
				action: 'RegistrationAjax',
				mode: 'checkin',
				record: registrationRow.data('id')
            };

            app.helper.showProgress();

			app.request.post({ data: params })
            .then(function (e, res) {
                app.helper.hideProgress();

                // Error
				if (e || !res || !res.success) {
                    app.helper.showErrorNotification({ message: app.vtranslate('CPEventRegistration.JS_CHECK_IN_MANUAL_ERROR_MSG') });
                    return;
				}

                // Success
                app.helper.showSuccessNotification({ message: app.vtranslate('CPEventRegistration.JS_CHECK_IN_MANUAL_SUCCESS_MSG') });
                $('.tab-item.active').trigger('click'); // Refresh the registration list
			});
		});
    });

    $('.detailview-content').on('click', '.cancel-registration', function() {
        var registrationRow = $(this).closest('.listViewEntries');

        const replaceParams = {
            'customer_name': registrationRow.find('[data-field-name="customer_name"]').find('.value').text().trim(),
        };

        app.helper.showConfirmationBox({ message: app.vtranslate('CPEventRegistration.JS_CANCEL_REGISTRATION_CONFIRM_MSG', replaceParams) })
        .then(function () {
			var params = {
				module: 'CPEventRegistration',
				action: 'RegistrationAjax',
				mode: 'cancelRegistration',
				record: registrationRow.data('id')
            };

            app.helper.showProgress();

			app.request.post({ data: params })
            .then(function (e, res) {
                app.helper.hideProgress();

                // Error
				if (e || !res || !res.success) {
                    app.helper.showErrorNotification({ message: app.vtranslate('CPEventRegistration.JS_CANCEL_REGISTRATION_ERROR_MSG') });
                    return;
				}

                // Success
                app.helper.showSuccessNotification({ message: app.vtranslate('CPEventRegistration.JS_CANCEL_REGISTRATION_SUCCESS_MSG') });
                $('.tab-item.active').trigger('click'); // Refresh the registration list
			});
		});
    });
    // End Phu Vo

    // Handle resend QR code
    $('.detailview-content').on('click', '.resend-qr-code', function () {
        var registrationRow = $(this).closest('.listViewEntries');
        var customerName = registrationRow.find('[data-field-name="customer_name"]').find('.value').html();

        if (!customerName) {
            customerName = registrationRow.find('[data-field-name="related_customer"]').find('.value').html();
        }

        const replaceParams = {
            'customer_name': customerName,
        };

        app.helper.showConfirmationBox({ message: app.vtranslate('CPEventRegistration.JS_RESEND_QR_CODE_CONFIRM_MSG', replaceParams) })
        .then(function () {
			var params = {
				module: 'CPEventRegistration',
				action: 'RegistrationAjax',
				mode: 'resendQRCode',
				record: registrationRow.data('id')
            };

            app.helper.showProgress();

			app.request.post({ data: params })
            .then(function (e, res) {
                app.helper.hideProgress();

                // Error
				if (e || !res || !res.success) {
                    app.helper.showErrorNotification({ message: app.vtranslate('CPEventRegistration.JS_RESEND_QR_CODE_ERROR_MSG') });
                    return;
				}

                // Success
                app.helper.showSuccessNotification({ message: app.vtranslate('CPEventRegistration.JS_RESEND_QR_CODE_SUCCESS_MSG') });
                $('.tab-item.active').trigger('click'); // Refresh the registration list
			});
		});
    });

    // Added by Phu Vo on 2020.07.27 to mark as customer not confirmed
    $('.detailview-content').on('click', '.mark-customer-not-confirmed', function () {
        var registrationRow = $(this).closest('.listViewEntries');

        const replaceParams = {
            'customer_name': registrationRow.find('[data-field-name="customer_name"]').find('.value').html(),
        };

        app.helper.showConfirmationBox({ message: app.vtranslate('CPEventRegistration.JS_MARK_AS_CUSTOMER_NOT_CONFIRMED_CONFIRM_MSG', replaceParams) })
        .then(function () {
			var params = {
				module: 'CPEventRegistration',
				action: 'RegistrationAjax',
				mode: 'markCustomerNotConfirmed',
				record: registrationRow.data('id')
            };

            app.helper.showProgress();

			app.request.post({ data: params })
            .then(function (e, res) {
                app.helper.hideProgress();

                // Error
				if (e || !res || !res.success) {
                    app.helper.showErrorNotification({ message: app.vtranslate('CPEventRegistration.JS_MARK_AS_CUSTOMER_NOT_CONFIRMED_ERROR_MSG') });
                    return;
				}

                // Success
                app.helper.showSuccessNotification({ message: app.vtranslate('CPEventRegistration.JS_MARK_AS_CUSTOMER_NOT_CONFIRMED_SUCCESS_MSG') });
                $('.tab-item.active').trigger('click'); // Refresh the registration list
			});
		});
    });
    // End Phu Vo

    // Handle mark as customer confirmed
    $('.detailview-content').on('click', '.mark-customer-confirmed', function () {
        var registrationRow = $(this).closest('.listViewEntries');

        const replaceParams = {
            'customer_name': registrationRow.find('[data-field-name="customer_name"]').find('.value').html(),
        };

        app.helper.showConfirmationBox({ message: app.vtranslate('CPEventRegistration.JS_MARK_AS_CUSTOMER_CONFIRMED_CONFIRM_MSG', replaceParams) })
        .then(function () {
			var params = {
				module: 'CPEventRegistration',
				action: 'RegistrationAjax',
				mode: 'markCustomerConfirmed',
				record: registrationRow.data('id')
            };

            app.helper.showProgress();

			app.request.post({ data: params })
            .then(function (e, res) {
                app.helper.hideProgress();

                // Error
				if (e || !res || !res.success) {
                    app.helper.showErrorNotification({ message: app.vtranslate('CPEventRegistration.JS_MARK_AS_CUSTOMER_CONFIRMED_ERROR_MSG') });
                    return;
				}

                // Success
                app.helper.showSuccessNotification({ message: app.vtranslate('CPEventRegistration.JS_MARK_AS_CUSTOMER_CONFIRMED_SUCCESS_MSG') });
                $('.tab-item.active').trigger('click'); // Refresh the registration list
			});
		});
    });
});