/*
	File MenuEditor.js
	Author: Hieu Nguyen
	Date: 2023-01-18
	Purpose: handle logic on the UI for Menu Editor
*/

window.MenuEditor = {

	init: function() {
		this.initForm();

		let indexController = new Settings_Vtiger_Index_Js();
		indexController.registerBasicSettingsEvents();
	},

	getContainer: function () {
		return jQuery('#menu-editor');
	},

	getListView: function (container) {
		return container.find('#list-view');
	},

	getMainMenus: function (container) {
		return container.find('#main-menus');
	},

	getSelectedMainMenu: function (container) {
		return container.find('.main-menu.selected');
	},

	getEditView: function (container) {
		return container.find('#edit-view');
	},

	initForm: function () {
		let container = this.getContainer();
		this.initListView(container);
		this.initEditView(container);
	},

	initListView: function (container) {
		let self = this;
		let listView = this.getListView(container);
		let mainMenus = this.getMainMenus(container);
		let dragging = false;

		// Handle main menu sort event
		mainMenus.sortable({
			items: '.main-menu',
			revert: true,
			over: function (e, ui) {
				dragging = true;
			},
			stop: function (e, ui) {
				dragging = false;
			},
			update: function (e, ui) {
				self.saveMainMenusSequence(mainMenus);
			}
		});

		mainMenus.disableSelection();

		// Handle main menu click event
		mainMenus.on('click', '.main-menu', function () {
			if (!dragging) {
				mainMenus.find('.selected').removeClass('selected');
				$(this).addClass('selected');
				self.loadSeletedMainMenuEditView($(this));
			}
		});

		// Handle main menu's button edit click event
		mainMenus.on('click', '.btn-edit', function (e) {
			e.stopPropagation();
			let mainMenu = $(this).closest('.main-menu');
			self.showEditMainMenuModal(mainMenu.data('id'));
		});

		// Handle main menu's button remove click event
		mainMenus.on('click', '.btn-remove', function (e) {
			e.stopPropagation();

			if (mainMenus.find('.main-menu').length == 1) {
				app.helper.showErrorNotification({ message: app.vtranslate('JS_REMOVE_LAST_MAIN_MENU_ERROR_MSG') });
				return;
			}
			
			let mainMenu = $(this).closest('.main-menu');
			self.removeMainMenu(mainMenu);
		});

		// Handle main menu's button add click event
		listView.find('#btn-add-main-menu').on('click', function (e) {
			self.showEditMainMenuModal();
		});
	},

	getMainMenusSequenceInfo: function (mainMenus) {
		let mainMenuElements = mainMenus.find('.main-menu');
		let sequenceInfo = {};

		mainMenuElements.each(function () {
			let id = $(this).data('id');
			sequenceInfo[id] = $(this).index() + 1;
		});

		return sequenceInfo;
	},

	saveMainMenusSequence: function (mainMenus) {
		app.helper.showProgress();
		let params = {
			module: app.getModuleName(),
			parent: app.getParentModuleName(),
			action: 'SaveAjax',
			mode: 'updateSequence',
			type: 'main_menu',
			sequence_info: this.getMainMenusSequenceInfo(mainMenus),
		};

		app.request.post({ data: params })
		.then(function (err, res) {
			app.helper.hideProgress();

			// Handle error
			if (err || !res) {
				app.helper.showErrorNotification({ message: app.vtranslate('Vtiger.JS_AJAX_ERROR_MSG') });
				return;
			}
		});
	},

	initEditView: function (container) {
		let self = this;
		let editView = this.getEditView(container);

		// Handle button change layout
		editView.find('#top-actions .btn[name="layout"]').on('click', function () {
			let selectedMainMenu = self.getSelectedMainMenu(container);
			let targetBtn = $(this);
			let selectedLayout = targetBtn.val();
			self.saveMainMenuLayout(selectedMainMenu.data('id'), selectedLayout, targetBtn);
		});

		// Handle button add group
		editView.find('#top-actions .btn[name="add_menu_group"]').on('click', function () {
			let selectedMainMenu = self.getSelectedMainMenu(container);
			self.showEditMenuGroupModal(selectedMainMenu.data('id'));
		});

		// Handle menu group events
		editView.on('click', '.menu-group .box-header .btn-edit', function () {
			let selectedMainMenu = self.getSelectedMainMenu(container);
			let menuGroup = $(this).closest('.menu-group');
			self.showEditMenuGroupModal(selectedMainMenu.data('id'), menuGroup.data('id'));
		});

		editView.on('click', '.menu-group .box-header .btn-remove', function () {
			let menuGroup = $(this).closest('.menu-group');
			self.removeMenuGroup(menuGroup);
		});

		// Handle menu item events
		editView.on('click', '.menu-group .btn-add-menu-item', function () {
			let selectedMainMenu = self.getSelectedMainMenu(container);
			let menuGroup = $(this).closest('.menu-group');
			self.showAddMenuItemActionSheet(selectedMainMenu.data('id'), menuGroup.data('id'));
		});

		editView.on('click', '.menu-item .btn-edit', function () {
			let selectedMainMenu = self.getSelectedMainMenu(container);
			let menuGroup = $(this).closest('.menu-group');
			let menuItem = $(this).closest('.menu-item');
			self.showMenuItemModal(selectedMainMenu.data('id'), menuGroup.data('id'), menuItem.data('type'), menuItem.data('id'));
		});

		editView.on('click', '.menu-item .btn-remove', function () {
			let menuItem = $(this).closest('.menu-item');
			self.removeMenuItem(menuItem);
		});
	},

	saveMainMenuLayout: function (mainMenuId, selectedLayout, targetBtn) {
		let self = this;
		let container = this.getContainer();
		let editView = this.getEditView(container);

		app.helper.showProgress();
		let params = {
			module: app.getModuleName(),
			parent: app.getParentModuleName(),
			action: 'SaveAjax',
			mode: 'updateMainMenuLayout',
			main_menu_id: mainMenuId,
			selected_layout: selectedLayout,
		};

		app.request.post({ data: params })
		.then(function (err, res) {
			app.helper.hideProgress();

			// Handle error
			if (err || !res) {
				app.helper.showErrorNotification({ message: app.vtranslate('Vtiger.JS_AJAX_ERROR_MSG') });
				return;
			}

			// Display current state
			editView.find('#top-actions .btn[name="layout"].selected').removeClass('selected');
			targetBtn.addClass('selected');

			self.updateEditViewLayout(editView, selectedLayout);
		});
	},

	updateEditViewLayout: function (editView, layout) {
		let editViewContent = editView.find('#edit-view-content');
		editViewContent.attr('layout', layout);
	},

	loadSeletedMainMenuEditView: function (selectedMainMenu) {
		let self = this;
		let container = this.getContainer();
		let editView = this.getEditView(container);
		editView.find('#top-actions .btn[name="layout"]').attr('disabled', true);
		editView.find('#edit-view-content').html('');

		app.helper.showProgress();
		let params = {
			module: app.getModuleName(),
			parent: app.getParentModuleName(),
			view: 'EditAjax',
			mode: 'getSelectedMainMenuEditView',
			main_menu_id: selectedMainMenu.data('id'),
		};

		app.request.post({ data: params })
		.then(function (err, res) {
			app.helper.hideProgress();

			// Handle error
			if (err || !res) {
				app.helper.showErrorNotification({ message: app.vtranslate('Vtiger.JS_AJAX_ERROR_MSG') });
				return;
			}

			// Display edit view
			editView.find('#edit-view-content').html(res);
			editView.find('#edit-view-hint-text').hide();

			// Display layout mode
			let layout = $(res).find('[name="layout"]').val();
			editView.find('#top-actions .btn').attr('disabled', false);
			editView.find('#top-actions .btn[name="layout"].selected').removeClass('selected');
			editView.find('#top-actions .btn[name="layout"][value="'+ layout +'"]').addClass('selected');

			self.updateEditViewLayout(editView, layout);

			// Init menu group sort event
			let menuGroups = editView.find('.menu-groups');

			menuGroups.sortable({
				items: '.menu-group.sortable',
				revert: true,
				over: function (e, ui) {
					dragging = true;
				},
				stop: function (e, ui) {
					dragging = false;
				},
				update: function (e, ui) {
					self.saveMenuGroupsSequence(menuGroups);
				}
			});

			menuGroups.disableSelection();

			// Init menu item sort event
			let menuItems = editView.find('.menu-items');

			menuItems.sortable({
				items: '.menu-item.sortable',
				revert: true,
				over: function (e, ui) {
					dragging = true;
				},
				stop: function (e, ui) {
					dragging = false;
				},
				update: function (e, ui) {
					let selectedMenuGroup = ui.item.closest('.menu-group');
					self.saveMenuItemsSequence(selectedMenuGroup);
				}
			});

			menuItems.disableSelection();
		});
	},

	reloadMainMenuList: function (afterAdded = false) {
		let self = this;
		let container = this.getContainer();
		let mainMenus = this.getMainMenus(container);
		let selectedMainMenu = self.getSelectedMainMenu(container);

		app.helper.showProgress();
		let params = {
			module: app.getModuleName(),
			parent: app.getParentModuleName(),
			view: 'Index',
			mode: 'getMainMenuList',
			selected_main_menu_id: selectedMainMenu.data('id'),
		};

		app.request.post({ data: params })
		.then(function (err, res) {
			app.helper.hideProgress();

			// Handle error
			if (err || !res) {
				app.helper.showErrorNotification({ message: app.vtranslate('Vtiger.JS_AJAX_ERROR_MSG') });
				return;
			}

			// Display main menu list
			mainMenus.html(res);

			// Scroll to bottom after added new menu item
			if (afterAdded) {
				mainMenus.scrollTop(mainMenus[0].scrollHeight);
			}
		});
	},

	showEditMainMenuModal: function (mainMenuId = '') {
		let self = this;

		app.helper.showProgress();
		let params = {
			module: app.getModuleName(),
			parent: app.getParentModuleName(),
			view: 'EditAjax',
			mode: 'getEditMainMenuModal',
			main_menu_id: mainMenuId,
		};

		app.request.post({ data: params })
		.then(function (err, res) {
			app.helper.hideProgress();

			// Handle error
			if (err || !res) {
				app.helper.showErrorNotification({ message: app.vtranslate('Vtiger.JS_AJAX_ERROR_MSG') });
				return;
			}

			// Display modal
			app.helper.showModal(res, {
				backdrop: 'static',
				keyboard: false,
				preShowCb: (modal) => {
					let form = modal.find('form');
					form.find('[name="color"]').customColorPicker();
					self.initIconPicker(form);

					form.vtValidate({
						submitHandler: function () {
							self.saveMainMenuInfo(form);
						}
					});
				}
			});
		});
	},

	saveMainMenuInfo: function (form) {
		let self = this;
		let formData = form.serializeObject();
		formData['module'] = app.getModuleName();
		formData['parent'] = app.getParentModuleName();
		formData['action'] = 'SaveAjax';
		formData['mode'] = 'saveMainMenuInfo';
		formData['icon'] = this.getIconPickerValue(form.find('.iconpicker-trigger'));
		app.helper.showProgress();

		app.request.post({ data: formData })
		.then(function (err, res) {
			app.helper.hideProgress();

			// Handle error
			if (err || !res) {
				app.helper.showErrorNotification({ message: app.vtranslate('Vtiger.JS_AJAX_ERROR_MSG') });
				return;
			}

			if (!res.success) {
				let message = '';

				if (res.message == 'NAME_VN_EXIST') {
					message = app.vtranslate('JS_DUPLICATE_MAIN_MENU_NAME_ERROR_MSG', { main_menu_name: formData.name_vn });
				}

				if (res.message == 'NAME_EN_EXIST') {
					message = app.vtranslate('JS_DUPLICATE_MAIN_MENU_NAME_ERROR_MSG', { main_menu_name: formData.name_en });
				}

				app.helper.showErrorNotification({ message: message });
				return;
			}

			// Hide modal and reload data
			app.helper.hideModal();
			self.reloadMainMenuList(!formData.main_menu_id);
		});
	},

	removeMainMenu: function (mainMenu) {
		let self = this;
		let mainMenuId = mainMenu.data('id');
		let mainMenuName = mainMenu.find('.name').text().trim();
		let message = app.vtranslate('JS_REMOVE_MAIN_MENU_CONFIRM_MSG', { main_menu_name: mainMenuName });

		app.helper.showConfirmationBox({ message: message })
		.then(function () {
			app.helper.showProgress();
			let params = {
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				action: 'SaveAjax',
				mode: 'deleteMainMenu',
				main_menu_id: mainMenuId,
			};

			app.request.post({ data: params })
			.then(function (err, res) {
				app.helper.hideProgress();

				// Handle error
				if (err || !res) {
					app.helper.showErrorNotification({ message: app.vtranslate('Vtiger.JS_AJAX_ERROR_MSG') });
					return;
				}

				// Remove main menu on the UI
				mainMenu.remove();
				
				// Empty edit view
				let container = self.getContainer();
				let editView = self.getEditView(container);
				editView.find('#edit-view-content').html('');
				editView.find('#edit-view-hint-text').show();
			});
		});
	},

	showEditMenuGroupModal: function (mainMenuId, menuGroupId = '') {
		let self = this;

		app.helper.showProgress();
		let params = {
			module: app.getModuleName(),
			parent: app.getParentModuleName(),
			view: 'EditAjax',
			mode: 'getEditMenuGroupModal',
			main_menu_id: mainMenuId,
			menu_group_id: menuGroupId
		};

		app.request.post({ data: params })
		.then(function (err, res) {
			app.helper.hideProgress();

			// Handle error
			if (err || !res) {
				app.helper.showErrorNotification({ message: app.vtranslate('Vtiger.JS_AJAX_ERROR_MSG') });
				return;
			}

			// Display modal
			app.helper.showModal(res, {
				backdrop: 'static',
				keyboard: false,
				preShowCb: (modal) => {
					let form = modal.find('form');

					form.vtValidate({
						submitHandler: function () {
							self.saveMenuGroupInfo(form);
						}
					});
				}
			});
		});
	},

	saveMenuGroupInfo: function (form) {
		let self = this;
		let formData = form.serializeObject();
		formData['module'] = app.getModuleName();
		formData['parent'] = app.getParentModuleName();
		formData['action'] = 'SaveAjax';
		formData['mode'] = 'saveMenuGroupInfo';
		app.helper.showProgress();

		app.request.post({ data: formData })
		.then(function (err, res) {
			app.helper.hideProgress();

			// Handle error
			if (err || !res) {
				app.helper.showErrorNotification({ message: app.vtranslate('Vtiger.JS_AJAX_ERROR_MSG') });
				return;
			}

			if (!res.success) {
				let message = '';

				if (res.message == 'NAME_VN_EXIST') {
					message = app.vtranslate('JS_DUPLICATE_MENU_GROUP_NAME_ERROR_MSG', { menu_group_name: formData.name_vn });
				}

				if (res.message == 'NAME_EN_EXIST') {
					message = app.vtranslate('JS_DUPLICATE_MENU_GROUP_NAME_ERROR_MSG', { menu_group_name: formData.name_en });
				}

				app.helper.showErrorNotification({ message: message });
				return;
			}

			// Hide modal and reload data
			app.helper.hideModal();
			let container = self.getContainer();
			let selectedMainMenu = self.getSelectedMainMenu(container);
			self.loadSeletedMainMenuEditView(selectedMainMenu);
		});
	},

	removeMenuGroup: function (menuGroup) {
		let menuGroupId = menuGroup.data('id');
		let menuGroupName = menuGroup.find('.box-header .name').text().trim();
		let message = app.vtranslate('JS_REMOVE_MENU_GROUP_CONFIRM_MSG', { menu_group_name: menuGroupName });

		app.helper.showConfirmationBox({ message: message })
		.then(function () {
			app.helper.showProgress();
			let params = {
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				action: 'SaveAjax',
				mode: 'deleteMenuGroup',
				menu_group_id: menuGroupId,
			};

			app.request.post({ data: params })
			.then(function (err, res) {
				app.helper.hideProgress();

				// Handle error
				if (err || !res) {
					app.helper.showErrorNotification({ message: app.vtranslate('Vtiger.JS_AJAX_ERROR_MSG') });
					return;
				}

				// Remove menu group on the UI
				menuGroup.remove();
			});
		});
	},

	getMenuGroupsSequenceInfo: function (menuGroups) {
		let menuGroupElements = menuGroups.find('.menu-group');
		let sequenceInfo = {};

		menuGroupElements.each(function () {
			let id = $(this).data('id');
			sequenceInfo[id] = $(this).index() + 1;
		});

		return sequenceInfo;
	},

	saveMenuGroupsSequence: function (menuGroups) {
		app.helper.showProgress();
		let params = {
			module: app.getModuleName(),
			parent: app.getParentModuleName(),
			action: 'SaveAjax',
			mode: 'updateSequence',
			type: 'menu_group',
			sequence_info: this.getMenuGroupsSequenceInfo(menuGroups),
		};

		app.request.post({ data: params })
		.then(function (err, res) {
			app.helper.hideProgress();

			// Handle error
			if (err || !res) {
				app.helper.showErrorNotification({ message: app.vtranslate('Vtiger.JS_AJAX_ERROR_MSG') });
				return;
			}
		});
	},

	showAddMenuItemActionSheet: function (mainMenuId, menuGroupId) {
		let self = this;
		let container = this.getContainer();
		let modal = container.find('.modal-add-menu-item').clone(true, true);
		modal.removeClass('hide');

		app.helper.showModal(modal, {
			backdrop: 'static',
			keyboard: false,
			preShowCb: (modal) => {
				let form = modal.find('form');

				form.find('#btn-add-modules').on('click', function () {
					self.showMenuItemModal(mainMenuId, menuGroupId, 'modules');
				});

				form.find('#btn-add-web-url').on('click', function () {
					self.showMenuItemModal(mainMenuId, menuGroupId, 'web_url');
				});

				form.find('#btn-add-report').on('click', function () {
					self.showMenuItemModal(mainMenuId, menuGroupId, 'report');
				});
			}
		});
	},

	showMenuItemModal: function (mainMenuId, menuGroupId, menuItemType, menuItemId = '') {
		let self = this;
		let mode = '';

		if (menuItemType == 'modules') {
			mode = 'getEditModulesMenuItemModal';
		}
		else if (menuItemType == 'web_url') {
			mode = 'getEditWebUrlMenuItemModal';
		}
		else if (menuItemType == 'report') {
			mode = 'getEditReportMenuItemModal';
		}

		app.helper.showProgress();
		let params = {
			module: app.getModuleName(),
			parent: app.getParentModuleName(),
			view: 'EditAjax',
			mode: mode,
			main_menu_id: mainMenuId,
			menu_group_id: menuGroupId,
			menu_item_id: menuItemId,
		};

		app.request.post({ data: params })
		.then(function (err, res) {
			app.helper.hideProgress();

			// Handle error
			if (err || !res) {
				app.helper.showErrorNotification({ message: app.vtranslate('Vtiger.JS_AJAX_ERROR_MSG') });
				return;
			}

			let handler = function () {
				// Display menu item modal
				app.helper.showModal(res, {
					backdrop: 'static',
					keyboard: false,
					preShowCb: (modal) => {
						let form = modal.find('form');

						if (menuItemType == 'modules') {
							// Handle filter
							form.find('[name="filter"]').on('input', function () {
								let keyword = $(this).val().toLowerCase();
								form.find('.module-list .module').hide();

								form.find('.module-list .module').each(function () {
									if ($(this).text().trim().toLowerCase().search(keyword) > -1) {
										$(this).show();
									}
								});
							});
						}
						else if (menuItemType == 'web_url') {
							form.find('.bootstrap-switch').bootstrapSwitch();	// Init switch button
							self.initIconPicker(form);
						}
						else if (menuItemType == 'report') {
							self.initIconPicker(form);
						}

						form.vtValidate({
							submitHandler: function () {
								self.saveMenuItemInfo(form, menuItemType);
							}
						});
					}
				});
			}

			// Hide action sheet
			if ($('.modal:visible')[0] != null) {
				app.helper.hideModal().then(handler);
			}
			else {
				handler();
			}
		});
	},

	initIconPicker: function (form) {
		// Input icon picker
		form.find('.iconpicker-trigger').iconpicker();

		// Prevent popover hide when click on the search input
		form.find('.iconpicker-search').on('click', function (e) {
			e.stopPropagation();
			e.preventDefault();
		});

		// Prevent submit when enter in the search input
		form.find('.iconpicker-search').on('keypress', function (e) {
			if (e.which == 13 || e.keyCode == 13) {
				return false;
			}

			return true;
		});
	},

	removeMenuItem: function (menuItem) {
		let menuItemId = menuItem.data('id');
		let menuItemName = menuItem.find('.name').text().trim();
		let message = app.vtranslate('JS_REMOVE_MENU_ITEM_CONFIRM_MSG', { menu_item_name: menuItemName });

		app.helper.showConfirmationBox({ message: message })
		.then(function () {
			app.helper.showProgress();
			let params = {
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				action: 'SaveAjax',
				mode: 'deleteMenuItem',
				menu_item_id: menuItemId,
			};

			app.request.post({ data: params })
			.then(function (err, res) {
				app.helper.hideProgress();

				// Handle error
				if (err || !res) {
					app.helper.showErrorNotification({ message: app.vtranslate('Vtiger.JS_AJAX_ERROR_MSG') });
					return;
				}

				// Remove menu item on the UI
				menuItem.remove();
			});
		});
	},

	getIconPickerValue: function (element) {
		let iconPickerData = element.data('iconpicker');
		let selectedValue = iconPickerData.iconpickerValue;

		if (!selectedValue) {
			selectedValue = iconPickerData.component.find('i').attr('class');
			selectedValue = selectedValue.replace('iconpicker-component', '').trim();
		}

		return selectedValue;
	},

	getMenuItemInfo: function (form, menuItemType) {
		let menuItemInfo = {};

		if (menuItemType == 'modules') {
			menuItemInfo = [];

			form.find('.module-list .module').each(function () {
				let checkbox = $(this).find('input');

				if (checkbox.is(':checked')) {
					menuItemInfo.push(checkbox.val());
				}
			});
		}
		else if (menuItemType == 'web_url') {
			menuItemInfo.name_vn = form.find('[name="name_vn"]').val().trim();
			menuItemInfo.name_en = form.find('[name="name_en"]').val().trim();
			menuItemInfo.url = form.find('[name="url"]').val().trim();
			menuItemInfo.open_in_new_tab = form.find('[name="open_in_new_tab"]').is(':checked');
			menuItemInfo.icon = this.getIconPickerValue(form.find('.iconpicker-trigger'));
		}
		else if (menuItemType == 'report') {
			menuItemInfo.name_vn = form.find('[name="name_vn"]').val().trim();
			menuItemInfo.name_en = form.find('[name="name_en"]').val().trim();
			menuItemInfo.report_id = form.find('[name="report_id"]').val().trim();
			menuItemInfo.icon = this.getIconPickerValue(form.find('.iconpicker-trigger'));
		}

		return menuItemInfo;
	},

	saveMenuItemInfo: function (form, menuItemType) {
		let self = this;
		let mainMenuId = form.find('[name="main_menu_id"]').val();
		let menuGroupId = form.find('[name="menu_group_id"]').val();
		let menuItemId = form.find('[name="menu_item_id"]').val();

		app.helper.showProgress();
		let params = {
			module: app.getModuleName(),
			parent: app.getParentModuleName(),
			action: 'SaveAjax',
			mode: 'saveMenuItemInfo',
			main_menu_id: mainMenuId,
			menu_group_id: menuGroupId,
			menu_item_type: menuItemType,
			menu_item_id: menuItemId,
			menu_item_info: this.getMenuItemInfo(form, menuItemType),
		};

		app.request.post({ data: params })
		.then(function (err, res) {
			app.helper.hideProgress();

			// Handle error
			if (err || !res) {
				app.helper.showErrorNotification({ message: app.vtranslate('Vtiger.JS_AJAX_ERROR_MSG') });
				return;
			}

			if (!res.success) {
				let message = '';
				let formData = form.serializeObject();

				if (res.message == 'NAME_VN_EXIST') {
					message = app.vtranslate('JS_DUPLICATE_MENU_ITEM_NAME_ERROR_MSG', { menu_item_name: formData.name_vn });
				}

				if (res.message == 'NAME_EN_EXIST') {
					message = app.vtranslate('JS_DUPLICATE_MENU_ITEM_NAME_ERROR_MSG', { menu_item_name: formData.name_en });
				}

				app.helper.showErrorNotification({ message: message });
				return;
			}

			// Hide modal and reload data
			app.helper.hideModal();
			let container = self.getContainer();
			let selectedMainMenu = self.getSelectedMainMenu(container);
			self.loadSeletedMainMenuEditView(selectedMainMenu);
		});
	},

	getMenuItemsSequenceInfo: function (menuItems) {
		let menuItemElements = menuItems.find('.menu-item');
		let sequenceInfo = {};

		menuItemElements.each(function () {
			let id = $(this).data('id');
			sequenceInfo[id] = $(this).index() + 1;
		});

		return sequenceInfo;
	},

	saveMenuItemsSequence: function (selectedMenuGroup) {
		app.helper.showProgress();
		let params = {
			module: app.getModuleName(),
			parent: app.getParentModuleName(),
			action: 'SaveAjax',
			mode: 'updateSequence',
			type: 'menu_item',
			sequence_info: this.getMenuItemsSequenceInfo(selectedMenuGroup),
		};

		app.request.post({ data: params })
		.then(function (err, res) {
			app.helper.hideProgress();

			// Handle error
			if (err || !res) {
				app.helper.showErrorNotification({ message: app.vtranslate('Vtiger.JS_AJAX_ERROR_MSG') });
				return;
			}
		});
	},
};

jQuery(function () {
	MenuEditor.init();
});