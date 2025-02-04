/*
	File: NewTelesalesCampaignForm.js
	Author: Hieu Nguyen
	Date: 2022-10-24
	Purpose: handle logic on the UI for Telesales Campaign's new record form
*/

jQuery(function ($) {
	let form = $('form#EditView');

	form.find('[name="campaigntype"]').ready(function () {
		// Don't allow to switch to other Campaign Type and vice versa
		if (form.find('[name="campaigntype"]').val() == 'Telesales') {
			form.find('[name="campaigntype"]').attr('readonly', true);
			initTelesalesCampaignForm(form);
			initFooterButtons(form);
		}
		else {
			form.find('[name="campaigntype"]').find('option[value="Telesales"]').remove();
		}
	});

	function initTelesalesCampaignForm (form) {
		let popupInstance = Vtiger_Popup_Js.getInstance();
		let tblMKTLists = form.find('#tbl-mkt-lists');

		// Step 2
		form.find('#btn-select-mkt-list').on('click', function () {
			var searchParams= [];
			searchParams[0] = [];
			searchParams[0].push(['cptargetlist_type', 'e', 'Default']);
			searchParams[0].push(['cptargetlist_status', 'e', 'Active']);

			var parameters = {
				'module': 'CPTargetList',
				'src_module': 'Campaign',
				'src_record': '',
				'multi_select': true,
				'search_params': searchParams,
			}

			popupInstance.show(parameters, function (data) {
				insertSelectedMKTLists(form, tblMKTLists, data);
			});
		});

		app.event.on('post.Popup.Load post.Popup.reload', function (event, data) {
			let searchRow = $('#popupContents').find('.searchRow');
			searchRow.find('[name="cptargetlist_type"]').attr('disabled', true);
			searchRow.find('[name="cptargetlist_status"]').attr('disabled', true);
		});

		tblMKTLists.on('click', '.btn-remove', function () {
			$(this).closest('tr').remove();

			// Reset data statistics
			form.find('#data-statistics').html('');
		});

		// Modified by Vu Mai on 2023-02-15 handle event click after btn check data is appended
		form.on('click', '#btn-check-data', function () {
			if (form.find('#tbl-mkt-lists').find('tbody tr').length == 0) {
				app.helper.showErrorNotification({ message: app.vtranslate('Campaigns.JS_TELESALES_CAMPAIGN_MKT_LISTS_EMPTY_ERROR_MSG') });
				return false;
			}

			TelesalesCampaignUtils.checkData(form);
		});

		// Step 3
		CustomOwnerField.initCustomOwnerFields(form.find('[name="users"]'));

		// Step 4
		form.find('[name="apply_quota"]').on('change', function () {
			if ($(this).is(':checked')) {
				form.find('.quota-value').show();
			}
			else {
				form.find('.quota-value').hide();
			}

			// Trigger validate manual distribution
			let distributionOptions = TelesalesCampaignUtils.getDistributionOptions(form);

			if (distributionOptions['distribution_method'] == 'manual') {
				TelesalesCampaignUtils.reCalcTotalManualDistributions(form);
			}
		});

		form.find('[name="quota_limit"]').on('change', function () {
			// Trigger validate manual distribution
			let distributionOptions = TelesalesCampaignUtils.getDistributionOptions(form);

			if (distributionOptions['distribution_method'] == 'manual') {
				TelesalesCampaignUtils.reCalcTotalManualDistributions(form);
			}
		});

		form.find('[name="distribution_method"]').on('change', function () {
			if ($(this).val() == 'auto') {
				form.find('.for-auto').show();
				form.find('.for-manual').hide();

				// Clear errors in table manual distribution when user switch to auto distribute mode
				form.find('#tbl-manual-distribution').find('.error-msg').text('');
			}
			else {
				form.find('.for-auto').hide();
				form.find('.for-manual').show();
			}
		});
	}

	function insertSelectedMKTLists (form, tblMKTLists, data) {
		data = JSON.parse(data);
		let selectedIds = Object.keys(data);
		console.log('Selected MKT Lists:', selectedIds);
		if (selectedIds.length == 0) return;
		let idsToInsert = []
		
		for (var i = 0; i < selectedIds.length; i++) {
			let id = selectedIds[i];

			if (tblMKTLists.find('tbody tr[data-id="'+ id +'"]')[0] == null) {
				idsToInsert.push(id);
			}
		}

		// Do nothing when ids to insert is empty
		if (idsToInsert.length == 0) return;

		// Call ajax to get row HTML to insert
		app.helper.showProgress();
		let params = {
			module: 'Campaigns',
			view: 'TelesalesAjax',
			mode: 'getMKTListsTableRows',
			mkt_list_ids: idsToInsert
		};

		app.request.post({ data: params })
		.then((err, res) => {
			app.helper.hideProgress();

			if (err) {					
				app.helper.showErrorNotification({ message: app.vtranslate('Vtiger.JS_AJAX_ERROR_MSG') });
				return;
			}

			tblMKTLists.find('tbody').append(res);

			// Reset data statistics
			form.find('#data-statistics').html('');
		});
	}

	function renderStep4 (form) {
		TelesalesCampaignUtils.getDistributableCustomersCount(form, function (distributableCustomersCount) {
			showDistributableCustomersCount(form, distributableCustomersCount);
			renderTableManualDistrubtion(form, distributableCustomersCount);
		});
	}

	function showDistributableCustomersCount (form, distributableCustomersCount) {
		let container = form.find('.step4');
		container.find('#distributable-count').text(distributableCustomersCount);
	}

	function renderTableManualDistrubtion (form, distributableCustomersCount) {
		let selectedUsers = TelesalesCampaignUtils.getSelectedUsers(form);
		let tblManualDistribution = form.find('#tbl-manual-distribution');
		tblManualDistribution.find('tbody').html('');
		
		for (var i = 0; i < selectedUsers.length; i++) {
			let userInfo = selectedUsers[i];
			// Modified By Vu Mai on 2022-12-08 to restyle according to mockup 
			let rowHtml = `<tr>
				<td>${userInfo.name}</td>
				<td class="text-right">
					<input type="number" name="${userInfo.id}" value="0" class="inputElement quota-percent text-right" data-rule-required="true" data-rule-number="true" data-rule-min="0" data-rule-max="100" />
				</td>
				<td class="text-right">
					<input type="number" name="${userInfo.id}" value="0" class="inputElement quota text-right" data-rule-required="true" data-rule-number="true" data-rule-min="0" />
					<div class="error-msg mt-2"></div>
				</td>
			</tr>`;
			// End Vu Mai
			
			tblManualDistribution.find('tbody').append(rowHtml);
		}

		// Added by Vu Mai on 2023-03-02 to calc customer count by percent
		tblManualDistribution.on('change', '.quota-percent', function () {
			let totalCustomers = tblManualDistribution.find('#total-customers').text();
			let customerCount = Math.round(($(this).val() * parseInt(totalCustomers)) / 100);

			// Assign value to quota input
			$(this).closest('tr').find('input.quota').val(customerCount);

			// Trigger re-calculate total distribution
			TelesalesCampaignUtils.reCalcTotalManualDistributions(form);

			// Do some smart operations
			if (tblManualDistribution.find('.error-msg:not(:empty)').length == 0) {
				let quotaPercentInputs = tblManualDistribution.find('.quota-percent');

				// Current input is next to the last input
				if (quotaPercentInputs.index($(this)) == quotaPercentInputs.length - 2) {
					let lastQuotaInput = tblManualDistribution.find('.quota:last');
					let lastQuotaPercentInput = tblManualDistribution.find('.quota-percent:last');

					// Do when last input value is 0
					if (lastQuotaInput.val() == 0) {
						let distributionOptions = TelesalesCampaignUtils.getDistributionOptions(form);
						let totalDistributed = tblManualDistribution.find('#total-distributed').text();
						let remainingCustomers = parseInt(totalCustomers) - parseInt(totalDistributed);
						let totalDistributedCustomersPercent = 0;

						quotaPercentInputs.each(function () {
							totalDistributedCustomersPercent += parseInt($(this).val()); 
						})

						let remainingCustomersPercent = 100 - totalDistributedCustomersPercent;

						if (distributionOptions['apply_quota'] && distributionOptions['quota_limit'] > 0) {
							if (remainingCustomers >= distributionOptions['quota_limit']) {
								lastQuotaInput.val(distributionOptions['quota_limit']).trigger('change');
							}
							else {
								lastQuotaInput.val(remainingCustomers);
								lastQuotaPercentInput.val(remainingCustomersPercent);
							}
						}
						else {
							lastQuotaInput.val(remainingCustomers);
							lastQuotaPercentInput.val(remainingCustomersPercent);
						}

						// Trigger re-calculate total distribution again
						TelesalesCampaignUtils.reCalcTotalManualDistributions(form);
					}
				}
			}

			// Trigger re-calculate total distribution again
			TelesalesCampaignUtils.reCalcTotalManualDistributions(form);

		});

		// Init validation
		// Modified by Vu Mai on 2023-03-02 to update customer percent when input change
		tblManualDistribution.on('change', '.quota', function () {
			// Trigger re-calculate total distribution
			TelesalesCampaignUtils.reCalcTotalManualDistributions(form);

			let totalCustomers = tblManualDistribution.find('#total-customers').text();
			let customerCountPercent = Math.round(($(this).val() / parseInt(totalCustomers)) * 100);

			// Assign value to quota percent input
			$(this).closest('tr').find('input.quota-percent').val(customerCountPercent);

			// Do some smart operations
			if (tblManualDistribution.find('.error-msg:not(:empty)').length == 0) {
				let quotaInputs = tblManualDistribution.find('.quota');

				// Current input is next to the last input
				if (quotaInputs.index($(this)) == quotaInputs.length - 2) {
					let lastQuotaInput = tblManualDistribution.find('.quota:last');

					// Do when last input value is 0
					if (lastQuotaInput.val() == 0) {
						let distributionOptions = TelesalesCampaignUtils.getDistributionOptions(form);
						let totalDistributed = tblManualDistribution.find('#total-distributed').text();
						let remainingCustomers = parseInt(totalCustomers) - parseInt(totalDistributed);
						let remainingCustomersPercent = Math.round(remainingCustomers / parseInt(totalCustomers) * 100);
						let distributionOptionsQuotaLimitPercent = Math.round(distributionOptions['quota_limit'] / parseInt(totalCustomers) * 100);

						if (distributionOptions['apply_quota'] && distributionOptions['quota_limit'] > 0) {
							if (remainingCustomers >= distributionOptions['quota_limit']) {
								lastQuotaInput.val(distributionOptions['quota_limit']);

								// Assign value to quota percent input
								lastQuotaInput.closest('tr').find('input.quota-percent').val(distributionOptionsQuotaLimitPercent);
							}
							else {
								lastQuotaInput.val(remainingCustomers);

								// Assign value to quota percent input
								lastQuotaInput.closest('tr').find('input.quota-percent').val(remainingCustomersPercent);
							}
						}
						else {
							lastQuotaInput.val(remainingCustomers);

							// Assign value to quota percent input
							lastQuotaInput.closest('tr').find('input.quota-percent').val(remainingCustomersPercent);
						}

						// Trigger re-calculate total distribution again
						TelesalesCampaignUtils.reCalcTotalManualDistributions(form);
					}
				}
			}
		});
		// End Vu Mai


		// Display unique customers count
		tblManualDistribution.find('#total-customers').text(distributableCustomersCount);
	}
	// End Vu Mai

	function renderStep5 (form) {
		TelesalesCampaignUtils.saveHiddenInputs(form);
		TelesalesCampaignUtils.showEstimationResult(form);
	}

	function initFooterButtons (form) {
		if (app.getRecordId() != '' || form.find('[name="campaigntype"]').val() != 'Telesales') return;    // Do nothing for existing record or not a Telesales Campaign

		// Insert button
		let btnSave = form.find('button.saveButton');
		let newBtnsHtml = '<button type="button" id="prev-step" class="btn btn-success" style="display:none"><i class="far fa-angle-left"></i> '+ app.vtranslate('Vtiger.JS_BACK') +'</button>&nbsp;&nbsp;' +
			'<button type="button" id="next-step" class="btn btn-success">'+ app.vtranslate('Vtiger.JS_NEXT') +' <i class="far fa-angle-right"></i></button>&nbsp;&nbsp;';
		$(newBtnsHtml).insertBefore(btnSave);
		btnSave.hide();

		// Handle click button next
		form.find('#next-step').on('click', function () {
			let curStep = form.find('.breadcrumb').data('step');
			let nextStep = curStep + 1;

			if (nextStep <= form.find('.breadcrumb .step').length) {
				// When user move to step > 1
				if (nextStep > 1) {
					// Do validate form for step 1
					if (curStep == 1 && !validateStep1(form)) {
						return false;
					}

					// Do validate form for step 2
					if (curStep == 2 && !validateStep2(form)) {
						return false;
					}

					// Do validate form for step 3
					if (curStep == 3 && !validateStep3(form)) {
						return false;
					}

					// Do validate form for step 4
					if (curStep == 4 && !validateStep4(form)) {
						return false;
					}
				}

				// Hide button Next and show button Save when user move to the last step
				if (nextStep == form.find('.breadcrumb .step').length) {
					form.find('#next-step').hide();
					btnSave.show();
				}

				// Display the selected step and hide other steps
				form.find('.breadcrumb').data('step', nextStep);
				form.find('.breadcrumb .step').removeClass('active');
				form.find('.breadcrumb .step' + nextStep).addClass('active');
				form.find('#form-content .step').hide();
				form.find('#form-content .step' + nextStep).show();

				// Display content for step 4
				if (nextStep == 4) {
					renderStep4(form);
				}

				// Display content for step 5
				if (nextStep == 5) {
					renderStep5(form);
				}
			}
			
			form.find('#prev-step').show();

			// Modified By Vu Mai on 2023-02-15 to move btn check duplicated data to footer in step 2
			insertBtnCheckData(form);
			// End Vu Mai
		});

		// Handle button prev
		form.find('#prev-step').on('click', function () {
			let prevStep = form.find('.breadcrumb').data('step') - 1;

			if (prevStep >= 1) {
				// Hide button save when user move back
				btnSave.hide();

				// When use move back to step 1
				if (prevStep == 1) {
					form.find('#prev-step').hide();					// Hide button Prev when user move to the first step
					form.find('div[name="editContent"]').show();	// Show the main form blocks back at step 2
				}

				// Display the selected step and hide other steps
				form.find('.breadcrumb').data('step', prevStep);
				form.find('.breadcrumb .step').removeClass('active');
				form.find('.breadcrumb .step' + prevStep).addClass('active');
				form.find('#form-content .step').hide();
				form.find('#form-content .step' + prevStep).show();
			}
			
			form.find('#next-step').show();

			// Modified By Vu Mai on 2023-02-15 to move btn check duplicated data to footer in step 2
			insertBtnCheckData(form);
			// End Vu Mai
		});

		// Handle form submit
		form.on('submit', function () {
			if (window.telesales_config_valid) {
				delete window.telesales_config_valid;

				if (form.find('#total-skipped').text() > 0) {
					if (window.user_confirmed_saving_with_skipped_data) {
						delete window.user_confirmed_saving_with_skipped_data;
					}
					else {
						let skippedCount = form.find('#total-skipped').text();
						let distributionOptions = TelesalesCampaignUtils.getDistributionOptions(form);

						// Confirm when skipped data > 0
						if (distributionOptions.apply_quota && distributionOptions.quota_limit > 0) {
							TelesalesCampaignUtils.confirmSavingWithSkippedData(form, skippedCount);
							return false;
						}
					}
				}

				return true;
			}
			// Check if telesales config already set for this campaign purpose
			else {
				checkTelesalesConfig(form, function () {
					// Allow to submit
					window.telesales_config_valid = true;
					form.find('.saveButton').trigger('click');
				});
			}

			form.find('.saveButton').attr('disabled', false);	// Disabled button can not be triggered when the config is valid
			return false;
		});
	}

	// Added by Vu Mai on 2023-02-15 to insert button check data to footer
	function insertBtnCheckData (form) {
		let btnCheckData = '<button type="button" id="btn-check-data" class="btn btn-default mr-2 btn-check-data">'+ app.vtranslate('Campaigns.JS_TELESALES_CAMPAIGN_WIZARD_PANEL_SELECT_MKT_LISTS_BTN_CHECK_DATA') +'</button>';
		let activeStep = form.find('.breadcrumb .step.active').data('step');

		if (activeStep == 2) {
			form.find('button.btn-check-data').remove();
			let btnNextStep = form.find('button#next-step');
			$(btnCheckData).insertBefore(btnNextStep);
		}
		else {
			form.find('button.btn-check-data').remove();
		}
	}

	function validateStep1 (form) {
		if (!form.valid()) {
			app.helper.showErrorNotification({ message: app.vtranslate('Campaigns.JS_TELESALES_CAMPAIGN_REQUIRED_FIELDS_EMPTY_ERROR_MSG') });
			return false;
		}

		if (window.telesales_config_valid) {
			delete window.telesales_config_valid;
			return true;
		}
		// Check if telesales config already set for this campaign purpose
		else {
			checkTelesalesConfig(form, function () {
				// Go to next step
				window.telesales_config_valid = true;
				form.find('#next-step').trigger('click');

				// Hide main form blocks
				form.find('div[name="editContent"]').hide();
			});
		}
	}

	function checkTelesalesConfig (form, successCallback) {
		let campaignPurpose = form.find('[name="campaigns_purpose"]').val();
		let campaignPurposeText = form.find('[name="campaigns_purpose"]').find('option[value="'+ campaignPurpose +'"]').text();

		TelesalesCampaignUtils.checkTelesalesConfig(campaignPurpose, campaignPurposeText, successCallback);
	}

	function validateStep2 (form) {
		if (form.find('#tbl-mkt-lists').find('tbody tr').length == 0) {
			app.helper.showErrorNotification({ message: app.vtranslate('Campaigns.JS_TELESALES_CAMPAIGN_MKT_LISTS_EMPTY_ERROR_MSG') });
			return false;
		}

		if (form.find('#tbl-data-statistics')[0] == null) {
			app.helper.showErrorNotification({ message: app.vtranslate('Campaigns.JS_TELESALES_CAMPAIGN_FORGET_CHECKING_DUPLICATES_ERROR_MSG') });
			return false;
		}

		if (form.find('#tbl-data-statistics').find('#duplicate-mobile-count').text() > 0) {
			if (window.user_skipped_duplicates) {
				delete window.user_skipped_duplicates;
				return true;
			}
			else {
				bootbox.confirm({
					buttons: {
						confirm: {
							label: app.vtranslate('Vtiger.JS_NEXT'), // Modified by Vu Mai on 2023-02-15 to change label
							className: 'confirm-box-ok confirm-box-btn-pad btn-danger'
						},
						cancel: {
							label: app.vtranslate('Vtiger.JS_CANCEL'),
							className: 'btn-default confirm-box-btn-pad pull-right'
						},
					},
					message: app.vtranslate('Campaigns.JS_TELESALES_CAMPAIGN_SKIP_DUPLICATES_CONFIRM_MSG'),
					htmlSupportEnable: true,
					callback: function (result) {
						if (result) {
							window.user_skipped_duplicates = true;
							form.find('#next-step').trigger('click');
						}
					}
				});

				return false;
			}
		}

		return true;
	}

	function validateStep3 (form) {
		let selectedUsers = TelesalesCampaignUtils.getSelectedUsers(form);
		
		if (selectedUsers.length == 0) {
			app.helper.showErrorNotification({ message: app.vtranslate('Campaigns.JS_TELESALES_CAMPAIGN_USER_LIST_EMPTY_ERROR_MSG') });
			return false;
		}

		return true;
	}

	function validateStep4 (form) {
		let container = form.find('.step4');
		let distributionOptions = TelesalesCampaignUtils.getDistributionOptions(form);

		// Validate manual distribution
		if (distributionOptions['distribution_method'] == 'manual') {
			TelesalesCampaignUtils.reCalcTotalManualDistributions(form);

			// All users can not be zero
			let zeroCount = 0;

			container.find('input.quota').each(function () {
				if ($(this).val() == 0) {
					zeroCount += 1;
				}
			});

			if (zeroCount == container.find('input.quota').length) {
				app.helper.showErrorNotification({ message: app.vtranslate('Campaigns.JS_TELESALES_CAMPAIGN_ALL_USERS_CANNOT_BE_ZERO_ERROR_MSG') });
				return false;
			}
		}

		// Check errors
		if (!form.valid() || container.find('.error-msg:not(:empty)').length > 0) {
			app.helper.showErrorNotification({ message: app.vtranslate('Campaigns.JS_TELESALES_CAMPAIGN_FIX_ERRORS_TO_CONTINUE_MSG') });
			return false;
		}

		return true;
	}
});