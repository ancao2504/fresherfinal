/**
 * Author: Phu Vo
 * Date: 2019.03.11
 * Last Update: 2019.04.17
 * Version: 1.0.2
 * Purpose: Vtiger Notification Controller
 * Require: JQuery, MomentJs, Vtiger app controller
 */

// Refactored by Hieu Nguyen on 2022-06-09
jQuery($ => {
	let Helper = {
		moduleName: 'CPNotifications',

		notifyIconMapping: {
			Default: '<i class="fad fa-file-alt"></i>',
			Campaigns: '<i class="fad fa-bullhorn"></i>',
			Leads: '<i class="fad fa-user"></i>',
			Contacts: '<i class="fad fa-user-tie"></i>',
			Accounts: '<i class="fad fa-building"></i>',
			Potentials: '<i class="fad fa-sack-dollar"></i>',
			HelpDesk: '<i class="fad fa-file-exclamation"></i>',
			Project: '<i class="fad fa-briefcase"></i>',
			Assets: '<i class="fad fa-cabinet-filing"></i>',
			ServiceContracts: '<i class="fad fa-file-signature"></i>',
			Products: '<i class="fad fa-box"></i>',
			Services: '<i class="fad fa-hand-holding-box"></i>',
			PriceBooks: '<i class="fad fa-book"></i>',
			Vendors: '<i class="fad fa-warehouse"></i>',
			Events: '<i class="fad fa-calendar-star"></i>',
			Calendar: '<i class="fad fa-tasks"></i>',
			Documents: '<i class="fad fa-folder-open"></i>',
			ProjectTask: '<i class="fad fa-briefcasetask"></i>',
			ProjectMilestone: '<i class="fad fa-flag"></i>',
			Message: '<i class="fad fa-comment" aria-hidden="true"></i>',
		},

		formatString: function (str, mapping) {
			if (!(mapping instanceof Object)) return str;
			if (!str) return "";

			for (let key in mapping) {
				let ex = `\\[${key}\\]`;
				str = str.replace(new RegExp(ex, 'g'), mapping[key]);
			}

			return str;
		},

		stripHtmlTags: function (str) {
			if ((str===null) || (str==='')) return '';
			
			str = str.toString();
			return str.replace(/<[^>]*>/g, '');
		},

		parseCreatedTime: function (timeString) {
			if (moment) {
				if (timeString) return moment(timeString).fromNow();
				return moment().fromNow();
			}
			
			return timeString || '';
		},

		getAvatar: function (data) {
			if (data.image) {
				return `<img class="avatar" src="${data.image}"/>`;
			} 
			else if (['inbound_msg'].includes(data.extra_data.action)) { // For new message
				return this.notifyIconMapping['Message'];
			}
			else if (data.related_module_name === 'Calendar' && data.extra_data.activity_type !== 'Task') {
				return this.notifyIconMapping['Events'];
			} else {
				return this.notifyIconMapping[data.related_module_name] || this.notifyIconMapping['Default'];
			}
		},

		getNotifyIcon: function (data) {
			let i;
			
			if (data.related_module_name === 'Calendar' && data.extra_data.activity_type !== 'Task') {
				i = this.notifyIconMapping['Events'];
			}
			else if (['inbound_msg'].includes(data.extra_data.action)) { // For new message
				i = this.notifyIconMapping['Message'];
			}
			else {
				i = this.notifyIconMapping[data.related_module_name] || this.notifyIconMapping['Default'];
			}

			return $(i).attr('class');
		},

		request: function (params, callback, background) {
			let defaultParams = {
				module: this.moduleName,
				action: 'HandleAjax'
			};

			params = {...defaultParams, ...params};

			if (!background) app.helper.showProgress();

			app.request.post({ data: params }).then((err, res) => {
				app.helper.hideProgress();
				if (err && !background) this.notifyError(app.vtranslate('JS_THERE_WAS_SOMETHING_ERROR'));
				if (typeof callback === 'function') callback(err, res);
			});
		},

		notifyError: function (msg) {
			app.helper.showErrorNotification({ message: msg });
		},

		notifySuccess: function (msg) {
			app.helper.showSuccessNotification({ message: msg });
		},

		getListName: function (type, subType) {
			let listName = subType ? type + '_' + subType: type;
			return listName;
		},

		getRelatedLink: function (itemData) {
			let { data } = itemData;

			// Added by Phu Vo 2019.08.01 to prevent empty related module notify
			if (!data.related_module_name || !data.related_record_id) return;
			// End prevent empty related module notify

			let extraData = data.extra_data;
			let link = `index.php?module=${data.related_module_name}&view=Detail&record=${data.related_record_id}`;
			let relatedInfo = window._RELATED_TABS_INFO;
			
			if (extraData.action === 'update') {
				let tabLabel = `${relatedInfo.modules[data.related_module_name]} ${relatedInfo.tabs.update}`;
				link += `&mode=showRecentActivities&page=1&tab_label=${tabLabel}`;
			}
			else if (['reply_comment', 'mention_comment'].includes(extraData.action)) {
				link += '';
			}
			else if (data.related_module_name !== 'Calendar') {
				let tabLabel = `${relatedInfo.modules[data.related_module_name]} ${relatedInfo.tabs.detail}`;
				link += `&mode=showDetailViewByMode&requestMode=full&tab_label=${tabLabel}`;
			}
			
			// Special case mass assign will open list view in new tab
			if (extraData['action'] == 'mass_assign') {
				let searchParams = [[]];
				searchParams[0].push(['assigned_user_id', 'c', `Users:${app.getUserId()}`]);

				// Stringify search params and replace " with &quot to use in html attribute
				let searchString = JSON.stringify(searchParams);

				// Create link to list view with search params and open in new tab
				link = `index.php?module=${data.related_module_name}&view=List&search_params=${searchString}`;
			}

			return link;
		},
	}

	window.Notifications = new class {
		constructor() {
			this.vars = {
				element: document.getElementById('notification'),
				createdTimeUpdateTime: 60000,
				maxInappNotify: 4,
			};

			this.helper = Helper; // public helper via Notifications object

			this.cache = {};

			this.inappNotify = {
				index: 0,
				list: {}
			}

			this.initNotificationPopoverPanel();
			this.initNotificationTabs();
			this.initDomEvents();
			this.initCreatedTimeUpdater();
			this.loadNotificationCounts();
		}

		getPopupContainer() {
			if (this.cache.popupContainer) return this.cache.popupContainer;
			return this.cache.popupContainer = $(this.vars.element).find('#notification-poup-container');
		}

		// Modified by Hieu Nguyen on 2022-06-30
		_createItem(itemData, listName) {
			itemData = itemData || {};
			let {data} = itemData || {};
			let template = $(this.vars.element).find('div#notification-templates').find('div.notify-item').clone();

			//process
			template.attr({
				'title': Helper.stripHtmlTags(itemData.message),
				'data-id': itemData.data.id,
			});

			template.tooltip();

			template.find('.avatar-container').html(Helper.getAvatar(data, listName));
			template.find('.notify-item-message').html(itemData.message);

			return template;
		}

		// Modified by Hieu Nguyen on 2022-06-30
		createItem(itemData, listName) {
			itemData = itemData || {};
			let self = this;
			let item = this._createItem(itemData, listName);
			let extraData = itemData.data.extra_data;

			// Notification item
			if (listName == 'notify_update' || listName == 'notify_checkin') {
				// Read status handle
				item.attr('data-read', itemData.data.read);
				item.find('.notify-item-createdtime').attr('data-value', itemData.data.created_time).html(Helper.parseCreatedTime(itemData.data.created_time));

				// Render button accept invitation
				if (extraData.action == 'invite' && !extraData.accepted) {
					let btnAcceptHtml = '<a href="#" class="btn-accept-invitation">'+ app.vtranslate('CPNotifications.JS_BTN_ACCEPT') +'</a>';
					item.find('.notify-item-actions').append(btnAcceptHtml);

					item.find('.btn-accept-invitation').on('click', function () {
						self.acceptInvitation(itemData.data.related_record_id, $(this));
					});
				}
			}
			// Reminder item
			else {
				item.addClass('single');    // Vertical align middle
				item.find('.notify-item-createdtime').remove();
			}

			// Handle item click
			item.on('click', function (e) {
				// Skip handle clicking when the target button is inside footer actions
				if ($(e.target).parent('div').hasClass('notify-item-actions')) {
					return;
				}

				// Open related record in new tab
				let relatedLink = Helper.getRelatedLink(itemData);
				if (relatedLink) window.open(Helper.getRelatedLink(itemData));

				// Mark as read when click on notification item				
				if (listName == 'notify_update' || listName == 'notify_checkin') {
					self.markAsRead(itemData.data.id);
				}
			});

			return item;
		}

		// Implemented by Hieu Nguyen on 2022-06-30 to accept the inviation right at the notification list
		acceptInvitation (eventId, targetBtn) {
			let self = this;

			let params = {
				mode: 'acceptInvitation',
				event_id: eventId,
			}

			Helper.request(params, (err, res) => {
				if (err || !res || !res.success) {
					Helper.notifyError(app.vtranslate('CPNotifications.JS_ACCEPT_INVITATION_ERROR_MSG'));
					return;
				}

				Helper.notifySuccess(app.vtranslate('CPNotifications.JS_ACCEPT_INVITATION_SUCCESS_MSG'));
				targetBtn.remove();
			}, true);
		}

		createPopup(popupData) {
			popupData = popupData || {};
			let {data} = popupData || {};
			let template = $(this.vars.element).find('div#notification-templates').find('div.notify-popup').clone();

			let getContentRows = extraData => {
				if ( !(extraData instanceof Object)) return "";
				let content = $([]);

				for (let field in extraData) {
					let row =  $('<div class="row clearfix"></div>');
					row.append($(`<div class="notify-popup-field-value col-sm-12">${extraData[field]}</div>`));
					content = content.add(row);
				}
				
				return content;
			}

			// process data
			template.attr({
				'data-type': 'notify-popup',
				'data-id': popupData.data.id,
			});

			// process content
			template.find('.notify-popup-title-content').html(popupData.message);
			template.find('.notify-popup-content').append(getContentRows(data.extra_data));

			// add event listers
			template.find('.cancelLink').on('click', function () {
				// dimiss
				template.remove();
			});

			template.find('form[name="notify-popup"]').on('submit', function (e) {
				e.preventDefault();
				// submit => go to related record
				window.open(`index.php?module=${data.related_module_name}&view=Detail&record=${data.related_record_id}`, '_blank');
				// dimiss
				template.remove();
			});

			return template;
		}

		markAsRead(target) {
			let self = this;

			let params = {
				mode: 'markAsRead',
				target: target,
			}

			Helper.request(params, (err, res) => {
				if (err || !res) return;

				// Update by sub type
				if (target == 'update' || target == 'checkin') {
					let listName = self.helper.getListName('notify', target);
					let listWrapper = this.cache.notificationPopover.find(`.notification-list[name="${listName}"]`);
					listWrapper.find('div.notify-item').attr('data-read', 1);   // Mark all as read

					this._updateCounter({
						total: this._getCounter('total') - this._getCounter(listName),
						notify: this._getCounter('notify') - this._getCounter(listName),
						[listName]: 0,
					});

					this.removeInappNotify('all');
				}
				// Update by id
				else {
					let item = this.cache.notificationPopover.find(`div.notify-item[data-id=${target}]`);
					item.attr('data-read', 1);  // Mark as read
					let listName = item.closest('.notification-list').attr('name');
					
					// Update Counter
					this._updateCounter({
						total: this._getCounter('total') - 1,
						notify: this._getCounter('notify') - 1,
						[listName]: this._getCounter(listName) - 1,
					});
				}
			}, true);
		}

		loadNotificationCounts() {
			let params = {
				mode: 'getNotificationCounts',
			}

			let callback = (err, res) => {
				if (res && res.counts) {
					this.updateCounter(res.counts);
				}
			}

			Helper.request(params, callback, true);
		}

		initNotificationPopoverPanel() {
			let self = this;
			this.cache.notificationPopoverTrigger = $(this.vars.element).find("a#notification-popover-trigger");
			this.cache.notificationPopover = $(this.vars.element).find(this.cache.notificationPopoverTrigger.data('for'));

			this.cache.notificationPopoverTrigger.on('click', () => {
				this.cache.notificationPopover.toggle({
					duration: 0,
					complete: () => self.triggerLoadFirstPage()
				});
			});

			// init event to close popover
			$('body').on('click', e => {
				if (
					e.target.data !== this.vars.element.id &&
					$(e.target).parents(`#${this.vars.element.id}`).length === 0 &&
					$(e.target).parents('div.popover.in').length === 0
				) {
					this.cache.notificationPopover.hide();
				}
			});
		}

		triggerLoadFirstPage() {
			let self = this;

			if (this.cache.notificationPopover.is(':visible')) {
				let activeList = this.cache.notificationPopover.find('div.notification-list:visible');
				let listItemsContainer = activeList.find('div.notification-items');

				if (listItemsContainer.find('.notify-item').length == 0) {
					self.loadList(activeList.data('type'), activeList.data('sub-type'), 0);
				}
			}
		}

		initNotificationTabs() {
			self = this;

			if (this.cache.notificationPopover) {
				let notificationPopover = this.cache.notificationPopover;
				let mainNav = notificationPopover.find('#notification-tabs');
				let mainTabs = mainNav.find('.nav-item');

				// Init notification list metadata
				notificationPopover.find('.notification-list').each(function () {
					let type = $(this).data('type');
					let subType = $(this).data('subType');
					let listName = self.helper.getListName(type, subType);
					$(this).attr('name', listName);
				});

				// Handle main tab click event
				mainTabs.on('click', function (e) {
					let targetTab = $(this);
					let targetTabPane = notificationPopover.find(targetTab.data('for'));

					// Set target tab as active
					mainTabs.removeClass('active');
					targetTab.addClass('active');

					// Set target tab pane as active
					notificationPopover.find('.tab-pane').removeClass('active');
					targetTabPane.addClass('active');

					// Trigger load notifications
					targetTabPane.find('.notification-subtabs').find('.nav-item:first').trigger('click');
				});

				// Handle sub tab click event
				notificationPopover.find('.notification-subtabs').find('.nav-item').on('click', function (e) {
					let targetSubTab = $(this);
					let targetSubTabPane = notificationPopover.find(targetSubTab.data('for'));

					// Set target sub tab as active
					targetSubTab.closest('.notification-subtabs').find('.nav-item').removeClass('active');
					targetSubTab.addClass('active');

					// Set target sub tab pane as active
					notificationPopover.find('.subtab-pane').removeClass('active');
					targetSubTabPane.addClass('active');

					self.notificationReloadList(targetSubTabPane.data('type'), targetSubTabPane.data('subType'), targetSubTabPane.data('offset'));
				});
			}
		}

		initDomEvents() {
			this.initListScrollEvent();
		}

		initListScrollEvent() {
			let self = this;

			$(this.vars.element).find('div.notification-list').on('scroll', function (e) {
				var elem = $(e.target);
				if ((elem.scrollTop() + elem.height() >= elem[0].scrollHeight) && (elem.data('offset') > 0 || elem.data('offset') == 0)) {
					self.loadList(elem.data('type'), elem.data('sub-type'), elem.data('offset'));
				}
			});
		}
		
		initCreatedTimeUpdater() {
			this.resetCreatedTimeUpdater();

			if (this.vars.createdTimeUpdateTime) {
				this.cache.createTimeUpdater = setInterval(() => {
					$('.notify-item-createdtime').each(function () {
						let createdTime = $(this)
						let value = createdTime.data('value');
						let createdTimeValue = Helper.parseCreatedTime(value);

						createdTime.html(createdTimeValue);
					});
				}, this.vars.createdTimeUpdateTime);
			}
		}

		resetCreatedTimeUpdater() {
			if (this.cache.createTimeUpdater) {
				clearInterval(this.cache.createTimeUpdater);
				delete this.cache.createTimeUpdater;
			}
		}

		notificationReloadList() {
			let self = this;

			if (this.cache.notificationPopover.is(':visible')) {
				let activeList = this.cache.notificationPopover.find('div.notification-list:visible');

				if (activeList.length > 0) {
					// select with class return list jquery ui
					activeList.each(function () {
						let target = $(this);
						let preLoadCb = (listName, res) => {
							let items = target.find('div.notification-items');
							items.html('');
						}

						self.loadList(target.data('type'), target.data('sub-type'), 0, preLoadCb);
					});
				}
			}
		}

		loadList(type, subType = '', offset, preLoadCb) {
			if (!offset && offset != 0) return;
			let listName = self.helper.getListName(type, subType);
			let listWrapper = $(this.vars.element).find(`div.notification-list[name="${listName}"]`);

			let params = {
				mode: 'loadData',
				type: type,
				sub_type: subType,
				offset: offset,
			};

			if (typeof preLoadCb === 'function') preLoadCb(listName);

			// create loader ui
			listWrapper.attr('data-status', 'loading').scrollTop(listWrapper[0].scrollHeight);

			Helper.request(params, (err, res) => {
				if (res === 'Invalid request') location.reload();

				if (!res || !res.data || res.data.length === 0) {
					listWrapper.attr('data-status', 'empty');
				}
				else {
					listWrapper.attr('data-status', '');
				}

				// if (typeof preRenderCb === 'function') preRenderCb(listName, res);

				this.renderList(listName, res);
			}, true);
		}

		renderList(listName, data, callback = '') {
			data = data || {};
			let items = data.data || [];
			let list = $(this.vars.element).find(`div.notification-list[name="${listName}"]`);
			let newList = $([]);

			items.forEach(item => {
				newList = newList.add(this.createItem(item, listName));
			});

			list.attr('data-offset', data.next_offset).data('offset', data.next_offset);
			list.find('div.notification-items').append(newList);

			if (data.counts) this.updateCounter(data.counts);

			if (typeof callback === 'function') callback();
		}

		updateCounter(params) {
			let adapter = {
				parseDetails: params => {
					for (let key in params) {
						if (key.indexOf('_detail') > -1) {
							for (let detail in params[key]) {
								params[key.replace('detail', detail)] = params[key][detail];
							}

							delete params[key];
						}
					}

					return params;
				}
			}

			this._updateCounter(adapter.parseDetails(params));
		}

		_selectCounter(counterName) {
			let selectorMapping = {
				total: 'span#notification-counter',
				notify: 'span#notification-counter-notify',
				activity: 'span#notification-counter-task',
				birthday: 'span#notification-counter-birthday',
				notify_update: 'span#notification-counter-notify-update',
				notify_checkin: 'span#notification-counter-notify-checkin',
				activity_coming: 'span#notification-counter-activity-coming',
				activity_overdue: 'span#notification-counter-activity-overdue',
				birthday_today: 'span#notification-counter-birthday-today',
				birthday_coming: 'span#notification-counter-birthday-coming',
			}

			return selectorMapping[counterName] ? $(this.vars.element).find(selectorMapping[counterName]) : $();
		}

		_getCounter(counterName) {
			return Number(this._selectCounter(counterName).text()) || 0;
		}

		_setCounter(counterName, value) {
			value = Number(value);
			let element = this._selectCounter(counterName);
			element.text(value);

			if (value > 0) {
				element.removeClass('hide');
			}
			else {
				element.addClass('hide');
			}

			return value;
		}

		_updateCounter(params) {
			Object.keys(params).forEach(key => {
				if (this._selectCounter(key) && Number(params[key]) > -1) {
					this._setCounter(key, params[key]);
				}
			});
		}

		newNotification(item) {
			if (item.data.type == 'popup') {
				this.openPopup(item);
				return;
			}

			if (item.data.extra_data.is_flash_msg) {
				this.openInAppNotify(item);
				return;
			}

			let list = $([]);
			let {data} = item;
			let listName = self.helper.getListName(data.type, data.subType);

			// assign default value
			data = {...data, ...{read: 0}};
			item.data = data;

			list = list.add(this.createItem(item, listName));

			// Append new item
			$(this.vars.element).find(`div.notification-list[name="${listName}"] div.notification-items`).prepend(list);

			// Update list UI
			let listUi = $(this.vars.element).find(`div.notification-list[name="${listName}"]`);
			listUi.attr('data-status', '');

			// Update Counter
			this._updateCounter({
				total: this._getCounter('total') + 1,
				[listName]: this._getCounter(listName) + 1,
			});
			
			this.openInAppNotify(item);
		}

		remove(id) {
			this.cache.notificationPopover.find(`div.notify-item[data-id=${id}]`).remove();
		}

		openPopup(props) {
			let popup = this.createPopup(props);
			//
			this.getPopupContainer().append(popup);
		}

		getInappNotifyCount() {
			return $('.notification-inapp-notify').length;
		}

		removeInappNotify(index) {
			if (index === 'all') {
				for (let index in this.inappNotify.list) {
					this.inappNotify.list[index].close();
				}

				this.inappNotify.index = 0;
				this.inappNotify.list = {};

				return;
			}

			if (this.inappNotify.list[index]) this.inappNotify.list[index].close();
			// delete this.inappNotify.list[index];
			return this;
		}

		_openInAppNotify(itemData, settings) {
			let self = this;
			let { data } = itemData;
			settings = settings || {};

			let options = {
				icon: Helper.getNotifyIcon(data),
				title: app.vtranslate('JS_CPNOTIFICATIONS'),
				message: itemData.message,
				url: Helper.getRelatedLink(itemData)?.replace(/"/g, '&quot;'),
				target: '_blank'
			};

			// Auto timeout fash message
			if (itemData.data.extra_data.is_flash_msg) {
				settings.delay = 3000;
			}

			// Added by Phu Vo on 2021.03.23 for chatclient logic
			if (itemData.data.extra_data && itemData.data.extra_data.action == 'inbound_msg') {
				if (typeof SocialChatboxPopup == 'undefined') return;
				if (!SocialChatboxPopup.willShownNotification(data.extra_data)) return;

				options.title = app.vtranslate('JS_CHAT_BOX_NEW_MESSAGE');

				let messageType = itemData.data.extra_data.msg_type.toUpperCase();
				let messageTypeKey = 'JS_CHAT_BOX_LAST_MSG_' + messageType;

				if (messageType != 'TEXT' && messageType != '') {
					let replaceParams = {
						customer_name: itemData.data.related_record_name,
						message_type: app.vtranslate(messageTypeKey),
					};

					options.message = app.vtranslate('JS_CHAT_BOX_NOTIFICATION_LAST_MSG', replaceParams);
				}

				delete options.url;
				delete options.target;

				let callback;
				if (typeof settings.onClick === 'function') callback = settings.onClick;
				settings.onClick = function (event, extraData) {
					if (typeof callback == 'function') callback(event, extraData);
					SocialChatboxPopup.open(itemData.data.extra_data.channel, itemData.data.related_record_id);
				}
			}

			// Added by Phu Vo on 2021.03.23 for chatclient logic
			if (itemData.data.extra_data && itemData.data.extra_data.action == 'transfer_chat') {
				if (typeof SocialChatboxPopup == 'undefined') return;

				options.title = app.vtranslate('JS_CHAT_BOX_TRANSFER_CHAT');
				delete options.url;
				delete options.target;

				let callback;
				
				if (typeof settings.onClick === 'function') callback = settings.onClick;

				settings.onClick = function (event, extraData) {
					if (typeof callback == 'function') callback(event, extraData);
					SocialChatboxPopup.open(itemData.data.extra_data.channel, itemData.data.related_record_id);
				}

				SocialChatboxPopup.updateTotalUnreadCounter();
			}

			// Onshow events
			settings.onShow = function () {
				$(this).addClass('notification-inapp-notify');

				$(this).find('[data-notify="message"]').attr('title', Helper.stripHtmlTags(itemData.message)); // Bug #258: Modified by Phu Vo to add title to message container

				if (typeof settings.postShowCb === 'function') settings.postShowCb($(this));
			}

			// Assign settings with default
			settings = {
				...{
					delay: 0,
					animate: {
						enter: null,
						exit: null,
					},
					// newest_on_top: true,
					onClose: function () {
						delete self.inappNotify.list[this.data('index')];
					}
				},
				... settings
			}

			// Show notify, assign to storage with auto increase index
			let notify = this.inappNotify.list[this.inappNotify.index] = $.notify(options, settings);

			// Add external data
			notify.$ele.data('index', this.inappNotify.index);

			// Add event listener
			notify.$ele.on('click', function (event) {
				if (typeof settings.onClick === 'function') settings.onClick(event, $(this));
				notify.close();
			});

			// Increase inappNotify index by 1
			this.inappNotify.index ++;

			return this;
		}

		openInAppNotify(itemData, settings) {
			settings = settings || {};

			// Check inapp notify quantity
			if (this.getInappNotifyCount() >= this.vars.maxInappNotify) {
				// Assign settings with default
				settings.onShown = () => {
					this.removeInappNotify(Math.min(...Object.keys(this.inappNotify.list)));
				}
			}

			settings.onClick = (event, data) => {
				let target = $(event.target);

				// Mark notification as read on click
				if (itemData.data.id && !target.is('button.close')) {
					this.markAsRead(itemData.data.id);
				}
			}

			return this._openInAppNotify(itemData, settings);
		}

		updateNotification(params) {
			if (params.counter) this.updateCounter(params.counter);
			if (params.insert) this.newNotification(params.insert);
			if (params.remove) this.remove(params.remove);
		}
	}
});