/*
	File: EditTelesalesCampaignForm.js
	Author: Hieu Nguyen
	Date: 2022-10-24
	Purpose: handle logic on the UI for Telesales Campaign's edit record form
*/

CustomView_BaseController_Js('Campaigns_EditTelesalesCampaignForm_Js', {}, {
	
	registerEvents: function () {
		this._super();
		this.registerEventFormInit();
	},

	getForm: function () {
		return $('form#redistribute');
	},

	registerEventFormInit: function () {
		let form = this.getForm();

		this.initPageBreadcrum(form);
		this.initWizard(form);
		this.initForm(form);
		this.registerFormSubmit(form);
	},

	initPageBreadcrum: function  (form) {
		let formTitle = form.find('#form-title').text().trim();
		let campaignName = form.find('[name="campaign_name"]').val();
		let template = `<p class="current-filter-name filter-name pull-left"><i class="far fa-angle-right" aria-hidden="true"></i>&nbsp;&nbsp;<a>%TEXT</a></p>&nbsp;&nbsp;`;
		let titleHtml = template.replace('%TEXT', formTitle) + template.replace('%TEXT', campaignName);
		$('.module-breadcrumb-EditTelesalesCampaignForm').append(titleHtml)
	},

	initWizard: function (form) {
		let self = this;
		let btnSave = form.find('button.saveButton');

		// Modified By Vu Mai on 2023-02-15 to move btn check duplicated data to footer in step 2
		self.insertBtnCheckData(form);
		// End Vu Mai

		// Handle click button next
		form.find('#next-step').on('click', function () {
			let curStep = form.find('.breadcrumb').data('step');
			let nextStep = curStep + 1;

			if (nextStep <= form.find('.breadcrumb .step').length) {
				// When user move to step > 1
				if (nextStep > 1) {
					// Do validate form for step 1
					if (curStep == 1 && !self.validateStep1(form)) {
						return false;
					}

					// Do validate form for step 2
					if (curStep == 2 && !self.validateStep2(form)) {
						return false;
					}

					// Do validate form for step 3
					if (curStep == 3 && !self.validateStep3(form)) {
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

				// Display content for step 2
				if (nextStep == 2) {
					self.renderStep2(form);
				}

				// Display content for step 3
				if (nextStep == 3) {
					self.renderStep3(form);
				}

				// Display content for step 4
				if (nextStep == 4) {
					self.renderStep4(form);
				}
			}
			
			form.find('#prev-step').show();

			// Modified By Vu Mai on 2023-02-15 to move btn check duplicated data to footer in step 2
			self.insertBtnCheckData(form);
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
			self.insertBtnCheckData(form);
			// End Vu Mai
		});
	},

	// Added by Vu Mai on 2023-02-15 to insert button check data to footer
	insertBtnCheckData: function (form) {
		let btnCheckData = '<button type="button" id="btn-check-data" class="btn btn-default mr-2 btn-check-data">'+ app.vtranslate('Campaigns.JS_TELESALES_CAMPAIGN_WIZARD_PANEL_SELECT_MKT_LISTS_BTN_CHECK_DATA') +'</button>';
		let activeStep = form.find('.breadcrumb .step.active').data('step');

		if (activeStep == 1) {
			form.find('button.btn-check-data').remove();
			let btnNextStep = form.find('button#next-step');
			$(btnCheckData).insertBefore(btnNextStep);
		}
		else {
			form.find('button.btn-check-data').remove();
		}
	},

	initForm: function (form) {
		let self = this;
		let popupInstance = Vtiger_Popup_Js.getInstance();
		let tblMKTLists = form.find('#tbl-mkt-lists');

		// Step 1
		self.reCalcTotalsForTableMKTLists(tblMKTLists);

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
				self.insertSelectedMKTLists(form, tblMKTLists, data);
			});
		});

		app.event.on('post.Popup.Load post.Popup.reload', function (event, data) {
			let searchRow = $('#popupContents').find('.searchRow');
			searchRow.find('[name="cptargetlist_type"]').attr('disabled', true);
			searchRow.find('[name="cptargetlist_status"]').attr('disabled', true);
		});

		tblMKTLists.on('click', '.btn-remove', function () {
			let targetBtn = $(this);
			let mktListInfo = $(this).closest('tr').data('mktListInfo');

			bootbox.confirm({
				buttons: {
					confirm: {
						label: app.vtranslate('Vtiger.JS_DELETE'),
						className: 'confirm-box-ok confirm-box-btn-pad btn-danger'
					},
					cancel: {
						label: app.vtranslate('Campaigns.JS_CANCEL'),
						className: 'btn-default confirm-box-btn-pad pull-right'
					},
				},
				title: app.vtranslate('Campaigns.JS_EDIT_TELESALES_CAMPAIGN_WIZARD_REMOVE_MKT_LIST_CONFIRM_TITLE'),
				message: app.vtranslate('Campaigns.JS_EDIT_TELESALES_CAMPAIGN_WIZARD_REMOVE_MKT_LIST_CONFIRM_MSG', { 'mkt_list_name': mktListInfo.name }),
				htmlSupportEnable: true,
				callback: function (result) {
					if (result) {
						self.removeMKTList(form, tblMKTLists, targetBtn);
					}
				}
			});
		});

		// Modified by Vu Mai on 2023-02-15 to handle event click after btn check data is appended
		form.on('click', '#btn-check-data', function () {
			if (form.find('#tbl-mkt-lists').find('tbody tr').length == 0) {
				app.helper.showErrorNotification({ message: app.vtranslate('Campaigns.JS_TELESALES_CAMPAIGN_MKT_LISTS_EMPTY_ERROR_MSG') });
				return false;
			}

			TelesalesCampaignUtils.checkData(form);
		});

		// Step 2
		let tblUserList = form.find('#tbl-user-list');
		let addUserInput = form.find('#add-user');
		
		setTimeout(() => {
			self.attachSkipUsersForAutoCompleteInput(form);
			CustomOwnerField.initCustomOwnerFields(addUserInput);
		}, 1000);

		addUserInput.on('change', function (e) {
			let userIdStr = $(this).val();
			self.insertSelectedUser(form, tblUserList, userIdStr);

			// Clear value and keep it open
			$(this).select2('val', '')
			$(this).select2('open');
		});

		tblUserList.on('click', '.btn-remove, .btn-transfer', function () {
			let targetBtn = $(this);
			let selectedUserInfo = targetBtn.closest('tr').data('userInfo');
			let tblUserList = form.find('#tbl-user-list');

			TelesalesCampaignUtils.getUserInfoWithStatistics(selectedUserInfo.id, function (dbUserInfo) {
				let statistics = dbUserInfo['statistics'] || { not_called_count: 0,  all_distributed_count: 0};

				if (targetBtn.hasClass('btn-transfer')) {
					if (statistics.not_called_count > 0) {
						self.showTransferDataModal(form, targetBtn);
					}
					else {
						// Reload selected row as data changed in the db that cause no not called customers to transfer
						self.reloadUserRow(tblUserList, selectedUserId);
					}
				}

				if (targetBtn.hasClass('btn-remove')) {
					if (statistics.all_distributed_count > 0) {
						self.showTransferDataModal(form, targetBtn);
					}
					else {
						// Just remove user without transfer as data changed 
						self.removeUserWithoutTransfer(form, targetBtn, selectedUserInfo);
					}
				}
			});
		});

		// Step 3
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
			// Validate quota value
			let maxCurrentDataCount = self.getMaxCurrentDataCount(form);

			if ($(this).val().trim() != '' && parseInt($(this).val()) < maxCurrentDataCount) {
				$(this).addClass('input-error');
				$('#quota-error-msg').text(app.vtranslate('Campaigns.JS_EDIT_TELESALES_CAMPAIGN_WIZARD_QUOTA_LESS_THAN_MAX_CURRENT_DISTRIBUTED_DATA_ERROR_MSG', { 'max_count': maxCurrentDataCount }));
			}
			else {
				$(this).removeClass('input-error');
				$('#quota-error-msg').text('');
			}

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
	},

	registerFormSubmit: function (form) {
		let self = this;

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

				app.helper.showProgress();	// Show progress to prevent click on any button
				return true;
			}
			// Check if telesales config already set for this campaign purpose
			else {
				self.checkTelesalesConfig(form, function () {
					// Allow to submit
					window.telesales_config_valid = true;
					form.find('.saveButton').trigger('click');
				});
			}

			form.find('.saveButton').attr('disabled', false);	// Disabled button can not be triggered when the config is valid
			return false;
		});
	},

	checkTelesalesConfig: function (form, successCallback) {
		let campaignPurpose = form.find('[name="campaigns_purpose"]').val();
		let campaignPurposeText = form.find('[name="campaigns_purpose_text"]').val();

		TelesalesCampaignUtils.checkTelesalesConfig(campaignPurpose, campaignPurposeText, successCallback);
	},

	validateStep1: function (form) {
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
							label: app.vtranslate('Vtiger.JS_NEXT'),	// Modified by Vu Mai on 2023-02-15 to change label
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
	},

	validateStep2: function (form) {
		// Check empty user list
		let selectedUsers = TelesalesCampaignUtils.getSelectedUsers(form);
		
		if (selectedUsers.length == 0) {
			app.helper.showErrorNotification({ message: app.vtranslate('Campaigns.JS_TELESALES_CAMPAIGN_USER_LIST_EMPTY_ERROR_MSG') });
			return false;
		}

		// Check users over quota
		let usersOverQuota = this.getUsersOverQuota(form);

		if (usersOverQuota.length > 0) {
			this.showEditQuotaModal(form, usersOverQuota);
			return false;
		}

		// Check no data
		if (form.find('#distributable-count').text() == 0) {
			this.showNoDataModal(form, usersOverQuota);
			return false;
		}

		return true;
	},

	getUsersOverQuota: function (form) {
		let usersOverQuota = [];
		let distributionOptions = TelesalesCampaignUtils.getDistributionOptions(form);

		if (distributionOptions.apply_quota && distributionOptions.quota_limit > 0) {
			let selectedUsers = TelesalesCampaignUtils.getSelectedUsers(form);

			for (var i = 0; i < selectedUsers.length; i++) {
				if (selectedUsers[i].statistics.all_distributed_count > distributionOptions.quota_limit) {
					usersOverQuota.push(selectedUsers[i]);
				}
			}
		}

		return usersOverQuota;
	},

	getMaxCurrentDataCount(form) {
		let max = 0;
		let selectedUsers = TelesalesCampaignUtils.getSelectedUsers(form);

		for (var i = 0; i < selectedUsers.length; i++) {
			if (selectedUsers[i].statistics.all_distributed_count > max) {
				max = selectedUsers[i].statistics.all_distributed_count;
			}
		}

		return max;
	},

	showEditQuotaModal: function (form, userList) {
		let self = this;
		let modal = $('.modal-template-md').clone(true, true);
		modal.removeClass('hide');

		let modalParams = {
			preShowCb: function (modal) {
				let quotaLimitInput = form.find('.step3').find('input[name="quota_limit"]');
				let userListHtml = [];

				for (var i = 0; i < userList.length; i++) {
					let userName = userList[i].full_name;
					let totalCount = userList[i].statistics.all_distributed_count;
					userListHtml.push('<strong>'+ userName +' <span class="redColor">('+ totalCount +')</span></strong>')
				}

				modal.find('.modal-header .pull-left').text(app.vtranslate('Campaigns.JS_EDIT_TELESALES_CAMPAIGN_WIZARD_EDIT_QUOTA_MODAL_TITLE'));
				modal.find('.modal-body').append('<div>'+ app.vtranslate('Campaigns.JS_EDIT_TELESALES_CAMPAIGN_WIZARD_EDIT_QUOTA_MODAL_TEXT', { 'user_list': userListHtml.join(', ') }) +'</div>');
				modal.find('.modal-body').append('<div style="margin-top:20px">'+ form.find('.step3 .quota-value').html() + '</div>');
				modal.find('input[name="quota_limit"]').val(quotaLimitInput.val());
				modal.find('button[type="submit"]').text(app.vtranslate('Vtiger.JS_SAVE'));

				// Handle button save new quota
				modal.find('button[type="submit"]').on('click', function () {
					let tblUserList = form.find('#tbl-user-list');
					let newQuotaLimit = modal.find('input[name="quota_limit"]').val();
					quotaLimitInput.val(newQuotaLimit).trigger('change');
					self.checkQuotaOnTableUserList(form, tblUserList);
					modal.find('button.close').trigger('click');
					return false;
				});
			}
		};

		app.helper.showModal(modal, modalParams);
	},

	showNoDataModal: function (form) {
		let modal = $('.modal-template-md').clone(true, true);
		modal.removeClass('hide');

		let modalParams = {
			preShowCb: function (modal) {
				modal.find('.modal-header .pull-left').text(app.vtranslate('Campaigns.JS_EDIT_TELESALES_CAMPAIGN_WIZARD_NO_DATA_MODAL_TITLE'));
				modal.find('.modal-body').append('<div>'+ app.vtranslate('Campaigns.JS_EDIT_TELESALES_CAMPAIGN_WIZARD_NO_DATA_MODAL_TEXT') +'</div>');
				modal.find('button[type="submit"]').text(app.vtranslate('Campaigns.JS_EDIT_TELESALES_CAMPAIGN_WIZARD_NO_DATA_MODAL_BTN_SAVE'));
				let btnBackHtml = '<button type="button" class="btn btn-primary btn-back">'+ app.vtranslate('Campaigns.JS_EDIT_TELESALES_CAMPAIGN_WIZARD_NO_DATA_MODAL_BTN_BACK') +'</button>';
				modal.find('a[type="reset"]').replaceWith(btnBackHtml);

				// Handle buttons
				modal.find('button[type="submit"]').on('click', function () {
					TelesalesCampaignUtils.saveHiddenInputs(form);
					modal.find('button.close').trigger('click');
					form.submit();
					return false;
				});

				modal.find('.btn-back').on('click', function () {
					form.find('button#prev-step').trigger('click');
					modal.find('button.close').trigger('click');
					return false;
				});
			}
		};

		app.helper.showModal(modal, modalParams);
	},

	validateStep3: function (form) {
		let container = form.find('.step3');
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
	},

	reCalcTotalsForTableMKTLists: function (tblMKTLists) {
		let totalOfTotals = 0;
		let totalOfDistributeds = 0;
		let totalOfRemainings = 0;

		tblMKTLists.find('tbody tr').each(function () {
			totalOfTotals += app.unformatCurrencyToUser($(this).find('.total-count').text());
			totalOfDistributeds += app.unformatCurrencyToUser($(this).find('.distributed-count').text());
			totalOfRemainings += app.unformatCurrencyToUser($(this).find('.remaining-count').text());
		});

		tblMKTLists.find('#total-of-totals').text(app.formatNumberToUserFromNumber(totalOfTotals));
		tblMKTLists.find('#total-of-distributeds').text(app.formatNumberToUserFromNumber(totalOfDistributeds));
		tblMKTLists.find('#total-of-remainings').text(app.formatNumberToUserFromNumber(totalOfRemainings));
	},

	insertSelectedMKTLists: function (form, tblMKTLists, data) {
		let self = this;
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
			mkt_list_ids: idsToInsert,
			campaign_id: app.getRecordId()
		};

		app.request.post({ data: params })
		.then((err, res) => {
			app.helper.hideProgress();

			if (err) {					
				app.helper.showErrorNotification({ message: app.vtranslate('Vtiger.JS_AJAX_ERROR_MSG') });
				return;
			}

			// Insert new row and re-calculate the totals
			tblMKTLists.find('tbody').append(res);
			self.reCalcTotalsForTableMKTLists(tblMKTLists);

			// Reset data statistics
			form.find('#data-statistics').html('');
		});
	},

	removeMKTList: function (form, tblMKTLists, targetBtn) {
		let self = this;
		let mktListId = targetBtn.closest('tr').data('id');

		// Call ajax to get result
		app.helper.showProgress();
		let params = {
			module: 'Campaigns',
			action: 'TelesalesAjax',
			mode: 'removeMKTList',
			mkt_list_id: mktListId,
			campaign_id: app.getRecordId()
		};

		app.request.post({ data: params })
		.then((err, res) => {
			app.helper.hideProgress();

			// Handle error
			if (err || !res) {
				app.helper.showErrorNotification({ message: app.vtranslate('Vtiger.JS_AJAX_ERROR_MSG') });
				return;
			}

			if (!res.success) {
				app.helper.showErrorNotification({ message: res.message });

				// Hide button Remove when this MKT List cannot be removed
				if (res.code == 'CANNOT_REMOVE') {
					targetBtn.remove();
				}

				return;
			}

			// Remove row and re-calculate the totals
			targetBtn.closest('tr').remove();
			self.reCalcTotalsForTableMKTLists(tblMKTLists);

			// Reset data statistics
			form.find('#data-statistics').html('');

			app.helper.showSuccessNotification({ message: app.vtranslate('Campaigns.JS_EDIT_TELESALES_CAMPAIGN_WIZARD_REMOVE_MKT_LIST_SUCCESS_MSG') });
		});
	},

	attachSkipUsersForAutoCompleteInput: function (form) {
		let selectedUserIds = TelesalesCampaignUtils.getSelectedUserIds(form);
		form.find('#add-user').data('skipUsers', selectedUserIds);
	},

	insertSelectedUser: function (form, tblUserList, userIdStr) {
		let self = this;
		let userId = userIdStr.replace('Users:', '');

		// Call ajax to get row HTML to insert
		app.helper.showProgress();
		let params = {
			module: 'Campaigns',
			view: 'TelesalesAjax',
			mode: 'getUserListTableRow',
			user_id: userId,
			campaign_id: app.getRecordId()
		};

		app.request.post({ data: params })
		.then((err, res) => {
			app.helper.hideProgress();

			if (err) {					
				app.helper.showErrorNotification({ message: app.vtranslate('Vtiger.JS_AJAX_ERROR_MSG') });
				return;
			}

			// Insert new row
			tblUserList.find('tbody').append(res);
			self.attachSkipUsersForAutoCompleteInput(form);
		});
	},

	removeUserWithoutTransfer: function (form, targetBtn, userInfo) {
		let self = this;

		bootbox.confirm({
			buttons: {
				confirm: {
					label: app.vtranslate('Vtiger.JS_DELETE'),
					className: 'confirm-box-ok confirm-box-btn-pad btn-danger'
				},
				cancel: {
					label: app.vtranslate('Campaigns.JS_CANCEL'),
					className: 'btn-default confirm-box-btn-pad pull-right'
				},
			},
			title: app.vtranslate('Campaigns.JS_EDIT_TELESALES_CAMPAIGN_WIZARD_REMOVE_USER_CONFIRM_TITLE'),
			message: app.vtranslate('Campaigns.JS_EDIT_TELESALES_CAMPAIGN_WIZARD_REMOVE_USER_CONFIRM_MSG', { 'user_name': userInfo.full_name }),
			htmlSupportEnable: true,
			callback: function (result) {
				if (result) {
					targetBtn.closest('tr').remove();
					self.attachSkipUsersForAutoCompleteInput(form);
				}
			}
		});
	},

	showTransferDataModal: function (form, targetBtn) {
		let self = this;
		let sourceUserInfo = targetBtn.closest('tr').data('userInfo');
		let modalType = targetBtn.hasClass('btn-remove') ? 'transfer_and_remove' : 'transfer_only';
		let modal = $('#modal-transfer-data').clone(true, true);
		modal.removeClass('hide');

		let getTransferNumber = function (modal, modalType) {
			let transferNumber = 'all';

			if (modalType == 'transfer_only') {
				transferNumber = modal.find('.for-transfer-only').find('[name="transfer_number"]').val();
			}

			return transferNumber;
		}

		let modalParams = {
			preShowCb: function (modal) {
				// Init modal form
				let modalForm = modal.find('form#transfer-data');

				// Set title
				if (modalType == 'transfer_only') {
					let title = app.vtranslate('Campaigns.JS_EDIT_TELESALES_CAMPAIGN_WIZARD_TRANSFER_DATA_MODAL_TRANSFER_ONLY_TITLE', { 'user_name': sourceUserInfo.full_name });
					modal.find('.modal-header .pull-left').text(title);
					modal.find('.for-transfer-only .customers-count').text(sourceUserInfo.statistics.not_called_count);
				}
				else {
					let title = app.vtranslate('Campaigns.JS_EDIT_TELESALES_CAMPAIGN_WIZARD_TRANSFER_DATA_MODAL_REMOVE_AND_TRANSFER_TITLE', { 'user_name': sourceUserInfo.full_name });
					modal.find('.modal-header .pull-left').text(title);
					modal.find('.for-transfer-only').remove();
					modal.find('.transfer-data-type').removeClass('hide');
				}

				// Init transfer number inputs
				if (modalType == 'transfer_only') {
					let maxTransferNumber = sourceUserInfo.statistics.not_called_count;
					modal.find('.for-transfer-only').find('.customers-count').text(maxTransferNumber);
					let transferNumberInput = modal.find('.for-transfer-only').find('[name="transfer_number"]');
					transferNumberInput.val(maxTransferNumber);

					// Handle transfer number input change event
					transferNumberInput.on('change', function () {
						// Reset error status
						$(this).removeClass('input-error');
						$(this).next('.error-msg').text('');

						// Disable reciever user input first
						modal.find('.transfer-receiver').find('.transfer-to-user').select2('disable');
						modal.find('.transfer-receiver').find('.out-of-quota-warning').addClass('hide');

						// Do nothing when input error
						if ($(this).val().trim() == '' || parseInt($(this).val()) < 1) {
							return;
						}

						// When input number > max number, display out of max number message
						if (parseInt($(this).val()) > maxTransferNumber) {
							$(this).addClass('input-error');
							$(this).next('.error-msg').text(app.vtranslate('Campaigns.JS_EDIT_TELESALES_CAMPAIGN_WIZARD_TRANSFER_DATA_MODAL_OUT_OF_MAX_NUMBER_ERROR_MSG'));
						}
						// Otherwise, enable receiver user input and trigger change to re-check quota
						else {
							modal.find('.transfer-receiver').find('.transfer-to-user').select2('enable');
							
							if (modal.find('.transfer-receiver').find('select.transfer-to-user.campaign-user').val() != '') {
								modal.find('.transfer-receiver').find('select.transfer-to-user.campaign-user').trigger('change');
							}
						}
					});
				}

				// Init transfer to campaign user input
				let transferToCampaignUserInput = modal.find('.transfer-receiver').find('select.transfer-to-user.campaign-user');
				let outOfQuotaWarningIcon = modal.find('.transfer-receiver').find('.out-of-quota-warning');
				
				let selectedUsers = TelesalesCampaignUtils.getSelectedUsers(form);
				let campaignUserOptions = '';

				for (var i = 0; i < selectedUsers.length; i++) {
					let userInfo = selectedUsers[i];

					if (userInfo.id != sourceUserInfo.id) {
						campaignUserOptions += '<option value="'+ userInfo.id +'">'+ userInfo.name +'</option>';
					}
				}

				transferToCampaignUserInput.append(campaignUserOptions);
				transferToCampaignUserInput.select2();

				// Init transfer to other user input
				let transferToOtherUserInput = modal.find('input.transfer-to-user.other-user');
				transferToOtherUserInput.data('skipUsers', TelesalesCampaignUtils.getSelectedUserIds(form));
				CustomOwnerField.initCustomOwnerFields(transferToOtherUserInput);

				// Toggle user inputs according user selection
				modal.find('[name="transfer_to"]').on('change', function () {
					if ($(this).val() == 'campaign_user') {
						modal.find('.transfer-to-campaign-user-input-wrapper').removeClass('hide');
						modal.find('.transfer-to-other-user-input-wrapper').addClass('hide');
					}
					else {
						transferToCampaignUserInput.select2('val', '');
						outOfQuotaWarningIcon.attr('title', '').addClass('hide');
						modal.find('.transfer-to-campaign-user-input-wrapper').addClass('hide');
						modal.find('.transfer-to-other-user-input-wrapper').removeClass('hide');
					}
				});

				// Validate transfer data
				transferToCampaignUserInput.on('change', function () {
					if ($(this).val() == '') return;
					let distributionOptions = TelesalesCampaignUtils.getDistributionOptions(form);

					// Verify new total with quota limit
					if (distributionOptions.apply_quota && distributionOptions.quota_limit > 0) {
						let targetUserId = transferToCampaignUserInput.val();

						if (targetUserId != '') {
							let dataType = modal.find('[name="transfer_data_type"]:checked').val();
							let transferNumber = getTransferNumber(modal, modalType);

							self.verifyTransferData(sourceUserInfo.id, targetUserId, dataType, transferNumber, distributionOptions.quota_limit, outOfQuotaWarningIcon);
						}
						else {
							outOfQuotaWarningIcon.attr('title', '').addClass('hide');
						}
					}
				});

				var params = {
					submitHandler: function (modalForm) {
						modalForm = $(modalForm);

						// Do nothing when there is validation error
						if (!modalForm.valid() || modalForm.find('.error-msg:not(:empty)').length > 0) {
							return;
						}

						let transferTo = modal.find('[name="transfer_to"]:checked').val();
						let dataType = modalForm.find('[name="transfer_data_type"]:checked').val();
						let transferNumber = getTransferNumber(modal, modalType);
						let targetUserId = '';

						if (transferTo == 'campaign_user') {
							targetUserId = transferToCampaignUserInput.val();
						}
						else if (transferTo == 'other_user') {
							targetUserId = transferToOtherUserInput.val().replace('Users:', '');	// Custom owner value format is 'Users:xxx'
						}

						// Validate target user
						if (targetUserId == '') {
							app.helper.showErrorNotification({ message: app.vtranslate('Campaigns.JS_EDIT_TELESALES_CAMPAIGN_WIZARD_TRANSFER_DATA_MODAL_TARGET_USER_EMPTY_ERROR_MSG') })
							return;
						}

						// Do transfer logic
						self.transferData(form, modal, modalType, targetBtn, sourceUserInfo.id, targetUserId, dataType, transferNumber);
					}
				};

				modalForm.vtValidate(params);
			}
		};

		app.helper.showModal(modal, modalParams);
	},

	verifyTransferData: function (sourceUserId, targetUserId, dataType, transferNumber, quotaLimit, outOfQuotaWarningIcon) {
		// Call ajax to get result
		app.helper.showProgress();
		let params = {
			module: 'Campaigns',
			action: 'TelesalesAjax',
			mode: 'verifyTransferData',
			campaign_id: app.getRecordId(),
			source_user_id: sourceUserId,
			target_user_id: targetUserId,
			data_type: dataType,
			quota_limit: quotaLimit,
		};

		if (dataType == 'not_called_customers') {
			params.transfer_number = transferNumber;	// Value is 'all' or a number <= max number
		}

		app.request.post({ data: params })
		.then((err, res) => {
			app.helper.hideProgress();

			// Handle error
			if (err || !res) {
				app.helper.showErrorNotification({ message: app.vtranslate('Vtiger.JS_AJAX_ERROR_MSG') });
				return;
			}

			if (!res.valid) {
				let warningMsg = app.vtranslate('Campaigns.JS_EDIT_TELESALES_CAMPAIGN_WIZARD_TRANSFER_DATA_MODAL_OUT_OF_QUOTA_WARNING_MSG', { 'new_total': res.new_total, 'quota_limit': quotaLimit });
				outOfQuotaWarningIcon.attr('title', warningMsg);
				outOfQuotaWarningIcon.attr('data-tippy-content', warningMsg);
				outOfQuotaWarningIcon.removeClass('hide');
				vtUtils.enableTooltips();
			}
			else {
				outOfQuotaWarningIcon.addClass('hide');
			}
		});
	},

	transferData: function (form, modal, modalType, targetBtn, sourceUserId, targetUserId, dataType, transferNumber) {
		let self = this;
		let tblUserList = form.find('#tbl-user-list');

		// Call ajax to get result
		app.helper.showProgress();
		let params = {
			module: 'Campaigns',
			action: 'TelesalesAjax',
			mode: 'transferData',
			campaign_id: app.getRecordId(),
			source_user_id: sourceUserId,
			target_user_id: targetUserId,
			data_type: dataType,
		};

		if (modalType == 'transfer_only') {
			params.transfer_number = transferNumber;	// Value is 'all' or a number <= max number
		}

		if (modalType == 'transfer_and_remove') {
			params.remove_source_user = true;
		}

		app.request.post({ data: params })
		.then((err, res) => {
			app.helper.hideProgress();

			// Handle error
			if (err || !res) {
				app.helper.showErrorNotification({ message: app.vtranslate('Vtiger.JS_AJAX_ERROR_MSG') });
				return;
			}

			// Hide button Transfer when this user has no not called customers to transfer
			if (res.code == 'NO_NOT_CALLED_CUSTOMERS') {
				app.helper.showErrorNotification({ message: res.message });
				modal.find('button.close').trigger('click');
				targetBtn.remove();
				return;
			}

			// Remove row
			if (modalType == 'transfer_and_remove') {
				targetBtn.closest('tr').remove();
				self.attachSkipUsersForAutoCompleteInput(form);
			}
			// Reload source user row to display the final value
			else if (modalType == 'transfer_only') {
				self.reloadUserRow(tblUserList, sourceUserId);
			}

			// Reload target user row and close modal
			self.reloadUserRow(tblUserList, targetUserId);
			modal.find('button.close').trigger('click');

			// Recheck new total with quota limit
			self.checkQuotaOnTableUserList(form, tblUserList);

			app.helper.showSuccessNotification({ message: app.vtranslate('Campaigns.JS_EDIT_TELESALES_CAMPAIGN_WIZARD_TRANSFER_DATA_MODAL_TRANSFER_SUCCESS_MSG') });
		});
	},

	reloadUserRow: function (tblUserList, userId) {
		// Call ajax to get result
		app.helper.showProgress();
		let params = {
			module: 'Campaigns',
			view: 'TelesalesAjax',
			mode: 'getUserListTableRow',
			user_id: userId,
			campaign_id: app.getRecordId()
		};

		app.request.post({ data: params })
		.then((err, res) => {
			app.helper.hideProgress();

			// Handle error
			if (err || !res) {
				app.helper.showErrorNotification({ message: app.vtranslate('Vtiger.JS_AJAX_ERROR_MSG') });
				return;
			}

			let currentRow = tblUserList.find('tbody tr[data-user-id="'+ userId +'"]');

			// Replace row content if it exist
			if (currentRow[0] != null) {
				currentRow.replaceWith(res);
			}
			// Otherwise, insert new row
			else {
				tblUserList.find('tbody').append(res);
			}
		});
	},

	// Display quota error message on each row that has total count > quota limit
	checkQuotaOnTableUserList: function (form, tblUserList) {
		let distributionOptions = TelesalesCampaignUtils.getDistributionOptions(form);
		if (!distributionOptions.apply_quota || distributionOptions.quota_limit <= 0) return;
		let outOfQuotaMsg = app.vtranslate('Campaigns.JS_TELESALES_CAMPAIGN_OUT_OF_QUOTA_ERROR_MSG');

		tblUserList.find('tbody tr').each(function () {
			let total = app.unformatCurrencyToUser($(this).find('.all-distributed-count').text());

			if (total > distributionOptions.quota_limit) {
				$(this).find('.error-msg').text(outOfQuotaMsg);
			}
			else {
				$(this).find('.error-msg').text('');
			}
		});
	},

	renderStep2: function (form) {
		let tblUserList = form.find('#tbl-user-list');
		this.checkQuotaOnTableUserList(form, tblUserList);
	},

	renderStep3: function (form) {
		let self = this;

		TelesalesCampaignUtils.getDistributableCustomersCount(form, function (distributableCustomersCount) {
			self.showDistributableCustomersCount(form, distributableCustomersCount);
			self.renderTableManualDistrubtion(form, distributableCustomersCount);
		});
	},

	showDistributableCustomersCount: function (form, distributableCustomersCount) {
		let container = form.find('.step3');
		container.find('#distributable-count').text(distributableCustomersCount);
	},

	renderTableManualDistrubtion: function (form, distributableCustomersCount) {
		let selectedUsers = TelesalesCampaignUtils.getSelectedUsers(form);
		let tblManualDistribution = form.find('#tbl-manual-distribution');
		tblManualDistribution.find('tbody').html('');
		let totalCurrentData = 0;
		
		for (var i = 0; i < selectedUsers.length; i++) {
			let userInfo = selectedUsers[i];
			// Modified By Vu Mai on 2022-12-08 to restyle according to mockup
			let rowHtml = `<tr>
				<td>${userInfo.name}</td>
				<td class="text-right"><span class="current-data">${userInfo.statistics.all_distributed_count}</span></td>
				<td class="text-right">
					<input type="number" name="${userInfo.id}" value="0" class="inputElement quota-percent text-right" data-rule-required="true" data-rule-number="true" data-rule-min="0" data-rule-max="100" />
				</td>
				<td class="text-right">
					<input type="number" name="${userInfo.id}" value="0" class="inputElement quota text-right" data-rule-required="true" data-rule-number="true" data-rule-min="0" />
				</td>
				<td class="text-right">
					<span class="final-data">${userInfo.statistics.all_distributed_count}</span>
					<div class="error-msg mt-2 no-wrap"></div>
				</td>
			</tr>`;
			// End Vu Mai

			tblManualDistribution.find('tbody').append(rowHtml);
			totalCurrentData += parseInt(userInfo.statistics.all_distributed_count);
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

		// Display total row
		tblManualDistribution.find('#total-current-data').text(totalCurrentData);
		tblManualDistribution.find('#total-customers').text(distributableCustomersCount);
		tblManualDistribution.find('#total-final-data').text(totalCurrentData);
	},
	// End Vu Mai

	renderStep4: function (form) {
		TelesalesCampaignUtils.saveHiddenInputs(form);
		TelesalesCampaignUtils.showEstimationResult(form);
	},
});