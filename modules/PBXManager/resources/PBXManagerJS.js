/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

// Refactored and removed unused functions by Hieu Nguyen on 2021-12-15
window.Vtiger_PBXManager_Js = {

	// Modified by Hieu Nguyen on 2019-12-31 to pass the call log id to update
	registerPBXOutboundCall: function (element, phoneNumber, recordId, callLogId) {
		// Added by Phu Vo on 2020.02.18 to prevent register outbound when processing another outbound
		if (CallPopup.has('PROCESSING')) {
			const errorMessage = app.vtranslate('PBXManager.JS_CALL_POPUP_PROCESSING_OUTBOUND_ERROR_MSG');
			return app.helper.showErrorNotification({ message: errorMessage });
		}
		// End Phu Vo

		// WebRTC suported call center
		if (CallCenterClient.webPhone) {
			// User prefer WebPhone than SIP Phone
			if (_CALL_CENTER_PREFERRED_OUTBOUND_DEVICE == 'web_phone') {
				Vtiger_PBXManager_Js.makeOutboundCall(element, phoneNumber, recordId, callLogId, CallCenterClient.webPhone);
			}
			// User prefer SIP Phone than SIP WebPhone
			else {
				Vtiger_PBXManager_Js.makeOutboundCall(element, phoneNumber, recordId, callLogId);
			}
		}
		// Classic call center
		else {
			Vtiger_PBXManager_Js.makeOutboundCall(element, phoneNumber, recordId, callLogId);
		}
	},

	// Added by Hieu Nguyen on 2020-02-18 to handle make call with phone selector
	makeCallWithPhoneSelector: function (element, customerId, customerName, phoneNumbers, callId) {
		// Make call right away if the customer has only 1 phone number
		if (phoneNumbers.length == 1) {
			Vtiger_PBXManager_Js.registerPBXOutboundCall(element, phoneNumbers[0].number, customerId, callId);
		}
		// Show phone number selector if the customer has more than 1 phone number
		else {
			var modalTemplate = $('div.modal-dialog.modal-template-md:first').clone(true, true);

			// Display modal title
			var title = $('#phone-selector-template').data('title');
			modalTemplate.find('.modal-header .pull-left').text(title);

			var selectorContent = $('#phone-selector-template').clone(true, true).removeClass('hide');

			phoneNumbers.forEach((phoneNumber) => {
				var phoneClickLogic = `Vtiger_PBXManager_Js.registerPBXOutboundCall(this, '${phoneNumber.number}', '${customerId}', '${callId}');`;
				var row = `<tr>
						<td>${phoneNumber.field_label}</td>
						<td>${phoneNumber.number}</td>
						<td class="text-center">
							<a href="javascript:void(0);" onclick="${phoneClickLogic}"><i class="far fa-2x fa-phone"></i></a>
						</td>
					</tr>`;

				selectorContent.find('tbody').append(row);
			});

			var callBackFunction = function (data) {
				var form = data.find('form');

				// Populate form content
				var hint = $('#phone-selector-template').data('hint');
				hint = hint.replace('%customer_name', customerName) + '<br/><br/>';

				form.find('.modal-body').append(hint);
				form.find('.modal-body').append(selectorContent);
				form.find('[type="submit"]').remove();
			};

			var modalParams = {
				cb: callBackFunction
			};

			app.helper.showModal(modalTemplate, modalParams);
			return false;
		}

		return false;
	},

	_showHotlineSelector: function (element, callback) {
		let self = this;
		let hotlineSelectorTemplate = jQuery('#click2call-hotline-selector');

		// Render popover
		this._hidePopover();
		element = jQuery(element);
		element.popover('destroy');
	
		let popoverContent = hotlineSelectorTemplate.clone(true, true);
		popoverContent.removeClass('hide');
		popoverContent.find('[type="radio"]:first').attr('checked', true);

		let distanceToRightBorder = ($(window).width() - element.offset().left);
		let insideModal = ($('.modal:visible')[0] != null);

		element.popover({
			'title': 'test',
			'content': popoverContent,
			'html': true,
			'placement': (distanceToRightBorder < 150) ? 'left' : 'right',
			'animation': true,
			'trigger': 'manual',
			'container': (insideModal) ? $('.modal:visible') : element.closest('body')
		});

		setTimeout(() => {
			element.popover('show');

			let activePopover = jQuery('.popover:visible');
			activePopover.addClass('click2call-hotline-selector');
			activePopover.find('.popover-title').text(app.vtranslate('PBXManager.JS_HOTLINE_SELECTOR_TITLE'));
			element.data('popoverId', activePopover.attr('id'));
		}, 100);

		// Handle buttons click event
		if (!this.popoverEventsRegistered) {
			// Handle button submit
			$(document).on('click', '.click2call-hotline-selector .btn-select', function (e) {
				// Hide modal if exist
				if ($('.modal:visible')[0] != null) {
					app.helper.hideModal();
				}

				// Call callback function if exist
				if (typeof callback == 'function') {
					callback($(this).val());
				}

				// Then hide the popover
				self._hidePopover();
			});

			// Handle click outside
			$(document).on('click', function (e) {
				// Hide popover when user clicked outside
				if ($(e.target).closest('.click2call-hotline-selector')[0] == null) {
					self._hidePopover();
				}
				// Stay popover when user clicked inside
				else {
					e.stopPropagation();
				}
			});

			// Set flag to prevent register multiple event handlers
			this.popoverEventsRegistered = true;
		}

		return false;
	},

	_hidePopover() {
		jQuery('.popover.click2call-hotline-selector').each(function () {
			let popoverId = $(this).attr('id');
			jQuery('[aria-describedby="'+ popoverId +'"]').popover('destroy');
		});
	},

	// Modified by Hieu Nguyen on 2021-12-15 to support click2call with hotline selection
	makeOutboundCall: function (element, phoneNumber, recordId, callLogId, webPhone) {
		let self = this;
		self.phoneNumber = phoneNumber;
		self.recordId = recordId;
		self.callLogId = callLogId;
		
		let hotlineSelector = jQuery('#click2call-hotline-selector');
		self.targetRecordId = '';	// To store target record id at ListView

		if (jQuery(element).closest('tr.listViewEntries')[0] != null) {
			self.targetRecordId = jQuery(element).closest('tr.listViewEntries').data('id');
		}

		// Modified by Vu Mai on 2022-10-05 to get right module for popup call.
		self.targetModule = app.module();

		// Get right target module if record was called in Telesales
		if (jQuery(element).closest('tr.listViewEntries')[0] != null) {
			let module = jQuery(element).closest('tr.listViewEntries').attr('data-target-module');

			if (module != null && module != '') {
				self.targetModule = module;
			}
		}

		// Modified By Vu Mai on 2022-11-03 to get right view for popup call
		self.targetView = app.getViewName();

		// Make call using webphone
		if (webPhone) {
			if (hotlineSelector[0] != null) {
				this._showHotlineSelector(element, function (hotlineNumber) {
					self._makeCallUsingWebPhone(webPhone, hotlineNumber, self.phoneNumber, self.recordId, self.callLogId, self.targetRecordId, self.targetModule, self.targetView);
				});
			}
			else {
				this._makeCallUsingWebPhone(webPhone, '', phoneNumber, recordId, callLogId, self.targetRecordId, self.targetModule, self.targetView);
			}
		}
		// Make call using SIP Phone (Softphone or IP Phone)
		else {
			if (hotlineSelector[0] != null) {
				this._showHotlineSelector(element, function (hotlineNumber) {
					self._makeCallUsingSIPPhone(hotlineNumber, self.phoneNumber, self.recordId, self.callLogId, self.targetRecordId, self.targetModule, self.targetView);
				});
			}
			else {
				this._makeCallUsingSIPPhone('', phoneNumber, recordId, callLogId, self.targetRecordId, self.targetModule, self.targetView);
			}
		}
		// End Vu Mai
	},

	// Added by Hieu Nguyen on 2021-12-15 to make call using webphone. Modified by Vu Mai on 2022-10-05 to get right module for popup call. Modified by Vu Mai on 2022-11-03 to get right view for popup call
	_makeCallUsingWebPhone(webPhone, hotlineNumber, phoneNumber, recordId, callLogId, targetRecordId, targetModule, targetView) {
		// Write oubound cache
		app.helper.showProgress();
		let params = {
			module: 'PBXManager',
			action: 'CallPopupAjax',
			mode: 'writeOutboundCache',
			customer_id: recordId,
			customer_number: phoneNumber,
			call_log_id: callLogId,
			target_record_id: targetRecordId,
			target_module: targetModule,	// Added by Vu Mai on 2022-10-05
			target_view: targetView,	// Added by Vu Mai on 2022-11-03
		};

		app.request.post({ data: params })
		.then(function (err, res) {
			app.helper.hideProgress();

			if (err || !res.success) {
				let message = app.vtranslate('PBXManager.JS_MAKE_CALL_ERROR_MSG');
				app.helper.showErrorNotification({ message: message });
				return;
			}

			// Then make call using webphone
			webPhone.makeCall(hotlineNumber, phoneNumber, recordId, callLogId);
		});
	},

	// Renamed from function makeOutboundCall by Hieu Nguyen on 2021-12-15 to be called by condition. Modified by Vu Mai on 2022-10-05 to get right module for popup call. Modified by Vu Mai on 2022-11-03 to get right view for popup call
	_makeCallUsingSIPPhone: function (hotlineNumber, phoneNumber, recordId, callLogId, targetRecordId, targetModule, targetView) {
		app.helper.showProgress(); // Added by Phu Vo on 2020.02.26 to show progress
		let params = {
			module: 'PBXManager',
			action: 'OutgoingCall',
			hotline_number: hotlineNumber, // Added by Hieu Nguyen on 2021-12-15
			phone_number: phoneNumber,
			record_id: recordId,
			call_log_id: callLogId, // Added by Hieu Nguyen on 2019-12-31
			target_record_id: targetRecordId,
			target_module: targetModule,	// Added by Vu Mai on 2022-10-05
			target_view: targetView,	// Added by Vu Mai on 2022-11-03
		};

		app.request.post({ data: params })
		.then(function (err, res) {
			// Modified by Phu Vo on 2020.02.26 handle progress and error
			app.helper.hideProgress();

			if (err) {
				return app.helper.showErrorNotification({ message: err.message });
			}
			// End Phu Vo

			// Modified by Hieu Nguyen on 2020-05-18
			if (res.success) {
				params = {
					title: app.vtranslate('JS_PBX_OUTGOING_SUCCESS'),
					type: 'info'
				};
			}
			else {
				params = {
					title: app.vtranslate('JS_PBX_OUTGOING_FAILURE'),
					type: 'error'
				};
			}
			// End Hieu Nguyen

			Vtiger_Helper_Js.showPnotify(params);
		});
	},
};