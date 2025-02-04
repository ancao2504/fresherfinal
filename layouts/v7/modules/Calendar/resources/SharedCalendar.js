Calendar_Calendar_Js('Calendar_SharedCalendar_Js', {

	calendarViewContainer : false

}, {
    // Added by Hieu Nguyen on 2019-10-30 to handle the user feed editor
    registerFeedAddEvent: function (widgetContainer) {
		var thisInstance = this;

		widgetContainer.find('.add-calendar-feed').on('click', function () {
			thisInstance.showUserFeedEditor();
		});
	},

    registerFeedsColorEditEvent: function (widgetContainer) {
		var thisInstance = this;
        
		widgetContainer.on('click', '.editCalendarFeedColor', function () {
            var selectedUserId = jQuery(this).closest('li.calendar-feed-indicator').find('input.toggleCalendarFeed').data('calendar-userid');
            thisInstance.showUserFeedEditor(selectedUserId);
        });
	},
    
    showUserFeedEditor: function (selectedUserId = '') {
        var thisInstance = this;

		var params = {
			module: app.getModuleName(),
			view: 'UserCalendarViews',
			mode: 'showUserFeedEditor',
            selected_user_id: selectedUserId
		};

		app.helper.showProgress();

		app.request.post({ data: params }).then(function (err, res) {
			app.helper.hideProgress();

			if (err || !res) {
                var message = app.vtranslate('JS_USER_FEED_EDITOR_LOAD_ERROR_MSG');
				app.helper.showErrorNotification({ message: message });
                return;
			}

            if (res == 'SELECTED_USER_NOT_IN_SAVED_FEED') {
                var message = app.vtranslate('JS_USER_FEED_EDITOR_SELECTED_USER_NOT_IN_SAVED_FEED_ERROR_MSG');
				app.helper.showErrorNotification({ message: message });
                return;
			}

            var modalParams = {
                cb: function (modalContainer) {
                    thisInstance.initUserFeedEditor(modalContainer);
                    modalContainer.find('form').vtValidate({ submitHandler: thisInstance.saveUserFeed.bind(thisInstance) });
                }
            };

            app.helper.showModal(res, modalParams);
		});
    },

    initUserFeedEditor: function (modalContainer) {
        var thisInstance = this;
		var editorMode = modalContainer.find('#editor-mode').val();
        var selectedUserIdInput = modalContainer.find('#selected-user-id');
        var selectedColorInput = modalContainer.find('#selected-color');
        var userSelector = modalContainer.find('#user-selector');
        var colorPicker = modalContainer.find('#color-picker');

        // Init user selector
        userSelector.select2({
            placeholder: app.vtranslate('JS_CUSTOM_OWNER_FIELD_SELECT2_PLACEHOLDER_USER_ONLY'),
            minimumInputLength: _VALIDATION_CONFIG.autocomplete_min_length,
            ajax: {
                type: 'POST',
                dataType: 'json',
                cache: true,
                data: function(term, page) {
                    term = term.trim();

                    if (term.length == 0) {
                        userSelector.select2('close');
                        userSelector.select2('open');
                        return null;
                    }

                    var data = {
                        module: 'Calendar',
                        action: 'CalendarUserActions',
                        mode: 'getAvailableUserFeedList',
                        keyword: term
                    };

                    return data;
                },
                results: function(data) {
                    return { results: data.result };
                },
                transport: function(params) {
                    return jQuery.ajax(params);
                },
            },
        });

		userSelector.on('change', function () {
            // Update value into hidden input
            selectedUserIdInput.val($(this).val());

            // Change color when the selected user changed
			var newColor = thisInstance.getRandomColor();
			selectedColorInput.val(newColor);
			colorPicker.ColorPickerSetColor(newColor);
		});
        
        // Init color picker
		thisInstance.initializeColorPicker(colorPicker, {}, function (hsb, hex, rgb) {
            // Update value into hidden input
			selectedColorInput.val('#' + hex);
		});

        if (editorMode == 'update') {
            // Display previously saved color
            colorPicker.ColorPickerSetColor(selectedColorInput.val());
        }
    },

    saveUserFeed: function (form) {
        form = jQuery(form);
        var thisInstance = this;
		var selectedUserId = form.find('#selected-user-id').val();
		var selectedColor = form.find('#selected-color').val();
		var editorMode = form.find('#editor-mode').val();
        var submitButton = form.find('[name="saveButton"]');

        if (selectedUserId == '') {
            var message = app.vtranslate('JS_USER_FEED_EDITOR_USER_NOT_SELECTED_ERROR_MSG');
            app.helper.showErrorNotification({ message: message });
            return;
        }

		var params = {
			module: 'Calendar',
			action: 'CalendarUserActions',
			mode: 'saveUserFeed',
			selected_user_id: selectedUserId,
			selected_color: selectedColor,
			editor_mode: editorMode,
		};

        submitButton.attr('disabled', true);
		app.helper.showProgress();

		app.request.post({ data: params }).then(function (err, data) {
            var message = '';

			if (err || !data) {
				message = app.vtranslate('JS_USER_FEED_EDITOR_SAVE_ERROR_MSG');
                app.helper.showErrorNotification({ message: message });
                app.helper.hideProgress();
                submitButton.attr('disabled', false);
                return;
			}

            // Commented out these lines as the new Calendar logic has no blind data anymore
			/*if (!data.success && data.message == 'USER_CALENDAR_IS_PRIVATE') {
				message = app.vtranslate('JS_USER_FEED_EDITOR_ADD_PRIVATE_CALENDAR_ERROR_MSG');
                app.helper.showErrorNotification({ message: message });
                app.helper.hideProgress();
                submitButton.attr('disabled', false);
                return;
			}

            if (!data.success && data.message == 'USER_CALENDAR_NOT_SHARED_TO_CURRENT_USER') {
				message = app.vtranslate('JS_USER_FEED_EDITOR_ADD_UNSHARED_CALENDAR_ERROR_MSG');
                app.helper.showErrorNotification({ message: message });
                app.helper.hideProgress();
                submitButton.attr('disabled', false);
                return;
			}*/

            var calendarFeedList = jQuery('#calendarview-feeds > ul.feedslist');
            message = app.vtranslate('JS_USER_FEED_EDITOR_UPDATE_FEED_SUCCESS_MSG');

            if (editorMode == 'add') {
                message = app.vtranslate('JS_USER_FEED_EDITOR_ADD_FEED_SUCCESS_MSG');

                // Create new feed element in sidebar after finish adding new feed
                var selectedUserName = form.find('#user-selector').closest('.fieldValue').find('.select2-choice').text().trim();
                var feedIndicatorTemplate = jQuery('#calendarview-feeds').find('ul.dummy > li.feed-indicator-template');
                var newFeedIndicator = feedIndicatorTemplate.clone(true, true);
                newFeedIndicator.removeClass('feed-indicator-template');
                newFeedIndicator.find('span:first').text(selectedUserName);
                newFeedIndicator.find('span:first').attr('title', selectedUserName); // Added by Phu Vo on 2021.05.31 to enable title

                var newFeedCheckbox = newFeedIndicator.find('.toggleCalendarFeed');
                newFeedCheckbox.data('calendar-fieldlabel', selectedUserName).data('calendar-sourcekey', 'Events_' + selectedUserId);
                newFeedCheckbox.attr('data-calendar-userid', selectedUserId).attr('checked', true);

                calendarFeedList.append(newFeedIndicator);
            }

            var contrast = app.helper.getColorContrast(selectedColor);
            var textColor = (contrast === 'dark') ? 'white' : 'black';

            var feedCheckbox = calendarFeedList.find('input[data-calendar-userid="'+ selectedUserId +'"]');
            feedCheckbox.data('calendar-feed-color', selectedColor).data('calendar-feed-textcolor', textColor);

            var feedIndicator = feedCheckbox.closest('.calendar-feed-indicator');
            feedIndicator.css({ 'background-color': selectedColor, 'color': textColor });
            thisInstance.refreshCalendar();

            app.helper.hideProgress();
            app.helper.hideModal();
            app.helper.showSuccessNotification({ message: message });
		});
    },

    getFeedUpdateVisibilityParams: function (feedCheckbox) {
        return {
			module: 'Calendar',
			action: 'CalendarUserActions',
			mode: 'updateUserFeedVisibility',
			user_feed_id: feedCheckbox.data('calendar-userid'),
            visible: feedCheckbox.is(':checked') ? '1' : '0',
		};
    },

    getFeedDeleteParameters: function(feedCheckbox) {
		return {
			module: 'Calendar',
			action: 'CalendarUserActions',
			mode: 'deleteUserFeed',
			user_feed_id: feedCheckbox.data('calendar-userid')
		};
	},
    // End Hieu Nguyen

	getCalendarViewContainer : function() {
		if(!Calendar_SharedCalendar_Js.calendarViewContainer.length) {
			Calendar_SharedCalendar_Js.calendarViewContainer = jQuery('#sharedcalendar');
		}
		return Calendar_SharedCalendar_Js.calendarViewContainer;
	},

	getFeedRequestParams : function(start,end,feedCheckbox) {
		var dateFormat = 'YYYY-MM-DD';
		var startDate = start.format(dateFormat);
		var endDate = end.format(dateFormat);
		return {
			'start' : startDate,
			'end' : endDate,
			'type' : feedCheckbox.data('calendarFeed'),
			'userid' : feedCheckbox.data('calendarUserid'),
			'group' : feedCheckbox.data('calendarGroup'),
			'color' : feedCheckbox.data('calendarFeedColor'),
			'textColor' : feedCheckbox.data('calendarFeedTextcolor'),
            'calendar_view': 'SharedCalendar',  // Added by Hieu Nguyen on 2019-11-12
		};
	},

	removeEvents : function(feedCheckbox) {
		var userId = feedCheckbox.data('calendarUserid');
		this.getCalendarViewContainer().fullCalendar('removeEvents', 
		function(eventObj) {
			return parseInt(userId) === parseInt(eventObj.userid);
		});
	},

	_colorize : function(feedCheckbox) {
		var thisInstance = this;
		var sourcekey = feedCheckbox.data('calendarSourcekey');
		var color = feedCheckbox.data('calendarFeedColor');
		if(color === '' || typeof color === 'undefined') {
			color = app.storage.get(sourcekey);
			if(!color) {
				color = thisInstance.getRandomColor();
				app.storage.set(sourcekey, color);
			}
			feedCheckbox.data('calendarFeedColor',color);
			feedCheckbox.closest('.calendar-feed-indicator').css({'background-color':color});
		}
	},

	colorizeFeed : function(feedCheckbox) {
		this._colorize(feedCheckbox);
		this.assignFeedTextColor(feedCheckbox);
	},

    // Commented out the functions below by Hieu Nguyen on 2019-10-30 to make the feed editor process simpler
    /*
	registerAddUserCalendarViewActions : function(modalContainer) {
		this.registerColorEditorEvents(modalContainer);
	},

	showAddUserCalendarView : function() {
		var thisInstance = this;
		var params = {
			module : app.getModuleName(),
			view : 'UserCalendarViews',
			mode : 'addUserCalendar'
		};
		app.helper.showProgress();
		app.request.post({'data':params}).then(function(e,data) {
			app.helper.hideProgress();
			if(!e) {
				if(jQuery(data).find('select[name="usersList"] > option').length) {
					app.helper.showModal(data,{
						'cb' : function(modalContainer) {
							thisInstance.registerAddUserCalendarViewActions(modalContainer);
						}
					});
				} else {
					app.helper.showErrorNotification({
						'message' : app.vtranslate('JS_NO_CALENDAR_VIEWS_TO_ADD')
					});
				}
			} else {
				console.log("network error : ",e);
			}
		});
	},

	showAddCalendarFeedEditor : function() {
		this.showAddUserCalendarView();
	},

	registerUserChangeEvent : function(modalContainer) {
		var thisInstance = this;
		var calendarFeedList = jQuery('#calendarview-feeds > ul.feedslist');
		modalContainer.find('select[name="usersList"]').on('change', 
		function() {
			var currentUserId = jQuery(this).val();
			var currentColor = thisInstance.getRandomColor();
			var feedCheckbox = calendarFeedList.find('input[data-calendar-userid="'+currentUserId+'"]');
			if(feedCheckbox.length) {
				currentColor = feedCheckbox.data('calendarFeedColor');
			}
			modalContainer.find('.selectedColor').val(currentColor);
			modalContainer.find('.calendarColorPicker').ColorPickerSetColor(currentColor);
		});
	},

	saveFeedSettings : function(modalContainer) {
		var thisInstance = this;
		var selectedType = modalContainer.find('.selectedType');
		var selectedUserId = selectedType.val();
		var selectedUserName = selectedType.data('typename');
		var calendarGroup = selectedType.data('calendarGroup');
		var selectedColor = modalContainer.find('.selectedColor').val();
		var editorMode = modalContainer.find('.editorMode').val();

		var params = {
			module: 'Calendar',
			action: 'CalendarUserActions',
			mode : 'addUserCalendar',
			selectedUser : selectedUserId,
			selectedColor : selectedColor
		};

		app.helper.showProgress();
		app.request.post({'data':params}).then(function(e) {
			if(!e) {
				var calendarFeedList = jQuery('#calendarview-feeds > ul.feedslist');
				var message = app.vtranslate('JS_CALENDAR_VIEW_COLOR_UPDATED_SUCCESSFULLY');
				if(editorMode === 'create') {
					var feedIndicatorTemplate = jQuery('#calendarview-feeds').find('ul.dummy > li.feed-indicator-template');
					feedIndicatorTemplate.removeClass('.feed-indicator-template');
					var newFeedIndicator = feedIndicatorTemplate.clone(true,true);
					newFeedIndicator.find('span:first').text(selectedUserName);
					var newFeedCheckbox = newFeedIndicator.find('.toggleCalendarFeed');
					newFeedCheckbox.attr('data-calendar-sourcekey','Events_'+selectedUserId).
					attr('data-calendar-feed','Events').
					attr('data-calendar-fieldlabel',selectedUserName).
					attr('data-calendar-userid',selectedUserId).
					attr('data-calendar-group',calendarGroup).
					attr('checked','checked');
					calendarFeedList.append(newFeedIndicator);
					message = app.vtranslate('JS_CALENDAR_VIEW_ADDED_SUCCESSFULLY');
				}

				var contrast = app.helper.getColorContrast(selectedColor);
				var textColor = (contrast === 'dark') ? 'white' : 'black';
				var feedCheckbox = calendarFeedList.find('input[data-calendar-userid="'+selectedUserId+'"]');
				feedCheckbox.data('calendarFeedColor',selectedColor).
				data('calendarFeedTextcolor',textColor);
				var feedIndicator = feedCheckbox.closest('.calendar-feed-indicator');
				feedIndicator.css({'background-color':selectedColor,'color':textColor});
				thisInstance.refreshFeed(feedCheckbox);

				app.helper.hideProgress();
				app.helper.hideModal();
				app.helper.showSuccessNotification({'message':message});
			} else {
				console.log("error : ",e);
			}
		});

	},

	registerColorEditorSaveEvent : function(modalContainer) {
		var thisInstance = this;
		modalContainer.find('[name="saveButton"]').on('click', function() {
			jQuery(this).attr('disabled','disabled');
			var usersList = modalContainer.find('select[name="usersList"]');
			var selectedUser = usersList.find('option:selected');
			var selectedType = modalContainer.find('.selectedType');
			selectedType.val(usersList.val()).data(
				'typename',
				selectedUser.text()
			).data(
				'calendarGroup',
				selectedUser.data('calendarGroup')
			);
			thisInstance.saveFeedSettings(modalContainer);
		});        
	},

	registerColorEditorEvents : function(modalContainer,feedIndicator) {
		var thisInstance = this;
		var editorMode = modalContainer.find('.editorMode').val();

		var colorPickerHost = modalContainer.find('.calendarColorPicker');
		var selectedColor = modalContainer.find('.selectedColor');
		thisInstance.initializeColorPicker(colorPickerHost, {}, function(hsb, hex, rgb) {
			var selectedColorCode = '#'+hex;
			selectedColor.val(selectedColorCode);
		});

		thisInstance.registerUserChangeEvent(modalContainer);

		var usersList = modalContainer.find('select[name="usersList"]');
		if(editorMode === 'edit') {
			var feedCheckbox = feedIndicator.find('input[type="checkbox"].toggleCalendarFeed');
			usersList.select2('val',feedCheckbox.data('calendarUserid'));
		}
		usersList.trigger('change');

		thisInstance.registerColorEditorSaveEvent(modalContainer);
	},

	showColorEditor : function(feedIndicator) {
		var thisInstance = this;
		var params = {
			module : app.getModuleName(),
			view : 'UserCalendarViews',
			mode : 'editUserCalendar'
		};
		app.helper.showProgress();
		app.request.post({'data':params}).then(function(e,data) {
			app.helper.hideProgress();
			if(!e) {
				app.helper.showModal(data,{
					'cb' : function(modalContainer) {
						thisInstance.registerColorEditorEvents(modalContainer,feedIndicator);
					}
				});
			} else {
				console.log("network error : ",e);
			}
		});
	},
    */

    // Modified by Hieu Nguyen on 2019-11-08 to show default calendar view as user config
	getDefaultCalendarView : function() {
        var preferedView = this._super();
        if (preferedView == 'vtAgendaList') return 'agendaDay';

        return preferedView;
	},
    // End Hieu Nguyen

	initializeCalendar : function() {
		var calendarConfigs = this.getCalendarConfigs();
		this.getCalendarViewContainer().fullCalendar(calendarConfigs);
		this.performPostRenderCustomizations();
	},

	registerEvents : function() {
		this._super();
	}
});