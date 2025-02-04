/*
	File: Telesales.js
	Author: Vu Mai
	Date: 2022-10-24
	Purpose: Telesales UI handle
*/

CustomView_BaseController_Js('CPTelesales_Telesales_Js', {}, {
	registerEvents: function () {
		this._super();
		this.registerEventInit();
	},

	registerEventInit: function () {
		let self = this;
		let page = this.getPage();
		
		// Init field select2
		page.find('.dropdown-filter').select2();
		$('select.modules-filter').trigger('change.select2');

		// Register Module Filter Event
		this.registerModuleFilterEvent(page); 

		page.find('#customer-status-filter').hide();
		page.find('#list-content').hide();

		// Handle event change time filter in call statistics
		page.on('change.select2', '.statistics-filter', function () {
			let campaignId = page.find('input[name="campaign_selector_id"]').val();
			let userId = page.find('select.users-filter').val();
			let time = $(this).val();
			self.loadCallStatistics(campaignId, userId, time);
		});

		// Handle event change custom view
		page.on('change.select2', '.module-listview-filter', function () {
			let targetModule = page.find('select.modules-filter').val();
			let customViewId = $(this).val();

			self.loadModuleStatusList(targetModule, customViewId);
		})

		// Handle event change user filter
		page.find('.users-filter').on('change.select2', function () {
			let campaignId = page.find('input[name="campaign_selector_id"]').val();
			let purpose = page.find('input[name="campaign_selector_purpose"]').val();
			let userId = $(this).val();
			let time = page.find('select.statistics-filter').val();
			self.loadCallStatistics(campaignId, userId, time);
			self.loadCustomerStatusList(campaignId, purpose, userId);
		});

		// Handle event cutomer status filter click
		page.on('click', '.customer-status', function () {
			page.find('.customer-status').removeClass('active');
			$(this).addClass('active');
			
			// Update search params
			let target_module = page.find('select.modules-filter').val();
			page.find('input[name="page"]').val(1);

			if (target_module != 'Campaigns') {
				let searchParam = JSON.stringify(self.getListSearchParams());
				page.find('input[name="search_params"]').val(searchParam);
			}

			self.loadListContent();
		});

		// Handle event follow customer
		page.on('click', '.mark-star', function () {
			let campaignId = page.find('input[name="campaign_selector_id"]').val();
			self.toggleStar($(this), campaignId);	
		});

		// Handle event follow record
		page.on('click', '.markStar', function () {
			let targetModule = page.find('select.modules-filter').val();
			let record = $(this).closest('tr.listViewEntries').attr('data-id');
			self.recordStarToggle($(this), targetModule, record);	
		});

		// Handle event show record quick view
		page.on('click', '.quick-view', function () {
			let id = $(this).attr('data-id');
			let type = $(this).attr('data-type');
			let vtigerInstance = Vtiger_Index_Js.getInstance();
			vtigerInstance.showQuickPreviewForId(id, type, app.getAppName());
		});

		// Handle event show record quick view
		page.on('click', '.quickView', function () {
			let id = $(this).closest('tr').attr('data-id');
			let targetModule = page.find('select.modules-filter').val();
			let vtigerInstance = Vtiger_Index_Js.getInstance();
			vtigerInstance.showQuickPreviewForId(id, targetModule, app.getAppName());
		});

		// Handle event next page
		page.on('click', '#next-page-button', function () {
			let pageNo = page.find('input[name="page"]').val();
			pageNo++; 
			page.find('input[name="page"]').val(pageNo);
			self.loadListContent();
		});

		// Handle event prev page
		page.on('click', '#previous-page-button', function () {
			let pageNo = page.find('input[name="page"]').val();
			pageNo--; 
			page.find('input[name="page"]').val(pageNo);
			self.loadListContent();
		});

		// Prevent page jump dropdown hide on click inside dropdown menu
		page.on('click', '#PageJumpDropDown', function (event) {
			event.stopImmediatePropagation();
		});

		// Handle page jump button click
		page.on('click', '#pageToJumpSubmit', function (event) {
			let totalPage = page.find('#total-page-count').text();
			let currentPage = page.find('input[name="page"]').val();
			let pageJump =  page.find('input#pageToJump').val();

			if (pageJump > totalPage) {
				app.helper.showErrorNotification({ 'message':  app.vtranslate('JS_PAGE_NOT_EXIST') });
				return;
			}

			if (pageJump == currentPage) {
				app.helper.showAlertNotification({ 'message':  app.vtranslate('JS_YOU_ARE_IN_PAGE_NUMBER') + ` ${currentPage}` });
				return;
			}

			page.find('input[name="page"]').val(pageJump);
			self.loadListContent();
		});
		
		// Handle listview sort event
		page.on('click', '.listViewContentHeaderValues', function () {
			var fieldName = $(this).attr('data-fieldname');
			var sortOrderVal = $(this).attr('data-nextsortorderval');

			page.find('input[name="sortOrder"]').val(sortOrderVal);
			page.find('input[name="orderBy"]').val(fieldName);
			page.find('input[name="page"]').val(1);
			self.loadListContent();
		});

		// Handle listview remove sort event
		page.on('click', '.remove-sorting', function () {
			page.find('input[name="sortOrder"]').val('ASC');
			page.find('input[name="orderBy"]').val('');
			page.find('input[name="page"]').val(1);
			self.loadListContent();
		});

		// Handle event listview filter
		page.on('click', '.list-search', function () {
			page.find('input[name="page"]').val(1);
			let searchParam = JSON.stringify(self.getListSearchParams());
			page.find('input[name="search_params"]').val(searchParam);
			self.loadListContent();
		});

		// Handle event listview clear filter
		page.on('click', '.clearFilters', function () {
			page.find('input[name="page"]').val(1);
			self.clearFilters();
		});

		// Handle event click button call
		page.on('click', '.btnCall', function (e) {
			e.preventDefault();
			let targetBtn = $(this);
			let targetModule = page.find('select.modules-filter').val();
			let campaignId = page.find('input[name="campaign_selector_id"]').val();
			let customerNumber = targetBtn.attr('data-value');
			let customerId = targetBtn.attr('record');

			if (targetModule == 'Campaigns') {
				self.handleClickButtonCall(targetBtn, campaignId, customerNumber, customerId);
			}
			else {
				Vtiger_PBXManager_Js.registerPBXOutboundCall(targetBtn, `${customerNumber}`, `${customerId}`);
			}
		});
	},

	getPage: function () {
		return $('#telesales-page');
	},

	registerModuleFilterEvent: function (page) {
		let self = this;

		page.find('select.modules-filter').on('change', function () {
			let moduleFilter = $(this).val();

			if (moduleFilter == 'Campaigns') {
				page.find('#filter-container').addClass('campaign-module-filter');
				page.find('#filter-container').removeClass('other-module-filter');

				page.find('#customer-status-filter').hide();
				page.find('#list-content').hide();
				
				// Init field campaign selector
				self.initCampaignSelector(page);

				// Validate campaign if have campain id
				let campaignSelectorId = page.find('input[name="campaign_selector_id"]').val();

				if (campaignSelectorId != null && campaignSelectorId != "") {
					self.validateCampaign(campaignSelectorId);
				}
				else {
					page.find('select.statistics-filter').attr('disabled', true);
				}
			}
			else {
				page.find('#filter-container').addClass('other-module-filter');
				page.find('#filter-container').removeClass('campaign-module-filter');

				// Load ListView filter by Module
				self.getCustomViewByModule(moduleFilter);

				// Load Call Statistics
				self.loadCallStatistics();

				// Remove record in url if exist
				var url = window.location.pathname;
				let urlParams = '?module=CPTelesales&view=Telesales&mode=getMainView';
				window.history.replaceState(null, null, urlParams);
			}
		});

		page.find('select.modules-filter').trigger('change');
	},

	initCampaignSelector: function (page) {
		let self = this;

		// Event listener for selecting campaign
		page.find('.btn-entity-select').on('click', function (e) {
			let element = $(this).closest('.entity-selector-wrapper');
			if (element[0] == null) return;
			let moduleName = element.data('module');
			if (!moduleName) return;

			let params = {
				module: moduleName,
				search_params: [[['campaigntype', 'c', 'Telesales'], ['campaignstatus', 'e', 'Active']]],	
				view: 'Popup',
			};

			Vtiger_Popup_Js.getInstance().showPopup(params, 'Entity.Popup.Selection');

			// Handle campaign select event
			app.event.off('Entity.Popup.Selection');

			app.event.on('Entity.Popup.Selection', (e, data) => {
				data = JSON.parse(data);
				let id;
				const input = element.find('.entity-selector-input');
				const display = element.find('.entity-selector-display');

				// Extract data info
				for (key in data) {
					id = key;
					data = data[id];
					break;
				}

				// Validated Campaign selected
				self.validateCampaign(id);
			});

			// Disabled campaigntype and campaignstatus fields to prevent user filter with this fields
			app.event.on('post.Popup.Load post.Popup.reload', function (event, data) { 
				let searchRow = $('#popupContents').find('.searchRow');
				searchRow.find('[name="campaignstatus"]').attr('disabled', true);
				searchRow.find('[name="campaigntype"]').attr('disabled', true);
			});
		});

		page.find('.btn-entity-deselect').on('click', function (e) {
			let element = $(this).closest('.entity-selector-wrapper');
			let input = element.find('.entity-selector-input');
			let display = element.find('.entity-selector-display');

			input.val('').trigger('change');

			if (display.is('input, selector')) {
				display.val('').trigger('change');
			}	
			else {
				display.html('').trigger('change');
			}

			page.find('input[name="campaign_selector_purpose"]').val('');
			page.find('select.statistics-filter').attr('disabled', true);
			page.find('#customer-status-filter').hide();
			page.find('#list-content').hide();
			self.loadUserList(display.val(''));
		});
	},

	getCustomViewByModule: function (moduleFilter) {
		let self = this;
		let page = this.getPage();

		let params = {
			module: 'CPTelesales',
			action: 'TelesalesAjax',
			mode: 'getCustomViewByModule',
			module_filter: moduleFilter,
		};

		app.request.post({ data: params })
		.then(function (err, data) {
			if (err) {
				app.helper.showErrorNotification({ 'message': err.message });
				return;
			}

			page.find('select.module-listview-filter').html('');

			$.each(data, function(key, value) {
				let optionGroup = '<optgroup label="' + value.group_label + '">';
				delete value.group_label;
				
				$.each(value, function(index, item) {
					let selected = index == 'All' ? 'selected' : '';

					optionGroup += '<option value="'+ item.id +'"'+ selected +'>' + item.display_name + '</option>';
				})

				optionGroup += '</optgroup>'

				page.find('select.module-listview-filter').append(optionGroup);
			})

			page.find('select.module-listview-filter').trigger('change');
		});
	},

	validateCampaign: function(id) {
		let self = this;
		let page = this.getPage();

		let params = {
			module: 'CPTelesales',
			action: 'TelesalesAjax',
			mode: 'getCampaignInfo',
			record: id
		};

		app.request.post({ data: params })
		.then(function (err, data) {
			if (err) {
				app.helper.showErrorNotification({ 'message': err.message });
				return;
			}

			let display = page.find('.entity-selector-display');

			if (data.type != 'Telesales') {
				app.helper.showErrorNotification({ 'message': app.vtranslate('JS_TELESALES_CAMPAIGN_TELESALES_CAMPAIGN_NOT_TELESALES_ERROR_MSG') });
				self.loadUserList(id, data.purpose);
				page.find('input[name="campaign_selector_id"]').val('').trigger('change');

				if (display.is('input, selector')) {
					display.val('').trigger('change');
				}	
				else {
					display.html('').trigger('change');
				}

				page.find('input[name="campaign_selector_purpose"]').val('');
				page.find('select.statistics-filter').attr('disabled', true);
				page.find('#customer-status-filter').hide();
				page.find('#list-content').hide();
				return;
			}

			page.find('input[name="campaign_selector_id"]').val(id).trigger('change');

			if (display.is('input, selector')) {
				display.val(data.name).trigger('change');
			}	
			else {
				display.html(data.name).trigger('change');
			}

			page.find('select.statistics-filter').attr('disabled', false);
			page.find('input[name="campaign_selector_purpose"]').val(data.purpose);
			self.loadUserList(id, data.purpose, data.assigned_users);

			// Update record in url
			var url = window.location.pathname;
			let urlParams = '?module=CPTelesales&view=Telesales&mode=getMainView&record=' + id;
			window.history.replaceState(null, null, urlParams);
		});
	},

	loadUserList: function(id, purpose = null, assigned_users = null) {
		let page = this.getPage();
		page.find('select.users-filter').html('');
		page.find('select.users-filter').attr('disabled', true);

		if (assigned_users) {
			let currentUserIsAssigne = false;
			page.find('select.users-filter').attr('disabled', false);

			$.each(assigned_users, function(key, value) {
				page.find('select.users-filter').append(`<option value="${value.id}">${value.name}</option>`);

				if (_CURRENT_USER_META.id == value.id) {
					currentUserIsAssigne = true;
				}
			});

			if (currentUserIsAssigne) {
				page.find('select.users-filter').val(_CURRENT_USER_META.id);
			}
			else {
				page.find('select.users-filter').val('all');
			}

			page.find('select.users-filter').trigger('change');
			let user_id = page.find('select.users-filter').val();

			this.loadCallStatistics(id);
			this.loadCustomerStatusList(id, purpose, user_id);
		}
	},

	loadCallStatistics: function (id = null, userId = 'all', timeOption = 'today') { 
		let page = this.getPage();
		app.helper.showProgress();
		let targetModule = page.find('select.modules-filter').val();

		let params = {
			module: 'CPTelesales',
			view: 'Telesales',
			mode: 'getCallStatistics',
			record: id,
			user_id: userId,
			target_module: targetModule,
			time_option: timeOption,
		};

		app.request.post({ data: params })
		.then(function (err, data) {
			app.helper.hideProgress();

			if (err) {
				app.helper.showErrorNotification({ 'message': err.message });
				return;
			}

			if (data) {
				page.find('#call-statistics-container').html('');
				page.find('#call-statistics-container').append(data);
				page.find('#call-statistics-container select.statistics-filter').select2();
				vtUtils.enableTooltips();
			}
		});
	},
	
	loadCustomerStatusList: function(id, purpose, userId = 'all') {
		let page = this.getPage();
		if (!purpose) return;

		let params = {
			module: 'CPTelesales',
			view: 'Telesales',
			mode: 'getCustomerStatusFilter',
			purpose: purpose,
			record: id,
			user_id: userId
		};

		app.request.post({ data: params })
		.then(function (err, data) {
			if (err) {
				app.helper.showErrorNotification({ 'message': err.message });
				return;
			}

			if (data) {
				page.find('#customer-status-filter').show();
				page.find('#customer-status-filter .box-body').html('');
				page.find('#customer-status-filter .box-body').append(data);
				page.find('input[name="page"]').val(1);
				page.find('#customer-status-container .all').click();
			}
		});
	},

	loadModuleStatusList: function (targetModule, customViewId) {
		let self = this;
		let page = this.getPage();

		let params = {
			module: 'CPTelesales',
			view: 'Telesales',
			mode: 'getModuleStatusList',
			target_module: targetModule,
			custom_view_id: customViewId
		};

		app.request.post({ data: params })
		.then(function (err, data) {
			app.helper.hideProgress();

			if (err) {
				app.helper.showErrorNotification({ 'message': err.message });
				return;
			}

			if (data) {
				page.find('#customer-status-filter').show();
				page.find('#customer-status-filter .box-body').html('');
				page.find('#customer-status-filter .box-body').append(data);
				page.find('input[name="page"]').val(1);
				page.find('#customer-status-container .all').click();
			}
		});
	},

	loadListContent: function (urlParams = null) { 
		let page = this.getPage();
		if (!urlParams) {
			urlParams = this.getDefaultParams();
		}

		urlParams.mode = 'getCustomerList';

		// Get record list if target module is not Campaigns
		if (urlParams.target_module != 'Campaigns') {
			urlParams.mode = 'getRecordList';
		}

		app.helper.showProgress();

		app.request.post({ data: urlParams })
		.then(function (err, data) {
			app.helper.hideProgress();

			if (err) {
				app.helper.showErrorNotification({ 'message': err.message });
				return;
			}

			if (data) {
				page.find('#list-content').show();
				page.find('#list-content').html('');
				page.find('#list-content').append(data);
				page.find('#table-content').perfectScrollbar();

				// Init field select2
				page.find('#list-content select').select2();

				// Init CustomOwnerField
				assignedToInput = page.find('#list-content input[name="assigned_user_id"]');
				CustomOwnerField.initCustomOwnerFields(assignedToInput);

				vtUtils.applyFieldElementsView(page);
				vtUtils.enableTooltips();
				
				page.find('#listview-table').floatThead({
					scrollContainer: function () {
						return page.find('#listview-table').closest('.table-container');
					}
				});

				// Remove onclick of btn call
				page.find('.btnCall').attr('onclick', '');
			}
		});
	},

	getDefaultParams: function () {
		let page = this.getPage();
		let target_module = page.find('select.modules-filter').val();
		let cvId = page.find('select.module-listview-filter').val();
		let pageNo = page.find('input[name="page"]').val();
		let id = page.find('input[name="campaign_selector_id"]').val();
		let userId = page.find('select.users-filter').val();
		let status = page.find('.customer-status.active').attr('data-status');
		let purpose = page.find('input[name="campaign_selector_purpose"]').val();
		let orderBy = page.find('[name="orderBy"]').val();
		let sortOrder = page.find('[name="sortOrder"]').val();
		let totalRecord = page.find('.customer-status.active .amount').attr('data-amount');
		let maxResult = 20;
		let offset = pageNo == 1 ? 0 : (pageNo - 1) * maxResult;

		var params = {
			'module': 'CPTelesales',
			'view': "Telesales",
			'mode': 'getMainView',
			'target_module': target_module,
			'cv_id': cvId,
			'record': id,
			'user_id': userId,
			'status': status,
			'purpose': purpose,
			'orderby': orderBy,
			'sortorder': sortOrder,
			'page_no': pageNo,
			'offset': offset,
			'max_result': maxResult,
			'total_record' : totalRecord,
		}

		params.search_params = page.find('input[name="search_params"]').val();
		params.list_headers = page.find('[name="list_headers"]').val();
		return params;
	},

	getListSearchParams : function() {
		let page = this.getPage();
		var listViewTable = page.find('.searchRow');
		var searchParams = new Array();
		var listSearchParams = new Array();
		listSearchParams = [];


		listViewTable.find('.listSearchContributor').each(function(index, domElement) {
			var searchContributorElement = jQuery(domElement);
			var fieldName = searchContributorElement.attr('name');
			var searchValue = searchContributorElement.val();

			if (typeof searchValue == "object") {
				if(searchValue == null) {
					searchValue = "";
				}
				else{
					searchValue = searchValue.join(',');
				}
			}

			if (searchValue.length <= 0 ) {
				//continue
				return true;
			}

			var searchOperator = 'c';

			if (fieldName == "assigned_time" || fieldName == "last_call_time") {
				searchOperator = 'bw';
			}
			else if (fieldName == "salutationtype" || fieldName == "status" || fieldName == "last_call_result" || fieldName == "customer_type"){
				searchOperator = 'e';
			}

			var storedOperator = searchContributorElement.parent().parent().find('.operatorValue').val();

			if (storedOperator) {
				searchOperator = storedOperator;
				storedOperator = false;
			}

			searchParams.push({
				name: fieldName,
				operator: searchOperator,
				value: searchValue,
			});
		});

		// Add status value to search param if target_module is not Campaigns
		let target_module = page.find('select.modules-filter').val();
		let status = page.find('.customer-status.active');

		if (target_module != 'Campaigns' && status.attr('data-status') != 'all') {
			searchParams.push({
				name: status.attr('data-field-name'),
				operator: 'e',
				value: status.attr('data-status'),
			});
		}
		
		if (searchParams.length > 0) {
			listSearchParams = searchParams;
		}

		return listSearchParams;
	},

	clearFilters: function() {
		let page = this.getPage();
		page.find('.searchRow input, .searchRow select').val('').trigger('change');
		page.find('input[name="search_params"]').val('').trigger('change');
		this.loadListContent();
	},

	toggleStar: function (target, campaignId) {
		let targetStar = $(target);

		if (targetStar.hasClass('processing')) return;
		targetStar.addClass('processing');

		let customerId = targetStar.closest('tr.listViewEntries').attr('data-customer-id');
		let starred = 1;

		if (targetStar.hasClass('active')) {
			starred = 0;
		} 

		targetStar.toggleClass('active');

		let params = {
			module: 'CPTelesales',
			action: 'TelesalesAjax',
			mode: 'toggleStar',
			record: campaignId,
			customer_id: customerId,
			value: starred,
		}

		app.request.post({ data: params })
		.then(function (err, data) {
			if (err) {
				app.helper.showErrorNotification({ 'message': err.message });
				return;
			}

			if (starred == 0) {
				targetStar.attr("title", app.vtranslate('JS_NOT_STARRED'));
			}
			else {
				targetStar.attr("title", app.vtranslate('JS_STARRED'));
			}

			targetStar.removeClass('processing');
		});

		if (targetStar.hasClass('active')) {
			app.helper.showSuccessNotification({ 'message': app.vtranslate('JS_FOLLOW_RECORD') });
		} 
		else {
			app.helper.showSuccessNotification({ 'message': app.vtranslate('JS_UNFOLLOW_RECORD') });
		}
	},

	handleClickButtonCall(element, campaignId, customerNumber, customerId) {
		let params = {
			module: 'CPTelesales',
			action: 'TelesalesAjax',
			mode: 'getCampaignInfo',
			record: campaignId
		};

		app.request.post({ data: params })
		.then(function (err, data) {
			if (err) {
				app.helper.showErrorNotification({ 'message': err.message });
				return;
			}

			if (data.status != 'Active') {
				app.helper.showErrorNotification({ 'message': app.vtranslate('JS_TELESALES_CAMPAIGN_TELESALES_CAMPAIGN_INACTIVE_ERROR_MSG') });
				return;
			}

			if ($.inArray(_CURRENT_USER_META.id, data.user_agent_ids) < 0) {
				app.helper.showErrorNotification({ 'message': app.vtranslate('JS_TELESALES_CAMPAIGN_TELESALES_CAMPAIGN_CURRENT_USER_NOT_AGENT_ERROR_MSG') });
				return;
			}

			Vtiger_PBXManager_Js.registerPBXOutboundCall(element, `${customerNumber}`, `${customerId}`);
		});
	},

	recordStarToggle: function (element, targetModule, record) {
		let params = {
			module: targetModule,
			action: 'SaveStar',
			record: record,
		};

		if (element.hasClass('active')) {
			params.value = 0;
			element.removeClass('fa-star').addClass('fa-star');
		} 
		else {
			params.value = 1;
			element.removeClass('fa-star').addClass('fa-star');
		}

		element.toggleClass('active');
		params._timeStampNoChangeMode = true;

		app.request.post({ data: params })
		.then(function (err, data) {
			if (data) {
				if (params.value == 0) {
					element.attr("title", app.vtranslate('JS_NOT_STARRED'));
				} 
				else {
					element.attr("title", app.vtranslate('JS_STARRED'));
				}
			}
		})

		if (element.hasClass('active')) {
			app.helper.showSuccessNotification({'message':app.vtranslate('JS_FOLLOW_RECORD')});
		} 
		else {
			app.helper.showSuccessNotification({'message':app.vtranslate('JS_UNFOLLOW_RECORD')});
		}
	},
});