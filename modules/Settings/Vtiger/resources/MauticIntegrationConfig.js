/*
	File: MauticIntegrationConfig.js
	Author:Phuc Lu
	Date: 2019.06.25
	Purpose: Save config for Mautic
*/

// Refactored by Hieu Nguyen on 2021-11-16
CustomView_BaseController_Js('Settings_Vtiger_MauticIntegrationConfig_Js', {}, {
	registerEvents: function() {
		this._super();
		this.registerEventFormInit();
	},
	registerEventFormInit: function() {
		this.initConnectionInfo();
		this.initSettingsForm(0)
	},
	initConnectionInfo: function () {
		let self = this;

		// Connect
		jQuery('#btn-connect').on('click', function () {
			self.showAuthorizeModal();
		});

		// Re-connect
		jQuery('#btn-reconnect').on('click', function () {
			var data = {
				'base_url': $('#connection-info').find('[name="base_url"]').val(),
				'client_id': $('#connection-info').find('[name="client_id"]').val(),
				'client_secret': $('#connection-info').find('[name="client_secret"]').val(),
			};

			self.showAuthorizeModal(data);
		});

		// Disconnect
		jQuery('#btn-disconnect').on('click', function () {
			app.helper.showConfirmationBox({ message: app.vtranslate('JS_MAUTIC_INTEGRATION_CONFIG_DISCONNECT_CONFIRM_MSG') })
			.then(function () {
				self.disconnect();
			});
		});
	},
	showAuthorizeModal: function (data) {
		let modal = $('#authorize-modal').clone(true, true);

		app.helper.showModal(modal, {
			preShowCb: function (modal) {
				const form = modal.find('form[name="authorize-form"]');
				let baseUrlInput = form.find('[name="base_url"]');
				let clientIdInput = form.find('[name="client_id"]');
				let clientSecret = form.find('[name="client_secret"]');

				if (data) {
					baseUrlInput.val(data.base_url);
					clientIdInput.val(data.client_id);
					clientSecret.val(data.client_secret);
				}

				form.vtValidate({
					submitHandler: (form) => {
						var popupUrl = 'index.php?module=Vtiger&parent=Settings&view=ConnectMautic';
						popupUrl += '&base_url=' + baseUrlInput.val().trim();
						popupUrl += '&client_id=' + clientIdInput.val().trim();
						popupUrl += '&client_secret=' + clientSecret.val().trim();

						UIUtils.popupCenter(popupUrl, 'Connect Mautic', 500, 600);
					}
				});
			}
		});
	},
	disconnect: function () {
		app.helper.showProgress();

		var params = {
			module: 'Vtiger',
			parent: 'Settings',
			action: 'SaveMauticIntegrationConfig',
			mode: 'disconnect'
		};

		app.request.post({ data: params })
		.then((err, res) => {
			app.helper.hideProgress();

			if (err) {
				app.helper.showErrorNotification({ message: app.vtranslate('JS_MAUTIC_INTEGRATION_CONFIG_SAVE_ERROR_MSG') });
				return;
			}

			app.helper.showSuccessNotification({ message: app.vtranslate('JS_MAUTIC_INTEGRATION_CONFIG_DISCONNECT_SUCCESS_MSG') });
			location.reload();
		});
	},
	initSettingsForm: function () {
		var self = this;
		var form = jQuery('form[name="settings"]');

		// Init select2 fields
		form.find('#sync_mautic_history_within_days').select2();
		form.find('#sync_mautic_history_when_customer_is_converted').select2();
		form.find('#mapping-fields').find('tr:not(.required) select').select2();
		form.find('#mapping-stages').find('tr:not(.required) select').select2();
		form.find('#mapping-stage-segments').find('tr:not(.required) select').select2();
		
		// Remove error class
		form.find('input').on('focus', function() {
			jQuery(this).removeClass('input-error');
		});

		// Toggle on/off mapping group
		form.find('.toggle-mapping').on('click', '[type="checkbox"]', function(e) {
			var checkbox = jQuery(this);
			var mappingContainer = checkbox.closest('.box').find('.box-body');
			var targetModule = checkbox.val();

			if (checkbox.is(':checked')) {
				mappingContainer.removeClass('hide');

				if (targetModule == 'leads') {
					jQuery('#mapping-stage-leads').addClass('hide');
					jQuery('#block-mapping-stage-leads').addClass('hide');
				}
				
				if (targetModule == 'contacts') {
					jQuery('#mapping-stage-quotes').addClass('hide');
					jQuery('#block-mapping-stage-quotes').addClass('hide');
					
					jQuery('#mapping-stage-potentials').addClass('hide');
					jQuery('#block-mapping-stage-potentials').addClass('hide');
					
					jQuery('#mapping-stage-salesorder').addClass('hide');
					jQuery('#block-mapping-stage-salesorder').addClass('hide');
					
					jQuery('#mapping-stage-salesorder').addClass('hide');
					jQuery('#block-mapping-stage-salesorder').addClass('hide');
					
					jQuery('#mapping-stage-servicecontracts').addClass('hide');
					jQuery('#block-mapping-stage-servicecontracts').addClass('hide');
				}
			}
			else {
				mappingContainer.addClass('hide');

				if (targetModule == 'leads') {
					jQuery('#mapping-stage-leads').removeClass('hide');
					jQuery('#block-mapping-stage-leads').removeClass('hide');
				}
				
				if (targetModule == 'contacts') {
					jQuery('#mapping-stage-quotes').removeClass('hide');
					jQuery('#block-mapping-stage-quotes').removeClass('hide');
					
					jQuery('#mapping-stage-potentials').removeClass('hide');
					jQuery('#block-mapping-stage-potentials').removeClass('hide');
					
					jQuery('#mapping-stage-salesorder').removeClass('hide');
					jQuery('#block-mapping-stage-salesorder').removeClass('hide');
					
					jQuery('#mapping-stage-salesorder').removeClass('hide');
					jQuery('#block-mapping-stage-salesorder').removeClass('hide');
					
					jQuery('#mapping-stage-servicecontracts').removeClass('hide');
					jQuery('#block-mapping-stage-servicecontracts').removeClass('hide');
				}
			}
		});

		// Add a new mapping row
		form.find('.add-mapping').on('click', function() {
			var mappingTableBody = jQuery(this).closest('table').find('tbody');
			var newRow = mappingTableBody.find('tr:first').clone(true, true);
			newRow.removeClass('required');
			newRow.find('span').remove();
			newRow.find('select').val('').removeClass('hide');
			newRow.find('select').attr('data-rule-required', 'true');
			newRow.find('select').select2();
			newRow.find('.remove-mapping').removeClass('hide');

			mappingTableBody.append(newRow);
		});

		// Remove a mapping row
		form.on('click', '.remove-mapping', function() {
			jQuery(this).closest('tr').remove();
		});

		// Handle form submit
		form.vtValidate({
			submitHandler: (form) => {
				form = $(form);
				var valid = true;

				form.find('.mapping-table').find('select').each(function () {
					var select2Label = jQuery(this).prev('.select2-container').find('a.select2-choice');

					if (!jQuery(this).val()) {
						valid = false;
						select2Label.addClass('input-error');
					}
					else {
						select2Label.removeClass('input-error');
					}
				});

				if (!valid) {
					app.helper.showErrorNotification({ message: app.vtranslate('JS_MAUTIC_INTEGRATION_CONFIG_MISSING_REQUIRED_FIELD') });
					return false;
				}

				self.saveConfig(form);
				return;
			}
		});
	},
	saveConfig: function (form) {
		app.helper.showProgress();
		let formData = form.serializeFormData();

		// Collect fields mapping
		form.find('#mapping-fields').find('.mapping-module').each(function () {
			let checkbox = jQuery(this).find('[type="checkbox"]');
			let targetModule = checkbox.val();
			let mappingFields = [];

			if (checkbox.is(':checked')) {
				jQuery(this).find('.mapping-table').find('tbody tr').each(function () {
					let required = jQuery(this).hasClass('required');

					let mapping = {
						crm: jQuery(this).find('select:first').val(),
						mautic: jQuery(this).find('select:last').val(),
						required: required ? 1 : 0
					};

					mappingFields.push(mapping);
				});
			}
			
			formData['mapping_field_' + targetModule] = mappingFields;
		});

		/*// Collect stages mapping
		formData.mapping_stages = [];

		jQuery('.mapping-stage').each(function () {
			let stages = [];

			if (!jQuery(this).hasClass('hide')) {
				jQuery(this).find('.form-group').each(function () {
					let stage = {crm: jQuery(this).find('select:first option:selected').val(), mautic: jQuery(this).find('select:last option:selected').val()};
					stages.push(stage);
				});
			}
			
			formData.mapping_stages.push({ module: jQuery(this).data('module-lower'), stages: stages });
		});

		// Collect stages-segments mapping
		let stagesSegments = [];

		jQuery('#mapping-stage-segment-settings').find('.form-group:not(".hide")').each(function () {
			let stage = jQuery(this).find('select:first option:selected').val();
			let segment = jQuery(this).find('select:last option:selected').val();

			if (stage != '' && segment != '') stagesSegments.push({stage: stage, segment: segment});
		});

		formData.mapping_stages_segments = stagesSegments;*/

		let params = {
			module: 'Vtiger',
			parent: 'Settings',
			action: 'SaveMauticIntegrationConfig',
			mode: 'saveSettings',
			config: formData
		}

		app.request.post({ data: params })
		.then((err, res) => {
			app.helper.hideProgress();

			if (err) {					
				app.helper.showErrorNotification({ message: app.vtranslate('JS_MAUTIC_INTEGRATION_CONFIG_SAVE_ERROR_MSG') });
				return;
			}

			app.helper.showSuccessNotification({ message: app.vtranslate('JS_MAUTIC_INTEGRATION_CONFIG_SAVE_SUCCESS_MSG') });
		});
	}
});