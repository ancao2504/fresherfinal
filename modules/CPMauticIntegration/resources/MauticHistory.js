/*
	File: MauticHistory.js
	Author: Hieu Nguyen
	Date: 2021-12-01
	Purpose: handle logic on Mautic History subpanel
*/

jQuery(function ($) {
	// When page load
	let activeSubpanel = getActiveSubpanel();
	
	if (activeSubpanel.data('module') == 'CPMauticContactHistory') {
		initMauticHistorySubpanel();
	}

	// When user switch subpanel
	app.event.on('post.relatedListLoad.click', function (data) {
		let activeSubpanel = getActiveSubpanel();

		if (activeSubpanel.data('module') == 'CPMauticContactHistory') {
			initMauticHistorySubpanel();
		}
	});

	function getActiveSubpanel () {
		return $('.detailview-content').find('.nav-tabs').find('li.active');
	}

	function initMauticHistorySubpanel () {
		// Render button Sync Mautic History
		if ($('#is_converted').val() == 'true') {
			return;	// Do not show button Sync Mautic History for converted Target and Lead
		}

		let toolbarContainer = $('.relatedContainer').find('.relatedHeader .btn-toolbar.col-lg-6');
		if (toolbarContainer.find('#btn-sync')['0'] != null) return;

		let btnTitle = app.vtranslate('CPMauticIntegration.JS_SYNC_MAUTIC_HISTORY_BUTTON_TITLE');
		let tooltip = app.vtranslate('CPMauticIntegration.JS_SYNC_MAUTIC_HISTORY_HINT');
		let btnSyncHistoryTemplate = '<button type="button" id="btn-sync" class="btn btn-primary">'+ btnTitle +'</button>&nbsp;&nbsp;<i class="far fa-question-circle" data-toggle="tooltip" title="'+ tooltip +'"></i>';
		toolbarContainer.append(btnSyncHistoryTemplate);

		// Handle click on button Sync Mautic History
		toolbarContainer.find('#btn-sync').on('click', function () {
			bootbox.confirm({
				message: app.vtranslate('CPMauticIntegration.JS_SYNC_MAUTIC_HISTORY_CONFIRM_MSG'),
				callback: function (result) {
					if (result) {
						syncMauticHistory();
					}
				}
			});
		});
	}

	function syncMauticHistory() {
		app.helper.showProgress();

		var params = {};
		params['module'] = 'CPMauticIntegration';
		params['action'] = 'MauticAjax';
		params['mode'] = 'syncMauticHistory';
		params['customer_id'] = app.getRecordId();
		params['customer_type'] = app.getModuleName();

		app.request.post({ 'data': params })
		.then(function (error, data) {
			app.helper.hideProgress();

			if (error || !data.success) {
				app.helper.showErrorNotification({ message: data.message ? data.message : app.vtranslate('CPMauticIntegration.JS_SYNC_MAUTIC_HISTORY_ERROR_MSG') });
				return;
			}

			app.helper.showSuccessNotification({ message: app.vtranslate('CPMauticIntegration.JS_SYNC_MAUTIC_HISTORY_SUCCESS_MSG') });
			getActiveSubpanel().trigger('click');
		});
	}
});