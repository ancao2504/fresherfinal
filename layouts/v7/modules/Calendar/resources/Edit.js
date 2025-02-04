/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Edit_Js("Calendar_Edit_Js",{

	uploadAndParse : function() {
		if (Vtiger_Import_Js.validateFilePath()) {
			var form = jQuery("form[name='importBasic']");
			jQuery('[name="mode"]').val('importResult');
			var data = new FormData(form[0]);
			var postParams = {
				data: data,
				contentType: false,
				processData: false
			};
			app.helper.showProgress();
			app.request.post(postParams).then(function(err, response) {
				app.helper.loadPageContentOverlay(response);
				app.helper.hideProgress();
			});
		}
		return false;
	},

	handleFileTypeChange: function() {
		var fileType = jQuery('[name="type"]').filter(':checked').val();
		var currentPage = jQuery('#group2');
		var selectedRecords = jQuery('#group1');

		if(fileType == 'ics') {
			currentPage.prop('disabled', true).prop('checked', false);
			selectedRecords.prop('disabled', true).prop('checked', false);
			jQuery('#group3').prop('checked', true);
		} else {
			currentPage.removeAttr('disabled');
			if (jQuery('.isSelectedRecords').val() == 1) {
				selectedRecords.removeAttr('disabled');
			}
		}
	},

	userChangedTimeDiff:false

},{

	relatedContactElement : false,

	recurringEditConfirmation : false,

	/**
	 * Function to get reference search params
	 */
	getReferenceSearchParams : function(element){
		var tdElement = jQuery(element).closest('td');
		var params = {};
		var previousTd = tdElement.prev();
		var multiModuleElement = jQuery('select.referenceModulesList', previousTd);

		var referenceModuleElement;
		if(multiModuleElement.length) {
			referenceModuleElement = multiModuleElement;
		} else {
			referenceModuleElement = jQuery('input[name="popupReferenceModule"]',tdElement).length ?
										jQuery('input[name="popupReferenceModule"]',tdElement) : jQuery('input.popupReferenceModule',tdElement);
		}
		var searchModule =  referenceModuleElement.val();
		params.search_module = searchModule;
		return params;
	},

	isEvents : function(form) {
		if(typeof form === 'undefined') {
			form = this.getForm();
		}
		var moduleName = form.find('[name="module"]').val();
		if(form.find('.quickCreateContent').length > 0 && form.find('[name="calendarModule"]').val()==='Events') {
			return true;
		}
		if(moduleName === 'Events') {
			return true;
		}
		return false;
	},

	addInviteesIds : function(form) {
		var thisInstance = this;
		if(thisInstance.isEvents(form)) {
			var inviteeIdsList = jQuery('#selectedUsers').val();
			if(inviteeIdsList) {
				inviteeIdsList = jQuery('#selectedUsers').val().join(';')
			}
			jQuery('<input type="hidden" name="inviteesid" />').
					appendTo(form).
					val(inviteeIdsList);
		}
	},

	resetRecurringDetailsIfDisabled : function(form) {
		var recurringCheck = form.find('input[name="recurringcheck"]').is(':checked');
		//If the recurring check is not enabled then recurring type should be --None--
		if(!recurringCheck) {
			jQuery('#recurringType').append(jQuery('<option value="--None--">None</option>')).val('--None--');
		}
	},

	registerRecurringEditOptions : function(e,form,InitialFormData) {
		var currentFormData = form.serialize();
		var editViewContainer = form.closest('.editViewPageDiv').length;
		var recurringEdit = form.find('.recurringEdit').length;
		var recurringEditMode = form.find('[name="recurringEditMode"]');
		var recurringCheck = form.find('input[name="recurringcheck"]').is(':checked');

		if(editViewContainer && InitialFormData === currentFormData && recurringEdit) {
			recurringEditMode.val('current');
		} else if(editViewContainer && recurringCheck && recurringEdit && InitialFormData !== currentFormData) {
			e.preventDefault();

			var recurringEventsUpdateModal = form.find('.recurringEventsUpdation');
			var clonedContainer = recurringEventsUpdateModal.clone(true, true);

            // Added by Hieu Nguyen on 2020-03-19 to prevent dismiss the popup as this process can not be undone
            clonedContainer.find('button.close').remove();
            // End Hieu Nguyen

			var callback = function(data) {
				var modalContainer = data.find('.recurringEventsUpdation');
				modalContainer.removeClass('hide');
				modalContainer.on('click', '.onlyThisEvent', function() {
					recurringEditMode.val('current');
					app.helper.hideModal();
					form.vtValidate({
						submitHandler : function() {
                            window.onbeforeunload = null;   // Added by Hieu Nguyen on 2020-03-17 to prevent showing leave page confirm message
							return true;
						}
					});
					form.submit();
				});
				modalContainer.on('click', '.futureEvents', function() {
					recurringEditMode.val('future');
					app.helper.hideModal();
					form.vtValidate({
						submitHandler : function() {
                            window.onbeforeunload = null;   // Added by Hieu Nguyen on 2020-03-17 to prevent showing leave page confirm message
							return true;
						}
					});
					form.submit();
				});
				modalContainer.on('click', '.allEvents', function() {
					recurringEditMode.val('all');
					app.helper.hideModal();
					form.vtValidate({
						submitHandler : function() {
                            window.onbeforeunload = null;   // Added by Hieu Nguyen on 2020-03-17 to prevent showing leave page confirm message
							return true;
						}
					});
					form.submit();
				});
			};

			app.helper.showModal(clonedContainer, {
                'backdrop': 'static',   // Added by Hieu Nguyen on 2020-03-19 to prevent dismiss the popup when clicking outside
				'cb' : callback
			});
		}
	},

	registerRecordPreSaveEvent : function(form) {
		var thisInstance = this;
		if(typeof form === "undefined") {
			form = this.getForm();
		}
		var InitialFormData = form.serialize();

        // Modified by Hieu Nguyen on 2020-03-24 to get correct logic for recurring edit check
        setTimeout(() => {
            InitialFormData = form.serialize();
        }, 500);

		app.event.one(Vtiger_Edit_Js.recordPresaveEvent,function(e) {
            
            thisInstance.resetRecurringDetailsIfDisabled(form);
            thisInstance.registerRecurringEditOptions(e, form, InitialFormData);
            
		});
        // End Hieu Nguyen
	},

	registerTimeStartChangeEvent : function(container) {
		container.on('changeTime', 'input[name="time_start"]', function() {
			var startDateElement = container.find('input[name="date_start"]');
			var startTimeElement = container.find('input[name="time_start"]');
			var endDateElement = container.find('input[name="due_date"]');
			var endTimeElement = container.find('input[name="time_end"]');

			var activityType = container.find('[name="activitytype"]').val();

			var momentFormat = vtUtils.getMomentCompatibleDateTimeFormat();
			var m = moment(startDateElement.val() + ' ' + startTimeElement.val(), momentFormat);

			var minutesToAdd = container.find('input[name="defaultOtherEventDuration"]').val();
			if(activityType === 'Call') {
				minutesToAdd = container.find('input[name="defaultCallDuration"]').val();
			}
			if(Calendar_Edit_Js.userChangedTimeDiff){
				minutesToAdd = Calendar_Edit_Js.userChangedTimeDiff;
			}
			m.add(parseInt(minutesToAdd), 'minutes');
			if ((container.find('[name="time_start"]').data('userChangedDateTime') !== 1) || (container.find('[name="module"]').val()==='Calendar' || container.find('[name="module"]').val()==='Events')) {
					if(m.format(vtUtils.getMomentDateFormat()) == 'Invalid date') {
						m.format(vtUtils.getMomentDateFormat()) = '';
					}
					endDateElement.val(m.format(vtUtils.getMomentDateFormat()));
				}
			endTimeElement.val(m.format(vtUtils.getMomentTimeFormat()));

			vtUtils.registerEventForDateFields(endDateElement);
			vtUtils.registerEventForTimeFields(endTimeElement);
			endDateElement.valid();
		});
	},

	// Modified by Hieu Nguyen on 2022-01-05 to register events for Contact Invitees field
	registerEventsForContactInvitees: function (form) {
		var thisInstance = this;

		if (typeof form == 'undefined') {
			form = this.getForm();
		}

		// If module is not events then we dont have to register events
		if (!this.isEvents(form)) {
			return;
		}

		// Init select2 with mode multiple
		form.find('[name="contact_invitees"]').select2({
			minimumInputLength: _VALIDATION_CONFIG.autocomplete_min_length,
			ajax: {
				url: 'index.php?module=Contacts&action=BasicAjax&search_module=Contacts',
				dataType: 'JSON',
				data: function (term, page) {
					var data = {};
					data['search_value'] = term;
					return data;
				},
				results: function(data) {
					data.results = data.result;

					for (var index in data.results) {
						var resultData = data.result[index];
						resultData.text = resultData.label;
					}

					return data;
				},
				transport: function (params) {
					return jQuery.ajax(params);
				}
			},
			multiple: true,
			dropdownCss: { 'z-index': '10001' }	// To Make the menu come up in the case of quick create
		});

		// Handle event when selected multiple records from popup
		form.find('[name="contact_invitees"]').on(Vtiger_Edit_Js.postReferenceSelectionEvent, function (e, result) {
			contactList = [];

			jQuery.each(result.data, function (id, info) {
				contactList.push({ id: id, name: info.name });
			});

			thisInstance.fillSelectedContactInvitees(contactList, form);
		});

		// Fill selected contact invitees when edit
		var selectedContactInvitees = form.find('[name="contact_invitees"]').data('selectedTags');
        
        if (selectedContactInvitees) {
            form.find('[name="contact_invitees"]').select2('data', selectedContactInvitees);
        }
	},

	// Modified by Hieu Nguyen on 2022-01-05 to fill selected contact into Contact Invitees field
	fillSelectedContactInvitees: function (records, form) {
		if (form[0] == null) {
			form = this.getForm();
		}

		var select2Data = [];
		var element = jQuery('#contact_invitees_display', form);

		// Collect current values
		var selectContainer = jQuery(element.data('select2').container, form);
		var choices = selectContainer.find('.select2-search-choice');

		choices.each(function(index,element){
			select2Data.push(jQuery(element).data('select2-data'));
		});

		// Collect newly selected values
		for (var i = 0; i < records.length; i++) {
			var recordResult = records[i];
			recordResult.text = recordResult.name;	// Select2 needs text attribute
			select2Data.push(recordResult);
		}

		element.select2('data', select2Data);
	},

	// Modified by Hieu Nguyen on 2022-01-05 to fill newly created contact into Contact Invitees field
	referenceCreateHandler: function (container) {
		var thisInstance = this;
		var form = thisInstance.getForm();
		var module = jQuery(form).find('[name="module"]').val();

		// When user create a new Contact right at Contact Invitees field, fill created Contact into the invitees list
		if (container.find('input.sourceField').attr('name') == 'contact_invitees') {
			var referenceModuleName = this.getReferencedModuleName(container);
			var quickCreateNode = jQuery('#quickCreateModules').find('[data-name="'+ referenceModuleName +'"]');

			if (quickCreateNode[0] == null) {
				return app.helper.showErrorNotification({ message: app.vtranslate('JS_NO_CREATE_OR_NOT_QUICK_CREATE_ENABLED') });
			}

			quickCreateNode.trigger('click', {
				'callbackFunction': function (data) {
					var record = {};
					record.name = data._recordLabel;
					record.id = data._recordId;

					thisInstance.fillSelectedContactInvitees([record], container);
				}
			});
		}
		// When user create new record at other relation field, do default logic
		else {
			this._super(container); 
		}
	},

	 /**
	 * Function which will register the change event for repeatMonth radio buttons
	 */
	registerRepeatMonthActions : function() {
		var thisInstance = this;
		thisInstance.getForm().find('input[name="repeatMonth"]').on('change', function(e) {
			//If repeatDay radio button is checked then only select2 elements will be enable
			thisInstance.repeatMonthOptionsChangeHandling();
		});
	},

	/**
	 * This function will handle the change event for RepeatMonthOptions
	 */
	repeatMonthOptionsChangeHandling : function() {
		//If repeatDay radio button is checked then only select2 elements will be enable
			if(jQuery('#repeatDay').is(':checked')) {
				jQuery('#repeatMonthDate').attr('disabled', true);
				jQuery('#repeatMonthDayType').select2("enable");
				jQuery('#repeatMonthDay').select2("enable");
			} else {
				jQuery('#repeatMonthDate').removeAttr('disabled');
				jQuery('#repeatMonthDayType').select2("disable");
				jQuery('#repeatMonthDay').select2("disable");
			}
	},

	 /**
	 * Function which will change the UI styles based on recurring type
	 * @params - recurringType - which recurringtype is selected
	 */
	changeRecurringTypesUIStyles : function(recurringType) {
		var thisInstance = this;
		if(recurringType == 'Daily' || recurringType == 'Yearly') {
			jQuery('#repeatWeekUI').removeClass('show').addClass('hide');
			jQuery('#repeatMonthUI').removeClass('show').addClass('hide');
		} else if(recurringType == 'Weekly') {
			jQuery('#repeatWeekUI').removeClass('hide').addClass('show');
			jQuery('#repeatMonthUI').removeClass('show').addClass('hide');
		} else if(recurringType == 'Monthly') {
			jQuery('#repeatWeekUI').removeClass('show').addClass('hide');
			jQuery('#repeatMonthUI').removeClass('hide').addClass('show');
		}
	},

	registerDateStartChangeEvent : function(container) {
		container.find('[name="date_start"]').on('change',function() {
			var timeStartElement = container.find('[name="time_start"]');
			timeStartElement.trigger('changeTime');
		});
	},

	registerTimeEndChangeEvent : function(container) {
		container.find('[name="time_end"]').on('changeTime', function() {
			var startDateElement = container.find('input[name="date_start"]');
			var startTimeElement = container.find('input[name="time_start"]');
			var endDateElement = container.find('input[name="due_date"]');
			var endTimeElement = container.find('input[name="time_end"]');
			var momentFormat = vtUtils.getMomentCompatibleDateTimeFormat();
			var m1 = moment(endDateElement.val() + ' ' + endTimeElement.val(), momentFormat);
			var m2 = moment(startDateElement.val() + ' ' + startTimeElement.val(), momentFormat);
			var newDiff = (m1.unix() - m2.unix())/60;
			Calendar_Edit_Js.userChangedTimeDiff = newDiff;
			container.find('[name="due_date"]').valid();
		});
		if(container.find('[name="record"]')!==''){
			container.find('[name="time_end"]').trigger('changeTime');
		}
	},

	registerDateEndChangeEvent : function(container) {
		container.find('[name="due_date"]').on('change', function() {});
	},

	registerActivityTypeChangeEvent : function(container) {
		container.find('[name="activitytype"]').on('change', function() {
			var time_start_element = container.find('[name="time_start"]');
				time_start_element.trigger('changeTime');
		});
	},

	registerUserChangedDateTimeDetection : function(container) {
		var initialValue;
		container.on('focus',
		'[name="date_start"], [name="due_date"], [name="time_start"], [name="time_end"]',
		function() {
			initialValue = jQuery(this).val();
		});
		container.on('blur',
		'[name="date_start"], [name="due_date"], [name="time_start"], [name="time_end"]',
		function() {
			if(typeof initialValue !== 'undefined' && initialValue !== jQuery(this).val()) {
				container.find('[name="time_start"]').data('userChangedDateTime',1);
			}
		});
	},

	 registerDateTimeHandlersEditView : function(container) {
		var thisInstance = this;
		var registered = false;

		container.on('focus','[name="date_start"],[name="time_start"]',function(){
			if(!registered) {
				thisInstance.registerDateStartChangeEvent(container);
				thisInstance.registerTimeStartChangeEvent(container);
				thisInstance.registerTimeEndChangeEvent(container);
				thisInstance.registerDateEndChangeEvent(container);
				thisInstance.registerUserChangedDateTimeDetection(container);
				thisInstance.registerActivityTypeChangeEvent(container);
				registered = true;
			}
		});
	},

	registerDateTimeHandlers : function(container) {
		var thisInstance = this;
	  if(container.find('[name="record"]').val()===''){
		this.registerDateStartChangeEvent(container);
		this.registerTimeStartChangeEvent(container);
			container.find('[name="time_end"]').on('focus', function () {
				thisInstance.registerTimeEndChangeEvent(container);
			});
		this.registerDateEndChangeEvent(container);
		this.registerUserChangedDateTimeDetection(container);
		this.registerActivityTypeChangeEvent(container);
		}else{
		this.registerDateTimeHandlersEditView(container);
		}
	},

	registerToggleReminderEvent : function(container) {
		container.find('input[name="set_reminder"]').on('change', function(e) {
			var element = jQuery(e.currentTarget);
			var reminderSelectors = element.closest('#js-reminder-controls')
			.find('#js-reminder-selections');
			if(element.is(':checked')) {
				reminderSelectors.css('visibility','visible');
			} else {
				reminderSelectors.css('visibility','collapse');
			}
		})
	},

	 /**
	  * Function register to change recurring type.
	  */

	 registerRecurringTypeChangeEvent: function() {
		 var thisInstance = this;
		jQuery('#recurringType').on('change', function(e){
			var currentTarget = jQuery(e.currentTarget);
			var recurringType = currentTarget.val();
			thisInstance.changeRecurringTypesUIStyles(recurringType);
		});
	 },

	 /**
	  * Function to register recurrenceField checkbox.
	  */
	 registerRecurrenceFieldCheckBox : function(container) {
		 container.find('input[name="recurringcheck"]').on('change', function(e){
		   var element =jQuery(e.currentTarget);
		   var repeatUI = jQuery('#repeatUI');
		   if(element.is(':checked')) {
			   repeatUI.css('visibility','visible');
		   } else {
			   repeatUI.css('visibility','collapse');
		   }
		 });
	 },

	registerBasicEvents : function(container) {
		this._super(container);
		this.registerRecordPreSaveEvent(container);
		this.registerDateTimeHandlers(container);
		this.registerToggleReminderEvent(container);
		this.registerRecurrenceFieldCheckBox(container);
		this.registerRecurringTypeChangeEvent();
		this.repeatMonthOptionsChangeHandling();
		this.registerRepeatMonthActions();
		this.registerEventsForContactInvitees(container);	// Refactored by Hieu Nguyen on 2022-01-05
	}
});
