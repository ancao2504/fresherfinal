/*
	ConfigEditor.js
	Author: Hieu Nguyen
	Date: 2022-06-13
	Purpose: handle logic on the UI for Config Editor
*/

// Inherited from ConfigEditorDetail.js
Vtiger.Class('Settings_Vtiger_ConfigEditor_Js', {}, {
	init: function () {
		this.addComponents();
	},
	addComponents: function () {
		this.addModuleSpecificComponent('Index', app.getModuleName, app.getParentModuleName());
	},
	registerEvents: function () {
		this.registerEventFormInit();
	},
	registerEventFormInit: function () {
		this.initSettingsForm();
	},
	initSettingsForm: function () {
		let self = this;

		// Init form
		let form = jQuery('#ConfigEditorForm');

		let params = {
			submitHandler: function (form) {
				form = jQuery(form);

				self.saveConfigEditor(form)
				.then(function (data) {
					if (data) {
						let message = app.vtranslate('JS_SAVE_SETTINGS_SUCCESS_MSG');
						app.helper.showSuccessNotification({ 'message': message });
					}
					else {
						let message = app.vtranslate('JS_SAVE_SETTINGS_ERROR_MSG');
						app.helper.showErrorNotification({ 'message': message });
					}
				});
			}
		};

		form.vtValidate(params);

		form.on('submit', function (e) {
			e.preventDefault();
			return false;
		});

		vtUtils.enableTooltips();

		// Init custom picklist fields
		form.find('[data-type="custom_picklist"]').each(function () {
			let element = $(this);

			// Init select 2 with no search
			element.select2({
				minimumResultsForSearch: -1,
				dropdownCssClass : 'no-search'
			});

			// Handle adding new option
			element.prev('.select2-container').find('.select2-input').on('keydown', function (e) {
				if (e.which == 13) {
					let value = $(this).val().trim();
					
					if (value) {
						let existingOption = element.find('opion[value="'+ value +'"]');

						// Mark selected option
						if (existingOption[0] != null) {
							existingOption.attr('selected', true);
						}
						// Insert new option
						else {
							element.append('<option value="'+ value +'" selected>'+ value +'</option>');
							element.trigger('change');
						}

						$(this).val('');
					}
				}
			});
		});
	},
	saveConfigEditor: function (form) {
		let aDeferred = jQuery.Deferred();
		let data = form.serializeFormData();
		let updatedFields = {};

		jQuery.each(data, function (key, value) {
			updatedFields[key] = value;
		});

		let params = {
			'module': app.getModuleName(),
			'parent': app.getParentModuleName(),
			'action': 'ConfigEditorSaveAjax',
			'updatedFields': JSON.stringify(updatedFields)
		};

		app.request.post({ 'data': params })
		.then(function (err, data) {
			if (err) {
				aDeferred.reject();
			}
			else {
				aDeferred.resolve(data);
			}
		});
		
		return aDeferred.promise();
	},
});