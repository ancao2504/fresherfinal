/*
	File: CallCenterUserConfig.js
	Author: Phu Vo
	Date: 2019.03.22
	Purpose: System notification ui handler
*/

jQuery.validator.addMethod(
	'remote-check-duplicate', 
	function(value, element, params) {
		let target = $(element);

		let param = {
			url: params,
			type: 'POST',
			dataType: 'JSON',
			async: false,
			data: {
				record_id: target.data('record-id'),
				check_field: target.data('check-field'),
				check_value: value,
			}
		};

		return $.validator.methods.remote.call(this, value, element, param);
	}, 
	jQuery.validator.format(app.vtranslate('JS_VALIDATE_DUPLICATE_VALUE'))
);

CustomView_BaseController_Js('Settings_Vtiger_CallCenterUserConfig_Js', {}, {

	getForm: function () {
		if (!this.form) this.form = $('form[name="settings"]');

		return this.form;
	},

	registerAudioInputHandler: function () {
		$('.audio-upload-input').each((index, ui) => {
			const element = $(ui);

			// Register remove button havavior
			element.find('.remove-btn').on('click', () => {
				element.find(':input[name="custom_ringtone"]').val(null).trigger('change');
				element.find(':input[name="ringtone_removed"]').val('1');
				element.find('.replace-warning').hide();
				element.find('.remove-warning').show();
				element.find('.remove-btn').hide();
			});

			// Register input on change behavior
			element.find(':input[name="custom_ringtone"]').on('change', (event) => {
				const fileInput = event.target;
				const fieldName = $(fileInput).attr('name');
				const previewElement = $(`audio[data-for="${fieldName}"]`);
				const fileName = fileInput.files.length ? fileInput.files[0].name : '';

				// Display file name process here
				if (fileInput.files.length) {
					element.find('.uploaded-file-name').html(fileName);
				}
				else {
					element.find('.uploaded-file-name').html('No file chosen');
				}

				// Preview audio file process here
				if (fieldName && previewElement[0]) {
					$(previewElement).stop();

					if (fileInput.files.length > 0) {
						this.convertFileToBase64(fileInput.files[0]).then((res) => {
							previewElement.attr('src', res);
							previewElement.show();
							element.find(':input[name="ringtone_removed"]').val('');
							element.find('.replace-warning').show();
							element.find('.remove-warning').hide();
							element.find('.remove-btn').show();
						});
					}
					else {
						previewElement.attr('src', '');
						previewElement.hide();
						element.find('.replace-warning').hide();
						element.find('.remove-warning').hide();
						element.find('.remove-btn').hide();
					}
				}
			});
		});
	},

	validateRingTone(blob) {
		if (blob && blob.size > 0) {
			const fileSizeInKb = blob.size / 1024;
			if (fileSizeInKb > 1024) {
				const replaceParams = { max_size: '1M' };
				app.helper.showErrorNotification({ message: app.vtranslate('JS_CALLCENTER_USER_CONFIG_UPLOAD_FILE_SIZE_TOO_LARGE_ERROR_MSG', replaceParams)})
				return false;
			}
		}
		return true;
	},

	convertFileToBase64(blob) {
		const reader = new FileReader();

		// Function return promises
		return new Promise((resolve, reject) => {
			reader.onload = function (event) {
				resolve(event.target.result);
			}
			reader.onerror = function (error) {
				reject(error);
			}

			// Trigger read data process
			reader.readAsDataURL(blob);
		});
	},

	registerEvents: function () {
		this._super();

		this.registerAudioInputHandler();

		const self = this;

		// Register submit form
		this.getForm().vtValidate({
			submitHandler: form => {
				const formData = new FormData(form);

				// Invoke validate ringtones
				$(form).find(':input[name="custom_ringtone"]').each((index, input) => {
					if (!self.validateRingTone(formData.get($(input).attr('name')))) return;
				});

				// Need to peform form data procession here
				app.helper.showProgress();

				$.ajax('index.php', {
					cache: false,
					contentType: false,
					processData: false,
					method: 'POST',
					type: 'POST', // For jQuery < 1.9
					data: formData,
				})
				.done((res) => {
					app.helper.hideProgress();

					// handle saving error
					if (res !== true && !res.result) {
						app.helper.showErrorNotification({message: app.vtranslate('JS_CALL_CENTER_CONFIG_SAVE_SETTINGS_ERROR_MSG')});
						return;
					}

					// Process res here
					app.helper.showSuccessNotification({message: app.vtranslate('JS_CALL_CENTER_CONFIG_SAVE_SETTINGS_SUCCESS_MSG')});

					$('.replace-warning').hide();
					$('.remove-warning').hide();
				})
				.fail((jqXHR) => {
					app.helper.hideProgress();
					app.helper.showErrorNotification({message: jqXHR.message});
				});

				return;
			}
		});
	}
});