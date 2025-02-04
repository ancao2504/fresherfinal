/* 
	CustomOwnerField.js
	Author: Hieu Nguyen
	Date: 2019-05-22
	Purpose: to provide util functions to handle custom owner fields on the UI
*/

var CustomOwnerField = {
	initCustomOwnerFields: function (ownerFields = null) {
		// Added by Phu Vo on 2019.06.20 init class scope
		let self = this;
		// End init class scope

		if (!ownerFields) ownerFields = $('input[name="assigned_user_id"]:not(.select2-bound)');

		// Bind select2 for all owner fields that are not bound
		ownerFields.each(function () {
			var inputField = $(this);

			// Init select2 // transferOwnerId added by Phu Vo on 2019.06.20
			var userOnly = $(this).data('userOnly') || $.inArray($(this).prop('name'), ['assigned_user_id', 'members', 'transferOwnerId']) < 0;
			var assignableUsersOnly = $(this).data('assignableUsersOnly');
			var singleSelection = $(this).data('singleSelection');  // Now support single selection
			var skipCurrentUser = $(this).data('skipCurrentUser');

			// Set empty value for mass update form
			if ($(this).closest('form#massEdit')[0] != null) {
				$(this).val('');
				$(this).data('selectedTags', '');
			}

			let options = {
				placeholder: app.vtranslate(self.getPlaceholderKey(userOnly)),
				minimumInputLength: _VALIDATION_CONFIG.autocomplete_min_length,
				ajax: {
					type: 'POST',
					url: 'index.php?module=Vtiger&action=HandleOwnerFieldAjax&mode=loadOwnerList',
					dataType: 'json',
					data: function (term, page) {
						term = term.trim();

						// Skip ajax request when user enter only spaces
						if (term.length == 0) {
							inputField.select2('close');
							inputField.select2('open');
							return null;
						}

						var data = {
							keyword: term,
							user_only: userOnly,
							assignable_users_only: assignableUsersOnly,
							skip_current_user: skipCurrentUser,
							skip_users: inputField.data('skipUsers'),   // Now support skipping a list of users on the fly
							skip_groups: inputField.data('skipGroups'),	// Now support skipping a list of groups on the fly
						};

						return data;
					},
					results: function (data) {
						return {results: data.result};
					},
					transport: function (params) {
						return jQuery.ajax(params);
					}
				},
				// Added by Phu Vo 2019.06.11 to manual format item
				formatSelection: function (object, container) {
					if (object.id) {
						let params = object.id.split(':');
						// Change from div to span on 2020.02.20 by Phu Vo to prevent css rule from docs.min.css
						let template =  `<span title="${object.text}">${object.text}</span>`;
	
						// Process item type
						container
							.closest('.select2-search-choice')
							.attr('data-type', params[0])

						return template;
					}

					return object.text;
				}
				// End Phu Vo
			};

			if (!singleSelection) {
				options.closeOnSelect = false;
				options.tags = [];
				options.tokenSeparators = [','];
			}

			$(this).select2(options);

			// Added by Phu Vo on 2019.05.13 to Process to highlight when init with value change trigger
			let wrapper = self.getMainOwnerWrapper($(this));
			let target = $(this);

			// Register Event
			$(this).on('change.select2', function () {
				let ownerFields = ['assigned_user_id', 'transferOwnerId', 'ownerid'];// Added ownerid by Phu Vo on 2019.09.22

				if (ownerFields.includes(target.attr('name'))) self.highlightMainOwner(target);
			});

			$(this).on('change', function () {
				// Update main owner field value
				if ($(this).attr('name') === 'assigned_user_id') {
					// process forms
					let processForms = ['edit', 'quickcreate', 'massedit', 'detail', 'overlay'];
					let formName = self.getWrapperName(wrapper);

					if (formName && processForms.includes(formName.toLowerCase())) { // Only process if it has valid form name
						self.updateMainOwnerId($(this), wrapper);
					}
				}
			});
			// End Phu Vo

			// Init selected values
			var selectedTags = $(this).data('selectedTags');

			if (selectedTags) {
				if (singleSelection) {
					$(this).select2('data', selectedTags[0]).trigger('change');
				}
				else {
					// Added by Phu Vo on 2019.06.10 to Process main owner
					let mainOwnerId = wrapper.find('[name="main_owner_id"]').val();
					selectedTags = self.processOwnerChain(selectedTags, mainOwnerId);
					// End Phu Vo

					$(this).select2('data', selectedTags).trigger('change');
				}
			}

			// Init sortable
			$(this).select2('container').find('ul.select2-choices').sortable({
				containment: 'parent',
				start: () => {
					$(this).select2('onSortStart');
				},
				update: () => {
					$(this).select2('onSortEnd');
				}
			});

			$(this).addClass('select2-bound');
		});

		// Added by Phu Vo on 2019.06.12 work around detail view validate issue
		if ($('.fieldValue.assigned_user_id').find('.editAction')[0] != null) {
			$(document).on('click', '.fieldValue.assigned_user_id .editAction', function () {
				vtUtils.doWhen(
					function () {
						return $('[name=assigned_user_id].select2-bound:visible')[0] != null;
					}, 
					function () {
						// Remove assigned_user_id template to void duplicate input field
						$('#owner-quickedit-template').find('[name=assigned_user_id]').remove();
						
						// Highlight process at detail view
						self.highlightMainOwner($('[name="assigned_user_id"]'));
					}
				);
			});
		}

		// Init Validate for MassEdit form
		if ($('#massEdit')[0] != null) {
			let massEdit = $('#massEdit');

			massEdit.find('[name="assigned_user_id"]').on('change', function () {
				$(this).closest('.fieldValue').find('.select2-choices').removeClass('input-error');
			});

			// Override form submit to validate
			massEdit.find('.saveButton').on('click', () => {
				if (self.isValid(massEdit.find('[name="assigned_user_id"]'))) {
					return true;
				}

				// Process message
				let massEditValidator = massEdit.vtValidate();
				massEditValidator.showErrors({'assigned_user_id': app.vtranslate('JS_MAIN_OWNER_VALIDATION_MSG')});
				massEdit.vtValidate('destroy'); // Hacking to show single method, destroy to pass other field validation and void memory leak

				return false;
			});
		}
		// End Phu Vo
	},

	// Return container UI for quick edit on detail view
	setupQuickEditTemplate: function (template) {
		var container = jQuery('<div id="owner-quickedit-container">'+ template +'</div>');
		container.find('.select2-container').remove();
		var field = container.find('input[name="assigned_user_id"]');
		field.select2('destroy');
		this.initCustomOwnerFields(field);

		return container;
	},

	/**
	 * Customer Owner display field event register
	 * @author Phu Vo (Date: 2019.05.28)
	 */
	initCustomOwnerTooltip: function (wrapper) {
		let target;

		if (wrapper && wrapper instanceof jQuery) {
			target = wrapper.find('.stand-owner-plus');
		}
		else {
			target = $('.stand-owner-plus');
		}

		target.each(function () {
			let container;

			if ($(this).closest('.mCustomScrollbar')[0] != null) {
				container = $(this).closest('.mCustomScrollbar');
			}
			else if ($(this).closest('.modal-body')[0] != null) {
				container = $(this).closest('.modal-body'); // Modified by Vu Mai on 2022-10-21 to select right parent of custom popover
			}
			else {
				container = 'body';
			}

			$(this).customPopover({'body-class': 'owners-container', size: 'sm', placement: 'left', container: container});
		});

		// Register event
		target.on('click', function (event) {
			event.stopPropagation();
			$('.stand-owner-plus').not($(this)).popover('hide');
		});
	},

	/**
	 * Handle behavior for single owner link
	 * @author Phu Vo (Date: 2019.10.29)
	 */
	registerOwnerLinkEvents: function (wrapper) {
		let target;

		if (wrapper && wrapper instanceof jQuery) {
			target = wrapper.find('.stand-owner');
		}
		else {
			target = $('.stand-owner');
		}

		target.on('click', function (event) {
			event.preventDefault();
			event.stopPropagation();

			window.open(event.target.href);
		});
	},

	/**
	 * Check if custom owner input valid
	 * @param {jQuery} input 
	 * @author Phu Vo (2019.06.13)
	 */
	isValid: function (input) {
		let value = input.val();

		if (value.toLowerCase().search('users') === -1) return true;

		// Process from here
		let mainOwner = value.split(',')[0];
		let type = mainOwner.split(':')[0];

		if (type.toLowerCase() !== 'users') return false;
		
		return true;
	},

	/**
	 * Find a wrapper which have main_owner_id hidden input
	 * @param {jQuery} input 
	 * @author Phu Vo (2019.06.13)
	 */
	getMainOwnerWrapper: function (input) {
		let wrapper = input.closest('form'); // Hope we can find its form

		// In case we don't really know, just find a wrapper have the info we need
		if (wrapper.length === 0) {
			if (input.closest('#taskManagementContainer')[0] != null) {
				wrapper = $('#taskManagementContainer');
			}
			else if ($('#overlayPageContent:visible')[0] != null) {
				wrapper = $('#overlayPageContent');
			}
			else if (input.closest('.relatedContainer')[0] != null) {
				wrapper = $('.relatedContainer');
			}
			else {
				// Default detail view for quick edit case
				wrapper = $('.detailViewContainer');
			}
		}
		// Case we already know that we are in detail view
		else if (wrapper.attr('id') === 'detailView' && wrapper.find('[name="assigned_user_id"]').length === 0) {
			wrapper = wrapper.closest('.detailViewContainer');
		}

		return wrapper;
	},

	/**
	 * Find wrapper name to proccess case by case actions
	 * @param {jQuery} wrapper 
	 * @author Phu Vo (2019.06.26)
	 */
	getWrapperName: function (wrapper) {
		let name;

		if (wrapper.is('form')) {
			name = wrapper.attr('name');
		}
		else if (wrapper.is('.detailViewContainer')) {
			name = 'detail';
		}
		else if (wrapper.is('#overlayPageContent')) {
			name = 'overlay';
		}
		else if (wrapper.is('#taskManagementContainer')) {
			name = 'taskmanagement';
		}

		return name;
	},

	/**
	 * Process to bring main user to chain's beginning
	 * @param {Array} ownerChain 
	 * @param {Number} mainOwnerId 
	 * @author Phu Vo (2019.06.13)
	 */
	processOwnerChain: function (ownerChain, mainOwnerId) {
		if (mainOwnerId && mainOwnerId > 0) {
			let mainOwnerIndex = ownerChain.findIndex(single => {
				let params = single.id.split(':');
				return Number(params[1]) === Number(mainOwnerId);
			});

			// Move main owner to the first place
			if (mainOwnerIndex !== -1) {
				let cache = ownerChain.splice(mainOwnerIndex, 1);
				ownerChain.unshift(cache[0]);
			}
		}

		return ownerChain;
	},

	/**
	 * Process highlight for main owner at assigned user id field
	 * @param {jQuery} input 
	 * @author Phu Vo (2019.06.13)
	 */
	highlightMainOwner: function (input) {
		let wrapper = this.getMainOwnerWrapper(input);

		// Modified by Phu Vo on 2019 to get owner field wrapper properly
		let fieldName = input.attr('name') ? input.attr('name') : input.data('name');
		if (!fieldName) return;

		let fieldwrapper = wrapper.find(`.fieldValue.${fieldName}`);
		// End get owner field wrapper properly

		let firstItem = fieldwrapper.find('.select2-search-choice:first-child');

		// Remove all tag
		fieldwrapper.find('.select2-search-choice').attr('title', '').removeClass('primary');

		// Highlight main owner with title
		if (firstItem.data('type') === 'Users') {
			firstItem.attr('title', app.vtranslate('JS_MAIN_OWNER')).addClass('primary');
		}
	},

	/**
	 * Load custom owner field for dashboard widget
	 * @param {jQuery} widgetContent 
	 * @author Phu Vo (2019.06.18)
	 */
	initCustomOwnerFieldForWidget: function (widgetContent) {
		let self = this;
		if (!(widgetContent instanceof jQuery)) widgetContent = jQuery(widgetContent);

		let container = widgetContent.closest('.dashboardWidget');
		let ownerField = container.find('[name="assigned_user_id"]');
		
		// Mark to search all user (without privileges)
		ownerField.data('assignableUsersOnly', false);

		let callBack = target => {
			let optionalFilter = container.find('[rel="optional_filter"]');
			
			optionalFilter.off('change').on('change', function () {
				if ($(this).is(':checked')) {
					optionalFilter.not(this).prop('checked', false);
					container.find('.user-list_input').find('.select2-chosen').html(app.vtranslate('JS_CUSTOM_OWNER_FIELD_SELECT2_PLACEHOLDER'));

					if ($(this).val() === 'all') {
						ownerField.val('all').trigger('change'); // Modified by Phu Vo on 2019.09.27 to fix widget send assigned_user_id filter null
					}
					else if ($(this).val() === 'mine') {
						ownerField.val(app.getUserId()).trigger('change');
					}
				}
				else {
					let content = container.find('.dashboardWidgetContent');
					let contentWrapper = content;

					if (content.find('.mCSB_container')[0] != null) {
						contentWrapper = content.find('.mCSB_container');
					}

					contentWrapper.html(`<span>${app.vtranslate('JS_WIDGET_NON_FILTER_CONTENT')}</span>`);
					container.find('.user-list_input').find('.select2-chosen').html(app.vtranslate(self.getPlaceholderKey()));
				}
			}).trigger('change'); // [CustomOwnerField] Bug #20: Modified by Phu Vo on 2020.02.06 to process case get all users

			container.on('select2-selecting', '[name=assigned_user_id]', function () {
				optionalFilter.prop('checked', false);
			});
		}

		if (ownerField[0] != null) this.initCustomOwnerFieldSingle(ownerField, callBack);
	},

	/**
	 * Process custom owner field for Event invitees
	 */
	initCustomOwnerFieldForEventInvitees: function () {
		let fieldOwner = $('[name="selectedusers"]');

		this.initCustomOwnerFields(fieldOwner);
	},

	/**
	 * Process custom owner field for single select2
	 * @param {jQuery} ownerFields 
	 * @param {Function} callBack 
	 * @author Phu Vo (2019.06.18)
	 */
	initCustomOwnerFieldSingle: function (ownerFields, callBack) {
		let self = this;
		if (!(ownerFields instanceof jQuery)) ownerFields = jQuery(ownerFields);

		ownerFields.each(function () {
			var inputField = $(this);   // Added by Hieu Nguyen on 2019-10-22
			let userOnly = $(this).data('user-only');
			let assignableUsersOnly = $(this).data('assignableUsersOnly');

			// Init select2
			$(this).select2({
				placeholder: app.vtranslate(self.getPlaceholderKey(userOnly)),
				minimumInputLength: _VALIDATION_CONFIG.autocomplete_min_length,
				ajax: {
					type: 'POST',
					dataType: 'json',
					cache: true,
					data: function (term, page) {
						// Added by Hieu Nguyen on 2019-10-22 to skip ajax request when user enter only spaces
						term = term.trim();

						if (term.length == 0) {
							inputField.select2('close');
							inputField.select2('open');
							return null;
						}

						var data = {
							module: 'Vtiger',
							action: 'HandleOwnerFieldAjax',
							mode: 'loadOwnerList',
							keyword: term,
							user_only: userOnly,
							assignable_users_only: assignableUsersOnly
						};

						return data;
					},
					results: function (data) {
						return {results: data.result};
					},
					transport: function (params) {
						return jQuery.ajax(params);
					},
				},
			});
		});

		if (typeof callBack === 'function') callBack();
	},

	/**
	 * Method return custom owner field select2 placeholder
	 * @param {Boolean} userOnly 
	 * @author Phu Vo (2019.06.20)
	 * @return {String} Vtiger js language key
	 */
	getPlaceholderKey(userOnly = false) {
		let key;

		key = 'JS_CUSTOM_OWNER_FIELD_SELECT2_PLACEHOLDER';
		if (userOnly) key = 'JS_CUSTOM_OWNER_FIELD_SELECT2_PLACEHOLDER_USER_ONLY';

		return key;
	},

	/**
	 * Process main owner use for assigned user id field
	 * @param {jQuery} ownerField 
	 * @param {jQuery} wrapper 
	 * @author Phu Vo (2019.06.20)
	 */
	updateMainOwnerId(ownerField, wrapper) {
		// By Pass case by case
		if (ownerField.closest('#listViewContent')[0] != null) { // Ignore list view content
			return;
		}

		// Process data
		let users = ownerField.val().split(',');
		let firstUserParams = users[0].split(':');

		// Update main owner id
		wrapper.find('[name="main_owner_id"]').val(firstUserParams[1]);
	},
};

// On document ready
jQuery(function ($) {
	// Init owner fields at page load
	CustomOwnerField.initCustomOwnerFields();
	CustomOwnerField.initCustomOwnerFields($('input[name="main_owner_id"]:not(:hidden)'));

	// Added by Phu Vo to init custom owner field for module Webforms
	if (app.getModuleName() == 'Webforms') {
		CustomOwnerField.initCustomOwnerFields($('[data-name="ownerid"]'));
	}
	// End init custom owner field for module Webforms

	// Init owner field at listview
	if (app.getViewName() === 'List') {
		// Init owner field on advanced search form
		app.event.on('post.listViewFilter.load', function (event, data) { // Modified by Phu Vo on 2019.10.29 to correct event listener
			var ownerField = data.find('input[name="assigned_user_id"], input[name="main_owner_id"]:not(:hidden)');
			CustomOwnerField.initCustomOwnerFields(ownerField);
		});

		// Re-Init after listview ajax filtered
		app.event.on('post.listViewFilter.click', function (event, data) {
			// Added by Phu Vo to ignore when event trigger without data
			if (!data) return;
			// End Phu Vo
			
			var ownerField = data.find('input[name="assigned_user_id"], input[name="main_owner_id"]:not(:hidden)');
			CustomOwnerField.initCustomOwnerFields(ownerField);

			if (app.getModuleName() == 'Reports') {
				CustomOwnerField.initCustomOwnerFields($('input[name="owner"]'));
			}
		});

		// Init owner field at mass update form
		app.event.on('post.listViewMassEdit.loaded', function (event, data) {
			var ownerField = data.find('input[name="assigned_user_id"]');
			CustomOwnerField.initCustomOwnerFields(ownerField);
		});
	}

	// Init owner field on detail view form
	if (app.getViewName() === 'Detail') {
		// Refresh the quick edit template for custom owner field after quick edit save
		app.event.on('PostAjaxSaveEvent', function (event, fieldBasicData, postSaveData, wrapper) {
			if (fieldBasicData.data('name') == 'assigned_user_id') {
				var editElement = fieldBasicData.closest('span.edit').find('div.editElement');
				editElement.find('#owner-quickedit-container').remove();

				var template = jQuery('#owner-quickedit-template').html();
				var container = CustomOwnerField.setupQuickEditTemplate(template);
				editElement.prepend(container);
			}
		});

		// Init owner field on related list
		app.event.on('post.relatedListLoad.click', function (event, data) {
			var ownerField = data.find('input[name="assigned_user_id"], input[name="main_owner_id"]:not(:hidden)');
			CustomOwnerField.initCustomOwnerFields(ownerField);
		});
	}

	// Init owner field on report edit form
	if (app.getModuleName() == 'Reports' && (app.getViewName() === 'Edit' || app.getViewName() === 'ChartEdit')) {
		CustomOwnerField.initCustomOwnerFields($('input[name="members"]'));
	}

	// Init owner field on report listview
	if (app.getModuleName() == 'Reports' && app.getViewName() === 'List') {
		CustomOwnerField.initCustomOwnerFields($('input[name="owner"]'));
	}

	// Added by Phu Vo on 2019.28.06 init custom owner tooltip
	if (app.getViewName() === 'List' || $('.relatedContainer')[0] != null) {
		CustomOwnerField.initCustomOwnerTooltip();
		CustomOwnerField.registerOwnerLinkEvents();
	}
	// End Phu Vo

	// Added by Phu Vo on 2019.06.24 to init custom owner tooltip for report detail
	if (app.getModuleName() === 'Reports' && app.getViewName() === 'Detail') {
		CustomOwnerField.initCustomOwnerTooltip('.reports-content-area');
		CustomOwnerField.registerOwnerLinkEvents();
	}

	// Init owner field on quick create form
	app.event.on('post.QuickCreateForm.show', function (event, data) {
		var ownerField = data.find('input[name="assigned_user_id"]');
		CustomOwnerField.initCustomOwnerFields(ownerField);
	});

	// Init owner field on overlay edit form
	app.event.on('post.overLayEditView.loaded', function (event, data) {
		var ownerField = data.find('input[name="assigned_user_id"]');
		CustomOwnerField.initCustomOwnerFields(ownerField);
	});

	// Init owner field on lookup popup load
	app.event.on('post.Popup.Load', function (event, data) {
		var ownerField = $('div.popupEntriesTableContainer').find('input[name="assigned_user_id"], input[name="main_owner_id"]:not(:hidden)');
		CustomOwnerField.initCustomOwnerFields(ownerField);
	});

	// Init owner field on lookup popup reload
	app.event.on('post.Popup.reload', function (event, data) {
		var ownerField = data.find('input[name="assigned_user_id"], input[name="main_owner_id"]:not(:hidden)');
		CustomOwnerField.initCustomOwnerFields(ownerField);
	});

	// Added by Phu Vo on 2019.06.20 to init custom owner field for transfer ownership popup
	app.event.on('post.transferOwnershipPopup.load', function (event, data) {
		CustomOwnerField.initCustomOwnerFields($('input[name="transferOwnerId"]'));
	});

	// Added by Phu Vo on 2020.10.01 to init custom owner field for Calendar List View Mass Edit popup
	app.event.on('post.listViewMassAction.loaded', function (event, data) {
		CustomOwnerField.initCustomOwnerFields(data.find('[name="assigned_user_id"]'));
	});

	// Added by Phu Vo on 2019.06.21 to init custom owner tooltip for account hierarchy popup
	app.event.on('post.accountHierarchyPopup.load', function (event, data) {
		CustomOwnerField.initCustomOwnerTooltip(data);
		CustomOwnerField.registerOwnerLinkEvents(data);
	});
	// End init custom owner tooltip for account hierarchy popup (Phu Vo)

	// Added by Phu Vo on 2019.06.22 to init custom owner field for task management
	app.event.on('post.taskManagementOverlay.load', function (event, data) {
		CustomOwnerField.initCustomOwnerFields(data.find('[name="assigned_user_id"]'));
	});
	// End init custom owner field for task management

	// Added by Phu Vo on 2019.06.22 to init custom owner field for quick create document
	app.event.on('post.documentQuickCreatePopup.load', function (event, data) {
		CustomOwnerField.initCustomOwnerFields(data.find('[name="assigned_user_id"]:not(.select2-bound)'));
	});
	// End init custom owner field for quick create document

	// Added by Phu Vo on 2019.18.06 init custom owner tooltip detail view
	app.event.on('post.relatedListLoad.click', function (event, container){
		CustomOwnerField.initCustomOwnerTooltip();
		CustomOwnerField.registerOwnerLinkEvents();
	});
	// End init custom owner tooltip detail view

	// Added by Phu Vo on 2019.05.29 to register custom owner tooltip for list view after refresh by search
	app.event.on('post.listViewFilter.click', function (event, searchRow) {
		CustomOwnerField.initCustomOwnerTooltip();
		CustomOwnerField.registerOwnerLinkEvents();
	});
	// End register custom owner tooltip for list view

	// Added by Phu Vo to init custom owner tooltip for related popup
	app.event.on('post.Popup.Load', function (event, params) {
		if (jQuery('#popupModal').find('.relatedContents')[0] != null) {
			CustomOwnerField.initCustomOwnerTooltip();
			CustomOwnerField.registerOwnerLinkEvents();
		}  
	});

	app.event.on('post.Popup.reload', function (event, params) {
		if (jQuery('#popupModal').find('.relatedContents')[0] != null) {
			CustomOwnerField.initCustomOwnerTooltip();
			CustomOwnerField.registerOwnerLinkEvents();
		}  
	});
	// End init custom owner tooltip for related popup

	app.event.on('post.generateReport.load', function (event, data) {
		CustomOwnerField.initCustomOwnerTooltip('.reports-content-area');
		CustomOwnerField.registerOwnerLinkEvents('.reports-content-area');
	});
	// End init custom owner tooltip for report detail

	// Added by Phu vo on 2019.06.26 to init custom owner field for dashboard widget
	app.event.on('post.widget.load', function (event, data) {
		CustomOwnerField.initCustomOwnerFieldForWidget(data);
	});
	// End Phu Vo

	// Added by Phu Vo on 2019.06.27 to process main owner update with assigned user id
	app.event.on('PostAjaxSaveEvent', function (event, fieldBasicData, postSaveRecordDetails, contentHolder) {
		let fieldName = fieldBasicData.data('name');
		
		if (fieldName === 'assigned_user_id') {
			let detailForm = fieldBasicData.closest('form');
			detailForm.find('.fieldValue.main_owner_id .value').html(postSaveRecordDetails['main_owner_id']['display_value']);
		}
	});
	// End Phu Vo

	// Added by Phu Vo on 2019.06.27 to process custom owner field at convert lead modal
	app.event.on('post.convertLeadModal.load', function (event, data) {
		CustomOwnerField.initCustomOwnerFields(data.find('[name="assigned_user_id"]:not(.select2-bound)'));
	});
	// End Phu Vo

	// Added by Phu Vo on 2019.07.09 to process custom owner field at convert potential modal
	app.event.on('post.convertPotentialModel.load', function (event, data) {
		CustomOwnerField.initCustomOwnerFields(data.find('[name="assigned_user_id"]:not(.select2-bound)'));
	});
	// End process custom owner field at convert potential modal

	// Added by Phu Vo on 2020.01.07 to process related list popup
	app.event.on('post.relatedListPopup.load', function (event, data) {
		CustomOwnerField.initCustomOwnerFields(data.find('input[name="assigned_user_id"]:not(.select2-bound)'));
		CustomOwnerField.initCustomOwnerTooltip(data);
	});

	// [Core] Added by Phu Vo on 2020.07.08 to process global search popup
	app.event.on('post.globalSearchResult.load', function (event, data) {
		CustomOwnerField.initCustomOwnerTooltip(data);
	});
	// End Phu Vo
});