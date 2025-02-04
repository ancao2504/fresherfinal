/*
	File: MauticHelper.js
	Author: Hieu Nguyen
	Date: 2021-11-25
	Purpose: provide util functions for Mautic Integration on the UI
	Credits: Merged from 2 original files AddToMauticSegment.js and UpdateMauticStage.js by Phuc Lu
*/


window.MauticHelper = {
	// Get current ListView params
	getListViewParams: function () {
		var listViewController = window.app.controller();
		var listSelectParams = listViewController.getListSelectAllParams(true);

		if (listSelectParams) {
			var listParams = listViewController.getDefaultParams();
			var params = jQuery.extend(listParams, listSelectParams);
			return params;
		}
		else {
			listViewController.noRecordSelectedAlert();
			return null;
		}
	},
	// Create a new segment in Mautic
	addNewSegment: function (element) {
		var element = jQuery(element);

		// Change div to form
		var declareModal = $('#div-add-new-mautic-segment').clone(true, true);
		var formHtml = '<form class="form-horizontal formAddNewMauticSegment" method="POST">' + declareModal.find('.formAddNewMauticSegment').html() + '</form>';  
		declareModal.find('.formAddNewMauticSegment').remove();
		declareModal.append(formHtml);

		var callBackFunction = function (data) {       
			data.find('#div-add-new-mautic-segment').removeClass('hide');        
			var form = data.find('.formAddNewMauticSegment'); 

			var controller = Vtiger_Edit_Js.getInstance();
			controller.registerBasicEvents(form);

			var params = {
				submitHandler: function(form) {               
					app.helper.showProgress();

					var form = $(form);
					var formData = form.serializeFormData();  
					formData['module'] = 'CPMauticIntegration';
					formData['action'] = 'MauticAjax';
					formData['mode'] = 'addNewSegment';

					app.request.post({ 'data': formData })
					.then(function (error, data) {
						app.helper.hidePopup();
						app.helper.hideProgress();

						if (error || !data.success) {
							app.helper.showErrorNotification({ message: data.message ? data.message : app.vtranslate('JS_ERROR') });	// Modified by Hieu Nguyen on 2021-11-24 to display error message from server response
							return;
						}

						app.helper.showSuccessNotification({ message: app.vtranslate('JS_SUCCESS') });
						element.prev().append("<option value='" + data.id + "'>" + data.name + "</option>");
						element.prev().val(data.id);
						element.prev().select2('destroy');
						element.prev().select2();
					});
				}
			};

			// Form validation
			form.vtValidate(params);
		}

		var modalParams = {
			cb: callBackFunction
		}

		app.helper.showPopup(declareModal, modalParams);
	},
	// Add customers into selected Segment in Mautic
	addToMauticSegment: function () {
		let self = this;
		app.helper.showProgress();
		
		var params = {
			'module': 'CPMauticIntegration',
			'view': 'MauticAjax',
			'mode': 'getAddToMauticSegmentModal',
			'related_module': app.getModuleName(),
		}

		app.request.post({ 'data': params })
		.then(function (error, data) {
			app.helper.hideProgress();

			if (error || !data.success) {
				app.helper.showErrorNotification({ message: data.message ? data.message : app.vtranslate('JS_ERROR') });	// Modified by Hieu Nguyen on 2021-11-24 to display error message from server response
				return;
			}

			var declareModal = $(data.content);

			var callBackFunction = function (data) {       
				data.find('#div-add-to-mautic-segment').removeClass('hide');        
				var form = data.find('.formAddToMauticSegment');
				data.find('.slc_segment').select2();
		
				var controller = Vtiger_Edit_Js.getInstance();
				controller.registerBasicEvents(form);
		
				var params = {
					submitHandler: function (form) {
						app.helper.showProgress();
		
						// Prepare submit data
						var form = $(form);
						var formData = form.serializeFormData();  
						formData['module'] = 'CPMauticIntegration';
						formData['action'] = 'MauticAjax';
						formData['mode'] = 'addRecordsToSegment';
						formData['list_params'] = self.getListViewParams();
		
						// Make ajax request
						app.request.post({ 'data': formData })
						.then(function (error,data) {
							app.helper.hideProgress();

							if (error || !data.success) {
								app.helper.showErrorNotification({ message: data.message ? data.message : app.vtranslate('JS_ERROR') });	// Modified by Hieu Nguyen on 2021-11-24 to display error message from server response
								return;
							}

							app.helper.showSuccessNotification({ message: data.message });
							app.helper.hideModal();
						});
					}
				};
				
				form.vtValidate(params);
			}
		
			var modalParams = {
				cb: callBackFunction
			}
		
			app.helper.showModal(declareModal, modalParams);
		});
	},
	// Create a new stage in Mautic
	addNewStage: function(element) {
		var element = jQuery(element);

		// Change div to form
		var declareModal = $('#div-add-new-mautic-stage').clone(true, true);
		var formHtml = '<form class="form-horizontal formAddNewMauticStage" method="POST">' + declareModal.find('.formAddNewMauticStage').html() + '</form>';  
		declareModal.find('.formAddNewMauticStage').remove();
		
		declareModal.append(formHtml);

		var callBackFunction = function (data) {       
			data.find('#div-add-new-mautic-stage').removeClass('hide');        
			var form = data.find('.formAddNewMauticStage'); 

			var controller = Vtiger_Edit_Js.getInstance();
			controller.registerBasicEvents(form);

			var params = {
				submitHandler: function (form) {               
					app.helper.showProgress();

					var form = $(form);
					var formData = form.serializeFormData();  
					formData['module'] = 'CPMauticIntegration';
					formData['action'] = 'MauticAjax';
					formData['mode'] = 'addNewStage';

					app.request.post({ 'data': formData })
					.then(function (error, data) {
						app.helper.hidePopup();
						app.helper.hideProgress();

						if (error || !data.success) {
							app.helper.showErrorNotification({ message: data.message ? data.message : app.vtranslate('JS_ERROR') });	// Modified by Hieu Nguyen on 2021-11-24 to display error message from server response
							return;
						}

						app.helper.showSuccessNotification({ message: app.vtranslate('JS_SUCCESS') });
						element.prev().append("<option value='" + data.id + "'>" + data.name + "</option>");
						element.prev().val(data.id);
						element.prev().select2('destroy');
						element.prev().select2();
					});
				}
			};

			// Form validation
			form.vtValidate(params);
		}

		var modalParams = {
			cb: callBackFunction
		}

		app.helper.showPopup(declareModal, modalParams);
	},
	// Update customers to selected Stage in Mautic
	updateMauticStage: function () {
		let self = this;
		app.helper.showProgress();
		
		var params = {
			'module': 'CPMauticIntegration',
			'view': 'MauticAjax',
			'mode': 'getUpdateMauticStageModal',
			'related_module': app.getModuleName(),
		}

		app.request.post({ 'data': params })
		.then(function (error, data) {
			app.helper.hideProgress();

			if (error || !data.success) {
				app.helper.showErrorNotification({ message: data.message ? data.message : app.vtranslate('JS_ERROR') });	// Modified by Hieu Nguyen on 2021-11-24 to display error message from server response
				return;
			}

			var declareModal = $(data.content);

			var callBackFunction = function (data) {       
				data.find('#div-update-mautic-stage').removeClass('hide');        
				var form = data.find('.formUpdateMauticStage');
				data.find('.slc_stage').select2();

				var controller = Vtiger_Edit_Js.getInstance();
				controller.registerBasicEvents(form);

				var params = {
					submitHandler: function (form) {
						app.helper.showProgress();

						var form = $(form);
						var formData = form.serializeFormData();  
						formData['module'] = 'CPMauticIntegration';
						formData['action'] = 'MauticAjax';
						formData['mode'] = 'updateRecordsStage';
						formData['list_params'] = self.getListViewParams();

						app.request.post({ 'data': formData })
						.then(function (error, data) {
							app.helper.hideProgress();

							if (error || !data.success) {
								app.helper.showErrorNotification({ message: data.message ? data.message : app.vtranslate('JS_ERROR') });	// Modified by Hieu Nguyen on 2021-11-24 to display error message from server response
								return;
							}

							app.helper.showSuccessNotification({ message: data.message });
							app.helper.hideModal();
						});
					}
				};
				
				form.vtValidate(params);
			}

			var modalParams = {
				cb: callBackFunction
			}

			app.helper.showModal(declareModal, modalParams);
		});
	},
}