/*
	File: TelesalesCampaignUtils.js
	Author: Hieu Nguyen
	Date: 2022-11-09
	Purpose: provide util functions in JS for new/edit Telesales Campaign forms
*/

window.TelesalesCampaignUtils = {

	getFormType: function (form) {
		if (form.attr('id') == 'EditView') {
			return 'New';
		}

		return 'Edit';
	},

	getSelectedMKTListIds: function (form) {
		let mktListRows = form.find('#tbl-mkt-lists').find('tbody tr');
		let selectedMktListIds = $.map(mktListRows, (row) => { return $(row).data('id') });
		return selectedMktListIds;
	},

	checkData: function (form) {
		let mktListIds = this.getSelectedMKTListIds(form);

		// Call ajax to get duplicates result
		app.helper.showProgress();
		let params = {
			module: 'Campaigns',
			view: 'TelesalesAjax',
			mode: 'getDataStatistics',
			mkt_list_ids: mktListIds,
			campaign_id: app.getRecordId(),
		};

		app.request.post({ data: params })
		.then((err, res) => {
			app.helper.hideProgress();

			if (err) {					
				app.helper.showErrorNotification({ message: app.vtranslate('Vtiger.JS_AJAX_ERROR_MSG') });
				return;
			}

			form.find('#data-statistics').html(res);
			vtUtils.enableTooltips();
		});
	},

	getSelectedUsers: function (form) {
		let selectedUsers = [];

		if (this.getFormType(form) == 'New') {
			let selectedUserTags = form.find('[name="users"]').select2('data');

			for (var i = 0; i < selectedUserTags.length; i++) {
				let tagInfo = selectedUserTags[i];
				selectedUsers.push({ id: tagInfo.id.replace('Users:', ''), name: tagInfo.text });
			}
		}
		else {
			form.find('#tbl-user-list').find('tbody tr').each(function () {
				let userInfo = $(this).data('userInfo');
				selectedUsers.push(userInfo);
			});
		}

		return selectedUsers;
	},

	getSelectedUserIds: function (form) {
		let selectedUsers = TelesalesCampaignUtils.getSelectedUsers(form);
		let selectedUserIds = $.map(selectedUsers, (info) => { return info.id.replace('Users:', '') });
		return selectedUserIds;
	},

	getUserInfoWithStatistics: function (userId, callback) {
		// Call ajax to get user info
		app.helper.showProgress();
		let params = {
			module: 'Campaigns',
			action: 'TelesalesAjax',
			mode: 'getUserInfoWithStatistics',
			user_id: userId,
			campaign_id: app.getRecordId(),
		};

		app.request.post({ data: params })
		.then((err, res) => {
			app.helper.hideProgress();

			if (err) {					
				app.helper.showErrorNotification({ message: app.vtranslate('Vtiger.JS_AJAX_ERROR_MSG') });
				return;
			}

			if (typeof callback == 'function') {
				callback(res.user_info);
			}
		});
	},

	getTotalCustomersCount: function (form) {
		let mktListRows = form.find('#tbl-mkt-lists').find('tbody tr');
		let totalCount = 0;

		mktListRows.each(function () {
			totalCount += parseInt($(this).find('.customers-count').text());
		});

		return totalCount;
	},

	getDistributableCustomersCount: function (form, callback) {
		let mktListIds = this.getSelectedMKTListIds(form);

		// Call ajax to get result
		app.helper.showProgress();
		let params = {
			module: 'Campaigns',
			action: 'TelesalesAjax',
			mode: 'getDistributableCustomersCount',
			mkt_list_ids: mktListIds,
			campaign_id: app.getRecordId(),
		};

		app.request.post({ data: params })
		.then((err, res) => {
			app.helper.hideProgress();

			if (err) {					
				app.helper.showErrorNotification({ message: app.vtranslate('Vtiger.JS_AJAX_ERROR_MSG') });
				return;
			}

			if (typeof callback == 'function') {
				callback(res.distributable_count);
			}
		});
	},

	reCalcTotalManualDistributions: function (form) {
		let self = this;
		let distributionOptions = this.getDistributionOptions(form);
		let tblManualDistribution = form.find('#tbl-manual-distribution');
		tblManualDistribution.find('.input-error').removeClass('input-error');
		tblManualDistribution.find('.error-msg').text('');

		// Calculate total
		let totalDistributedValue = 0;
		let totalFinalValue = 0;

		tblManualDistribution.find('tbody').find('input.quota').each(function () {
			let row = $(this).closest('tr');
			let currentValue = 0;

			if (self.getFormType(form) == 'Edit') {
				currentValue = parseInt(row.find('.current-data').text());
			}

			let newValue = 0;
			
			if ($(this).val().trim() != '') {
				newValue = parseInt($(this).val());
			}

			let finalValue = currentValue + newValue;

			// Update final value
			row.find('.final-data').text(finalValue);

			// Check quota error
			if (distributionOptions['apply_quota'] && distributionOptions['quota_limit'] > 0) {
				if (finalValue > distributionOptions['quota_limit']) {
					$(this).addClass('input-error');
					row.find('.error-msg').text(app.vtranslate('Campaigns.JS_TELESALES_CAMPAIGN_OUT_OF_QUOTA_ERROR_MSG'));
				}
			}

			totalDistributedValue += newValue;
			totalFinalValue += finalValue;
		});

		tblManualDistribution.find('#total-distributed').text(totalDistributedValue);
		tblManualDistribution.find('#total-final-data').text(totalFinalValue);

		// Check total error
		let totalCustomersCount = parseInt(tblManualDistribution.find('#total-customers').text());

		if (totalDistributedValue > totalCustomersCount) {
			tblManualDistribution.find('#total-error-msg').text(app.vtranslate('Campaigns.JS_TELESALES_CAMPAIGN_OUT_OF_TOTAL_CUSTOMERS_ERROR_MSG'));
			tblManualDistribution.find('#total-distributed').addClass('redColor');
		}
		else {
			tblManualDistribution.find('#total-error-msg').text('');
			tblManualDistribution.find('#total-distributed').removeClass('redColor');
		}
	},

	getDistributionOptions: function (form) {
		let self = this;

		let distributionOptions = {
			apply_quota: form.find('[name="apply_quota"]').is(':checked'),
			distribution_method: form.find('[name="distribution_method"]:checked').val(),
			auto_distribute_new_customers_added_later: form.find('[name="auto_distribute_new_customers_added_later"]').is(':checked'),
		};

		if (distributionOptions.apply_quota) {
			distributionOptions.quota_limit = parseInt(form.find('[name="quota_limit"]').val());
		}

		if (distributionOptions.distribution_method == 'auto') {
			distributionOptions.auto_distribution_priority = form.find('[name="auto_distribution_priority"]:checked').val();
		}
		else {
			let tblManualDistribution = form.find('#tbl-manual-distribution');
			let manualDistributionConfig = {};

			tblManualDistribution.find('tbody tr').each(function () {
				let quotaInput = $(this).find('input.quota');
				let userId = quotaInput.attr('name');
				let quota = 0;
				
				if (self.getFormType(form) == 'New') {
					quota = parseInt(quotaInput.val());
				}
				else {
					// Quota is applied for both new and old data
					quota = parseInt($(this).find('.final-data').text());
				}

				manualDistributionConfig[userId] = quota;
			});

			distributionOptions.manual_distribution_config = manualDistributionConfig;
		}

		return distributionOptions;
	},

	checkTelesalesConfig: function (campaignPurpose, campaignPurposeText, successCallback) {
		// Call ajax to get telesales config
		app.helper.showProgress();
		let params = {
			module: 'Campaigns',
			action: 'TelesalesAjax',
			mode: 'getTelesalesConfig'
		};

		app.request.post({ data: params })
		.then((err, res) => {
			app.helper.hideProgress();

			if (err) {					
				app.helper.showErrorNotification({ message: app.vtranslate('Vtiger.JS_AJAX_ERROR_MSG') });
				return;
			}

			// Config is valid
			if (res && res.config && res.config.customer_in_campaign_status && res.config.customer_in_campaign_status[campaignPurpose]) {
				if (typeof successCallback == 'function') {
					successCallback();
				}
			}
			// Config is not valid
			else {
				let errorMsg = app.vtranslate('Campaigns.JS_TELESALES_CAMPAIGN_INVALID_CONFIG_ERROR_MSG', { 'purpose': campaignPurposeText });
				app.helper.showAlertBox({ message: errorMsg });
			}				
		});
	},

	saveHiddenInputs: function (form) {
		let mktListIds = this.getSelectedMKTListIds(form);
		let selectedUserIds = this.getSelectedUserIds(form);
		let distributionOptions = this.getDistributionOptions(form);

		form.find('[name="mkt_list_ids"]').val(JSON.stringify(mktListIds));
		form.find('[name="selected_user_ids"]').val(JSON.stringify(selectedUserIds));
		form.find('[name="distribution_options"]').val(JSON.stringify(distributionOptions));
	},

	showEstimationResult: function (form) {
		// Call ajax to get estimation result
		app.helper.showProgress();
		let params = {
			module: 'Campaigns',
			view: 'TelesalesAjax',
			mode: 'getEstimationResult',
			campaign_id: app.getRecordId(),
			mkt_list_ids: form.find('[name="mkt_list_ids"]').val(),
			selected_user_ids: form.find('[name="selected_user_ids"]').val(),
			distribution_options: form.find('[name="distribution_options"]').val()
		};

		app.request.post({ data: params })
		.then((err, res) => {
			app.helper.hideProgress();

			if (err) {					
				app.helper.showErrorNotification({ message: app.vtranslate('Vtiger.JS_AJAX_ERROR_MSG') });
				return;
			}

			form.find('#estimation-result').html(res);
			vtUtils.enableTooltips();
		});
	},

	confirmSavingWithSkippedData: function (form, skippedCount) {
		form.find('.saveButton').attr('disabled', false);	// Disabled button can not be triggered when the config is valid

		bootbox.confirm({
			buttons: {
				confirm: {
					label: app.vtranslate('Vtiger.JS_NEXT'),
					className: 'confirm-box-ok confirm-box-btn-pad btn-danger'
				},
				cancel: {
					label: app.vtranslate('Vtiger.JS_CANCEL'),
					className: 'btn-default confirm-box-btn-pad pull-right'
				},
			},
			title: app.vtranslate('Campaigns.JS_TELESALES_CAMPAIGN_WIZARD_SAVE_WITH_SKIPPED_DATA_CONFIRM_TITLE'),
			message: app.vtranslate('Campaigns.JS_TELESALES_CAMPAIGN_WIZARD_SAVE_WITH_SKIPPED_DATA_CONFIRM_MSG', { 'skipped_count': skippedCount }),
			htmlSupportEnable: true,
			callback: function (result) {
				if (result) {
					window.user_confirmed_saving_with_skipped_data = true;
					form.submit();
				}
			}
		});
	}
}