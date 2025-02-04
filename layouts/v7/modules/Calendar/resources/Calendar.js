/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger.Class("Calendar_Calendar_Js", {
	calendarViewContainer: false,
	feedsWidgetPostLoadEvent: 'Calendar.Viewtypes.PostLoad.Event',
	disabledFeedsStorageKey: 'calendar.feeds.disabled',
	calendarInstance: false,
	numberOfDaysInAgendaView: 7,
	userPreferenceCache: [],
	sideBarEssentialsState: '',
	getInstance: function () {
		if (!Calendar_Calendar_Js.calendarInstance) {
			if (app.view() == 'SharedCalendar') {
				Calendar_Calendar_Js.calendarInstance = new Calendar_SharedCalendar_Js();
			} else {
				Calendar_Calendar_Js.calendarInstance = new Calendar_Calendar_Js();
			}
		}
		return Calendar_Calendar_Js.calendarInstance;
	},
    // Modified by Hieu Nguyen on 2019-10-28 to set default values on event quick create modal
	showCreateEventModal: function (defaultValues) {
		var instance = Calendar_Calendar_Js.getInstance();
		instance.showCreateEventModal(defaultValues);
	},
    // End Hieu Nguyen
	showCreateTaskModal: function () {
		var instance = Calendar_Calendar_Js.getInstance();
		instance.showCreateTaskModal();
	},
	showCalendarSettings: function () {
		var instance = Calendar_Calendar_Js.getInstance();
		instance.showCalendarSettings();
	},
	deleteCalendarEvent: function (eventId, sourceModule, isRecurring) {
		var instance = Calendar_Calendar_Js.getInstance();
		instance.deleteCalendarEvent(eventId, sourceModule, isRecurring);
	},
	editCalendarEvent: function (eventId, isRecurring) {
		var instance = Calendar_Calendar_Js.getInstance();
		instance.editCalendarEvent(eventId, isRecurring);
	},
	editCalendarTask: function (taskId) {
		var instance = Calendar_Calendar_Js.getInstance();
		instance.editCalendarTask(taskId);
	},
	markAsHeld: function (recordId) {
		var instance = Calendar_Calendar_Js.getInstance();
		instance.markAsHeld(recordId);
	},
	holdFollowUp: function (eventId) {
		var instance = Calendar_Calendar_Js.getInstance();
		instance.holdFollowUp(eventId);
	}

}, {
	init: function () {
		this.addComponents();
	},
	addComponents: function () {
		this.addIndexComponent();
	},
	addIndexComponent: function () {
		this.addModuleSpecificComponent('Index', 'Vtiger', app.getParentModuleName());
	},
	registerCreateFollowUpEvent: function (modalContainer) {
		var thisInstance = this;
		var params = {
			submitHandler: function (form) {
				form = jQuery(form);
				form.find('[type="submit"]').attr('disabled', 'disabled');
				var formData = form.serializeFormData();
				app.helper.showProgress();
				app.request.post({'data': formData}).then(function (err, res) {
					app.helper.hideProgress();
					app.helper.hideModal();
					if (!err && res['created']) {
						jQuery('.vt-notification').remove();
						thisInstance.updateListView();
						thisInstance.updateCalendarView("Event");
					} else {
						app.event.trigger('post.save.failed', err);
					}
				});
			}
		};
		modalContainer.find('form#followupQuickCreate').vtValidate(params);
	},
	holdFollowUp: function (eventId) {
		var thisInstance = this;
		var requestParams = {
			'module': 'Calendar',
			'view': 'QuickCreateFollowupAjax',
			'record': eventId
		};
		app.helper.showProgress();
		app.request.get({'data': requestParams}).then(function (err, resp) {
			app.helper.hideProgress();
			if (!err && resp) {
				app.helper.showModal(resp, {
					'cb': function (modalContainer) {
						thisInstance.registerCreateFollowUpEvent(modalContainer);
					}
				});
			}
		});
	},
	updateListView: function () {
		if (app.view() === 'List') {
			var listInstance = Vtiger_List_Js.getInstance();
			listInstance.loadListViewRecords();
		}
	},
	updateCalendarView: function (activitytype) {
		if (app.view() === 'Calendar' || app.view() === 'SharedCalendar') {
			if (activitytype === 'Event') {
				this.updateAllEventsOnCalendar();
			} else {
				this.updateAllTasksOnCalendar();
			}
		}
	},
	markAsHeld: function (recordId) {
		var thisInstance = this;
		app.helper.showConfirmationBox({
			message: app.vtranslate('JS_CONFIRM_MARK_AS_HELD')
		}).then(function () {
			var requestParams = {
				module: "Calendar",
				action: "SaveFollowupAjax",
				mode: "markAsHeldCompleted",
				record: recordId
			};

			app.request.post({'data': requestParams}).then(function (e, res) {
				jQuery('.vt-notification').remove();
				if (e) {
					app.event.trigger('post.save.failed', e);
				} else if (res && res['valid'] === true && res['markedascompleted'] === true) {
					thisInstance.updateListView();
					thisInstance.updateCalendarView(res.activitytype);
				} else {
					app.helper.showAlertNotification({
						'message': app.vtranslate('JS_FUTURE_EVENT_CANNOT_BE_MARKED_AS_HELD')
					});
				}
			});
		});
	},
    // Added by Hieu Nguyen to 
    initCalendarSharingSelectedUsersField: function (modalContainer) {
        CustomOwnerField.initCustomOwnerFields(modalContainer.find('[name="selected_users"]'));
    },
    //End Hieu Nguyen

    // Modified by Hieu Nguyen on 2020-02-26
	registerCalendarSharingTypeChangeEvent: function (modalContainer) {
		var selectedUsersContainer = modalContainer.find('#selected-users');

		jQuery('[name="sharedtype"]').on('change', function () {
			var sharedType = jQuery(this).val();

			if (sharedType === 'selectedusers') {
				selectedUsersContainer.removeClass('hide');
			} 
            else {
				selectedUsersContainer.addClass('hide');
			}
		});
	},
    // End Hieu Nguyen
	registerHourFormatChangeEvent: function (modalContainer) {
		var hourFormatConditionMapping = jQuery('input[name="timeFormatOptions"]', modalContainer).data('value');
		var form = modalContainer.find('form');
		form.find('input[name="hour_format"]').on('click', function () {
			var hourFormatVal = jQuery(this).val();
			var startHourElement = jQuery('select[name="start_hour"]', form);
			var conditionSelected = startHourElement.val();
			var list = hourFormatConditionMapping['hour_format'][hourFormatVal]['start_hour'];
			var options = '';
			for (var key in list) {
				if (list.hasOwnProperty(key)) {
					var conditionValue = list[key];
					options += '<option value="' + key + '"';
					if (key === conditionSelected) {
						options += ' selected="selected" ';
					}
					options += '>' + conditionValue + '</option>';
				}
			}
			startHourElement.html(options).trigger("change");
		});
	},
	registerCalendarSettingsShownEvents: function (modalContainer) {
        this.initCalendarSharingSelectedUsersField(modalContainer); // Added by Hieu Nguyen on 2020-02-26 to init autocomplete mode for selected users field
		this.registerCalendarSharingTypeChangeEvent(modalContainer);
		this.registerHourFormatChangeEvent(modalContainer);
		app.helper.showVerticalScroll(jQuery('.modal-body'), {setHeight: '400px'});
		vtUtils.enableTooltips();
		modalContainer.find('button[name="saveButton"]').on('click', function () {
            // Added by Hieu Nguyen on 2019-11-21 to validate activity type field
            var activityTypesInput = modalContainer.find('[name="shared_calendar_activity_types[]"]');
            var activityTypes = activityTypesInput.val();
            
            if (!activityTypes) {
                app.helper.showErrorNotification({ 'message': app.vtranslate('JS_CALENDAR_SETTINGS_SHARED_CALENDAR_ACTIVITY_TYPES_EMPTY_ERROR_MSG') });
                return;
            }
            // End Hieu Nguyen

			jQuery(this).attr('disabled', 'disabled');
			modalContainer.find('form').find('[name="sourceView"]').val(app.view());
			modalContainer.find('form').submit();
		});
	},
	showCalendarSettings: function () {
		var thisInstance = this;
		var params = {
			'module': app.getModuleName(),
			'view': 'Calendar',
			'mode': 'Settings'
		};
		app.helper.showProgress();
		app.request.post({'data': params}).then(function (e, data) {
			app.helper.hideProgress();
			if (!e) {
				app.helper.showModal(data, {
					'cb': function (modalContainer) {
						thisInstance.registerCalendarSettingsShownEvents(modalContainer);
					}
				});
			} else {
				console.log("network error : ", e);
			}
		});
	},
	getDisabledFeeds: function () {
		return app.storage.get(Calendar_Calendar_Js.disabledFeedsStorageKey, []);
	},
	disableFeed: function (sourceKey) {
		var disabledFeeds = this.getDisabledFeeds();
		if (disabledFeeds.indexOf(sourceKey) === -1) {
			disabledFeeds.push(sourceKey);
			app.storage.set(Calendar_Calendar_Js.disabledFeedsStorageKey, disabledFeeds);
		}
	},
	enableFeed: function (sourceKey) {
		var disabledFeeds = this.getDisabledFeeds();
		if (disabledFeeds.indexOf(sourceKey) !== -1) {
			disabledFeeds = jQuery.grep(disabledFeeds, function (value) {
				return value !== sourceKey;
			});
			app.storage.set(Calendar_Calendar_Js.disabledFeedsStorageKey, disabledFeeds);
		}
	},
	getFeedRequestParams: function (start, end, feedCheckbox) {
		var dateFormat = 'YYYY-MM-DD';
		var startDate = start.format(dateFormat);
		var endDate = end.format(dateFormat);
		return {
			'start': startDate,
			'end': endDate,
			'type': feedCheckbox.data('calendarFeed'),
			'fieldname': feedCheckbox.data('calendarFieldname'),
			'color': feedCheckbox.data('calendarFeedColor'),
			'textColor': feedCheckbox.data('calendarFeedTextcolor'),
			'conditions': feedCheckbox.data('calendarFeedConditions'),
            'calendar_view': 'MyCalendar',  // Added by Hieu Nguyen on 2019-11-12
		};
	},
	renderEvents: function () {
		var thisInstance = this;
		this.getCalendarViewContainer().fullCalendar('addEventSource',
				function (start, end, timezone, render) {
					thisInstance.getCalendarViewContainer().fullCalendar('removeEvents');
					var activeFeeds = jQuery('input[data-calendar-feed]:checked');
					var activeFeedsRequestParams = {};
					activeFeeds.each(function () {
						var feedCheckbox = jQuery(this);
						var feedRequestParams = thisInstance.getFeedRequestParams(start, end, feedCheckbox);
						activeFeedsRequestParams[feedCheckbox.data('calendarSourcekey')] = feedRequestParams;
					});

					var requestParams = {
						'module': app.getModuleName(),
						'action': 'Feed',
						'mode': 'batch',
						'feedsRequest': activeFeedsRequestParams,
                        'calendar_view': (app.getViewName() == 'Calendar') ? 'MyCalendar' : app.getViewName(),   // Added by Hieu nguyen on 2019-11-14
					};
					var events = [];
					app.helper.showProgress();
					activeFeeds.attr('disabled', 'disabled');
					app.request.post({'data': requestParams}).then(function (e, data) {
						if (!e) {
							data = JSON.parse(data);
							for (var feedType in data) {
                                // Modified by Hieu Nguyen on 2019-11-12 to change the Calendar Feed logic
								var feed = data[feedType];

                                if (feed != null) {
                                    feed.forEach(function (entry) {
                                        events.push(entry);
                                    });
                                }
                                // End Hieu Nguyen
							}
						} else {
							console.log("error in response : ", e);
						}
						render(events);
						activeFeeds.removeAttr('disabled');
						app.helper.hideProgress();
					});
				});
	},
	assignFeedTextColor: function (feedCheckbox) {
		var color = feedCheckbox.data('calendarFeedColor');
		var contrast = app.helper.getColorContrast(color);
		var textColor = (contrast === 'dark') ? 'white' : 'black';
		feedCheckbox.data('calendarFeedTextcolor', textColor);
		feedCheckbox.closest('.calendar-feed-indicator').css({'color': textColor});
	},
	colorizeFeed: function (feedCheckbox) {
		this.assignFeedTextColor(feedCheckbox);
	},
	restoreFeedsState: function (widgetContainer) {
		var thisInstance = this;
		var disabledFeeds = this.getDisabledFeeds();
		var feedsList = widgetContainer.find('#calendarview-feeds > ul.feedslist');
		var calendarfeeds = feedsList.find('[data-calendar-feed]');
		calendarfeeds.each(function () {
			var feedCheckbox = jQuery(this);
			var sourceKey = feedCheckbox.data('calendarSourcekey');

            // Commented out by Hieu Nguyen on 2019-11-14 to prevent un-ticked checkboxes to be ticked automatically 
			/*if (disabledFeeds.indexOf(sourceKey) === -1) {
				feedCheckbox.attr('checked', true);
			}*/

			thisInstance.colorizeFeed(feedCheckbox);
		});
	},
	fetchEvents: function (feedCheckbox) {
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();
		var view = thisInstance.getCalendarViewContainer().fullCalendar('getView');

		var feedRequestParams = thisInstance.getFeedRequestParams(view.start, view.end, feedCheckbox);
		feedRequestParams.module = app.getModuleName();
		feedRequestParams.action = 'Feed';

		var events = [];
		app.request.post({'data': feedRequestParams}).then(function (e, data) {
			if (!e) {
				events = JSON.parse(data);
				aDeferred.resolve(events);
			} else {
				aDeferred.reject(e);
			}
		});
		return aDeferred.promise();
	},
	addEvents: function (feedCheckbox) {
		var thisInstance = this;
		if (feedCheckbox.is(':checked')) {
			app.helper.showProgress();
			feedCheckbox.attr('disabled', 'disabled');
			thisInstance.fetchEvents(feedCheckbox).then(function (events) {
				thisInstance.getCalendarViewContainer().fullCalendar('addEventSource', events);
				feedCheckbox.removeAttr('disabled');
				app.helper.hideProgress();
			}, function (e) {
				console.log("error while fetching events : ", feedCheckbox, e);
			});
		}
	},
	removeEvents: function (feedCheckbox) {
		var module = feedCheckbox.data('calendarFeed');
		var conditions = feedCheckbox.data('calendarFeedConditions');
		var fieldName = feedCheckbox.data('calendarFieldname');
		this.getCalendarViewContainer().fullCalendar('removeEvents',
				function (eventObj) {
					return module === eventObj.module && eventObj.conditions === conditions && fieldName === eventObj.fieldName;
				});
	},
    // Modified by Hieu Nguyen on 2019-11-14
	registerFeedChangeEvent: function (widgetContainer) {
        var thisInstance = this;
        
		widgetContainer.on('change', 'input.toggleCalendarFeed', function () {
            var feedIndicator = jQuery(this).closest('.calendar-feed-indicator');
            var visibilityCheckbox = jQuery(this);
            var sourceKey = visibilityCheckbox.data('calendar-sourcekey');

            if (visibilityCheckbox.is(':checked')) {
                thisInstance.enableFeed(sourceKey);
            } 
            else {
                thisInstance.disableFeed(sourceKey);
            }

            thisInstance.displayToggleAllStatus();
            thisInstance.updateFeedVisibility(feedIndicator);
        });
	},
    // End Hieu Nguyen
	updateRangeFields: function (container, options) {
		var moduleName = container.find('select[name="modulesList"]').val();
		var fieldSelectElement = container.find('select[name="fieldsList"]');

		var sourceFieldSelect = container.find('select[name="sourceFieldsList"]');
		var targetFieldSelect = container.find('select[name="targetFieldsList"]');
		fieldSelectElement.removeAttr('disabled');

		var optionsCount = fieldSelectElement.find('option').not('option[value="birthday"]');

		if (moduleName === 'Events' || moduleName === 'Calendar') {
			optionsCount = fieldSelectElement.find('option').not('option[value="date_start,due_date"]');
		}

		if (optionsCount.length > 1) {
			container.find('[name="rangeFields"]').removeAttr('disabled').trigger('change');
		} else {
			container.find('[name="rangeFields"]').attr('disabled', true).attr('checked', false).trigger('change');
		}

		var selectedValue = fieldSelectElement.find('option:selected').val();
		sourceFieldSelect.select2('destroy').html(options).select2();
		targetFieldSelect.select2('destroy').html(options).select2();

		if (moduleName === 'Events' || moduleName === 'Calendar') {
			sourceFieldSelect.find('option[value="date_start,due_date"]').remove();
			targetFieldSelect.find('option[value="date_start,due_date"]').remove();
		}
		sourceFieldSelect.find('option[value="birthday"]').remove();
		targetFieldSelect.find('option[value="birthday"]').remove();
		if (selectedValue === 'birthday') {
			selectedValue = fieldSelectElement.find('option:selected').next().val();
		}
		var otherOption = targetFieldSelect.find('option').not('option[value="' + selectedValue + '"]');
		sourceFieldSelect.select2('val', selectedValue);
		if (otherOption.length > 0) {
			targetFieldSelect.select2('val', otherOption.val());
		} else {
			targetFieldSelect.select2('destroy').html('').select2();
		}
	},
	updateDateFields: function (container) {
		var fieldMeta = container.find('[name="moduleDateFields"]').data('value');
		var moduleSelectElement = container.find('select[name="modulesList"]');
		var moduleName = moduleSelectElement.val();

		var fieldSelectElement = container.find('select[name="fieldsList"]');

		var options = '';
		for (var key in fieldMeta) {
			if (fieldMeta.hasOwnProperty(key) && key === moduleName) {
				var moduleSpecificFields = fieldMeta[key];
				for (var fieldName in moduleSpecificFields) {
					if (moduleSpecificFields.hasOwnProperty(fieldName)) {
						options += '<option value="' + fieldName + '" data-viewfieldname="' + fieldName + '">' +
								moduleSpecificFields[fieldName] + '</option>';
					}
				}
			}
		}
		if (options === '')
			options = '<option value="">NONE</option>';

		fieldSelectElement.select2('destroy').html(options).select2().trigger('change');

		var editorMode = container.find('.editorMode').val();
		if (editorMode === 'create') {
			this.updateRangeFields(container, options);
		}
	},
	initializeColorPicker: function (element, customParams, onChangeFunc) {
		var params = {
			flat: true,
			onChange: onChangeFunc
		};
		if (typeof customParams !== 'undefined') {
			params = jQuery.extend(params, customParams);
		}
		element.ColorPicker(params);
	},
	getRandomColor: function () {
		return '#' + (0x1000000 + (Math.random()) * 0xffffff).toString(16).substr(1, 6);
	},
	registerDateFieldChangeEvent: function (modalContainer) {
		var thisInstance = this;
		var parentElement = jQuery('#calendarview-feeds');
		var fieldsSelect = modalContainer.find('[name="fieldsList"]');

		fieldsSelect.on('change', function () {
			var moduleName = modalContainer.find('[name="modulesList"]').find('option:selected').val();
			var selectedOption = jQuery(this).find('option:selected');
			var fieldName = selectedOption.val();
			var currentColor = thisInstance.getRandomColor();

			var calendarSourceKey = moduleName + '_' + fieldName;
			if (moduleName === 'Events') {
				var conditions = modalContainer.find('#calendarviewconditions').val();
				conditions = thisInstance._getParsedConditions(conditions);
				if (conditions.hasOwnProperty('value')) {
					calendarSourceKey += '_' + conditions.value;
				}
			}

			var feedCheckbox = jQuery('[data-calendar-sourcekey="' + calendarSourceKey + '"]', parentElement);
			if (feedCheckbox.length) {
				currentColor = feedCheckbox.data('calendarFeedColor');
			}
			modalContainer.find('.selectedColor').val(currentColor);
			modalContainer.find('.calendarColorPicker').ColorPickerSetColor(currentColor);
		});
		modalContainer.find('#calendarviewconditions').on('change', function () {
			fieldsSelect.trigger('change');
		});
	},
	_getParsedConditions: function (conditions) {
		var parsedConditions = {};
		if (conditions !== '') {
			parsedConditions = JSON.parse(conditions);
			if (typeof parsedConditions !== 'object') {
				parsedConditions = JSON.parse(parsedConditions);
			}
		}
		return parsedConditions;
	},
	saveFeedSettings: function (modalContainer, feedIndicator) {
		var thisInstance = this;
		var modulesList = modalContainer.find('select[name="modulesList"]');
		var moduleName = modulesList.val();
		var fieldName = modalContainer.find('select[name="fieldsList"]').val();
		var selectedColor = modalContainer.find('input.selectedColor').val();
		var conditions = '';
		if (moduleName === 'Events') {
			conditions = modalContainer.find('[name="conditions"]').val();
			if (conditions !== '') {
				conditions = JSON.stringify(conditions);
			}
		}

		var editorMode = modalContainer.find('.editorMode').val();
		if (editorMode === 'create') {
			var translatedFieldName = modalContainer.find('.selectedType').data('typename');
			if (modalContainer.find('[name="rangeFields"]').is(':checked')) {
				var sourceValue = modalContainer.find('[name="sourceFieldsList"]').val();
				var targetValue = modalContainer.find('[name="targetFieldsList"]').val();
				fieldName = sourceValue + ',' + targetValue;
				translatedFieldName = modalContainer.find('[name="sourceFieldsList"] option:selected').text() + ',' + modalContainer.find('[name="targetFieldsList"] option:selected').text();
			}
		}

		var params = {
			module: 'Calendar',
			action: 'CalendarUserActions',
			mode: 'saveCalendarView',   // Modified by Hieu Nguyen on 2019-12-19
			viewmodule: moduleName,
			viewfieldname: fieldName,
			viewColor: selectedColor,
			viewConditions: conditions,
            calendar_view_id: feedIndicator ? feedIndicator.find('[type="checkbox"]').data('calendarViewId') : '',  // Added by Hieu Nguyen on 2019-12-18
		};

		app.helper.showProgress();
		app.request.post({'data': params}).then(function (e, data) {
			if (!e) {
				var contrast = app.helper.getColorContrast(selectedColor);
				var textColor = (contrast === 'dark') ? 'white' : 'black';
				var message = app.vtranslate('JS_CALENDAR_VIEW_COLOR_UPDATED_SUCCESSFULLY');
				var parsedConditions = thisInstance._getParsedConditions(conditions);

                // Modified by Hieu Nguyen on 2020-01-06 to display translated module name in the newly created calendar view
                var translatedModuleName = modulesList.find('option:selected').text();
				var feedIndicatorTitle = translatedModuleName + '-' + translatedFieldName;
				var calendarSourceKey = moduleName + '_' + fieldName;

				if (parsedConditions.hasOwnProperty('value')) {
                    feedIndicatorTitle = translatedModuleName + '(' + app.vtranslate(parsedConditions.value) + ') -' + translatedFieldName;
					calendarSourceKey += '_' + parsedConditions.value;
				}
                // End Hieu Nguyen

				if (editorMode === 'create') {
					var translatedModuleName = modulesList.find('option:selected').text();
					var feedIndicatorTemplate = jQuery('#calendarview-feeds').find('ul.dummy > li.feed-indicator-template');
					feedIndicatorTemplate.removeClass('.feed-indicator-template');
					var newFeedIndicator = feedIndicatorTemplate.clone(true, true);
					newFeedIndicator.find('span:first').text(feedIndicatorTitle);

					// Added by Phu Vo on 2021.05.31 to enable title
					newFeedIndicator.find('span:first').attr('title', feedIndicatorTitle);
					newFeedIndicator.find('span:first').attr('data-toggle', 'tooltip');
					// End Phu Vo
					
					var newFeedCheckbox = newFeedIndicator.find('.toggleCalendarFeed');

                    // Added by Hieu Nguyen on 2019-12-18
                    newFeedCheckbox.attr('data-calendar-view-id', data.id);
                    // End Hieu Nguyen

					newFeedCheckbox.attr('data-calendar-sourcekey', calendarSourceKey).
							attr('data-calendar-feed', moduleName).
							attr('data-calendar-fieldlabel', translatedFieldName).
							attr('data-calendar-fieldname', fieldName).
							attr('title', translatedModuleName).
							attr('checked', 'checked');
					if (data['type']) {
						newFeedCheckbox.attr('data-calendar-type', data['type']);
					}
					feedIndicator = newFeedIndicator;
					jQuery('#calendarview-feeds').find('ul:first').append(feedIndicator);
					message = app.vtranslate('JS_CALENDAR_VIEW_ADDED_SUCCESSFULLY');
				} else {
					feedIndicator = jQuery('#calendarview-feeds')
							.find('[data-calendar-sourcekey="' + calendarSourceKey + '"]')
							.closest('.calendar-feed-indicator');
				}

				feedIndicator.css({'background-color': selectedColor, 'color': textColor});
				var feedCheckbox = feedIndicator.find('.toggleCalendarFeed');
				feedCheckbox.data('calendarFeedColor', selectedColor).
						data('calendarFeedTextcolor', textColor).
						data('calendarFeedConditions', conditions);
				thisInstance.refreshFeed(feedCheckbox);

				app.helper.hideProgress();
				app.helper.hideModal();
				app.helper.showSuccessNotification({'message': message});
			} else {
				console.log("error occured while saving : ", params, e);
			}
		});
	},
	registerColorEditorSaveEvent: function (modalContainer, feedIndicator) {
		var thisInstance = this;
		modalContainer.find('[name="saveButton"]').on('click', function () {
			var currentTarget = jQuery(this);
			currentTarget.attr('disabled', 'disabled');
			var modulesSelect = modalContainer.find('select[name="modulesList"]');
			var fieldsSelect = modalContainer.find('select[name="fieldsList"]');
			var selectedType = modalContainer.find('.selectedType');

			var moduleName = modulesSelect.val();
			var fieldName = fieldsSelect.val();

			selectedType.val(fieldName).data(
					'typename',
					fieldsSelect.find('option:selected').text()
					);

			var selectedColor = modalContainer.find('.selectedColor').val(),
					conditions = '';
			if (moduleName === 'Events') {
				conditions = modalContainer.find('[name="conditions"]').val();
				if (conditions !== '') {
					conditions = JSON.stringify(conditions);
				}
			}

			thisInstance.checkDuplicateFeed(moduleName, fieldName, selectedColor, conditions).then(
					function (result) {
						thisInstance.saveFeedSettings(modalContainer, feedIndicator);
					},
					function () {
						app.helper.showErrorNotification({'message': app.vtranslate('JS_CALENDAR_VIEW_YOU_ARE_EDITING_NOT_FOUND')});
						currentTarget.removeAttr('disabled');
					});
		});
	},
	registerColorEditorEvents: function (modalContainer, feedIndicator) {
		var thisInstance = this;
		var feedCheckbox = feedIndicator.find('input[type="checkbox"].toggleCalendarFeed');

		var colorPickerHost = modalContainer.find('.calendarColorPicker');
		var selectedColor = modalContainer.find('.selectedColor');
		thisInstance.initializeColorPicker(colorPickerHost, {}, function (hsb, hex, rgb) {
			var selectedColorCode = '#' + hex;
			selectedColor.val(selectedColorCode);
		});

		thisInstance.registerDateFieldChangeEvent(modalContainer);

		var modulesSelect = modalContainer.find('[name="modulesList"]');
		modulesSelect.on('change', function () {
			thisInstance.updateDateFields(modalContainer);
//handling eventtype condition element display
			var module = jQuery(this).val();
			if (module === 'Events') {
				modalContainer.find('#js-eventtype-condition').removeClass('hide');
				var feedConditions = feedCheckbox.data('calendarFeedConditions');
				if (feedConditions !== '') {
					modalContainer.find('[name="conditions"]').val(JSON.parse(feedConditions)).trigger('change');
				}
			} else {
				modalContainer.find('#js-eventtype-condition').addClass('hide');
			}
		}).select2('val', feedCheckbox.data('calendarFeed')).trigger('change');

		var fieldSelectElement = modalContainer.find('[name="fieldsList"]');
		fieldSelectElement.select2('val', feedCheckbox.data('calendarFieldname')).trigger('change');

		thisInstance.registerColorEditorSaveEvent(modalContainer, feedIndicator);
	},
	showColorEditor: function (feedIndicator) {
		var thisInstance = this;
		var params = {
			module: app.getModuleName(),
			view: 'ActivityTypeViews',
			mode: 'editActivityType'
		};
		app.helper.showProgress();
		app.request.post({'data': params}).then(function (e, data) {
			app.helper.hideProgress();
			if (!e) {
				app.helper.showModal(data, {
					'cb': function (modalContainer) {
						thisInstance.registerColorEditorEvents(modalContainer, feedIndicator);
					}
				});
			} else {
				console.log("network error : ", e);
			}
		});
	},
	registerFeedsColorEditEvent: function () {
		var thisInstance = this;
		jQuery('#calendarview-feeds').on('click', '.editCalendarFeedColor',
				function () {
					var feedIndicator = jQuery(this).closest('li.calendar-feed-indicator');
					thisInstance.showColorEditor(feedIndicator);
				});
	},
    // Modified by Hieu Nguyen on 2019-12-18 to delete calendar view by id
	getFeedDeleteParameters: function (feedCheckbox) {
		return {
			module: 'Calendar',
			action: 'CalendarUserActions',
			mode: 'deleteCalendarView',
			calendar_view_id: feedCheckbox.data('calendarViewId')
		};
	},
    // End Hieu Nguyen
	deleteFeed: function (feedIndicator) {
		var thisInstance = this;
		var feedCheckbox = feedIndicator.find('input[type="checkbox"].toggleCalendarFeed');
		var params = thisInstance.getFeedDeleteParameters(feedCheckbox);

        // Added by Hieu Nguyen on 2019-11-14 to support mass delete
        if (feedIndicator.hasClass('feed-mass-actions')) {
            params['delete_all'] = '1';
        }
        // End Hieu Nguyen

		app.helper.showProgress();
		app.request.post({'data': params}).then(function (e) {
			if (!e) {
                // Modified by Hieu Nguyen on 2019-11-14 to support mass delete
                var calendarViewName = app.getViewName();

                // For Shared Calendar
                if (calendarViewName == 'SharedCalendar') {
                    if (feedIndicator.hasClass('feed-mass-actions')) {
                        jQuery('.feedslist').find('.deleteCalendarFeed').closest('li').remove();
                    }
                    else {
                        feedIndicator.remove();
                    }

                    thisInstance.refreshCalendar();
                }
                // For My Calendar
                else {
                    if (feedIndicator.hasClass('feed-mass-actions')) {
                        jQuery('.feedslist').find('li').each(function () {
                            var feedIndicator = jQuery(this);
                            var feedCheckbox = feedIndicator.find('input[type="checkbox"]');

                            if (feedIndicator.find('.deleteCalendarFeed')[0] != null) {
                                thisInstance.removeEvents(feedCheckbox);
                                feedIndicator.remove();
                            }
                        });
                    }
                    else {
                        thisInstance.removeEvents(feedCheckbox);
                        feedIndicator.remove();
                    }
                }

                thisInstance.displayToggleAllStatus();
                // End Hieu Nguyen

				app.helper.showSuccessNotification({
					message: app.vtranslate('JS_CALENDAR_VIEW_DELETED_SUCCESSFULLY')
				});
			} else {
				console.log("error : ", e);
			}
			app.helper.hideProgress();
		});
	},
	registerFeedDeleteEvent: function () {
		var thisInstance = this;
		jQuery('#calendarview-feeds').on('click', '.deleteCalendarFeed',
				function () {
					var feedIndicator = jQuery(this).closest('.calendar-feed-indicator');
					app.helper.showConfirmationBox({
						message: app.vtranslate('JS_CALENDAR_VIEW_DELETE_CONFIRMATION')
					}).then(function () {
						thisInstance.deleteFeed(feedIndicator);
					});
				});
	},
	checkDuplicateFeed: function (moduleName, fieldName, selectedColor, conditions) {
		var aDeferred = jQuery.Deferred();
		var params = {
			'module': 'Calendar',
			'action': 'CalendarUserActions',
			'mode': 'checkDuplicateView',
			'viewmodule': moduleName,
			'viewfieldname': fieldName,
			'viewColor': selectedColor,
			'viewConditions': conditions
		};
		app.request.post({'data': params}).then(function (e, result) {
			if (!e) {
				if (result['success']) {
					aDeferred.resolve(result);
				} else {
					aDeferred.reject(result);
				}
			} else {
				console.log("error : ", e);
			}
		});
		return aDeferred.promise();
	},
	registerAddActivityTypeEvent: function (modalContainer) {
		var thisInstance = this;
		modalContainer.find('[name="saveButton"]').on('click', function () {
			var currentTarget = jQuery(this);
			currentTarget.attr('disabled', 'disabled');
			var fieldSelect = modalContainer.find('select[name="fieldsList"]');
			var selectedType = modalContainer.find('.selectedType');
			selectedType.val(fieldSelect.val()).data(
					'typename',
					fieldSelect.find('option:selected').text()
					);
			var moduleName = modalContainer.find('select[name="modulesList"]').val();
			var fieldName = fieldSelect.val();
			if (modalContainer.find('[name="rangeFields"]').is(':checked')) {
				var sourceValue = modalContainer.find('[name="sourceFieldsList"]').val();
				var targetValue = modalContainer.find('[name="targetFieldsList"]').val();
				fieldName = sourceValue + ',' + targetValue;
			}
			var selectedColor = modalContainer.find('.selectedUserColor').val(),
					conditions = '';
			if (moduleName === 'Events') {
				conditions = modalContainer.find('[name="conditions"]').val();
				if (conditions !== '') {
					conditions = JSON.stringify(conditions);
				}
			}

			thisInstance.checkDuplicateFeed(moduleName, fieldName, selectedColor, conditions).then(
					function (result) {
						app.helper.showErrorNotification({'message': result['message']});
						currentTarget.removeAttr('disabled');
					},
					function () {
						thisInstance.saveFeedSettings(modalContainer);
					});
		});
	},
	registerAddActivityTypeFeedActions: function (modalContainer) {
		var thisInstance = this;
		var colorPickerHost = modalContainer.find('.calendarColorPicker');
		var selectedColor = modalContainer.find('.selectedColor');
		thisInstance.initializeColorPicker(colorPickerHost, {}, function (hsb, hex, rgb) {
			var selectedColorCode = '#' + hex;
			selectedColor.val(selectedColorCode);
		});

		thisInstance.registerDateFieldChangeEvent(modalContainer);

		var modulesSelect = modalContainer.find('[name="modulesList"]');
		modulesSelect.on('change', function () {
			thisInstance.updateDateFields(modalContainer);
//handling eventtype condition element display
			var module = jQuery(this).val();
			if (module === 'Events') {
				modalContainer.find('#js-eventtype-condition').removeClass('hide');
			} else {
				modalContainer.find('#js-eventtype-condition').addClass('hide');
			}
		}).trigger('change');

		var sourceFieldsSelect = modalContainer.find('select[name="sourceFieldsList"]');
		sourceFieldsSelect.on('change', function () {
			var selectedValue = sourceFieldsSelect.find('option:selected').val();
			if (selectedValue === targetFieldsSelect.find('option:selected').val()) {
				var otherOption = targetFieldsSelect.find('option').not('option[value="' + selectedValue + '"]');
				targetFieldsSelect.select2('val', otherOption.val());
			}
		});

		var targetFieldsSelect = modalContainer.find('select[name="targetFieldsList"]');
		targetFieldsSelect.on('change', function () {
			var selectedValue = targetFieldsSelect.find('option:selected').val();
			if (selectedValue === sourceFieldsSelect.find('option:selected').val()) {
				var otherOption = sourceFieldsSelect.find('option').not('option[value="' + selectedValue + '"]');
				sourceFieldsSelect.select2('val', otherOption.val());
			}
		});

		var rangeFieldsOption = modalContainer.find('[name="rangeFields"]');
		rangeFieldsOption.on('change', function () {
			var fieldSelectEle = modalContainer.find('select[name="fieldsList"]');
			var sourceFieldsSelect = modalContainer.find('select[name="sourceFieldsList"]');
			var targetFieldsSelect = modalContainer.find('select[name="targetFieldsList"]');
			if (rangeFieldsOption.is(':checked')) {
				fieldSelectEle.attr('disabled', true);
				sourceFieldsSelect.removeAttr('disabled');
				targetFieldsSelect.removeAttr('disabled');
			} else {
				fieldSelectEle.removeAttr('disabled');
				sourceFieldsSelect.attr('disabled', true);
				targetFieldsSelect.attr('disabled', true);
			}

			//after disabling or enabling, set the options and selected value for select2 elements
			var fieldSelectedValue = fieldSelectEle.find('option:selected').val();
			var fieldOptions = fieldSelectEle.find('option');
			fieldSelectEle.select2('destroy').html(fieldOptions).select2();
			fieldSelectEle.select2('val', fieldSelectedValue);

			var sourceOptions = sourceFieldsSelect.find('option');
			sourceFieldsSelect.select2('destroy').html(sourceOptions).select2();
			sourceFieldsSelect.select2('val', fieldSelectedValue);

			var sourceSelectValue = sourceFieldsSelect.find('option:selected').val();
			var otherOption = targetFieldsSelect.find('option').not('option[value="' + sourceSelectValue + '"]');
			var targetOptions = targetFieldsSelect.find('option');
			targetFieldsSelect.select2('destroy').html(targetOptions).select2();
			targetFieldsSelect.select2('val', otherOption.val());
		});

		thisInstance.registerAddActivityTypeEvent(modalContainer);
	},
	showAddActivityTypeFeedView: function () {
		var thisInstance = this;
		var params = {
			module: app.getModuleName(),
			view: 'ActivityTypeViews',
			mode: 'addActivityType'
		};
		app.helper.showProgress();
		app.request.post({'data': params}).then(function (e, data) {
			app.helper.hideProgress();
			if (!e) {
				app.helper.showModal(data, {
					'cb': function (modalContainer) {
						thisInstance.registerAddActivityTypeFeedActions(modalContainer);
					}
				});
			} else {
				console.log("network error : ", e);
			}
		});
	},
	showAddCalendarFeedEditor: function () {
		this.showAddActivityTypeFeedView();
	},
	registerFeedAddEvent: function (widgetContainer) {
		var thisInstance = this;
		widgetContainer.find('.add-calendar-feed').on('click', function () {
			thisInstance.showAddCalendarFeedEditor();
		});
	},

    // Added by Hieu Nguyen on 2019-11-14 to support mass feed actions
    displayToggleAllStatus: function () {
        var feedItems = jQuery('.feedslist').find('li');
        var totalItemsCount = feedItems.length;
        var checkedItemsCount = feedItems.find('input[type="checkbox"]:checked').length;
        var allChecked = checkedItemsCount == totalItemsCount;
        
        jQuery('#toggle-visible-all').prop('checked', allChecked);
    },

    registerFeedMassActionEvents: function () {
        var thisInstance = this;
        var feedMassActionsIndicator = jQuery('.feed-mass-actions');

        feedMassActionsIndicator.find('#toggle-visible-all').on('change', function() {
            thisInstance.updateFeedVisibility(feedMassActionsIndicator);
        });

        feedMassActionsIndicator.find('#btn-remove-all').on('click', function() {
            var calendarView = app.getViewName();
            var remainingFeedsCount = jQuery('.feedslist').find('li').length;

            if ((calendarView == 'Calendar' && remainingFeedsCount == 0) || (calendarView == 'SharedCalendar' && remainingFeedsCount == 1)) {
                app.helper.showErrorNotification({ message: app.vtranslate('JS_FEED_MASS_ACTION_DELETE_ALL_NO_FEED_REMAIN_WARNING_MSG') });
                return;
			}

            app.helper.showConfirmationBox({ message: app.vtranslate('JS_FEED_MASS_ACTION_DELETE_ALL_CONFIRM_MSG') }).then(function () {
				thisInstance.deleteFeed(feedMassActionsIndicator);
			});
        });
    },

    updateFeedVisibility: function(feedIndicator) {
        var thisInstance = this;
        var feedCheckbox = feedIndicator.find('input[type="checkbox"]');
        var params = thisInstance.getFeedUpdateVisibilityParams(feedCheckbox);

        if (feedIndicator.hasClass('feed-mass-actions')) {
            params['update_all'] = '1';
        }

		app.helper.showProgress();

		app.request.post({ data: params }).then(function (err, data) {
			if (err || !data) {
				console.log(err);
			}

            app.helper.hideProgress();
            var calendarViewName = app.getViewName();

            // For Shared Calendar
            if (calendarViewName == 'SharedCalendar') {
                if (feedIndicator.hasClass('feed-mass-actions')) {
                    jQuery('.feedslist').find('input[type="checkbox"]').prop('checked', feedCheckbox.is(':checked'));
                }

                // Need to refresh all calendar events as each feed change will cause other's result change!!!
                thisInstance.refreshCalendar();
            }
            // For My Calendar
            else {
                // Mass action will cause all calendar feeds change so we need to refresh all the calendar events
                if (feedIndicator.hasClass('feed-mass-actions')) {
                    jQuery('.feedslist').find('input[type="checkbox"]').prop('checked', feedCheckbox.is(':checked'));
                    thisInstance.refreshCalendar();
                }
                // Single action will affect only a single feed
                else {
                    if (feedCheckbox.is(':checked')) {
                        thisInstance.addEvents(feedCheckbox);
                    }
                    else {
                        thisInstance.removeEvents(feedCheckbox);
                    }
                }
            }
		});
    },

    getFeedUpdateVisibilityParams: function (feedCheckbox) {
        return {
            module: 'Calendar',
            action: 'CalendarUserActions',
            mode: 'updateCalendarViewVisibility',
            calendar_view_id: feedCheckbox.data('calendar-view-id'),
            visible: feedCheckbox.is(':checked') ? '1' : '0',
        };
    },

    refreshCalendar: function () {
        this.getCalendarViewContainer().fullCalendar('refetchEvents');
		vtUtils.enableTooltips(); // Added by Phu Vo on 2021.05.31
    },
    // End Hieu Nguyen

	registerWidgetPostLoadEvent: function () {
		var thisInstance = this;
		app.event.on(Calendar_Calendar_Js.feedsWidgetPostLoadEvent,
				function (e, widgetContainer) {
					thisInstance.restoreFeedsState(widgetContainer);
					thisInstance.renderEvents();
					thisInstance.registerFeedAddEvent(widgetContainer);

                    // Passed widgetContainer into these functions be Hieu Nguyen on 2019-10-30
					thisInstance.registerFeedChangeEvent(widgetContainer);
					thisInstance.registerFeedsColorEditEvent(widgetContainer);
					thisInstance.registerFeedDeleteEvent(widgetContainer);
                    // End Hieu Nguyen

                    // Added by Hieu Nguyen on 2019-11-14 to register feed mass action events
                    thisInstance.registerFeedMassActionEvents();
                    // End Hieu Nguyen

                    // Added by Hieu Nguyen on 2019-11-15 to display toggle all checkbox status based on the checkbox list status
                    thisInstance.displayToggleAllStatus();
                    // End Hieu Nguyen

					// Added by Phu Vo on 2021.05.21 to enable tooltip on calendar ready
					vtUtils.enableTooltips();
					// End Phu Vo
				});
	},
	changeWidgetDisplayState: function (widget, state) {
		var key = widget.data('widgetName') + '_WIDGET_DISPLAY_STATE';
		app.storage.set(key, state);
	},
	registerCollapseEvents: function (widget) {
		var thisInstance = this;
		widget.on('show.bs.collapse hide.bs.collapse', function (e) {
			var widgetStateIndicator = widget.find('i.widget-state-indicator');
			if (e.type === 'hide') {
				widgetStateIndicator.removeClass('fa-chevron-down').addClass('fa-chevron-right');
				thisInstance.changeWidgetDisplayState(widget, 'hide');
			} else {
				widgetStateIndicator.removeClass('fa-chevron-right').addClass('fa-chevron-down');
				thisInstance.changeWidgetDisplayState(widget, 'show');
			}
		});
	},
	getWidgetDisplayState: function (widget) {
		var key = widget.data('widgetName') + '_WIDGET_DISPLAY_STATE';
		var value = app.storage.get(key);
		return (value !== null) ? value : 'show';
	},
	restoreWidgetState: function (widget) {
		if (this.getWidgetDisplayState(widget) === 'show') {
			widget.find('.sidebar-widget-header > a').trigger('click');
		}
	},
	initializeWidgets: function () {
		var thisInstance = this;
		var widgets = jQuery('.sidebar-widget');
		jQuery.each(widgets, function () {
			var widget = jQuery(this);
			var widgetHeader = widget.find('.sidebar-widget-header');
			var dataUrl = widgetHeader.data('url');
			var dataParams = app.convertUrlToDataParams(dataUrl);
			var widgetBody = widget.find('.sidebar-widget-body');
			app.request.post({data: dataParams}).then(function (e, data) {
				if (!e) {
					widgetBody.html(data);
					app.helper.showVerticalScroll(
							widgetBody,
							{
								'autoHideScrollbar': true,
								'scrollbarPosition': 'outside'
							}
					);
//thisInstance.registerCollapseEvents(widget);
//thisInstance.restoreWidgetState(widget);
					app.event.trigger(Calendar_Calendar_Js.feedsWidgetPostLoadEvent, widget);
				} else {
					console.log("error in response : ", e);
				}
			});
		});
	},
	getCalendarViewContainer: function () {
		if (!Calendar_Calendar_Js.calendarViewContainer.length) {
			Calendar_Calendar_Js.calendarViewContainer = jQuery('#mycalendar');
		}
		return Calendar_Calendar_Js.calendarViewContainer;
	},
	getUserPrefered: function (setting) {
		if (typeof Calendar_Calendar_Js.userPreferenceCache[setting] === 'undefined') {
			Calendar_Calendar_Js.userPreferenceCache[setting] = jQuery('#' + setting).val();
		}
		return Calendar_Calendar_Js.userPreferenceCache[setting];
	},
	transformToEventObject: function (eventData, feedCheckbox) {
		var eventObject = {};
		eventObject.id = eventData._recordId;
		eventObject.title = eventData.subject.display_value;

		eventObject.start = eventData.date_start.calendar_display_value;
		eventObject.end = eventData.due_date.calendar_display_value;

		eventObject.url = 'index.php?module=Calendar&view=Detail&record=' + eventData._recordId;

		var module = feedCheckbox.data('calendarFeed');
		var color = feedCheckbox.data('calendarFeedColor');
		var textColor = feedCheckbox.data('calendarFeedTextcolor');

		eventObject.activitytype = eventData.activitytype.value;
		eventObject.status = eventData.eventstatus.value;
		eventObject.allDay = false;
		eventObject.module = module;

		eventObject.color = color;
		eventObject.textColor = textColor;
		return eventObject;
	},
	updateAgendaListView: function () {
		var calendarView = this.getCalendarViewContainer().fullCalendar('getView');
		if (calendarView.name === 'vtAgendaList') {
			this.getCalendarViewContainer().fullCalendar('rerenderEvents');
		}
	},
	updateAllEventsOnCalendar: function () {
		this._updateAllOnCalendar("Events");
		this.updateAgendaListView();
	},
	showEventOnCalendar: function (eventData) {
//method 1
//var feedCheckbox = jQuery('[data-calendar-type="Events_1"]');
//var eventObject = this.transformToEventObject(eventData,feedCheckbox);
//this.getCalendarViewContainer().fullCalendar('renderEvent',eventObject);

//method 2
//var thisInstance = this;
//var eventFeeds = jQuery('[data-calendar-feed="Events"]');
//eventFeeds.each(function(i, eventFeed) {
//thisInstance.refreshFeed(jQuery(eventFeed));
//});

//method 3 - Need to update all events, 
//since support for multiple calendar views for events is enabled
		this.updateAllEventsOnCalendar();
	},
	validateAndSaveEvent: function (modalContainer) {
		var thisInstance = this;
		var params = {
			submitHandler: function (form) {
				// Modified by Hieu Nguyen on 2020-03-03 to fix bug validate required fields on Event QuickCreat form
                var jqForm = jQuery(form);
                var submitButton = jqForm.find('button[name="saveButton"]');
				submitButton.attr('disabled', true);

				if (jqForm.find('.input-error:visible').length > 0) {
                    submitButton.attr('disabled', false);
					return false;
				}

				var e = jQuery.Event(Vtiger_Edit_Js.recordPresaveEvent);
				app.event.trigger(e);

				if (e.isDefaultPrevented()) {
                    submitButton.attr('disabled', false);
					return false;
				}
                // End Hieu Nguyen

				var formData = jQuery(form).serialize();
				app.helper.showProgress();
				app.request.post({data: formData}).then(function (err, data) {
					app.helper.hideProgress();
					if (!err) {
						jQuery('.vt-notification').remove();
						app.helper.hideModal();
						var message = typeof formData.record !== 'undefined' ? app.vtranslate('JS_EVENT_UPDATED') : app.vtranslate('JS_RECORD_CREATED');
						app.helper.showSuccessNotification({"message": message});
						thisInstance.showEventOnCalendar(data);
					} else {
						app.event.trigger('post.save.failed', err);
						jQuery("button[name='saveButton']").removeAttr('disabled');
					}
				});
			}
		};
		modalContainer.find('form').vtValidate(params);
	},
	registerCreateEventModalEvents: function (modalContainer) {
		this.validateAndSaveEvent(modalContainer);
	},
    // Added by Hieu Nguyen on 2019-10-28 to set activity type on event quick create modal
    setActivityType: function(modalContainer, value) {
        var activityTypeInput = modalContainer.find('[name="activitytype"]');
		activityTypeInput.val(value);
		activityTypeInput.trigger('change');
    },
    // End Hieu Nguyen
	setStartDateTime: function (modalContainer, startDateTime) {
		var startDateElement = modalContainer.find('input[name="date_start"]');
		var startTimeElement = modalContainer.find('input[name="time_start"]');
		startDateElement.val(startDateTime.format(vtUtils.getMomentDateFormat()));
		startTimeElement.val(startDateTime.format(vtUtils.getMomentTimeFormat()));
		vtUtils.registerEventForDateFields(startDateElement);
		vtUtils.registerEventForTimeFields(startTimeElement);
		startDateElement.trigger('change');
	},
	showCreateModal: function (moduleName, defaultValues) { // Modified function arguments to set more than 1 default values by Hieu Nguyen on 2019-10-28
		var isAllowed = jQuery('#is_record_creation_allowed').val();

		// Added by Hieu Nguyen on 2022-09-06 to process showing create Task modal only when the Create Task button exist
		if (moduleName == 'Calendar' && jQuery('#btn_LBL_ADD_TASK')[0] == null) {
			this.performingDayClickOperation = false;
			return false;
		}
		// End Hieu Nguyen

		if (isAllowed) {
			var thisInstance = this;
			var quickCreateNode = jQuery('#quickCreateModules').find('[data-name="' + moduleName + '"]');
			if (quickCreateNode.length <= 0) {
				app.helper.showAlertNotification({
					'message': app.vtranslate('JS_NO_CREATE_OR_NOT_QUICK_CREATE_ENABLED')
				});
			} else {
				quickCreateNode.trigger('click');
			}

			app.event.one('post.QuickCreateForm.show', function (e, form) {
				thisInstance.performingDayClickOperation = false;
				var modalContainer = form.closest('.modal');

                // Added by Hieu Nguyen on 2019-10-28 to set default activity type
                if (defaultValues && defaultValues.activityType) {
					thisInstance.setActivityType(modalContainer, defaultValues.activityType);
					// [Calendar] Added by Phu Vo to make activity field become readonly
					form.find('[name="activitytype"]').attr('readonly', true);
					// End Phu Vo
				}
                // End Hieu Nguyen

                // Modified by Hieu Nguyen on 2019-10-28 to get start date time from defaultValues param
				if (defaultValues && defaultValues.startDateTime) {
					thisInstance.setStartDateTime(modalContainer, defaultValues.startDateTime);
				}
                // End Hieu Nguyen

				if (moduleName === 'Events') {
					thisInstance.registerCreateEventModalEvents(form.closest('.modal'));
				}
			});
		}
	},
	_updateAllOnCalendar: function (calendarModule) {
		var thisInstance = this;
		this.getCalendarViewContainer().fullCalendar('addEventSource',
				function (start, end, timezone, render) {
					var activeFeeds = jQuery('[data-calendar-feed="' + calendarModule + '"]:checked');

					var activeFeedsRequestParams = {};
					activeFeeds.each(function () {
						var feedCheckbox = jQuery(this);
						var feedRequestParams = thisInstance.getFeedRequestParams(start, end, feedCheckbox);
						activeFeedsRequestParams[feedCheckbox.data('calendarSourcekey')] = feedRequestParams;
					});

					if (activeFeeds.length) {
						var requestParams = {
							'module': app.getModuleName(),
							'action': 'Feed',
							'mode': 'batch',
							'feedsRequest': activeFeedsRequestParams,
                            'calendar_view': (app.getViewName() == 'Calendar') ? 'MyCalendar' : app.getViewName(),   // Added by Hieu nguyen on 2019-11-14
						};
						var events = [];
						app.helper.showProgress();
						activeFeeds.attr('disabled', 'disabled');
						app.request.post({'data': requestParams}).then(function (e, data) {
							if (!e) {
								data = JSON.parse(data);
								for (var feedType in data) {
                                    // Modified by Hieu Nguyen on 2019-11-12 to change the Calendar Feed logic
                                    var feed = data[feedType];

                                    if (feed != null) {
                                        feed.forEach(function (entry) {
                                            events.push(entry);
                                        });
                                    }
                                    // End Hieu Nguyen
								}
							} else {
								console.log("error in response : ", e);
							}
							activeFeeds.each(function () {
								var feedCheckbox = jQuery(this);
								thisInstance.removeEvents(feedCheckbox);
							});
							render(events);
							activeFeeds.removeAttr('disabled');
							app.helper.hideProgress();
						});
					}
				});
	},
	showCreateTaskModal: function () {
		this.showCreateModal('Calendar');
	},
    // Modified by Hieu Nguyen on 2019-10-28 to set default values on event quick create modal
	showCreateEventModal: function (defaultValues) {
		this.showCreateModal('Events', defaultValues);
	},
    // End Hieu Nguyen
	updateAllTasksOnCalendar: function () {
		this._updateAllOnCalendar("Calendar");
	},
	showTaskOnCalendar: function (data) {
		this.updateAllTasksOnCalendar();
	},
	updateCalendar: function (calendarModule, data) {
		if (calendarModule === 'Events') {
			this.showEventOnCalendar(data);
		} else if (calendarModule === 'Calendar') {
			this.showTaskOnCalendar(data);
		}
	},
	registerPostQuickCreateSaveEvent: function () {
		var thisInstance = this;
		app.event.on("post.QuickCreateForm.save", function (e, data, formData) {
			if (formData.module === 'Calendar' || formData.module === 'Events') {
				thisInstance.updateCalendar(formData.calendarModule, data);
			}
		});
	},
	performingDayClickOperation: false,
	performDayClickAction: function (date, jsEvent, view) {
		if (!this.performingDayClickOperation) {
			this.performingDayClickOperation = true;

            // Modified by Hieu Nguyen on 2019-10-28 to set default value based on the new argument format
			if (date.hasTime() || view.type == 'month') {
				this.showCreateEventModal({ startDateTime: date });
			} 
            else {
				this.showCreateModal('Calendar', { startDateTime: date });  
			}
            // End Hieu Nguyen
		}
	},
	daysOfWeek: {
		Sunday: 0,
		Monday: 1,
		Tuesday: 2,
		Wednesday: 3,
		Thursday: 4,
		Friday: 5,
		Saturday: 6
	},
	refreshFeed: function (feedCheckbox) {
		var thisInstance = this;
		if (feedCheckbox.is(':checked')) {
			feedCheckbox.attr('disabled', 'disabled');
			thisInstance.fetchEvents(feedCheckbox).then(function (events) {
				thisInstance.removeEvents(feedCheckbox);
				thisInstance.getCalendarViewContainer().fullCalendar('addEventSource', events);
				feedCheckbox.removeAttr('disabled');
			}, function (e) {
				console.log("error while fetching events : ", feedCheckbox, e);
			});
		}
		vtUtils.enableTooltips();
	},
	_updateEventOnResize: function (postData, revertFunc) {
		var thisInstance = this;
		app.helper.showProgress();
		app.request.post({'data': postData}).then(function (e, resp) {
			app.helper.hideProgress();
			if (!e) {
				jQuery('.vt-notification').remove();
				if (!resp['ispermitted']) {
					revertFunc();
					app.helper.showErrorNotification({
						'message': app.vtranslate('JS_NO_EDIT_PERMISSION')
					});
				} else if (resp['error']) {
					revertFunc();
				} else {
					if (resp['recurringRecords'] === true) {
						thisInstance.updateAllEventsOnCalendar();
					}
					app.helper.showSuccessNotification({
						'message': app.vtranslate('JS_EVENT_UPDATED')
					});
				}
			} else {
				app.event.trigger('post.save.failed', e);
				thisInstance.updateAllEventsOnCalendar();
			}
		});
	},
	updateEventOnResize: function (event, delta, revertFunc, jsEvent, ui, view) {
		var thisInstance = this;
		if (event.module !== 'Calendar' && event.module !== 'Events') {
			revertFunc();
			return;
		}

		var postData = {
			'module': app.getModuleName(),
			'action': 'DragDropAjax',
			'mode': 'updateDeltaOnResize',
			'id': event.id,
			'activitytype': event.activitytype,
			'secondsDelta': delta.asSeconds(),
			'view': view.name,
			'userid': event.userid
		};

		if (event.recurringcheck) {
			app.helper.showConfirmationForRepeatEvents({ mode: 'edit' }).then(function (recurringData) {    // Modified by Hieu Nguyen on 2020-03-17 to specify the desired confirm popup view
				jQuery.extend(postData, recurringData);
				thisInstance._updateEventOnResize(postData, revertFunc);
			});
		} else {
			thisInstance._updateEventOnResize(postData, revertFunc);
		}
	},
	updateEventOnDrop: function (event, delta, revertFunc, jsEvent, ui, view) {
		var thisInstance = this;
		if (event.module !== 'Calendar' && event.module !== 'Events') {
			revertFunc();
			return;
		}

		// Modified by Phu Vo on 2020.02.28 to prevent drag and drop event to all day
		if (event.allDay && event.module === 'Events') {
			revertFunc();
			const message = app.vtranslate('Calendar.JS_CALENDAR_MOVE_EVENTS_TO_ALLDAY_ERROR_MSG');
			app.helper.showErrorNotification({ message });
			return;
		}
		if (!event.allDay && event.module === 'Calendar') {
			revertFunc();
			const message = app.vtranslate('Calendar.JS_CALENDAR_MOVE_TASK_TO_EVENTS_ZONE_ERROR_MSG');
			app.helper.showErrorNotification({ message });
			return;
		}
		//End Phu Vo

		var postData = {
			'module': 'Calendar',
			'action': 'DragDropAjax',
			'mode': 'updateDeltaOnDrop',
			'id': event.id,
			'activitytype': event.activitytype,
			'secondsDelta': delta.asSeconds(),
			'view': view.name,
			'userid': event.userid
		};

		if (event.recurringcheck) {
			app.helper.showConfirmationForRepeatEvents({ mode: 'edit' }).then(function (recurringData) {    // Modified by Hieu Nguyen on 2020-03-17 to specify the desired confirm popup view
				jQuery.extend(postData, recurringData);
				thisInstance._updateEventOnResize(postData, revertFunc);
			});
		} else {
			thisInstance._updateEventOnResize(postData, revertFunc);
		}
	},
	getActivityTypeClassName: function (activitytype) {
		var className = 'far fa-calendar';
		switch (activitytype) {
			case 'Meeting' :
				className = 'far fa-users';
				break;
			case 'Call' :
				className = 'far fa-phone';
				break;
			case 'Mobile Call' :
				className = 'far fa-mobile';
				break;
		}
		return className;
	},
	addActivityTypeIcons: function (event, element) {
		element.find('.fc-content > .fc-time').prepend(
				'<span>' +
				'<i class="' + this.getActivityTypeClassName(event.activitytype) + '"></i>' +
				'</span>&nbsp;'
				);
	},
	_deleteCalendarEvent: function (eventId, sourceModule, extraParams) {
		var thisInstance = this;
		if (typeof extraParams === 'undefined') {
			extraParams = {};
		}
		var params = {
			"module": "Calendar",
			"action": "DeleteAjax",
			"record": eventId,
			"sourceModule": sourceModule
		};
		jQuery.extend(params, extraParams);

		app.helper.showProgress();
		app.request.post({'data': params}).then(function (e, res) {
			app.helper.hideProgress();
			if (!e) {
				var deletedRecords = res['deletedRecords'];
				for (var key in deletedRecords) {
					var eventId = deletedRecords[key];
					thisInstance.getCalendarViewContainer().fullCalendar('removeEvents', eventId);
				}
				app.helper.showSuccessNotification({
					'message': app.vtranslate('JS_RECORD_DELETED')
				});
			} else {
				app.helper.showErrorNotification({
					'message': app.vtranslate('JS_NO_DELETE_PERMISSION')
				});
			}
		});
	},
	deleteCalendarEvent: function (eventId, sourceModule, isRecurring) {
		var thisInstance = this;
		if (isRecurring) {
			app.helper.showConfirmationForRepeatEvents({ mode: 'delete' }).then(function (postData) {    // Modified by Hieu Nguyen on 2020-03-17 to specify the desired confirm popup view
				thisInstance._deleteCalendarEvent(eventId, sourceModule, postData);
			});
		} else {
			app.helper.showConfirmationBox({
				message: app.vtranslate('LBL_DELETE_CONFIRMATION')
			}).then(function () {
				thisInstance._deleteCalendarEvent(eventId, sourceModule);
			});
		}
	},
	updateEventOnCalendar: function (eventData) {
		this.updateAllEventsOnCalendar();
	},
	_updateEvent: function (form, extraParams) {
		var formData = jQuery(form).serializeFormData();
		extraParams = extraParams || {};
		jQuery.extend(formData, extraParams);
		app.helper.showProgress();
		app.request.post({data: formData}).then(function (err, data) {
			app.helper.hideProgress();
			if (!err) {
				jQuery('.vt-notification').remove();
				app.helper.showSuccessNotification({"message": ''});
				app.event.trigger("post.QuickCreateForm.save", data, jQuery(form).serializeFormData());
				app.helper.hideModal();
			} else {
				app.event.trigger('post.save.failed', err);
				jQuery("button[name='saveButton']").removeAttr("disabled");
			}
		});
	},
	validateAndUpdateEvent: function (modalContainer, isRecurring, initialFormData) {   // Modified by Hieu Nguyen on 2020-03-24 to add initialFormData params
		var thisInstance = this;
		var params = {
			submitHandler: function (form) {
			    // Modified by Hieu Nguyen on 2020-03-03 to fix bug validate required fields on Event QuickCreate form
                var jqForm = jQuery(form);
                var submitButton = jqForm.find('button[name="saveButton"]');
				submitButton.attr('disabled', true);

				if (jqForm.find('.input-error:visible').length > 0) {
                    submitButton.attr('disabled', false);
					return false;
				}

				var e = jQuery.Event(Vtiger_Edit_Js.recordPresaveEvent);
				app.event.trigger(e);

				if (e.isDefaultPrevented()) {
                    submitButton.attr('disabled', false);
					return false;
				}
                // End Hieu Nguyen

				if (isRecurring && initialFormData != jqForm.serialize()) { // Modified by Hieu Nguyen on 2020-03-24 to fix bug show recurring confirm when no data changed
					app.helper.showConfirmationForRepeatEvents({ mode: 'edit' }).then(function (postData) {    // Modified by Hieu Nguyen on 2020-03-17 to specify the desired confirm popup view
						thisInstance._updateEvent(form, postData);
					});
				} else {
					thisInstance._updateEvent(form);
				}
			}
		};
		modalContainer.find('form').vtValidate(params);
	},
    // Modified by Hieu Nguyen on 2020-03-24 to pass in the initialFormData param
	registerEditEventModalEvents: function (modalContainer, isRecurring) {
        var form = modalContainer.find('form');
        var initialFormData = form.serialize();

		this.validateAndUpdateEvent(modalContainer, isRecurring, initialFormData);
	},
    // End Hieu Nguyen
	showEditModal: function (moduleName, record, isRecurring) {
		var thisInstance = this;
		var quickCreateNode = jQuery('#quickCreateModules').find('[data-name="' + moduleName + '"]');
		if (quickCreateNode.length <= 0) {
			app.helper.showAlertNotification({
				'message': app.vtranslate('JS_NO_CREATE_OR_NOT_QUICK_CREATE_ENABLED')
			});
		} else {
			var quickCreateUrl = quickCreateNode.data('url');
			var quickCreateEditUrl = quickCreateUrl + '&mode=edit&record=' + record;
			quickCreateNode.data('url', quickCreateEditUrl);
			quickCreateNode.trigger('click');
			quickCreateNode.data('url', quickCreateUrl);

			if (moduleName === 'Events') {
				app.event.one('post.QuickCreateForm.show', function (e, form) {
					thisInstance.registerEditEventModalEvents(form.closest('.modal'), isRecurring);
				});
			}
		}
	},
	showEditTaskModal: function (taskId) {
		this.showEditModal('Calendar', taskId);
	},
	editCalendarTask: function (taskId) {
		this.showEditTaskModal(taskId);
	},
	showEditEventModal: function (eventId, isRecurring) {
		this.showEditModal('Events', eventId, isRecurring);
	},
	editCalendarEvent: function (eventId, isRecurring) {
		this.showEditEventModal(eventId, isRecurring);
	},
	registerPopoverEvent: function (event, element, calendarView) {
		var dateFormat = this.getUserPrefered('date_format');
		dateFormat = dateFormat.toUpperCase();
		var hourFormat = this.getUserPrefered('time_format');
		var timeFormat = 'HH:mm';
		if (hourFormat === '12') {
			timeFormat = 'hh:mm a';
		}

		var generatePopoverContentHTML = function (eventObj) {
			var timeString = '';
			if (eventObj.activitytype === 'Task') {
				timeString = moment(eventObj._start._i, eventObj._start._f).format(timeFormat);
			} else if (eventObj.module === "Events") {
				if (eventObj._start && typeof eventObj._start != 'undefined') {
					timeString = eventObj._start.format(timeFormat);
				}
				if (eventObj._end && typeof eventObj._end != 'undefined') {
					timeString += ' - ' + eventObj._end.format(timeFormat);
				}
			} else {
				timeString = eventObj._start.format(dateFormat);
			}
			var sourceModule = eventObj.module;
			if (!sourceModule) {
				sourceModule = 'Calendar';
			}

            // Modified by Hieu Nguyen on 2019-11-08
			var popOverHTML = `<strong>${app.vtranslate('JS_EVENT_POPOVER_MAIN_OWNER_NAME')}</strong>: ${eventObj.main_owner_name}<br/>
                <strong>${app.vtranslate('JS_EVENT_POPOVER_TIME')}</strong>: ${timeString}<br/>`;

            if (!eventObj.busy) {
                if (sourceModule == 'Events' || sourceModule == 'Calendar') {
                    popOverHTML += `<strong>${app.vtranslate('JS_EVENT_POPOVER_LOCATION')}</strong>: <a href="#" onclick="GoogleMaps.showMaps('${eventObj.location}')" title="${app.vtranslate('JS_EVENT_POPOVER_SHOW_MAP')}">${eventObj.location}</a><br/>
                        <strong>${app.vtranslate('JS_EVENT_POPOVER_STATUS')}</strong>: ${eventObj.status_label}<br/>`;
                }

                popOverHTML += `<strong>${app.vtranslate('JS_EVENT_POPOVER_DESCRIPTION')}</strong>: ${eventObj.short_description}<br/>
                    <hr/>`;
            }
                
            // End Hieu Nguyen

			if (sourceModule === 'Calendar' || sourceModule == 'Events') {
				popOverHTML += '' +
						'<span class="pull-right cursorPointer" ' +
						'onClick="Calendar_Calendar_Js.deleteCalendarEvent(\'' + eventObj.id +
						'\',\'' + sourceModule + '\',' + eventObj.recurringcheck + ');" title="' + app.vtranslate('JS_DELETE') + '">' +
						'&nbsp;&nbsp;<i class="far fa-trash-alt"></i>' +
						'</span> &nbsp;&nbsp;';

				if (sourceModule === 'Events') {
					popOverHTML += '' +
							'<span class="pull-right cursorPointer" ' +
							'onClick="Calendar_Calendar_Js.editCalendarEvent(\'' + eventObj.id +
							'\',' + eventObj.recurringcheck + ');" title="' + app.vtranslate('JS_EDIT') + '">' +
							'&nbsp;&nbsp;<i class="far fa-pen"></i>' +
							'</span>';
				} else if (sourceModule === 'Calendar') {
					popOverHTML += '' +
							'<span class="pull-right cursorPointer" ' +
							'onClick="Calendar_Calendar_Js.editCalendarTask(\'' + eventObj.id + '\');" title="' + app.vtranslate('JS_EDIT') + '">' +
							'&nbsp;&nbsp;<i class="far fa-pen"></i>' +
							'</span>';
				}

				if (eventObj.status !== 'Held' && eventObj.status !== 'Completed') {
					popOverHTML += '' +
							'<span class="pull-right cursorPointer"' +
							'onClick="Calendar_Calendar_Js.markAsHeld(\'' + eventObj.id + '\');" title="' + app.vtranslate('JS_MARK_AS_HELD') + '">' +
							'<i class="far fa-check"></i>' +
							'</span>';
				} else if (eventObj.status === 'Held') {
					popOverHTML += '' +
							'<span class="pull-right cursorPointer" ' +
							'onClick="Calendar_Calendar_Js.holdFollowUp(\'' + eventObj.id + '\');" title="' + app.vtranslate('JS_CREATE_FOLLOW_UP') + '">' +
							'<i class="far fa-flag"></i>' +
							'</span>';
				}
			}
			return popOverHTML;
		};

		var params = {
			'title': event.title,
			'content': generatePopoverContentHTML(event),
			'trigger': 'hover',
			'closeable': true,
			'placement': 'auto',
			'animation': 'fade'
		};
		if (calendarView.name === 'agendaDay') {
			params.constrains = 'vertical';
		}
		element.webuiPopover(params);
	},
	performPreEventRenderActions: function (event, element) {
		var calendarView = this.getCalendarViewContainer().fullCalendar('getView');
		this.addActivityTypeIcons(event, element);
		this.registerPopoverEvent(event, element, calendarView);
	},
	performMouseOutActions: function (event, jsEvent, view) {
//var currentTarget = jQuery(jsEvent.currentTarget);
	},
	performMouseOverActions: function (event, jsEvent, view) {
//var currentTarget = jQuery(jsEvent.currentTarget);
	},
	getCalendarHeight: function (view) {
		var portion = 0.86;
		if (typeof view !== 'undefined') {
			if (view === 'AgendaList') {
				portion = 1;
			}
		}
//calendar-height is 86% of window height
		return jQuery(window).height() * portion;
	},
	getDefaultCalendarView: function () {
		var userDefaultActivityView = this.getUserPrefered('activity_view');
		if (userDefaultActivityView === 'Today') {
			userDefaultActivityView = 'agendaDay';
		} else if (userDefaultActivityView === 'This Week') {
			userDefaultActivityView = 'agendaWeek';
		} else if (userDefaultActivityView === 'Agenda') {
			userDefaultActivityView = 'vtAgendaList';
		} else {
			userDefaultActivityView = 'month';
		}
		return userDefaultActivityView;
	},
	getDefaultCalendarTimeFormat: function () {
		var userDefaultTimeFormat = this.getUserPrefered('time_format');
		if (parseInt(userDefaultTimeFormat) === 24) {
			userDefaultTimeFormat = 'H(:mm)';
		} else {
			userDefaultTimeFormat = 'h(:mm)a';
		}
		return userDefaultTimeFormat;
	},
	getCalendarConfigs: function () {
		var thisInstance = this;
		var userDefaultActivityView = thisInstance.getDefaultCalendarView();
		var userDefaultTimeFormat = thisInstance.getDefaultCalendarTimeFormat();
		var calenderConfigs = {
			header: {
				left: 'month,agendaWeek,agendaDay,vtAgendaList',
				center: 'title',
				right: 'today prev,next',
			},
			views: {
				vtAgendaList: {
					duration: {days: Calendar_Calendar_Js.numberOfDaysInAgendaView}
				}
			},
			fixedWeekCount: false,
			firstDay: thisInstance.daysOfWeek[thisInstance.getUserPrefered('start_day')],
			scrollTime: thisInstance.getUserPrefered('start_hour'),
			editable: true,
			eventLimit: true,
			defaultView: userDefaultActivityView,
			slotLabelFormat: userDefaultTimeFormat,
			timeFormat: userDefaultTimeFormat,
			events: [],
			monthNames: [
				app.vtranslate('LBL_JANUARY'),
				app.vtranslate('LBL_FEBRUARY'),
				app.vtranslate('LBL_MARCH'),
				app.vtranslate('LBL_APRIL'),
				app.vtranslate('LBL_MAY'),
				app.vtranslate('LBL_JUNE'),
				app.vtranslate('LBL_JULY'),
				app.vtranslate('LBL_AUGUST'),
				app.vtranslate('LBL_SEPTEMBER'),
				app.vtranslate('LBL_OCTOBER'),
				app.vtranslate('LBL_NOVEMBER'),
				app.vtranslate('LBL_DECEMBER')
			],
            // Modified by Hieu Nguyen on 2020-02-26 to prevent short month name override the long month name at May
			monthNamesShort: [
				app.vtranslate('LBL_SHORT_JAN'),
				app.vtranslate('LBL_SHORT_FEB'),
				app.vtranslate('LBL_SHORT_MAR'),
				app.vtranslate('LBL_SHORT_APR'),
				app.vtranslate('LBL_SHORT_MAY'),
				app.vtranslate('LBL_SHORT_JUN'),
				app.vtranslate('LBL_SHORT_JUL'),
				app.vtranslate('LBL_SHORT_AUG'),
				app.vtranslate('LBL_SHORT_SEP'),
				app.vtranslate('LBL_SHORT_OCT'),
				app.vtranslate('LBL_SHORT_NOV'),
				app.vtranslate('LBL_SHORT_DEC')
			],
            // End Hieu Nguyen
			dayNames: [
				app.vtranslate('LBL_SUNDAY'),
				app.vtranslate('LBL_MONDAY'),
				app.vtranslate('LBL_TUESDAY'),
				app.vtranslate('LBL_WEDNESDAY'),
				app.vtranslate('LBL_THURSDAY'),
				app.vtranslate('LBL_FRIDAY'),
				app.vtranslate('LBL_SATURDAY')
			],
			dayNamesShort: [
				app.vtranslate('LBL_SUN'),
				app.vtranslate('LBL_MON'),
				app.vtranslate('LBL_TUE'),
				app.vtranslate('LBL_WED'),
				app.vtranslate('LBL_THU'),
				app.vtranslate('LBL_FRI'),
				app.vtranslate('LBL_SAT')
			],
			buttonText: {
				'today': app.vtranslate('LBL_TODAY'),
				'month': app.vtranslate('LBL_MONTH'),
				'week': app.vtranslate('LBL_WEEK'),
				'day': app.vtranslate('LBL_DAY'),
				'vtAgendaList': app.vtranslate('LBL_AGENDA')
			},
            titleFormat: 'D MMMM, YYYY',    // Added by Hieu Nguyen on 2020-02-26 to display friendly date format
            columnFormat: 'ddd ' + _CALENDAR_USER_SETTINGS.date_format.replace(/(yyyy-|-yyyy)/, '').replace('dd', 'D').replace('mm', 'M').replace('-', '/'),   // Added by Hieu Nguyen on 2020-03-17 to display date format according to user settings
			allDayText: app.vtranslate('LBL_ALL_DAY'),
			dayClick: function (date, jsEvent, view) {
				thisInstance.performDayClickAction(date, jsEvent, view);
			},
			// [Calendar] Bug #5: Modified by Phu Vo on 2020.02.28: Lỗi tooltip khi kéo thả
			eventResizeStart: function(event, jsEvent, ui, view) {
				// Hide popover when the resize start (will show again when resize get done)
				thisInstance.toggleVisibilityAllWebUiPopover(false);
			},
			eventResizeStop: function(event, jsEvent, ui, view) {
				$('.webui-popover').remove(); // We don't need them, anyway popover will regenerate later
				thisInstance.toggleVisibilityAllWebUiPopover(true);
			},
			eventResize: function (event, delta, revertFunc, jsEvent, ui, view) {
				thisInstance.updateEventOnResize(event, delta, revertFunc, jsEvent, ui, view);
			},
			eventDragStart: function (event, jsEvent, ui, view) {
				// Hide popover when the drag start (will show again when resize get done)
				thisInstance.toggleVisibilityAllWebUiPopover(false);
			},
			eventDragStop: function (event, jsEvent, ui, view) {
				$('.webui-popover').remove(); // We don't need them, anyway popover will regenerate later
				thisInstance.toggleVisibilityAllWebUiPopover(true);
			},
			eventDrop: function (event, delta, revertFunc, jsEvent, ui, view) {
				thisInstance.updateEventOnDrop(event, delta, revertFunc, jsEvent, ui, view);
			},
			// End Phu Vo
			eventRender: function (event, element) {
				thisInstance.performPreEventRenderActions(event, element);
			},
			eventMouseover: function (event, jsEvent, view) {
				thisInstance.performMouseOverActions(event, jsEvent, view);
			},
			eventMouseout: function (event, jsEvent, view) {
				thisInstance.performMouseOutActions(event, jsEvent, view);
			},
			viewRender: function (view, element) {
				if (view.name === 'vtAgendaList') {
					jQuery(".sidebar-essentials").addClass("hide");
					jQuery(".content-area").addClass("full-width");
					jQuery(".essentials-toggle").addClass("hide");
				} else {
					jQuery(".essentials-toggle").removeClass("hide");
					if (Calendar_Calendar_Js.sideBarEssentialsState === 'show') {
						jQuery(".sidebar-essentials").removeClass("hide");
						jQuery(".content-area").removeClass("full-width");
					} else if (Calendar_Calendar_Js.sideBarEssentialsState === 'hidden') {
						jQuery(".sidebar-essentials").addClass("hide");
						jQuery(".content-area").addClass("full-width");
					}
				}
			}
		};
		return calenderConfigs;
	},
	toggleVisibilityAllWebUiPopover: function(status = true) {
		if (status) {
			return setTimeout(() => {
				$('.webui-popover').remove(); // We don't need them, anyway popover will regenerate later
				$('#hide-all-popover').remove();
			}, 100);
		}
		
		return $('body').append('<style id="hide-all-popover">.webui-popover { display: none !important }</style>');
	},
	fetchAgendaEvents: function (date) {
		var aDeferred = jQuery.Deferred();

		var dateFormat = this.getUserPrefered('date_format');
		dateFormat = dateFormat.toUpperCase();
		var startDate = date.format(dateFormat);

		var requestParams = {
			'module': app.getModuleName(),
			'action': 'FetchAgendaEvents',
			'startDate': startDate,
			'numOfDays': Calendar_Calendar_Js.numberOfDaysInAgendaView
		};

		app.helper.showProgress();
		app.request.post({'data': requestParams}).then(function (e, res) {
			app.helper.hideProgress();
			if (!e) {
				aDeferred.resolve(res);
			} else {
				aDeferred.reject(e);
			}
		});

		return aDeferred.promise();
	},
	fetchEventDetails: function (eventId) {
		var aDeferred = jQuery.Deferred();

		var requestParams = {
			'module': app.getModuleName(),
			'action': 'CalendarActions',
			'mode': 'fetchAgendaViewEventDetails',
			'id': eventId
		};

		app.helper.showProgress();
		app.request.post({'data': requestParams}).then(function (e, res) {
			app.helper.hideProgress();
			if (!e) {
				aDeferred.resolve(res);
			} else {
				aDeferred.reject(e);
			}
		});

		return aDeferred.promise();
	},
	registerAgendaListView: function () {
		var thisInstance = this;
		var FC = jQuery.fullCalendar;
		var view = FC.View;
		var agendaListView;

		agendaListView = view.extend({
			initialize: function () {
				var dateFormat = thisInstance.getUserPrefered('date_format');
				this.vtDateFormat = dateFormat.toUpperCase();
			},
			getCourseDay: function (date) {
				var today = moment();
				var dateFormat = this.vtDateFormat;
				var todayDate = moment().format(dateFormat);
				if (todayDate === date.format(dateFormat)) {
					return app.vtranslate('LBL_TODAY').toUpperCase();
				} else {
					var tomorrow = today.add(1, 'days');
					if (tomorrow.format(dateFormat) === date.format(dateFormat)) {
						return app.vtranslate('LBL_TOMORROW').toUpperCase();
					}
				}
				return date.format('LL');
			},
			getWeekDay: function (date) {
				var weekDay = date.format('dddd');
				/*Modified by Kelvin Thang - Date:2018-08-27*/
				/*var label = 'LBL_' + weekDay.toUpperCase();*/
				var label = weekDay.toUpperCase();
				return app.vtranslate(label).toUpperCase();
			},
			renderHtml: function () {
				var startDate = moment(this.intervalStart);
				var dateFormat = this.vtDateFormat;
				var skeleton = '' +
						'<div class="agendaListView">';
				for (var i = 0; i < Calendar_Calendar_Js.numberOfDaysInAgendaView; i++) {
					var daysToAdd = i ? 1 : 0;
					var date = startDate.add(daysToAdd, 'days').format(dateFormat);
					var day = this.getCourseDay(startDate);
					var weekDay = this.getWeekDay(startDate);
					var part = '' +
							'<div class="agendaListDay" data-date="' + date + '">' +
							'<div class="agendaListViewHeader clearfix">' +
							'<div class="day">' + day + '</div>' +
							'<div class="weekDay">' + weekDay + '</div>' +
							'</div>' +
							'<hr>' +
							'<div class="agendaListViewBody">' +
							'</div>' +
							'</div>';
					skeleton += part;
				}
				skeleton +=
						'</div>';
				return skeleton;
			},
			generateEventDetailsHTML: function (res) {
				var html = '<div class="agenda-table-cell"></div>' +
						'<div class="agenda-table-cell"></div>' +
						'<div class="agenda-table-cell">' +
						'<div class="agenda-table details">';
				for (var fieldLabel in res) {
					var eachItem = '<div class="agenda-details">';
					eachItem += '<span class="detailLabel">' + fieldLabel + '</span>';
					eachItem += '<span class="separator"> : </span>';
					eachItem += '<span class="fieldValue">' + jQuery.trim(res[fieldLabel]) + '</span>';
					eachItem += '</div>';
					html += eachItem;
				}
				html += '</div>' +
						'</div>';
				return html;
			},
			registerToggleMoreDetailsEvent: function (container) {
				var fcInstance = this;
				container.on('click', '.agenda-more-details', function () {
					var target = jQuery(this);
					var indicator = target.find('i');
					var wrapper = target.closest('.agenda-event-wrapper');
					var eventId = wrapper.data('eventId');
					var details = wrapper.find('.agenda-event-details');
					if (indicator.hasClass('fa-plus-square')) {
						if (details.data('isDetailsLoaded')) {
							details.removeClass('hide');
						} else {
							thisInstance.fetchEventDetails(eventId).then(function (res) {
								details.append(fcInstance.generateEventDetailsHTML(res));
								details.removeClass('hide');
								details.data('isDetailsLoaded', true)
							});
						}
						indicator.removeClass('fa-plus-square').
								addClass('fa-minus-square');
					} else {
						details.addClass('hide');
						indicator.removeClass('fa-minus-square').
								addClass('fa-plus-square');
					}
				});
			},
			registerAgendaViewEvents: function (container) {
				this.registerToggleMoreDetailsEvent(container);
			},
			render: function () {
				this.el.html(this.renderHtml());
				var height = thisInstance.getCalendarHeight('AgendaList') + 'px';
				var agendaListContainer = this.el.find('.agendaListView');
				agendaListContainer.css('max-height', height).css('min-height', height);
				this.registerAgendaViewEvents(agendaListContainer);
			},
			renderEvents: function () {
				this.renderVtAgendaEvents();
			},
			getAgendaActionsHTML: function (event) {
				var actionsMarkup = '' +
						'<div class="agenda-event-actions verticalAlignMiddle">' +
						'<span class="pull-right cursorPointer" ' +
						'onClick="Calendar_Calendar_Js.deleteCalendarEvent(\'' + event.id +
						'\',\'Events\',' + event.recurringcheck + ');" title="' + app.vtranslate('JS_DELETE') + '">' +
						'&nbsp;&nbsp;<i class="far fa-trash-alt"></i>' +
						'</span>' +
						'<span class="pull-right cursorPointer" ' +
						'onClick="Calendar_Calendar_Js.editCalendarEvent(\'' + event.id +
						'\',' + event.recurringcheck + ');" title="' + app.vtranslate('JS_EDIT') + '">' +
						'&nbsp;&nbsp;<i class="far fa-pen"></i>' +
						'</span>';

				if (event.status !== 'Held') {
					actionsMarkup += '' +
							'<span class="pull-right cursorPointer"' +
							'onClick="Calendar_Calendar_Js.markAsHeld(\'' + event.id + '\');" title="' + app.vtranslate('JS_MARK_AS_HELD') + '">' +
							'&nbsp;&nbsp;<i class="far fa-check"></i>' +
							'</span>';
				} else if (event.status === 'Held') {
					actionsMarkup += '' +
							'<span class="pull-right cursorPointer" ' +
							'onClick="Calendar_Calendar_Js.holdFollowUp(\'' + event.id + '\');" title="' + app.vtranslate('JS_CREATE_FOLLOW_UP') + '">' +
							'&nbsp;&nbsp;<i class="far fa-flag"></i>' +
							'</span>';
				}
				actionsMarkup +=
						'</div>';
				return actionsMarkup;
			},
			getAgendaEventTitle: function (event) {
				return event.status === 'Held' ?
						'<span><strike>' + event.title + '</strike><span>' :
						'<span>' + event.title + '</span>';
			},
			generateEventHTML: function (event) {
				var html = '' +
						'<div class="agenda-event-wrapper" data-event-id="' + event.id + '">' +
						'<div class="agenda-event-info">' +
						'<div class="agenda-event-time verticalAlignMiddle">' +
						'<div>' + event.startTime + ' - ' + event.endTime + '</div>' +
						'</div>' +
						'<div class="agenda-more-details cursorPointer verticalAlignMiddle">' +
						'<i class="far fa-pen" title=' + app.vtranslate('JS_DETAILS') + '></i>' +
						'</div>' +
						'<div class="agenda-event-title verticalAlignMiddle">&nbsp;' +
						'<i class="' + thisInstance.getActivityTypeClassName(event.activitytype) + '" title="' + app.vtranslate(event.activitytype) + '"></i>&nbsp;&nbsp;&nbsp;';
				if (event.recurringcheck) {
					html += '<i class="far fa-repeat" style="font-size:10px;" title="' + app.vtranslate('JS_RECURRING_EVENT') + '"></i>&nbsp;';
				}
				html += this.getAgendaEventTitle(event) +
						'</div>' +
						'<div class="agenda-event-status verticalAlignMiddle">' + event.status + '</div>' +
						this.getAgendaActionsHTML(event) +
						'</div>' +
						'<div class="agenda-event-details hide verticalAlignMiddle">' +
						'</div>' +
						'</div>';
				return html;
			},
			displayNoEventsMessage: function () {
				jQuery('.agendaListViewBody').each(function (i, element) {
					var currentList = jQuery(element);
					var eventsElements = currentList.find('.agenda-event-wrapper');
					if (!eventsElements.length) {
						currentList.html(
								'<div class="agendaNoEvents">' +
								app.vtranslate('JS_NO_EVENTS_F0R_THE_DAY') +
								'</div>'
								);
					}
				});
			},
			renderVtAgendaEvents: function () {
				var fcInstance = this;
				var currentDate = moment(this.intervalStart);
				thisInstance.fetchAgendaEvents(currentDate).then(function (agendaEvents) {
//cleanup before render
					jQuery('.agendaListViewBody').empty();
					for (var key in agendaEvents) {
						var container = jQuery('[data-date="' + key + '"]');
						var containerBody = container.find('.agendaListViewBody');
						var eventsPerDay = agendaEvents[key];
						jQuery.each(eventsPerDay, function (i, event) {
							containerBody.append(fcInstance.generateEventHTML(event));
						});
					}
					fcInstance.displayNoEventsMessage();
				});
			}
		});

		FC.views.vtAgendaList = agendaListView;
	},
	registerGotoDateButtonAction: function (navigationsContainer) {
		var thisInstance = this;
		var gotoButton = navigationsContainer.find('.vt-goto-date');
		gotoButton.datepicker({
			'autoclose': true,
			'todayBtn': "linked",
			'format': thisInstance.getUserPrefered('date_format'),
		}).on('changeDate', function (e) {
			thisInstance.getCalendarViewContainer().fullCalendar('gotoDate', moment(e.date));
		});
	},
	addGotoDateButton: function () {
		var navigationsContainer = this.getCalendarViewContainer().find(
				'.fc-right > .fc-button-group'
				);
		var buttonHTML = '' +
				'<button type="button" class="vt-goto-date fc-button fc-state-default fc-corner-left">' +
				'<span class="far fa-calendar"></span>' +
				'</button>';
		navigationsContainer.find('.fc-prev-button').after(buttonHTML);
		this.registerGotoDateButtonAction(navigationsContainer);
	},
	performPostRenderCustomizations: function () {
		this.addGotoDateButton();
	},
	initializeCalendar: function () {
		this.registerAgendaListView();
		var calendarConfigs = this.getCalendarConfigs();
		this.getCalendarViewContainer().fullCalendar(calendarConfigs);
		this.performPostRenderCustomizations();
		this.performSidebarEssentialsRecognition();
	},
	performSidebarEssentialsRecognition: function () {
		app.event.on("Vtiger.Post.MenuToggle", function () {
			var essentialsHidden = jQuery(".sidebar-essentials").hasClass("hide");
			if (essentialsHidden) {
				Calendar_Calendar_Js.sideBarEssentialsState = 'hidden';
			} else {
				Calendar_Calendar_Js.sideBarEssentialsState = 'show';
			}
		});
		var essentialsHidden = jQuery(".sidebar-essentials").hasClass("hide");
		if (essentialsHidden) {
			Calendar_Calendar_Js.sideBarEssentialsState = 'hidden';
		} else {
			Calendar_Calendar_Js.sideBarEssentialsState = 'show';
		}
	},
	// Implemented by Phu Vo on 2021.05.21 to handle dropdown shown on scrollable container
	registerDropdownAutoPosition: function () {
		$('body').on('shown.bs.dropdown', '.activitytype-actions', function (event) {
			let container = $(event.target);
			let scrollContainer = container.closest('.sidebar-widget-body');
			let dropdownMenu = container.find('.dropdown-menu');
			let scrollContainerHeight = scrollContainer.innerHeight();
			let dropdownMenuHeight = dropdownMenu.innerHeight();
			let dropdownMenuTotalTop = container.position().top + dropdownMenu.position().top + dropdownMenuHeight;
            let gap = dropdownMenuTotalTop - scrollContainerHeight;

            if (gap > 0) {
            	dropdownMenu.css('top', - dropdownMenuHeight);
            }
		});
	},
	registerEvents: function () {
		this._super();
		this.initializeCalendar();
		this.registerWidgetPostLoadEvent();
		this.initializeWidgets();
		this.registerPostQuickCreateSaveEvent();
		this.registerDropdownAutoPosition(); // Added by Phu Vo on 2021.05.21
	}
});