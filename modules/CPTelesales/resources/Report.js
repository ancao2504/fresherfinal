/*
	File: Report.js
	Author: Vu Mai
	Date: 2022-11-28
	Purpose: Telesales Report UI handle
*/

CustomView_BaseController_Js('CPTelesales_Report_Js', {}, {
	registerEvents: function () {
		this._super();
		this.registerEventInit();
	},

	registerEventInit: function () {
		let self = this;
		let page = this.getPage();
		let record = this.getRecord();

		// Init dropdown field
		page.find('.dropdown-filter').select2();

		// Get date from and date to
		let dateFrom = page.find('input[name="date_from"]').val();
		let dateTo = page.find('input[name="date_to"]').val();

		// First load value
		self.getReportStatistics('all', dateFrom, dateTo);
		self.getTelesalesSummaryByEmployee(dateFrom, dateTo);
		self.getCustomerStatusByEmployee(dateFrom, dateTo);

		// Handle event change campaign filter
		page.find('select.campaigns-filter').on('change', function () {
			let campaignId = $(this).val();
			if (campaignId == '') return;
			let href = `?module=CPTelesales&view=Report&record=${campaignId}`;
			window.location.href = href;
		});

		// Handle event change date filter
		page.find('input.dateField').on('change', function () {
			dateFrom = page.find('input[name="date_from"]').val();
			dateTo = page.find('input[name="date_to"]').val();
			let userFilter = page.find('select.statistics-filter').val();

			// Validate date range
			if (!self.validateDateRange(dateFrom, dateTo)) {
				return;
			}

			self.getReportStatistics(userFilter, dateFrom, dateTo);
			self.getTelesalesSummaryByEmployee(dateFrom, dateTo);
			self.getCustomerStatusByEmployee(dateFrom, dateTo);
		});

		// Handle print report button click
		page.find('#print-report-btn').on('click', function () {
			page.print({
				globalStyles : true,
				stylesheet : ['modules/CPTelesales/resources/Report.css', 'layouts/v7/resources/custom.css'],
				rejectWindow : true,
				noPrintSelector : '.no-print',
				iframe : true,
				append : null,
				prepend : null,
				size: 'landscape'
			});
		});

		// Handle event change employee filter
		page.on('change', '.statistics-filter', function () {
			let user_filter = $(this).val();

			// Validate date range
			if (!self.validateDateRange(dateFrom, dateTo)) {
				return;
			}

			self.getReportStatistics(user_filter, dateFrom, dateTo);
		});
	},

	getPage: function () {
		return $('#report-page');
	},

	getRecord: function () {
		return $('input[name="record"]').val();
	},

	validateDateRange: function (dateFrom, dateTo) {
		let page = this.getPage();
		let record = this.getRecord();
		let dateFormat = page.find('.dateField').attr('data-date-format');
		let formatedDateFrom = dateFrom;
		let formatedDateTo = dateTo;

		// Format date to compare
		if (dateFormat == "dd-mm-yyyy") {
			formatedDateFrom = dateFrom.split("-").reverse().join("-");
			formatedDateTo = dateTo.split("-").reverse().join("-");
		}

		let params = {
			module: 'CPTelesales',
			action: 'TelesalesAjax',
			mode: 'getCampaignInfo',
			record: record,
		};

		app.request.post({ data: params })
		.then(function (err, data) {
			if (err) {
				app.helper.showErrorNotification({ 'message': err.message });
				return;
			}

			// Format date to assign value
			if (dateFormat == "dd-mm-yyyy") {
				dateFromValue = data.start_date.split("-").reverse().join("-");
				dateToValue = data.end_date.split("-").reverse().join("-");
			}

			if (formatedDateFrom < data.start_date || formatedDateFrom > data.end_date) {
				app.helper.showErrorNotification({ 'message': app.vtranslate('JS_TELESALES_CAMPAIGN_REPORT_DATE_SELECTED_COMPARE_WITH_CAMPAIGN_DATE_RANGE_ERROR_MSG') });
				page.find('input[name="date_from"]').val(dateFromValue);
				return false;
			}

			if (formatedDateTo < data.start_date || formatedDateTo > data.end_date) {
				app.helper.showErrorNotification({ 'message': app.vtranslate('JS_TELESALES_CAMPAIGN_REPORT_DATE_SELECTED_COMPARE_WITH_CAMPAIGN_DATE_RANGE_ERROR_MSG') });
				page.find('input[name="date_to"]').val(dateToValue);
				return false;
			}
		});

		if (formatedDateFrom > formatedDateTo) {
			app.helper.showErrorNotification({ 'message': app.vtranslate('JS_TELESALES_CAMPAIGN_REPORT_DATE_RANGE_ERROR_MSG') });
			page.find('input[name="date_from"]').focus();
			return false;
		}

		return true;
	},

	getReportStatistics: function(user, dateFrom, dateTo) {
		let page = this.getPage();
		let record = this.getRecord();
		app.helper.showProgress();

		let params = {
			module: 'CPTelesales',
			view: 'ReportAjax',
			mode: 'getReportStatistics',
			record: record,
			user_filter: user,
			date_from: dateFrom,
			date_to: dateTo,
		}

		app.request.post({ data: params })
		.then(function (err, data) {
			app.helper.hideProgress();

			if (err) {
				app.helper.showErrorNotification({ 'message': err.message });
				return;
			}

			if (data) {
				page.find('#report-statistics-container').html('');
				page.find('#report-statistics-container').html(data);
				page.find('#report-statistics-container .dropdown-filter').select2();
				vtUtils.enableTooltips();
			}
		});
	},

	getTelesalesSummaryByEmployee: function(dateFrom, dateTo) {
		let page = this.getPage();
		let record = this.getRecord();
		app.helper.showProgress();

		let params = {
			module: 'CPTelesales',
			view: 'ReportAjax',
			mode: 'getTelesalesSummaryByEmployee',
			record: record,
			date_from: dateFrom,
			date_to: dateTo,
		}

		app.request.post({ data: params })
		.then(function (err, data) {
			app.helper.hideProgress();

			if (err) {
				app.helper.showErrorNotification({ 'message': err.message });
				return;
			}

			if (data) {
				page.find('#telesales-summary-container').html('');
				page.find('#telesales-summary-container').html(data);
				vtUtils.enableTooltips();
			}
		});
	},

	getCustomerStatusByEmployee: function(dateFrom, dateTo) {
		let page = this.getPage();
		let record = this.getRecord();
		app.helper.showProgress();

		let params = {
			module: 'CPTelesales',
			view: 'ReportAjax',
			mode: 'getCustomerStatusByEmployee',
			record: record,
			date_from: dateFrom,
			date_to: dateTo,
		}

		app.request.post({ data: params })
		.then(function (err, data) {
			app.helper.hideProgress();

			if (err) {
				app.helper.showErrorNotification({ 'message': err.message });
				return;
			}

			if (data) {
				page.find('#customer-status-by-employee-container').html('');
				page.find('#customer-status-by-employee-container').html(data);
			}
		});
	},
});