/*
	File: CallCenterConfig.js
	Author: Phu Vo
	Date: 2019.03.22
	Purpose: System notification ui handler
	Refactored by Vu Mai on 2022-07-19
*/

CustomView_BaseController_Js('Settings_Vtiger_CallCenterConfig_Js', {}, {

	registerEvents: function () {
		this._super();
		this.registerEventFormInit();
	},

	registerEventFormInit: function () {
		let self = this;
		let form = this.getForm();
		let tab = this.getActiveTab();
		let switchButton = form.find('[name="switch_button"]');

		// Init toggle button
		form.find('.bootstrap-switch').bootstrapSwitch();

		// Handle switch button
		switchButton.on('switchChange.bootstrapSwitch', function () {
			let enable = $(this).is(':checked');
			self.toggleConfig(enable);
		});

		// Init form
		if (tab == 'GeneralConfig') {
			this.initGeneralConfigForm(form);
		}
		
		if (tab == 'Connection') {
			if (form.data('mode') == 'ShowList') {
				this.initConnectionForm(form);
			}
			else {
				this.initVendorDetailForm(form);
			}
		}
	},

	getForm: function () {
		return $('form#config');
	},

	getActiveTab: function () {
		return $('#main-tabs-container ul li.active a').attr('data-tab');
	},

	getBaseUrl: function (mode) {
		return 'index.php?module=Vtiger&parent=Settings&view=CallCenterConfig&tab=Connection&mode=' + mode;
	},

	getConfigListUrl: function () {
		return this.getBaseUrl('ShowList');
	},

	toggleConfig: function (enable) {
		let form = this.getForm();
		app.helper.showProgress();
		let params = {
			module: 'Vtiger',
			parent: 'Settings',
			action: 'SaveCallCenterConfig',
			mode: 'toggleConfig',
			enable: enable,
		}

		app.request.post({ data: params })
		.then((err, res) => {
			app.helper.hideProgress();

			if (err) {
				app.helper.showErrorNotification({ message: err.message });
				return;
			}

			if (enable) {
				form.find('#config-container').removeClass('hide');
				form.find('#inactive-config-hint-text').addClass('hide');
				form.find('#config-footer').removeClass('hide');
				app.helper.showSuccessNotification({ message: app.vtranslate('JS_ENABLE_CONFIG_SUCCESS_MSG') });
			}
			else {
				form.find('#config-container').addClass('hide');
				form.find('#inactive-config-hint-text').removeClass('hide');
				form.find('#config-footer').addClass('hide');
				app.helper.showSuccessNotification({ message: app.vtranslate('JS_DISABLE_CONFIG_SUCCESS_MSG') });
			}
		});
	},

	// Begin General Config logic
	// Modified by Vu Mai on 2022-08-01 to update logic validate table outbound hotline
	initGeneralConfigForm: function (form) {
		this.initUsersSelector(form);
		this.initEntitySelector(form);

		// Init dynamic tables
		form.find('#tbl-inbound-call, #tbl-outbound-call').dynamicTable({
			delAction: 'hide',

			// Init select input when add row
			postAddCallback: function (insertedRow) {
				insertedRow.find('select').select2();
			},

			// Handle logic for delete row
			preDelCallback: function (selectedRow) {
				let table = selectedRow.closest('table');

				if (table.attr('id') == 'tbl-outbound-call' && $(table).find('tbody').find('tr').length == 1) {
					app.helper.showErrorNotification({ message: app.vtranslate('JS_CALLCENTER_INTEGRATION_SAVE_CONFIG_HOLINE_EMPTY_ERROR_MSG') });
					return false; 
				}
				else {
					if (selectedRow.closest('tr').find('.hotline').val().trim() != '') {
						app.helper.showConfirmationBox({ message: app.vtranslate('JS_CALLCENTER_INTEGRATION_HOLINE_REMOVE_CONFIRM_MSG') })
						.then(function () {
							selectedRow.closest('tr').remove();
						});
	
						return false;
					}
					else {
						selectedRow.closest('tr').remove();
					}
				}
			}
		});

		if (form.find('#tbl-inbound-call').find('tbody').find('tr').length != 0) {
			form.find('#tbl-inbound-call tbody').find('tr').find('select').select2();
		}

		if (form.find('#tbl-outbound-call').find('tbody').find('tr').length != 0) {
			form.find('#tbl-outbound-call tbody').find('tr').find('select').select2();
		}
		else {
			form.find('#tbl-outbound-call').find('.btnAddRow').click();
		}

		// Init click2call_users_can_use_all_hotlines field
		CustomOwnerField.initCustomOwnerFields($('[name="click2call_users_can_use_all_hotlines"]'));

		form.on('submit', function () {
			form.find('.input-error').removeClass('input-error');	// Reset validate status
			let dynamicTableRows = form.find('#tbl-inbound-call, #tbl-outbound-call').find('tbody').find('tr');

			// Check required input in tbl-inbound-call & tbl-outbound-call
			if (dynamicTableRows.length != 0) {
				if (!dynamicTableRows.find('.hotline, select.outbound-roles').valid()) {
					return false;
				}
			}
		});

		// Register submit form general config
		form.vtValidate({
			submitHandler: function () {
				if (form.find('.input-error').length > 0) {
					return false;
				}

				app.helper.showProgress();

				// Collect data from inboundConfig & outboundConfig
				let inboundConfig = {};

				if (form.find('#tbl-inbound-call').find('tbody').find('tr').length != 0) {
					form.find('#tbl-inbound-call tbody').find('tr').each(function () {
						let hotline = $(this).find('input.hotline').val();
						let role = $(this).find('select.inbound-role').val();
						inboundConfig[hotline] = role;
					});
				}	

				let outboundConfig = {};

				if (form.find('#tbl-outbound-call').find('tbody').find('tr').length != 0) {
					form.find('#tbl-outbound-call tbody').find('tr').each(function () {
						let hotline = $(this).find('input.hotline').val();
						let roles = $(this).find('select.outbound-roles').val();
						outboundConfig[hotline] = roles;
					});
				}	

				let config = form.deepSerializeFormData();
		
				// Collect data from general[external_report_allowed_roles] field and assign to formdata
				config.general.external_report_allowed_roles =  form.find('[name="general[external_report_allowed_roles]"]').val();

				// Process to remove specific user if missed call no main owner aim to group members
				if (config.general.existing_customer_missed_call_alert_no_main_owner !== 'specific_user') {
					delete config.general.missed_call_alert_no_main_owner_specific_user;
				}

				if (config.general.external_report_allowed_roles && !(config.general.external_report_allowed_roles instanceof Array)) {
					config.general.external_report_allowed_roles = [ config.general.external_report_allowed_roles ];
				}

				let params = {
					module: 'Vtiger',
					parent: 'Settings',
					action: 'SaveCallCenterConfig',
					mode: 'saveConfig',
					config: config,
					inbound_config: inboundConfig,
					outbound_config: outboundConfig,
				};

				app.request.post({ data: params }).then((err, res) => {
					app.helper.hideProgress();

					if (err) {
						app.helper.showErrorNotification({ message: err.message });
						return;
					}

					if (res !== true && !res.result) {
						app.helper.showErrorNotification({ message: app.vtranslate('JS_CALL_CENTER_CONFIG_SAVE_SETTINGS_ERROR_MSG') });
						return;
					}

					app.helper.showSuccessNotification({ message: app.vtranslate('JS_CALL_CENTER_CONFIG_SAVE_SETTINGS_SUCCESS_MSG') });
				});

				return;
			}
		});
	},

	initUsersSelector: function (form) {
		dom = form.find('.user-selector-input');

		// It may pass in a jquery list of dom
		dom.each((index, target) => {
			target = $(target); // Alternative for $(this);
			// Init some options
			let multiple = target.data('multiple') ? target.data('multiple') : target.prop('multiple'); // Use this param to control multiple select logic
			let userOnly = target.data('user-only') ? true : false; // Set to false to include group in option list
			let params = {}; // Params to work with jquery select2
			let useType = target.data('use-type') ? true : false; // Set to True to include owner type (Users|Groups) in select value
			let selectedTags = target.data('selectedTags');
			let placeholder = target.data('placeholder') || target.attr('placeholder') || '';

			// Ajax data process method, use specific method to apply with recursive solution (Don't bother it now)
			let resultProcessor = (results) => {
				results = results.map((result, index) => {// May use to peform other logic process, we will refactor it later
					// It may contain sub level
					if (result.children) resultProcessor(result.children);

					// Prety sure that ajax handler will alway return data with owner type at id, so we can process it here to remove with condition
					if (!useType && result.id) result.id = result.id.replace(/Users\:|Groups\:/g, '').trim();
					return result;
				});
			}

			// Init default params
			params = {
				minimumInputLength: _VALIDATION_CONFIG.autocomplete_min_length,    // Refactored by Hieu Nguyen on 2021-01-15
				ajax: {
					type: 'POST',
					dataType: 'json',
					cache: true,
					data: (term, page) => {
						let data = {
							module: 'Vtiger',
							action: 'HandleOwnerFieldAjax',
							mode: 'loadOwnerList',
							assignable_users_only: false, // It get all user|group without privilege
							keyword: term, // String to search
						};

						// Receive only user list or include group list
						if (userOnly) {
							data['user_only'] = true;
						}

						return data;
					},
					results: (data) => {
						let results = data.result || []; // Make sure it will have something to return
						// Process logic hook start from here to modify result output data
						resultProcessor(results);

						return { results };
					},
					transport: (params) => {
						return jQuery.ajax(params);
					},
				},
			};

			// Extra params for multiple select
			if (multiple) {
				params.closeOnSelect = false;
				params.tags = [];
				params.tokenSeparators = [','];

				// Manual format item
				params.formatSelection = (object, container) => {
					if (object.id) {
						let params = object.id.split(':');
						let template =  `<div>${object.text}</div>`;

						// Process item type
						if (useType) {
							container
							.closest('.select2-search-choice')
							.attr('data-type', params[0]);
						}

						return template;
					}

					return object.text;
				}
			}

			// Process selected tag before apply
			if (!useType && selectedTags) resultProcessor(selectedTags);
			if (!multiple && selectedTags) selectedTags = selectedTags[0];

			// Apply select2 with ajax
			target.select2(params);

			// Apply and trigger data
			if (selectedTags) target.select2('data', selectedTags).trigger('change');

			// Process Single select clear
			// [Todo] Refactor to peform this action after select2 was fully applied to void async problem
			if (!multiple) {
				//target.select2('container').closest('.fieldValue').addClass('users-selector-wrapper');

				// Create clear button next to select2 container
				let btnClearUser = target.select2('container').closest('.user-selector-wrapper').find('.btn-clear-user');

				// And then bind it with click event
				btnClearUser.on('click', e => {
					// Replace display value with placeholder text
					target.select2('data', { id: '', text: placeholder }).trigger('change');
				});
			} 

			// Handle user selector input change
			let btnClearUser = target.select2('container').closest('.user-selector-wrapper').find('.btn-clear-user');
			
			target.on('change', function () {
				if ($(this).val() != '') {
					btnClearUser.addClass('active');
				}
				else {
					btnClearUser.removeClass('active');
				}
			}).trigger('change');
		});
	},

	initEntitySelector: function (form) {
		// Event listener for existing customer no main owner missed call
		form.find('[name="general[existing_customer_missed_call_alert_no_main_owner]"]').on('change', function (e) {
			let target = $(this);

			if (target.val() == 'group_members') {
				form.find('.no-main-owner-specific-owner').hide();
			} 
			else if (target.val() == 'specific_user') {
				form.find('.no-main-owner-specific-owner').show();
			}
			else {
				form.find('.no-main-owner-specific-owner').hide();
			}
		}).trigger('change');

		// Event listener for selecting missed call email template
		form.find('.btn-entity-select').on('click', function (e) {
			let element = $(this).closest('.entity-selector-wrapper');
			if (element[0] == null) return;
			let moduleName = element.data('module');
			if (!moduleName) return;

			let params = {
				module: moduleName,
				view: 'Popup',
			};

			Vtiger_Popup_Js.getInstance().showPopup(params, 'Entity.Popup.Selection');

			// Handle email list select event
			app.event.off('Entity.Popup.Selection');

			app.event.on('Entity.Popup.Selection', (e, data) => {
				data = JSON.parse(data);
				let id;
				const input = element.find('.entity-selector-input');
				const display = element.find('.entity-selector-display');
				const entityReview = element.find('.btn-entity-preview');

				// Extract data info
				for (key in data) {
					id = key;
					data = data[id];
					break;
				}

				input.val(id).trigger('change');

				if (display.is('input, selector')) {
					display.val(data.name).trigger('change');
				} 
				else {
					display.html(data.name).trigger('change');
				}

				// Update email template review data
				const href = `index.php?module=EmailTemplates&view=Detail&record=${id}`;
				entityReview.attr('href', href);
			});
		});

		form.find('.btn-entity-deselect').on('click', function (e) {
			let element = $(this).closest('.entity-selector-wrapper');
			let input = element.find('.entity-selector-input');
			let display = element.find('.entity-selector-display');

			input.val('').trigger('change');

			if (display.is('input, selector')) {
				display.val('').trigger('change');
			}	
			else {
				display.html('').trigger('change');
			}
		});

		// Event listener handle show/hide email template preview logic
		form.find('[name="general[missed_call_alert_email_template]"]').on('change', function (e) {
			if ($(this).val() != '') {
				form.find('.btn-entity-deselect, .btn-entity-preview').addClass('active');
			}
			else {
				form.find('.btn-entity-deselect, .btn-entity-preview').removeClass('active');
			}
		}).trigger('change');
	},

	// Begin Vendor List logic
	initConnectionForm: function (form) {
		let self = this;
		let searchInput = form.find('[name="search_input"]');
		let vendorList = form.find('#vendor-list-container').find('#vendor-list');

		// Handle nav tab provider
		form.find('#providers-nav li a').on('click', function () {
			let seletedTab = $(this).data('type');

			if (seletedTab == 'cloud') {
				form.find('#vendor-list-container #vendor-list').removeClass('physical');
				form.find('#vendor-list-container #vendor-list').addClass('cloud');
			} 
			else {
				form.find('#vendor-list-container #vendor-list').removeClass('cloud');
				form.find('#vendor-list-container #vendor-list').addClass('physical');
			}

			searchInput.val('');

			// Wait until the selected tab actually changed by bootstrap
			setTimeout(function () {
				self.handleSearch(form, vendorList, '');
			}, 100)
		});

		// Handle search input
		searchInput.on('input', function () {
			let keyword = searchInput.val().toLowerCase();
			self.handleSearch(form, vendorList, keyword);
		});

		// Handle vendor item click
		vendorList.find('.vendor[connected]').on('click', function (e) {
			if ($(e.target).is('button, a')) return;
			e.preventDefault();

			let provider = $(this).closest('.vendor').data('name');
			let url = self.getConfigDetailUrl(provider);
			window.location.href = url;
		});

		// Handle connect button
		vendorList.find('.btn-connect:not(:disabled)').on('click', function (e) {
			e.preventDefault();

			let provider = $(this).closest('.vendor').data('name');
			let url = self.getConfigDetailUrl(provider);
			window.location.href = url;
		});

		// Handle disconnect button
		vendorList.find('.btn-disconnect').on('click', function () {
			let providerName = $(this).closest('.vendor').attr('data-display-name');
			self.disconnect(providerName);
		});
	},

	// Handle search
	handleSearch: function (form, vendorList, keyword) {
		let seletedTab = form.find('#providers-nav li.active a').data('type');
		vendorList.find('.vendor-container').hide();

		vendorList.find('.vendor-container.' + seletedTab).each(function () {
			if ($(this).find('.vendor').data('displayName').toLowerCase().search(keyword) > -1) {
				$(this).show();
			}
		});
	},

	// Begin Vendor Detail logic
	initVendorDetailForm: function (form) {
		let self = this;

		// Validate form
		form.vtValidate({
			submitHandler: function () {
				// Do saving config
				self.saveConnection(form);
				return false;
			}
		});

		// Handle disconnect button
		$('#btn-disconnect').on('click', function () {
			let providerDisplayName = form.find('[name="provider_name"]').val();
			self.disconnect(providerDisplayName);
		});
	},

	getConfigDetailUrl: function (provider) {
		return this.getBaseUrl('ShowDetail') + '&provider='+ provider;
	},

	saveConnection: function (form) {
		app.helper.showProgress();
		let data = form.deepSerializeFormData();

		let params = {
			module: 'Vtiger',
			parent: 'Settings',
			action: 'SaveCallCenterConfig',
			mode: 'saveConnection',
			config: data
		}

		app.request.post({ data: params })
		.then((err, res) => {
			app.helper.hideProgress();

			if (err) {					
				app.helper.showErrorNotification({ message: err.message });
				return;
			}

			app.helper.showSuccessNotification({ message: app.vtranslate('JS_SAVE_SETTINGS_SUCCESS_MSG') });
		});
	},

	// Begin common logic
	disconnect: function (providerDisplayName) {
		let self = this;
		let replaceParams = { gateway: providerDisplayName };
		let confirmMsg = app.vtranslate('JS_CALLCENTER_INTEGRATION_DISCONNECT_CONFIRM_MSG', replaceParams);

		app.helper.showConfirmationBox({ message: confirmMsg })
		.then(() => {
			app.helper.showProgress();

			let params = {
				module: 'Vtiger',
				parent: 'Settings',
				action: 'SaveCallCenterConfig',
				mode: 'disconnect',
			};
	
			app.request.post({ data: params })
			.then((err, res) => {
				app.helper.hideProgress();
	
				if (err) {
					return app.helper.showErrorNotification({ message: err.message });
				}
	
				if (!res) {
					return app.helper.showErrorNotification({ message: app.vtranslate('JS_VENDOR_INTEGRATION_DISCONNECT_ERROR_MSG') });
				}

				app.helper.showSuccessNotification({ message: app.vtranslate('JS_VENDOR_INTEGRATION_DISCONNECT_SUCCESS_MSG') });
				window.location.href = self.getConfigListUrl();
			});
		});
	}
});