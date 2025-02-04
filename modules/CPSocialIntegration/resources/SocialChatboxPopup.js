/**
 * Name: SocialChatboxPopup.js
 * Author: Phu Vo
 * Date: 2021.01.13
 * Description: JS Controller for Social Chatbox Popup
 */

// Create private code zone
(() => {
	let publicStore = {
		module: 'CPSocialIntegration',
		action: 'SocialChatboxPopupAjax',
		channel_images: {
			Zalo: 'resources/images/zalo.png',
		},
		social_pages: [],
		size: 'NONE', // NONE | NORMALIZED | MINIMIZED
		new_unread: 0,
		conversations: [],
		active_social_page: {
			id: null,
			channel: null,
			customer_type: '',
			keyword: '',
			next_offset: 0,
		},
		active_conversation: {
			customer_social_id: null,
			messages: [],
			next_offset: 0,
			customer_name: '',
			metadata: {},
			refresing: false,
			active_typing_msg: '',
			message: '',
		},
		active_customer_profile: {
			success: true,
			refresing: false,
			record_id: '',
			record_module: '',
			full_name: '',
			email: '',
			mobile: '',
			full_address: '',
			account_id: '',
			account_name: '',
			description: '',
			tags_list: [],
			comments: [],
		},
		active_customer_profile_mode: 'detail',
		customer_profile_form: {
			customer_id: '',
			customer_type: '',
			salutationtype: '',
			firstname: '',
			lastname: '',
			email: '',
			mobile: '',
			address: '',
			account_id: '',
			company: '',
			main_owner_id: '',
			description: '',
		},
		active_customer_comment_form: {
			commentcontent: '',
			files: [],
		},
		acount_search_results: [],
		left_panel_header_loading: false,
		left_panel_body_loading: false,
		main_panel_header_loading: false,
		main_panel_body_loading: false,
		right_panel_body_loading: false,
		social_pages_scroll_left_enabled: false,
		social_pages_scroll_right_enabled: false,
		searching_timeout: 300,
		intervals: {},
		threshold: 100,
		msg_type_mapping: {
			'[EMOJ]': '[' + app.vtranslate('JS_CHAT_BOX_LAST_MSG_EMOJ') + ']',
			'[FILE]': '[' + app.vtranslate('JS_CHAT_BOX_LAST_MSG_FILE') + ']',
			'[VIDEO]': '[' + app.vtranslate('JS_CHAT_BOX_LAST_MSG_VIDEO') + ']',
			'[IMAGE]': '[' + app.vtranslate('JS_CHAT_BOX_LAST_MSG_IMAGE') + ']',
			'[LOCATION]': '[' + app.vtranslate('JS_CHAT_BOX_LAST_MSG_LOCATION') + ']',
			'[CARD]': '[' + app.vtranslate('JS_CHAT_BOX_LAST_MSG_CARD') + ']',
			'[DOODLE]': '[' + app.vtranslate('JS_CHAT_BOX_LAST_MSG_DOODLE') + ']',
			'[MP3]': '[' + app.vtranslate('JS_CHAT_BOX_LAST_MSG_MP3') + ']',
			'[LIST]': '[' + app.vtranslate('JS_CHAT_BOX_LAST_MSG_LIST') + ']',
		},
		social_pages_paging: {
			total: 0,
			length: 3,
			max_page: 0,
			current_page: 0,
		},
		customer_type_config: _CUSTOMER_TYPE_CONFIG || {},
		picklist_fields: _SOCIAL_CHATBOX_PICKLIST_FIELDS || {},
		customers_tags_list: {},
		save_mode: null,
		meta_data: _SOCIAL_CHATBOX_META_DATA,
		scroll_to_bottom_visibility: false,
		having_new_message: false,
		posting_comment: false,
		duplicated_profiles: [],
		duplicated_fields: {
			email: false,
			phone: false,
		},
		profile_error_count: 0,
		conversations_loading: false,
	};
	
	let DataTableUtils = {
		languages: {
			emptyTable: app.vtranslate('JS_DATATABLES_NO_DATA_AVAILABLE'),
			info: app.vtranslate('JS_DATATABLES_FOOTER_INFO'),
			infoEmpty: app.vtranslate('JS_DATATABLES_FOOTER_INFO_NO_ENTRY'),
			lengthMenu: app.vtranslate('JS_DATATABLES_LENGTH_MENU'),
			loadingRecords: app.vtranslate('JS_DATATABLES_LOADING_RECORD'),
			processing: app.vtranslate('JS_DATATABLES_PROCESSING'),
			search: app.vtranslate('JS_DATATABLES_SEARCH'),
			zeroRecords: app.vtranslate('JS_DATATABLES_NO_RECORD'),
			sInfoFiltered: app.vtranslate('JS_DATATABLES_INFO_FILTERED'),
			paginate: {
				first: app.vtranslate('JS_DATATABLES_FIRST'),
				last: app.vtranslate('JS_DATATABLES_LAST'),
				next: app.vtranslate('JS_DATATABLES_PAGINATE_NEXT_PAGE'),
				previous: app.vtranslate('JS_DATATABLES_PAGINATE_PREVIOUS_PAGE')
			}
		}
	}

	let Error = new class {
		notify (msg) {
			app.helper.showErrorNotification({ message: msg });
		}

		alert (msg) {
			app.helper.showAlertBox({message: msg});
		}

		silent (msg) {
			Helper.debugTrace(msg);
		}
	}

	let Success = new class {
		notify (msg) {
			app.helper.showSuccessNotification({ message: msg });
		}

		alert (msg) {
			app.helper.showAlertBox({message: msg});
		}

		silent (msg) {
			Helper.debugLog(msg);
		}
	}

	let LocalStorage = new class {
		constructor () {
			this.namespace = 'SocialChatboxPopup';
			this.storage = {};

			if (window.localStorage.getItem(this.namespace)) {
				this.storage = JSON.parse(window.localStorage.getItem(this.namespace));
			}
		}

		set (key, value) {
			this.storage[key] = value;
			this.save();
		}

		get (key, value = '') {
			if (!this.storage[key] && value) {
				this.set(key, value);
			}

			return this.storage[key];
		}

		flush () {
			this.storage = {};
			this.save();
		}

		save () {
			window.localStorage.setItem(this.namespace, JSON.stringify(this.storage));
		}
	}

	Helper = new class {
		constructor () {
			this.debug = false;
			this.store = null;
		}

		debugLog (...msg) {
			if (this.debug == true) {
				let timestamp = new Date().toLocaleString();
				console.log(timestamp, '[SocialChatboxPopup]', ...msg);
			}
		}

		debugTrace (...msg) {
			if (this.debug == true) {
				let timestamp = new Date().toLocaleString();
				console.trace(timestamp, '[SocialChatboxPopup]', ...msg);
			}
		}

		getStore () {
			if (this.store) return this.store;

			this.store = Object.assign({}, publicStore);
			
			let localData = LocalStorage.get('data');
			
			if (localData && Object.keys(localData).length > 0) {
				this.store.size = 'MINIMIZED';
				this.store.loaded_from_cache = true;
			}

			return this.store;
		}

		ajax (params) {
			let requestParams = {};
			let defaultParams = {
				module: this.getStore().module,
				action: this.getStore().action,
			}

			if (params instanceof FormData) {
				requestParams.contentType = false;
				requestParams.processData = false;
			}
			else {
				params = $.extend(defaultParams, params);
			}

			requestParams.data = params;

			return app.request.post(requestParams);
		}

		/**
		 * Help resolve ajax request error automaticaly
		 * @param {*} err
		 * @param {*} res
		 * @param {*} method notify | alert
		 */
		resolveError (err, res, method = 'notify') {
			if (err) {
				Error[method](err.message);
				this.debugTrace(err.message);
				return false;
			}

			if (!res || res?.result === false) {
				Error[method](app.vtranslate('JS_THERE_WAS_SOMETHING_ERROR'));
				this.debugTrace(app.vtranslate('JS_THERE_WAS_SOMETHING_ERROR'));
				return false;
			}

			if (typeof res == 'string' && res.search('xdebug-error') > -1) {
				Error.alert(res);
				return false;
			}

			return true;
		}

		stripHtml (html) {
			let tmp = document.createElement('div');
			tmp.innerHTML = html;
			
			return tmp.textContent || tmp.innerText || '';
		}
	}

	let popupOptions = {
		el: '#social-chatbox-popup',
		data: Helper.getStore(),
		watch: {
			'size': function (value, preValue) {
				VueUtils.hideBootstrapTooltip();
				setTimeout(() => this.updateTotalUnreadCounter(), 100);
			},
			'social_pages': function (value, preValue) {
				this.social_pages_paging.total = value ? value.length : 0;
				this.social_pages_paging.max_page = Math.ceil(this.social_pages_paging.total / this.social_pages_paging.length);
			},
			'active_social_page.keyword': function (value, preValue) {
				this.filterConversations(true);
			},
			'active_social_page.customer_type': function (value, preValue) {
				this.filterConversations(false);
			},
			'social_pages_paging.current_page': function (value, preValue) {
				if (value == 1) {
					this.social_pages_scroll_left_enabled = false;
				}
				else {
					this.social_pages_scroll_left_enabled = true;
				}
				
				if (value == this.social_pages_paging.max_page) {
					this.social_pages_scroll_right_enabled = false;
				}
				else {
					this.social_pages_scroll_right_enabled = true;
				}
			},
			'active_social_page.id': function (value, preValue) {
				this.social_pages_paging.fall_back_page = this.social_pages_paging.current_page;
			},
			'customer_profile_form.salutationtype': function (value, preValue) {
				setTimeout(() => {
					$(this.$el).find(':input[name="salutationtype"]').trigger('change');
				}, 0);
			},
			'customer_profile_form.leadsource': function (value, preValue) {
				setTimeout(() => {
					$(this.$el).find(':input[name="leadsource"]').trigger('change');
				}, 0);
			},
			'customer_profile_form.owner_id': function (value, preValue) {
				setTimeout(() => {
					let data = {
						id: this.customer_profile_form.owner_id,
						text: this.customer_profile_form.owner_name,
					};
					$(this.$el).find(':input[name="main_owner_id"]').select2('data',data).trigger('change');
				}, 0);
			},
			'active_customer_profile.success': function (value, preValue) {
				if (!value) {
					setTimeout(() => {
						let message = app.vtranslate('JS_SOCIAL_CHATBOX_CUSTOMER_PROFILE_ERROR_RELOAD_NOTIFICATION');

						app.helper.showAlertBox({ message }, () => {
							this.reloadConversationList();
						});
					});
				}
			},
			active_customer_profile_mode: function (value, preValue) {
				VueUtils.hideBootstrapTooltip();
			},
		},
		computed: {
			social_pages_size: function () {
				return this.social_pages.length;
			},
		},
		methods: {
			getRandomSeed: function () {
				return Math.floor(100000000 * Math.random());
			},
			getJsonContent: function (string) {
				try {
					return JSON.parse(string);
				}
				catch {
					return {};
				}
			},
			getGoogleMapUrl: function (latitude, longitude, zoomLevel) {
				zoomLevel = zoomLevel || 16;
				return `https://www.google.com/maps/dir/${latitude},${longitude}/@${latitude},${longitude},${zoomLevel}z`;
			},
			formatLastMsgTime: function (time) {
				if (!time) return '';

				let momentTime = moment(time);

				if (momentTime.format('YYYY') == moment().format('YYYY')) {
					return momentTime.format('DD-MM');
				}

				return momentTime.format('DD-MM-YYYY');
			},
			formatMsgTime: function (time) {
				if (!time) return '';

				let momentTime = moment(time);

				if (momentTime.format('YYYY-MM-DD') == moment().format('YYYY-MM-DD')) {
					return momentTime.format('HH:mm');
				}

				if (momentTime.format('YYYY-MM') == moment().format('YYYY-MM')) {
					return momentTime.format('MM-DD HH:mm');
				}

				return momentTime.format('YYYY-MM-DD HH:mm');
			},
			formatTagsList: function (tags) {
				tags = Object.keys(tags).map(id => {
					return {
						id,
						text: tags[id]['name'],
						name: tags[id]['name'],
						type: tags[id]['type'],
					}
				});

				return tags;
			},
			getMappingValue: function (value) {
				if (value == '-') return value;
				return '***' + value.slice(-3);
			},
			getMediaUrl: function (value) {
				if (!value) return '';

				if (!value.startsWith('http')) return _CHATBOX_STORAGE_SERVER_URL + value;

				return value;
			},
			getAvatarUrl: function (personId, type, seed = '') {
				if (type == 'Channel') {
					return this.active_social_page.avatar;
				}

				if (type == 'Users' && !personId) {
					return this.active_social_page.avatar;
				}
				
				let url =  `entrypoint.php?name=GetAvatar&record=${personId}&module=${type}`;
				if (seed) url += `&v=${seed}`;

				return url;
			},
			getCommentUrl: function (customerId, customerType) {
				if (!customerId || !customerType) return '';

				let relationId = this.meta_data.relation_ids[customerType]['ModComments'];

				return `index.php?module=${customerType}&relatedModule=ModComments&view=Detail&record=${customerId}&mode=showRelatedList&tab_label=ModComments&relationId=${relationId}`;
			},
			getCommentTitle: function (comment) {
				let replaceParams = {
					user_name: comment.user_name,
					comment_time: MomentHelper.getDisplayTime(comment.createdtime),
					comment_date: MomentHelper.getDisplayDate(comment.createdtime),
					comment_content: Helper.stripHtml(comment.commentcontent),
				}

				let commentContent = app.vtranslate('JS_SOCIAL_CHATBOX_COMMENT_TITLE', replaceParams);

				return commentContent;
			},
			getSenderName: function (message) {
				if (message.sender_name) {
					let replaceParams = { sender_name: message.sender_name };
					
					return app.vtranslate('JS_SOCIAL_CHATBOX_SENDER_NAME', replaceParams);
				}

				return app.vtranslate('JS_SOCIAL_CHATBOX_UNDEFINED_SENDER');
			},
			getRecordDetailUrl: function (recordId, moduleName) {
				if (!recordId || !moduleName) return '';

				let tabLabel = this.meta_data.detail_tab_labels[moduleName]?.['summary'];

				return `index.php?module=${moduleName}&view=Detail&record=${recordId}&mode=showDetailViewByMode&requestMode=summary&tab_label=${tabLabel}`;
			},
			getLastMsg: function (value, typingMessage = '') {
				if (typingMessage) return typingMessage;
				if (!value) return '';
				if (Object.keys(this.msg_type_mapping).includes(value)) return this.msg_type_mapping[value];
				return value;
			},
			getUserProfileUrl: function (userId) {
				if (!userId) return '';
				userId = userId + '';
				userId = userId.replace(/Users:/g, '');
				return `index.php?module=Users&view=PreferenceDetail&parent=Settings&record=${userId}`;
			},
			getTypingMsg: function (conversation, withUserName = false) {
				if (!conversation.typing_status || !conversation.typing_status.typing) return '';

				if (!withUserName) {
					return app.vtranslate('JS_SOCIAL_CHATBOX_TYPING_STATUS_MSG') + '...';
				}

				let replaceParams = {
					'user_full_name' : conversation.typing_status.user_full_name,
				}

				return app.vtranslate('JS_SOCIAL_CHATBOX_TYPING_STATUS_MSG_WITH_USER_NAME', replaceParams) + '...';
			},
			getMessageText: function (value) {
				value = value || '';
				value = value.replace(/\n/g, '<br />');

				return value;
			},
			getFileSize: function (sizeInByte) {
				sizeInByte = sizeInByte || 0;
				sizeInByte = parseInt(sizeInByte);

				if (sizeInByte < 1000000) return (sizeInByte / 1000).toFixed(2) + ' Kb';

				return (sizeInByte / 1000000).toFixed(2) + ' Mb';
			},
			loadSocialPages: function (callback = null) {
				this.left_panel_header_loading = true;

				Helper.ajax({ mode: 'getSocialPageList' }).then((err, res) => {
					this.left_panel_header_loading = false;

					if (!Helper.resolveError(err, res)) return;

					this.social_pages = res;
					this.social_pages_paging.current_page = 1;

					if (callback && typeof callback == 'function') callback(res);
				});
			},
			loadConversationsBySocialPage: function (activePage, loadMore = false) {
				if (!activePage) return;

				if (this.conversations_loading) return;

				let scrollContainer = $(this.$el).find('.conversations-container.scrollable-container');
				let newActiveSocialPage = Object.assign(this.active_social_page, activePage);

				if (loadMore && newActiveSocialPage.next_offset == null) return;

				this.active_social_page = newActiveSocialPage;
				this.conversations_loading = true;

				let params = {
					mode: 'getConversationsBySocialPage',
					channel: this.active_social_page.channel,
					page_id: this.active_social_page.id,
					customer_type: this.active_social_page.customer_type,
					keyword: this.active_social_page.keyword,
					offset: loadMore ? this.active_social_page.next_offset : 0,
				}

				Helper.ajax(params).then((err, res) => {
					this.conversations_loading = false;

					if (!Helper.resolveError(err, res)) return;

					if (!loadMore) scrollContainer.scrollTop(0);

					let conversations = res.data || [];

					conversations = conversations.map(single => Object.assign({
						seed: this.getRandomSeed(),
						active_typing_msg: '',
					}, single));

					let customerIds = conversations.map(single => single.customer_id);
					this.loadCustomersTagsList(customerIds);

					this.conversations = (loadMore ? this.conversations : []).concat(conversations);

					this.active_social_page.next_offset = res.next_offset;

					if (!loadMore) {
						if (this.active_conversation.customer_social_id) {
							this.loadConversation(this.active_conversation);
						}
						else if (this.conversations.length > 0) {
							this.loadConversation(this.getFirstAccessableConversation());
						}
						else {
							this.active_conversation = Object.assign({}, publicStore.active_conversation); // Reset active conversation
							this.active_customer_profile = Object.assign({}, publicStore.active_customer_profile);
						}
					}
				});
			},
			getFirstAccessableConversation: function () {
				conversation = null;
				
				for (let i = 0; i < this.conversations.length; i++) {
					if (this.conversations[i].can_access == 1) {
						conversation = this.conversations[i];
						break;
					}
				}

				return conversation;
			},
			loadConversationsByCustomer: function (channel, customerId, customerSocialId = null) {
				let params = {
					mode: 'getConversationsByCustomer',
					channel: channel,
					customer_id: customerId,
				}

				Helper.ajax(params).then((err, res) => {
					if (!Helper.resolveError(err, res)) return;

					let conversations = res.data || [];

					this.conversations = conversations.map(single => Object.assign({
						can_access: 1,
						seed: this.getRandomSeed(),
						active_typing_msg: '',
					}, single));

					let customerIds = conversations.map(single => single.customer_id);
					this.loadCustomersTagsList(customerIds);

					let activeConversation = this.getFirstAccessableConversation();

					if (customerSocialId) {
						let activeConversationIndex = this.conversations.findIndex(single => single.customer_social_id == customerSocialId);

						if (activeConversationIndex > -1) {
							activeConversation = this.conversations[activeConversationIndex];
						}
					}

					if (activeConversation) {    
						// Calculate current social page pagination
						let activeSocialPageIndex = this.social_pages.findIndex(single => single.id == activeConversation.social_page_id);
						let activeSocialPageNumber = Math.ceil((activeSocialPageIndex + 1) / this.social_pages_paging.length);
						this.socialPagesGoToPage(activeSocialPageNumber);
						
						let newActiveSocialPage = Object.assign(this.active_social_page, this.social_pages[activeSocialPageIndex]);
	
						this.active_social_page = newActiveSocialPage;
	
						if (this.conversations.length > 0) {
							setTimeout(() => {
								this.loadConversation(activeConversation);
							}, 0);
						}
					}
				});
			},
			loadCustomersTagsList: function (customerIds) {
				if (customerIds.length == 0) return;

				let requestParams = {
					mode: 'getCustomersTagsList',
					customer_ids: customerIds,
				};

				Helper.ajax(requestParams).then((err, res) => {
					if (!Helper.resolveError(err, res)) return;

					let customersTagsList = Object.assign(this.customers_tags_list, res);
					this.customers_tags_list = customersTagsList;
				});
			},
			loadCustomerTagList: function (customerId, customerModule) {
				if (!customerId) return;
				
				let params = {
					mode: 'getCustomerTags',
					customer_id: customerId,
					customer_type: customerModule,
				};

				Helper.ajax(params).then((err, res) => {
					if (!Helper.resolveError(err, res)) return;
					
					this.customers_tags_list[customerId] = res;

					if (this.active_customer_profile.record_id == customerId) {
						this.active_customer_profile.tags_list = res;
					}
				});
			},
			loadIncommingConversation: function (channel, socialPageId, customerSocialId) {
				let params = {
					mode: 'getConversationsByCustomerSocialId',
					channel: channel,
					social_page_id: socialPageId,
					customer_social_id: customerSocialId,
				};

				Helper.ajax(params).then((err, res) => {
					if (!Helper.resolveError(err, res)) return;

					this.conversations = res.data.concat(this.conversations);
				});
			},
			conversationClickHandler: function (event, conversation) {
				let target = $(event.target);

				if (target.is('.dropdown-toggle')) {
					return;
				}

				this.loadConversation(conversation);
			},
			loadConversation: function (activeConversation, loadMore = false) {
				if (!activeConversation) return;
				if (activeConversation.can_access != '1') return;
				if (this.active_conversation.loading) return;
				if (loadMore && this.active_conversation.next_offset == null) return;

				if (!loadMore) this.preserveMessageContent();

				let newActiveConversation = Object.assign({}, publicStore.active_conversation, activeConversation);
				let scrollContainer = $(this.$el).find('.messages-container .scrollable-container');
				let currentScrollLevel = scrollContainer[0].scrollHeight;

				this.active_conversation = newActiveConversation;
				if (this.active_conversation.message && !this.active_conversation.readonly) this.setupNotifyTyping();

				if (!loadMore) {
					this.loadCustomerProfile();
					this.main_panel_body_loading = true;
					this.changeCustomerProfileMode('detail');
				}

				let params = {
					mode: 'getConversation',
					channel: this.active_social_page.channel,
					page_id: this.active_social_page.id,
					customer_social_id: this.active_conversation.customer_social_id,
					offset: loadMore ? this.active_conversation.next_offset : 0,
				};
				
				this.active_conversation.loading = true;

				Helper.ajax(params).then((err, res) => {
					if (!Helper.resolveError(err, res)) return;

					let newMessages = res.messages || [];

					this.main_panel_body_loading = false;
					this.active_conversation.loading = false;
					this.active_conversation.refresing = false;
					this.active_conversation.messages = newMessages.concat(loadMore ? this.active_conversation.messages : []);
					this.active_conversation.metadata = res.metadata;
					this.active_conversation.next_offset = res.next_offset;
					this.active_conversation.seed = this.getRandomSeed();

					setTimeout(() => {
						if (!loadMore) {
							scrollContainer.scrollTop(scrollContainer[0].scrollHeight);
						}
						else {
							scrollContainer.scrollTop(scrollContainer[0].scrollHeight - currentScrollLevel);
						}
					}, 0);

					// Mark that conversation as read
					let socialPage = this.findActiveSocialPage();
					let conversation = this.findActiveConverstation();

					if (conversation && conversation.is_read == 0) {
						conversation.is_read = 1;
						socialPage.unread_count = parseInt(socialPage.unread_count) > 0 ? parseInt(socialPage.unread_count) - 1 : 0;
					}

					setTimeout(() => this.updateTotalUnreadCounter(), 100);
				});
			},
			checkDuplicateProfile: function (fieldName, fieldValue) {
				if (!fieldValue) {
					this.duplicated_fields[fieldName] = false;
					return;
				}

				let params = {
					mode: 'checkDuplicate',
					field_name: fieldName,
					field_value: fieldValue,
				};

				Helper.ajax(params).then((err, res) => {
					if (!Helper.resolveError(err, res)) {
						this.active_customer_profile.success = false;
						return;
					}

					// Process in duplicated case
					this.duplicated_fields[fieldName] = res.duplicated;
				});
			},
			loadCustomerProfile: function () {
				let customerId = this.active_conversation.customer_id;
				let customerType = this.active_conversation.customer_type;
				let channel = this.active_social_page.channel;
				let customerSocialId = this.active_conversation.customer_social_id;
				let socialHolderId = this.active_social_page.id;

				let params = {
					mode: 'getCustomerProfile',
					customer_id: customerId,
					customer_type: customerType,
					channel: channel,
					social_holder_id: socialHolderId,
					customer_social_id: customerSocialId,
				}

				this.active_customer_profile.refresing = true;

				Helper.ajax(params).then((err, res) => {
					this.active_customer_profile.refresing = false;

					if (res && res.error_code == 'deleted') {
						let message = app.vtranslate('JS_SOCIAL_CHATBOX_CUSTOMER_PROFILE_DELETED_RELOAD_NOTIFICATION');

						app.helper.showAlertBox({ message }, () => {
							this.reloadConversationList();
						});

						return;
					}
					else if (res && (res.error_code == 'permission_denied' || res.can_access != '1')) {
						let message = app.vtranslate('JS_SOCIAL_CHATBOX_CUSTOMER_PROFILE_PERMISSION_DENIED_RELOAD_NOTIFICATION');

						app.helper.showAlertBox({ message }, () => {
							this.reloadConversationList();
						});

						return;
					}
					else if (res && res.error_code == 'mapping_updated') {
						let targetRecordId = res.target_id;
						let targetModule = res.target_module;
						let mappingField = res.mapping_field; 
						let mappingValue = res.mapping_value; 
						let message = app.vtranslate('JS_SOCIAL_CHATBOX_CUSTOMER_PROFILE_MAPPING_UPDATE_RELOAD_NOTIFICATION');

						app.helper.showAlertBox({ message }, () => {
							this.conversations[index].customer_id = targetRecordId;
							this.conversations[index].customer_type = targetModule;
							this.active_conversation.customer_id = targetRecordId;
							this.active_conversation.customer_type = targetModule;
							if (mappingField) this.conversations[index].mapping_field = mappingField;
							if (mappingValue) this.conversations[index].mapping_value = mappingValue;

							this.loadCustomerProfile();
						});
						
						return;
					}

					if (!Helper.resolveError(err, res)) {
						this.active_customer_profile.success = false;
						return;
					}

					let customerProfile = res;
					this.updateCustomerProfile(customerProfile);
				});
			},
			loadCustomerComments: function () {
				let params = {
					mode: 'getRelatedComments',
					customer_id: this.active_customer_profile.record_id,
				};

				Helper.ajax(params).then((err, res) => {
					this.active_customer_profile.refresing = false;

					if (!Helper.resolveError(err, res, 'silent')) {
						return;
					}

					let comments = res;
					this.active_customer_profile.comments = comments;
				});
			},
			refreshActiveConversation: function () {
				if (this.active_conversation.refresing == true) return;
				this.active_conversation.refresing = true;
				this.loadConversation(this.active_conversation);
			},
			newEvent: function (msg) {
				// Process user are typing
				if (msg.state == 'USER_TYPING') {
					this.markAsTyping(msg);
					return;
				}

				if (msg.state == 'DELETED') {
					this.processDeletedRecord(msg);
					return;
				}

				if (msg.state == 'CONVERTED') {
					this.processConvertedRecord(msg);
					return;
				}

				this.newMessage(msg);
			},
			getMergedMessages: function (message, messages) {
				messages = messages || [];

				let messageIndex = messages.findIndex(single => single.msg_id == message.msg_id);
				
				if (messageIndex > -1) {
					messages[messageIndex] = message;
				}
				else {
					messages = messages.concat([message]);
				}

				return messages;
			},
			newMessage: function (msg) {
				let messageInfo = msg['message_info'];
				let metadata = msg['metadata'];
				let conversation = null;
				let updateCounter = false;
				let lastUser = metadata.outbound_users_history.length > 0 ? metadata.outbound_users_history[-1] : {};

				// Logic for new message is in current conversation
				if (this.active_conversation.customer_social_id == messageInfo.customer_social_id) {
					// Mark active conversation as read 
					this.markActiveConversationAsRead();

					// Display notification at scroll to bottom button
					if (this.scroll_to_bottom_visibility) {
						this.having_new_message = true;
					}
					else {
						setTimeout(() => {
							let scrollContainer = $(this.$el).find('.messages-container .scrollable-container');
							scrollContainer.scrollTop(scrollContainer[0].scrollHeight);
						}, 0);
					}
				}

				// Handle logic for active conversation
				if (this.active_conversation.customer_social_id == messageInfo.customer_social_id) {
					let messages = this.getMergedMessages(messageInfo, this.active_conversation.messages);
					this.active_conversation.messages = messages;
					this.active_conversation.metadata = metadata;
				}
				
				// Handle logic for inactive conversation
				let conversationIndex = this.conversations.findIndex(single => single.customer_social_id == messageInfo.customer_social_id);

				// Bring selected conversation to the first place
				if (conversationIndex > -1) {
					if (conversationIndex != 0) {
						let temp = this.conversations.splice(conversationIndex, 1);
						this.conversations.unshift(temp[0]);
						conversationIndex = 0;
					}

					conversation = this.conversations[conversationIndex];

					// Update selected conversation status
					if (conversation.customer_social_id != this.active_conversation.customer_social_id) {
						if (conversation.is_read == 1) updateCounter = true;
						conversation.is_read = 0;
					}

					conversation.last_msg = messageInfo.msg_type == 'text' ? messageInfo.msg_text : '[' + messageInfo.msg_type.toUpperCase() + ']';
					conversation.last_msg_direction = messageInfo.msg_direction.toUpperCase();
					conversation.last_msg_time = messageInfo.msg_time;

					if (lastUser && lastUser.id) {
						conversation.last_user_id = lastUser.id;
						conversation.last_user_name = lastUser.full_name;
					}

					// Force update dom
					this.conversations = [].concat(this.conversations);
				}
				else {
					updateCounter = true;
					this.loadIncommingConversation(messageInfo.channel, messageInfo.page_id, messageInfo.customer_social_id);
				}

				// Update unread counter
				let socialPage = this.social_pages.find(single => single.id == messageInfo.page_id);

				if (socialPage && updateCounter) {
					socialPage.unread_count = parseInt(socialPage.unread_count || 0) + 1;
				}

				// Update new unread counter
				setTimeout(() => this.updateTotalUnreadCounter(), 100);
			},
			handleTyping: function (event) {
				if (event.target.value) {
					this.setupNotifyTyping();
				}
			},
			handleBlur: function (event) {
				if (event.target.name == 'email' || event.target.name == 'mobile') {
					if (this.active_customer_profile.record_module != 'CPTarget') return;

					let fieldName = event.target.name;
					if (fieldName == 'mobile') fieldName = 'phone';

					this.checkDuplicateProfile(fieldName, event.target.value);
				}
			},
			sendMessage: function (event) {
				if (event.shiftKey) return;

				if (event.ctrlKey || event.altKey) {
					event.target.value = event.target.value += "\n";
					event.target.scrollTop = event.target.scrollHeight
					return;
				}

				event.preventDefault();

				if (!event.target.value || event.target.value.trim().length == 0) return;

				let params = {
					mode: 'sendMessage',
					channel: this.active_social_page.channel,
					page_id: this.active_social_page.id,
					customer_social_id: this.active_conversation.customer_social_id,
					message: event.target.value,
					last_inbound_msg_id: $('.message-container[data-direction="INBOUND"]:last').data('msgId'),	// Added by Hieu Nguyen on 2022-12-08 to support reply to message from Followed/Unfollowed customer
					customer_type: this.active_customer_profile.record_module,
					customer_id: this.active_customer_profile.record_id
				}

				this.active_conversation.message = '';
				event.target.value = '';
				event.target.style.height = '34px'; // Added by Vu Mai on 2022-10-21 to reset height after send message

				Helper.ajax(params).then((err, res) => {
					if (!Helper.resolveError(err, res)) return;

					// Customer unfollowed oa
					// if (!res.success && res.code == 'unfollowed_oa') {
					// 	this.active_customer_profile.zalo_id_synced = '0';
					// 	Error.notify(app.vtranslate('JS_SOCIAL_CHATBOX_CUSTOMER_SEND_MESSAGE_UNFOLLOW_ERROR_NOTIFICATION'));
					// 	return;
					// }

					// Rerender message
					let newMessage = {
						channel: this.active_social_page.channel,
						page_id: this.active_social_page.id,
						customer_social_id: this.active_conversation.customer_social_id,
						msg_id: res.id,
						msg_type: 'text',
						msg_direction: 'outbound',
						msg_text: params.message,
						msg_description: null,
						msg_attachments: [],
						msg_time: moment().format('YYYY-MM-DD HH:mm:ss'),
						sender_id: _CURRENT_USER_META.id,
						sender_name: _CURRENT_USER_META.name.trim(),
					};
					
					let messages = this.getMergedMessages(newMessage, this.active_conversation.messages);
					this.active_conversation.messages = messages;
					this.scrollMessagesToBottom();
				});
			},
			sendImage: function (event) {
				if (event.target.files.length == 0) return;

				this._sendFile({ type: 'image' }, event.target.files);

				event.target.value = '';
			},
			sendFile: function (event) {
				if (event.target.files.length == 0) return;

				this._sendFile({ type: 'file' }, event.target.files);

				event.target.value = '';
			},
			handleConversationScroll: function (event) {
				if (event.target.scrollTop == 0 && event.target.scrollHeight > event.target.parentElement.scrollHeight) {
					this.loadConversation(this.active_conversation, true);
				}

				// Handle scroll to bottom button visibility
				if (event.target.scrollHeight - event.target.scrollTop - event.target.clientHeight > 100) {
					this.scroll_to_bottom_visibility = true;
				}
				else {
					if (this.scroll_to_bottom_visibility) this.scroll_to_bottom_visibility = false;
					if (this.having_new_message) this.having_new_message = false;
				}
			},
			handleConversationsScroll: function (event) {
				if (event.target.scrollTop + event.target.parentElement.scrollHeight > event.target.scrollHeight) {
					this.loadConversationsBySocialPage(this.active_social_page, true);
				}
			},
			close: function () {
				this.size = 'NONE';
				this.active_conversation = Object.assign({}, publicStore.active_conversation); // Reset active conversation
			},
			open: function (channel = null, customerId = null) {
				if (this.size == 'NORMALIZED') return;

				if (
					this.size == 'MINIMIZED'
					&& !this.loaded_from_cache
					&& !customerId
					&& this.active_conversation.customer_id
				) {
					return this.normalize();
				}

				this.loaded_from_cache = false;

				if (!customerId && this.active_conversation.customer_id) return this.normalize();
				
				this.normalize(channel, customerId);

				this.loadSocialPages((socialPages) => {
					if (!customerId) {
						let activePage = socialPages[0];
						this.loadConversationsBySocialPage(activePage);
					}
					else {
						this.loadConversationsByCustomer(channel, customerId);
					}
				});
			},
			minimize: function () {
				this.size = 'MINIMIZED';
			},
			normalize: function (channel = null, customerId = null) {
				this.size = 'NORMALIZED';

				if (this.loaded_from_cache) {
					this.loaded_from_cache = false;
					
					let localData = LocalStorage.get('data');

					this.loadSocialPages((socialPages) => {
						if (!customerId) {
							this.loadConversationsByCustomer(localData.active_channel, localData.active_customer_id, localData.active_customer_social_id);
						}
						else {
							this.loadConversationsByCustomer(channel, customerId);
						}
					});
				}
			},
			openFromMinimize: function (event) {
				let target = $(event.target);

				if (target.is('button') || target.closest('button').length > 0) return;

				if (this.size == 'MINIMIZED') this.normalize();
			},
			_sendFile: function (params, fileList) {
				// TODO validate file type and size

				let defaultParams = {
					module: this.module,
					action: this.action,
					mode: 'sendFile',
					channel: this.active_social_page.channel,
					page_id: this.active_social_page.id,
					customer_social_id: this.active_conversation.customer_social_id,
					last_inbound_msg_id: $('.message-container[data-direction="INBOUND"]:last').data('msgId'),	// Added by Hieu Nguyen on 2022-12-08 to support reply to message from Followed/Unfollowed customer
				}

				params = Object.assign(defaultParams, params);

				formData = new FormData();

				// Process form data information
				for (prop in params) {
					formData.append(prop, params[prop]);
				}

				// Process form data files
				for (let i = 0; i < fileList.length; i++) {
					formData.append('files[]', fileList[i]);
				}

				$.ajax('index.php', {
					cache: false,
					contentType: false,
					processData: false,
					method: 'POST',
					type: 'POST', // For jQuery < 1.9
					data: formData,
				}).done((res) => {
					res = res.result;
					
					if (res.success == true) {
						Success.notify(app.vtranslate('JS_CHAT_BOX_SEND_SEND_FILE_SUCCESS'));
					}
					else {
						Error.notify(res.message);
					}

				}).fail((jqXHR) => {
					Error.notify(jqXHR.message);
				});
			},
			filterConversations: function (useTimeout = false) {
				if (this.intervals.keyword) clearTimeout(this.intervals.keyword);

				this.intervals.keyword = setTimeout(() => {
					this.loadConversationsBySocialPage(this.active_social_page);
				}, useTimeout ? this.searching_timeout : 50);
			},
			markAsTyping: function (params) {
				let activeSocialPage = this.active_social_page;
				let activeConversation = this.active_conversation;
	
				// Validate to reduce processes
				if (params.user_id == _CURRENT_USER_META.id) return;
				if (activeSocialPage.channel != params.channel) return;
				if (activeSocialPage.id != params.social_page_id) return;
				
				// First thing we have to do is assign typing status to the right conversation
				let conversation = this.conversations.find(single => single.customer_social_id == params.customer_social_id);
				this._setUpTypingStatus(conversation, params);

				// And then we setup typing status for the active conversation too
				if (activeConversation.customer_social_id == params.customer_social_id) {
					this._setUpTypingStatus(activeConversation, params);
				}
			},
			processDeletedRecord: function (params) {
				if (!this.conversations) return;

				let deletedRecord = params.customer_id;

				this.conversations.forEach((conversation, index) => {
					if (conversation.customer_id == deletedRecord) {
						if (
							conversation.customer_social_id == this.active_conversation.customer_social_id
							&& this.size == 'NORMALIZED'
						) {
							let message = app.vtranslate('JS_SOCIAL_CHATBOX_CUSTOMER_PROFILE_DELETED_RELOAD_NOTIFICATION');
	
							app.helper.showAlertBox({ message }, () => {
								this.reloadConversationList();
							});
						}
						else {
							this.conversations.splice(index, 1);
						}
					}
				});

				setTimeout(() => this.updateTotalUnreadCounter(), 100);
			},
			processConvertedRecord: function (params) {
				if (!this.conversations) return;

				let sourceRecordId = params.source_id;
				let targetRecordId = params.target_id;
				let targetModule = params.target_module;
				let mappingField = params.mapping_field; 
				let mappingValue = params.mapping_value; 

				this.conversations.forEach((conversation, index) => {
					if (conversation.customer_id == sourceRecordId) {
						if (conversation.customer_social_id == this.active_conversation.customer_social_id) {
							let message = app.vtranslate('JS_SOCIAL_CHATBOX_CUSTOMER_PROFILE_MAPPING_UPDATE_RELOAD_NOTIFICATION');
	
							app.helper.showAlertBox({ message }, () => {
								this.conversations[index].customer_id = targetRecordId;
								this.conversations[index].customer_type = targetModule;
								this.active_conversation.customer_id = targetRecordId;
								this.active_conversation.customer_type = targetModule;
								if (mappingField) this.conversations[index].mapping_field = mappingField;
								if (mappingValue) this.conversations[index].mapping_value = mappingValue;

								this.loadCustomerProfile();
							});
						}
						else {
							this.conversations[index].customer_id = targetRecordId;
							this.conversations[index].customer_type = targetModule;
							if (mappingField) this.conversations[index].mapping_field = mappingField;
							if (mappingValue) this.conversations[index].mapping_value = mappingValue;
						}
					}
				});

				setTimeout(() => this.updateTotalUnreadCounter(), 100);
			},
			_setUpTypingStatus: function (conversation, params) {
				if (conversation.intervals?.typing) {
					clearTimeout(conversation.intervals?.typing);
				}
				conversation.typing_status = {
					typing: true,
					user_id: params.user_id,
					user_full_name: params.user_info.name,
				};

				conversation.active_typing_msg = this.getTypingMsg(conversation, true);
				
				// Hack to force reload component
				this.conversations = [...this.conversations];

				if (!conversation.intervals) conversation.intervals = {};

				conversation.intervals.typing = setTimeout(() => {
					conversation.typing_status.typing = false;
					conversation.active_typing_msg = '';
					
					// Hack to force reload component
					this.conversations = [...this.conversations];
				}, 5000);
			},
			setupNotifyTyping: function () {
				this._notifyTyping();

				if (this.intervals.typing) clearInterval(this.intervals.typing);

				this.intervals.typing = setInterval(() => {
					if (this.active_conversation.message && !this.active_conversation.readonly) {
						this._notifyTyping();
					}
					else {
						clearInterval(this.intervals.typing);
					}
				}, 5000);
			},
			_notifyTyping: function () {
				let params = [
					this.active_social_page.channel,
					this.active_social_page.id,
					this.active_conversation.customer_social_id,
					_CURRENT_USER_META.id,
				];

				SocialChatClient.notifyUserTyping(...params);
			},
			updateTotalUnreadCounter: function () {
				let params = {
					mode: 'getUnreadCount',
				};

				Helper.ajax(params).then((err, res) => {
					if (!Helper.resolveError(err, res, 'silent')) return;

					let totalUnread = res || 0;

					$('#social-chat-counter').html(totalUnread);
					this.new_unread = totalUnread;
					this.toggleTopBarIconCounter();
				});
			},
			markActiveConversationAsRead: function () {
				let params = {
					mode: 'markConversationAsRead',
					channel: this.active_social_page.channel,
					page_id: this.active_social_page.id,
					customer_social_id: this.active_conversation.customer_social_id,
				};

				Helper.ajax(params).then((err, res) => {
					if (!Helper.resolveError(err, res, 'silent')) return;

					this.active_conversation.is_read = 1;

					$('#social-chat-counter').html(res.total_unread);
					this.new_unread = res.total_unread;
					this.toggleTopBarIconCounter();
				});
			},
			toggleTopBarIconCounter: function () {
				if (parseInt($('#social-chat-counter').text()) > 0) {
					$('#social-chat-counter').show();
					$('#social-chat-counter').removeClass('hide');
				}
				else {
					$('#social-chat-counter').hide();
					$('#social-chat-counter').addClass('hide');
				}
			},
			makeCall: function (element, phoneNumber, recordId) {
				Vtiger_PBXManager_Js.registerPBXOutboundCall(element, phoneNumber, recordId);
			},
			openEmailComposer: function (customerId, customerType) {
				Vtiger_Helper_Js.getInternalMailer(customerId, 'email', customerType);
			},
			showMap: function () {
				let module = this.active_customer_profile.record_module;
				let record = this.active_customer_profile.record_id;
				let dom = $(`<a data-module="${module}" data-record="${record}"></a>`);
				Vtiger_Index_Js.showMap(dom[0]);
			},
			socialPagesNext: function () {
				this.socialPagesGoToPage(this.social_pages_paging.current_page + 1);
			},
			socialPagesPrev: function () {
				this.socialPagesGoToPage(this.social_pages_paging.current_page - 1);
			},
			socialPagesGoToPage: function (page) {
				if (page == undefined) return;

				page = parseInt(page);

				if (page < 1) return;
				if (page > this.social_pages_paging.max_page) return;
				
				let borderLeft = parseInt($(this.$el).find('.social-page-container').css('border-left-width').replace('px', ''));
				let borderRight = parseInt($(this.$el).find('.social-page-container').css('border-right-width').replace('px', ''));
				let widthPerPage = parseInt($(this.$el).find('.social-page-container').width()) + borderLeft + borderRight;
				let firstPageinPage = (page * 3) - 2;
				let widthPadLeft = (firstPageinPage - 1) * widthPerPage;

				$(this.$el).find('.social-pages-content').animate({left: -widthPadLeft});
				this.social_pages_paging.current_page = page;

				if (page != this.social_pages_paging.fall_back_page) {
					if (this.intervals.social_pages_paging) clearTimeout(this.intervals.social_pages_paging);

					this.intervals.social_pages_paging = setTimeout(() => {
						if (this.social_pages_paging.current_page != this.social_pages_paging.fall_back_page) {
							this.socialPagesGoToPage(this.social_pages_paging.fall_back_page);
						}
					}, 3000);
				}
			},
			willShownNotification: function (data) {
				if (this.size != 'NORMALIZED') return true;
				if (this.active_conversation.customer_social_id != data.customer_social_id) return true;
				return false;
			},
			preserveMessageContent: function () {
				let conversation = this.findActiveConverstation();
				if (conversation) {
					conversation.message = this.active_conversation.message;
				}
			},
			changeCustomerProfileMode: function (mode, convertToModule = '') {
				if (mode == 'edit') {
					let customerProfileForm = Object.assign({}, this.active_customer_profile);

					if (convertToModule) {
						customerProfileForm.record_module = convertToModule;
						this.save_mode = 'convert';
					}

					if (customerProfileForm.account_id == 0 || customerProfileForm.account_id == '0') customerProfileForm.account_id = '';

					customerProfileForm.avatar = this.getAvatarUrl(this.active_customer_profile.record_id, this.active_customer_profile.record_module);
					this.customer_profile_form = customerProfileForm;

					// Check duplicate field
					this.checkDuplicateProfile('email', this.customer_profile_form.email);
					this.checkDuplicateProfile('phone', this.customer_profile_form.mobile);
				}
				
				if (mode == 'detail') {
					$(this.$el).find('.changeAvatarInput')[0].value = '';
					this.customer_profile_form = Object.assign({}, publicStore.customer_profile_form);
					this.save_mode = null;
					this.customer_profile_form = {};
				}

				this.active_customer_profile_mode = mode;
			},
			saveCustomerProfile: function (callback = null) {
				// Process in duplicated case
				if (
					this.active_customer_profile.record_module == 'CPTarget'
					&& (this.duplicated_fields.email || this.duplicated_fields.phone)
				) {
					this.openProcessDuplicatePopup();
					return;
				}

				// Form validation
				const customerProfileForm = $(this.$el).find('.customer-profile-container.edit-form');

				customerProfileForm.vtValidate();

				if (!customerProfileForm.find(':input').valid()) return;

				let params = Object.assign(
					{},
					this.customer_profile_form,
					{
						mode: 'saveCustomerProfile',
						module: this.module,
						action: this.action,
						save_mode: this.save_mode,
					},
				);

				delete params.avatar;

				let formData = new FormData();

				for (prop in params) {
					formData.append(prop, params[prop]);
				}

				if (this.active_customer_profile.record_module == 'Contacts' && $(this.$el).find('.changeAvatarInput')[0].files) {
					formData.append('imagename[]', $(this.$el).find('.changeAvatarInput')[0].files[0]);
				}

				app.helper.showProgress();

				Helper.ajax(formData).then((err, res) => {
					app.helper.hideProgress();

					if (res && res.error_code == 'deleted') {
						let message = app.vtranslate('JS_SOCIAL_CHATBOX_CUSTOMER_PROFILE_DELETED_RELOAD_NOTIFICATION');

						app.helper.showAlertBox({ message }, () => {
							this.reloadConversationList();
						});

						return;
					}
					else if (res && (res.error_code == 'permission_denied' || res.can_access != '1')) {
						let message = app.vtranslate('JS_SOCIAL_CHATBOX_CUSTOMER_PROFILE_PERMISSION_DENIED_RELOAD_NOTIFICATION');

						app.helper.showAlertBox({ message }, () => {
							this.reloadConversationList();
						});

						return;
					}

					// Process in duplicated case
					if (!err && res.duplicated == true) {
						this.openProcessDuplicatePopup();
						return;
					}

					if (!err && res.converted == true) {
						setTimeout(() => {
							let replaceParams = { customer_type: res.target_module_display };
							let message = app.vtranslate('JS_SOCIAL_CHATBOX_CUSTOMER_CONVERTED_RELOAD_NOTIFICATION', replaceParams);
							
							app.helper.showAlertBox({ message });
							this.loadConversation(this.active_conversation);
						});
						return;
					}

					if (!Helper.resolveError(err, res)) return;

					let customerProfile = Object.assign(res);
					this.updateCustomerProfile(customerProfile);

					setTimeout(() => this.changeCustomerProfileMode('detail'), 0);
					
					if (callback && typeof callback == 'function') callback();
				});
			},
			openProcessDuplicatePopup: function () {
				let self = this;
				let idString = this.active_conversation.customer_social_id;

				// Load modal template
				const modal = $('#duplicateProcessModal').clone().attr('id', `duplicate-process-modal-${idString}`);
				
				// Init DOM Elements
				modal.find('.select2-container').remove();
				
				// Init Dom Controller
				Vtiger_Edit_Js.getInstanceByModuleName('Vtiger').registerBasicEvents(modal.find('form'));

				let params = {
					mode: 'getDuplicatedCustomers',
					customer_email: this.customer_profile_form.email,
					customer_phone: this.customer_profile_form.mobile,
				};

				Helper.ajax(params).then((err, res) => {
					if (!Helper.resolveError(err, res)) return;

					let datatable = modal.find('.duplicate-process-table').DataTable({
						ordering: false,
						searching: false,
						language: DataTableUtils.languages,
						data: res,
						columns: [
							{
								data: 'select',
								name: 'select',
								width: '10%',
								render: (data, type, row) => {
									return `<div class="text-center"><input type="radio" name="select" value="${row.id}" /></div>`;
								}
							},
							{
								data: 'full_name',
								name: 'full_name',
								width: '15%',
								render: (data, type, row) => {
									return `<div class="data-row-content">${data}</div>`;
								}
							},
							{
								data: 'email',
								name: 'email',
								width: '15%',
								render: (data, type, row) => {
									return `<div class="data-row-content nowrap">${data}</div>`;
								}
							},
							{
								data: 'mobile',
								name: 'mobile',
								width: '15%',
								render: (data, type, row) => {
									return `<div class="data-row-content nowrap">${data}</div>`;
								}
							},
							{
								data: 'module',
								name: 'module',
								width: '15%',
								render: (data, type, row) => {
									return `<div class="data-row-content nowrap">${data}</div>`;
								}
							},
							{
								data: 'main_owner_id',
								name: 'main_owner_id',
								width: '15%',
							},
							{
								data: 'field_labels',
								name: 'field_labels',
								width: '15%',
								render: (data, type, row) => {
									let content = '';
									
									data.forEach(single => {
										content += `<li>${single}</li>`;
									});

									content = `<div class="duplicate-information"><ul>${content}</ul></div>`;

									return content;
								}
							}
						],
					});                
	
					modal.find('.linkButton').on('click', () => {
						let formData = modal.find('form').serializeFormData();
					  
						if (!formData.select) {
							app.helper.showErrorNotification({ message: app.vtranslate('JS_SOCIAL_CHATBOX_SELECT_LINK_ERROR_MSG')});
							return;
						}

						let confirmMessage = app.vtranslate('JS_SOCIAL_CHATBOX_DUPLICATE_LINK_CONFIRMATION_MSG');

						app.helper.showConfirmationBox({ message: confirmMessage }).then(() => {
							self.linkCustomer(self.active_customer_profile.record_id, formData.select);
						});
					});
	
					modal.find('.mergeButton').on('click', () => {
						let formData = modal.find('form').serializeFormData();
					  
						if (!formData.select) {
							app.helper.showErrorNotification({ message: app.vtranslate('JS_SOCIAL_CHATBOX_SELECT_MERGE_ERROR_MSG')});
							return;
						}

						let confirmMessage = app.vtranslate('JS_SOCIAL_CHATBOX_DUPLICATE_MERGE_CONFIRMATION_MSG');

						app.helper.showConfirmationBox({ message: confirmMessage }).then(() => {
							self.mergeCustomer(self.active_customer_profile.record_id, formData.select);
						});
					});
					
					app.helper.showProgress();
				
					// Show modal
					app.helper.showModal(modal, {
						cb: () => {
							app.helper.hideProgress();
						}
					});
				});
			},
			linkCustomer: function (sourceId, targetId) {
				let params = {
					mode: 'linkCustomerProfile',
					source_id: sourceId,
					target_id: targetId
				}

				Helper.ajax(params).then((err, res) => {
					if (!Helper.resolveError(err, res)) return;

					Success.notify(app.vtranslate('JS_SOCIAL_CHATBOX_LINK_CUSTOMER_SUCCESS_MSG'));

					this.updateCustomerProfile(res);
					this.changeCustomerProfileMode('detail');
					app.helper.hideModal();
				});
			},
			mergeCustomer: function (sourceId, targetId) {
				let params = Object.assign(
					{},
					this.customer_profile_form,
					{
						mode: 'mergeCustomerProfile',
						source_id: sourceId,
						target_id: targetId
					},
				);

				Helper.ajax(params).then((err, res) => {
					if (!Helper.resolveError(err, res)) return;

					Success.notify(app.vtranslate('JS_SOCIAL_CHATBOX_LINK_CUSTOMER_SUCCESS_MSG'));

					this.updateCustomerProfile(res);
					this.changeCustomerProfileMode('detail');
					app.helper.hideModal();
				});
			},
			handleSearchCompany: function (event) {
				if (!event.target.value) {
					this.hideSearchCompany();
					return;
				}

				let appInstance = Vtiger_Index_Js.getInstance();

				let params = {
					module: this.active_customer_profile.record_module,
					search_module: 'Accounts',
					search_value: event.target.value,
				}
				
				appInstance.searchModuleNames(params).then((res) => {
					if (!res) {
						res = [
							{
								'label': 'No Results Found',
								'value': 'No Results Found',
								'type': 'no results',
							}
						];
					}

					this.acount_search_results = res;
				});
			},
			hideSearchCompany: function (event) {
				this.acount_search_results = [];
			},
			selectCompanyName: function (value, type = '', id = '') {
				if (type == 'no results') return;
				this.customer_profile_form.company = value;
				if (id) this.customer_profile_form.account_id = id;
				this.hideSearchCompany();
			},
			handleContainerClicked: function (event) {
				if (!$(event.target).is('.account-options') && $(event.target).closest('.account-options')[0] == null) {
					this.hideSearchCompany();
				}

				if (!$(event.target).is('.social-pages-container') && $(event.target).closest('.social-pages-container')[0] == null) {
					if (this.social_pages_paging.current_page != this.social_pages_paging.fall_back_page) {
						this.socialPagesGoToPage(this.social_pages_paging.fall_back_page);
					}
				}
			},
			clearAccountInfo: function () {
				this.customer_profile_form.account_id = '';
				this.customer_profile_form.company = '';
			},
			openQuickCreateAccount: function (event) {
				let params = {
					postSaveCb: data => {
						if (data._recordId) {
							this.customer_profile_form.account_id = data._recordId;
							this.customer_profile_form.company = data._recordLabel;
						}
					},
				};

				vtUtils.openQuickCreateModal('Accounts', params);
			},
			reloadCustomerAvatar: function () {
				let url = this.getAvatarUrl(this.active_customer_profile.record_id, this.active_customer_profile.record_module) + '?time=' + moment().format();
				this.active_customer_profile.avatar = url;
			},
			reloadConversationList: function () {
				this.active_conversation = Object.assign({}, publicStore.active_conversation); // Reset active conversation
				this.loadConversationsBySocialPage(this.active_social_page);
			},
			openQuickCreatePopup: function (moduleName, activityType = '') {
				const params = {
					parentModule: this.active_customer_profile.record_module,
					parentId: this.active_customer_profile.record_id,
					data: {},
					preShowCb: popup => {
						popup.find('#goToFullForm').remove();
					},
					postShowCb: popup => {
						const relateTo = popup.find('[name="related_to_display"]');
	
						relateTo.closest('.fieldValue').find('.clearReferenceSelection').remove();
						relateTo.closest('.fieldValue').find('.relatedPopup').remove();
					}
				};

				if (moduleName === 'Events') {
					params.data.activitytype = activityType;
					params.data.visibility = 'Public';
				}

				if (moduleName === 'Potentials' && this.active_customer_profile.record_module) {
					if (this.active_customer_profile.account_id && this.active_customer_profile.account_id > 0) {
						params.data.related_to = this.active_customer_profile.account_id;
					}

					params.data.contact_id = this.active_customer_profile.record_id;
				}

				if (moduleName === 'HelpDesk') params.data.ticketstatus = 'Open';

				if (moduleName === 'HelpDesk' && this.active_customer_profile.record_module == 'Contacts') {
					params.data.contact_id = this.active_customer_profile.record_id;
				}

				if (moduleName === 'HelpDesk' && this.active_customer_profile.record_module == 'Leads') {
					params.data.related_lead = this.active_customer_profile.record_id;
				}

				if (moduleName === 'SalesOrder') {
					let params = {
						module: 'SalesOrder',
						view: 'Edit',
						app: 'CUSTOMERS',
						contact_id: this.active_customer_profile.record_id,
					};

					if (this.active_customer_profile.account_id && this.active_customer_profile.account_id > 0) {
						params.account_id = this.active_customer_profile.account_id;
					}

					let url = 'index.php?' + $.param(params);
					window.open(url, '_blank').focus();
					return;
				}
	
				app.helper.showProgress();
	
				vtUtils.openQuickCreateModal(moduleName, params);
			},
			openQuickCreatePotentialPopup: function () {
				if (this.active_customer_profile.record_module == 'Contacts') {
					return this.openQuickCreatePopup('Potentials');
				}
				
				if (this.active_customer_profile.record_module == 'Leads') {
					if (_CUSTOMER_TYPE_CONFIG.customer_type == 'personal') {
						this.autoConvertLead(() => {
							this.openQuickCreatePopup('Potentials');
						});
					}
					else if (_CUSTOMER_TYPE_CONFIG.customer_type == 'company') {
						this.openConvertLeadPopup(() => {
							this.openQuickCreatePopup('Potentials');
						});
					}
					else if (this.active_customer_profile.leads_business_type == 'Personal Customer') {
						this.autoConvertLead(() => {
							this.openQuickCreatePopup('Potentials');
						});
					}
					else {
						this.openConvertLeadPopup(() => {
							this.openQuickCreatePopup('Potentials');
						});
					}
				}
				
				if (this.active_customer_profile.record_module == 'CPTarget') {
					if (_CUSTOMER_TYPE_CONFIG.customer_type == 'personal') {
						this.autoConvertTarget(() => {
							this.openQuickCreatePopup('Potentials');
						});
					}
					else if (_CUSTOMER_TYPE_CONFIG.customer_type == 'company') {
						this.convertTarget(() => {
							this.openQuickCreatePopup('Potentials');
						});
					}
					else if (this.active_customer_profile.leads_business_type == 'Personal Customer') {
						this.autoConvertTarget(() => {
							this.openQuickCreatePopup('Potentials');
						});
					}
					else {
						this.convertTarget(() => {
							this.openQuickCreatePopup('Potentials');
						});
					}
				}
			},
			openQuickCreateSalesOrderPopup: function () {
				if (this.active_customer_profile.record_module == 'Contacts') {
					return this.openQuickCreatePopup('SalesOrder');
				}
				
				if (this.active_customer_profile.record_module == 'Leads') {
					if (_CUSTOMER_TYPE_CONFIG.customer_type == 'personal') {
						this.autoConvertLead(() => {
							this.openQuickCreatePopup('SalesOrder');
						});
					}
					else if (_CUSTOMER_TYPE_CONFIG.customer_type == 'company') {
						this.openConvertLeadPopup(() => {
							this.openQuickCreatePopup('SalesOrder');
						});
					}
					else if (this.active_customer_profile.leads_business_type == 'Personal Customer') {
						this.autoConvertLead(() => {
							this.openQuickCreatePopup('SalesOrder');
						});
					}
					else {
						this.openConvertLeadPopup(() => {
							this.openQuickCreatePopup('SalesOrder');
						});
					}
				}
				
				if (this.active_customer_profile.record_module == 'CPTarget') {
					if (_CUSTOMER_TYPE_CONFIG.customer_type == 'personal') {
						this.autoConvertTarget(() => {
							this.openQuickCreatePopup('SalesOrder');
						});
					}
					else if (_CUSTOMER_TYPE_CONFIG.customer_type == 'company') {
						this.convertTarget(() => {
							this.openQuickCreatePopup('SalesOrder');
						});
					}
					else if (this.active_customer_profile.leads_business_type == 'Personal Customer') {
						this.autoConvertTarget(() => {
							this.openQuickCreatePopup('SalesOrder');
						});
					}
					else {
						this.convertTarget(() => {
							this.openQuickCreatePopup('SalesOrder');
						});
					}
				}
			},
			toggleStarred: function (status) {
				let params = {
					module: this.active_customer_profile.record_module,
					action: 'SaveStar',
					record: this.active_customer_profile.record_id,
					value: status ? 1 : 0,
				}

				app.helper.showProgress();

				Helper.ajax(params).then((err, res) => {
					app.helper.hideProgress();

					if (!Helper.resolveError(err, res)) return;

					this.active_customer_profile.starred = status;
				});
			},
			openConvertLeadPopup: function (postSave = null) {
				let self = this;
				let recordId = this.active_customer_profile.record_id;

				let callback = modal => {
					modal.find(':input[name="module"]').val(this.module);
					modal.find(':input[name="view"]').attr('name', 'action').val(this.action);
					modal.find('form#convertLeadForm').append('<input type="hidden" name="mode" value="convertLead" />');
					 
					modal.find('form#convertLeadForm').vtValidate({                        
						submitHandler: () => {
							let form = modal.find('form#convertLeadForm'); 
							let convertLeadModuleElements = form.find('.convertLeadModuleSelection');
							let moduleArray = [];

							jQuery.each(convertLeadModuleElements, function (index, element) {
								if (jQuery(element).is(':checked')) {
									moduleArray.push(jQuery(element).val());
								}
							});

							form.find('input[name="modules"]').val(JSON.stringify(moduleArray));
							
							let formData = new FormData(form[0]);

							app.helper.showProgress();

							Helper.ajax(formData).then((err, res) => {
								app.helper.hideProgress();

								if (!Helper.resolveError(err, res)) return;
								
								self.updateCustomerProfile(res);
								Success.notify(app.vtranslate('JS_SOCIAL_CHATBOX_CONVERT_LEAD_SUCCESSFUL_MSG'));

								if (typeof postSave == 'function') postSave(res);

								modal.find('.close').trigger('click');
							});

							return false;
						}
					});
				};

				Leads_Detail_Js.cache = {};
				Leads_Detail_Js.convertLead(`index.php?module=Leads&view=ConvertLead&record=${recordId}`, callback);
			},
			autoConvertLead: function (postSave = null) {
				let replaceParams = {
					'customer_name': this.active_customer_profile.full_name,
				};

				app.helper.showConfirmationBox({
					message: app.vtranslate('JS_SOCIAL_CHATBOX_AUTO_CONVERT_LEAD_CONFIRMATION', replaceParams),
				}).then(() => {
				
					let params = {
						mode: 'autoConvertLead',
						customer_id: this.active_customer_profile.record_id,
						customer_type: this.active_customer_profile.record_module,
					};
	
					app.helper.showProgress();
	
					Helper.ajax(params).then((err, res) => {
						app.helper.hideProgress();
	
						if (!Helper.resolveError(err, res)) return;
	
						this.updateCustomerProfile(res);
	
						if (typeof postSave == 'function') postSave(res);
	
						app.vtranslate('JS_SOCIAL_CHATBOX_CONVERT_LEAD_SUCCESSFUL_MSG');
					});
				});
			},
			convertTarget: function (postSave = null) {
				let email = this.active_customer_profile.email;
				let mobile = this.active_customer_profile.mobile;

				if (!email || !mobile) {
					Error.notify(app.vtranslate('JS_SOCIAL_CHATBOX_CONVERT_TARGET_MISSING_FIELD_MSG'));
					return;
				}

				let replaceParams = {
					'customer_name': this.active_customer_profile.full_name,
				};

				app.helper.showConfirmationBox({
					message: app.vtranslate('JS_SOCIAL_CHATBOX_AUTO_CONVERT_TARGET_CONFIRMATION', replaceParams),
				}).then(() => {
					let params = { 
						mode: 'convertTarget',
						customer_id: this.active_customer_profile.record_id,
						customer_type: this.active_customer_profile.record_module,
					}

					app.helper.showProgress();

					Helper.ajax(params).then((err, res) => {
						app.helper.hideProgress();

						if (!Helper.resolveError(err, res)) return;

						this.updateCustomerProfile(res);

						if (typeof postSave == 'function') postSave(res);

						app.vtranslate('CPTarget.JS_CONVERT_TARGET_SUCCESS_MSG', replaceParams);
					});
				});
			},
			autoConvertTarget: function (postSave = null) {
				let replaceParams = {
					'customer_name': this.active_customer_profile.full_name,
				};

				app.helper.showConfirmationBox({
					message: app.vtranslate('JS_SOCIAL_CHATBOX_AUTO_CONVERT_TARGET_CONFIRMATION', replaceParams),
				}).then(() => {
					let params = { 
						mode: 'convertTarget',
						customer_id: this.active_customer_profile.record_id,
						customer_type: this.active_customer_profile.record_module,
					}
	
					app.helper.showProgress();
	
					Helper.ajax(params).then((err, res) => {
						app.helper.hideProgress();
	
						if (!Helper.resolveError(err, res)) return;
	
						this.updateCustomerProfile(res);
	
						if (typeof postSave == 'function') postSave(res);
	
						app.vtranslate('JS_SOCIAL_CHATBOX_CONVERT_TARGET_SUCCESSFUL_MSG');
					});
				});
			},
			resetCommentForm: function () {
				$(this.$el).find('#addCommentTextArea').html('');
				this.active_customer_comment_form = Object.assign({}, publicStore.active_customer_comment_form);
			},
			findActiveSocialPage: function () {
				let socialPage = this.social_pages.find(single => single.id == this.active_social_page.id);
				return socialPage;
			},
			findActiveConverstation: function () {
				if (!this.conversations || this.conversations.length == 0) return;
				return this.conversations.find(single => single.customer_social_id == this.active_conversation.customer_social_id);
			},
			updateCustomerProfile: function (customerProfile) {
				let seed = this.getRandomSeed();

				customerProfile.avatar = this.getAvatarUrl(customerProfile.record_id, customerProfile.record_module);
				customerProfile.success = true;
				customerProfile.refresing = false;
				customerProfile.seed = seed;

				// Assign new values
				this.active_customer_profile = customerProfile;

				// Process side logic
				this.active_conversation.customer_name = this.active_customer_profile.full_name;
				this.active_conversation.customer_id = this.active_customer_profile.record_id;
				this.active_conversation.customer_type = this.active_customer_profile.record_module;
				this.active_conversation.seed = seed;
				this.active_customer_profile.leads_business_type = customerProfile.leads_business_type || 'Personal Customer';

				let conversation = this.findActiveConverstation();
				
				if (conversation) {
					conversation.customer_name = this.active_customer_profile.full_name;
					conversation.customer_id = this.active_customer_profile.record_id;
					conversation.customer_type = this.active_customer_profile.record_module;
					conversation.seed = seed;
					conversation.readonly = this.active_customer_profile.readonly;
					conversation.can_access = this.active_customer_profile.can_access;
					
					if (this.active_customer_profile.mapping_value) conversation.mapping_value = this.active_customer_profile.mapping_value;
					
					if (conversation.tags_list) this.customers_tags_list[conversation.record_id] = conversation.tags_list;
				}

				// Reset duplicate information
				this.duplicated_fields = Object.assign({}, publicStore.duplicated_fields);

				this.resetCommentForm();

				// Perform reload on unfollow
				// if (this.active_customer_profile.zalo_id_synced != '1') {
				//     let message = app.vtranslate('JS_SOCIAL_CHATBOX_CUSTOMER_PROFILE_UNFOLLOW_RELOAD_NOTIFICATION');

				//     app.helper.showConfirmationBox({ message }).then(() => {
				//         this.reloadConversationList();
				//     });
				// }
			},
			openTransferChatPopup: function () {
				let idString = this.active_conversation.customer_social_id;
				// Load modal template
				const modal = $('#transferChat').clone().attr('id', `transfer-chat-${idString}`);
	
				// Init DOM Elements
				modal.find('.select2-container').remove();
				vtUtils.applyFieldElementsView(modal);

				// Init Dom Controller
				Vtiger_Edit_Js.getInstanceByModuleName('Vtiger').registerBasicEvents(modal.find('form'));

				// Init events
				this.initTransferChatModalEvents(modal);
	
				app.helper.showModal(modal);
			},
			requestShareZaloContactInfo: function () {
				let replaceParams = {
					customer_name: this.active_customer_profile.full_name,
				};

				let confirmMessage = app.vtranslate('JS_SOCIAL_CHATBOX_REQUEST_SHARE_ZALO_INFO_CONFIRMATION_MSG', replaceParams);

				app.helper.showConfirmationBox({ message: confirmMessage }).then(() => {
					let params = {
						module: 'CPSocialIntegration',
						action: 'SocialMessageAjax',
						mode: 'send',
						target_module: this.active_customer_profile.record_module,
						current_view: 'Detail',
						target_record: this.active_customer_profile.record_id,
						channel: 'Zalo',
						sender_id: this.active_social_page.id,
						message_type: 'request_info'
					};
	
					app.helper.showProgress();
			
					app.request.post({ data: params }).then(function (error, res) {
						app.helper.hideProgress();

						var message = '';
			
						if (error || (res && res.status == 'ERROR')) {
							if (res && res.error_code == 'follower_id_not_synced_with_oa') { // Modified by Phu Vo on 2019.10.24 to map with response result
								message = app.vtranslate('CPSocialIntegration.JS_ZALO_REQUEST_SHARE_CONTACT_INFO_FOLLOWER_ID_NOT_SYNCED_WITH_ZALO_OA_ERROR_MSG');
							}
							// Error code: https://developers.zalo.me/docs/api/official-account-api/phu-luc/ma-loi-post-735
							else if (res && (res.error_message == 'user_id is not valid' || res.error_code == '-213' || res.error_code == '-20109')) { // Modified by Phu Vo on 2019.10.24 to map with response result
								message = app.vtranslate('CPSocialIntegration.JS_ZALO_REQUEST_SHARE_CONTACT_INFO_SOCIAL_ID_NOT_FOLLOWED_TO_ZALO_OA_ERROR_MSG');
							}
							else if (res && res.error_code == '-32') { // Modified by Phu Vo on 2019.10.24 to map with response result
								message = app.vtranslate('CPSocialIntegration.JS_ZALO_OA_API_OUT_OF_RATE_LIMIT_ERROR_MSG');
							}
							else {
								message = app.vtranslate('CPSocialIntegration.JS_ZALO_REQUEST_SHARE_CONTACT_INFO_UNKNOWN_ERROR_MSG');
							}
			
							app.helper.showErrorNotification({ message: message }, { delay: 5000 });

							return false;
						}
			
						if (error || res.status == 'ERROR') {
							message = app.vtranslate('CPSocialIntegration.JS_ZALO_REQUEST_SHARE_CONTACT_INFO_ERROR_MSG');
							app.helper.showErrorNotification({ message: message });
							return false;
						}
			
						if (res.status == 'SENT') {
							message = app.vtranslate('CPSocialIntegration.JS_ZALO_REQUEST_SHARE_CONTACT_INFO_SUCCESS_MSG');
						}
						else if (res.status == 'QUEUED') {
							message = app.vtranslate('CPSocialIntegration.JS_ZALO_REQUEST_SHARE_CONTACT_INFO_QUEUED_MSG');
						}
	
						app.helper.showSuccessNotification({ message: message }, { delay: 5000 });
					});
				});
			},
			initTransferChatModalEvents: function (modal) {
				let self = this;

				let datatable = modal.find('.transfer-chat-table').DataTable({
					ordering: false,
					searching: false,
					processing: true,
					serverSide: true,
					language: DataTableUtils.languages,
					ajax: {
						url: 'index.php',
						type: 'POST',
						dataType: 'JSON',
						data: function (data) {
							const filterFormData = modal.find('form[name="transfer_chat"]').serializeFormData();
							return $.extend({}, data, {
								module: self.module,
								action: self.action,
								mode: 'getTransferableList',
								filters: filterFormData,
								channel: self.active_social_page.channel,
								social_page_id: self.active_social_page.id,
								customer_id: self.active_conversation.customer_id,
								customer_type: self.active_conversation.customer_type,
							});
						},
					},
					columns: [
						{
							data: 'action',
							name: 'action',
							render: (data, type, row) => {
								const container = $('<span class="action-container"></span>');
								const actionButton = $('<a></a>');
								actionButton.attr('class', 'transferBtn btn btn-sm btn-primary');
								actionButton.attr('href', 'javascript:void(0)');
								actionButton.html(app.vtranslate('JS_SOCIAL_CHATBOX_TRANSFER'));
								actionButton.attr('data-id', row.id);
								actionButton.attr('data-user_name', row.user_name);
								actionButton.attr('data-full_name', row.full_name);
								container.append(actionButton);
	
								return container.prop('outerHTML');
							}
						},
						{ data: 'full_name', name: 'full_name' },
						{ data: 'user_name', name: 'user_name' },
						{
							data: 'is_online',
							name: 'is_online',
							render: function (data, type, row) {
								if (row['raw_is_online'] == 1) return `<span class="badge btn-success">${data}</span>`;
								return `<span class="badge">${data}</span>`;
							}
						},
						{ data: 'rolename', name: 'rolename' },
					],
					initComplete: function () {
						const table = this;
	
						table.find('.clearFilters').on('click', function () {
							table.find(':input.form-search').val('').trigger('change');
							setTimeout(() => table.api().ajax.reload(), 0);
						});
	
						table.find('.trigerSearch').on('click', function () {
							setTimeout(() => table.api().ajax.reload(), 0);
						});
					}
				});

				// Handle event each time data table update
				datatable.on('draw.dt', (a, b) => {
					modal.find('.transferBtn').on('click', function (event) {
						event.preventDefault();
	
						const replaceParams = {
							'full_name': $(this).data('full_name'),
						};
						const confirmMessage = app.vtranslate('JS_SOCIAL_CHATBOX_TRANSFER_CHAT_CONFIRM', replaceParams);
	
						app.helper.showConfirmationBox({ message: confirmMessage }).then(() => {
							app.helper.showProgress();
							
							self.transferChat($(this).data('id')).then((err, res) => {
								app.helper.hideProgress();

								if (res && res.result) res = res.result;

								if (!Helper.resolveError(err, res)) return;

								// After transfer, check if current user still can chat with customer
								if (res.can_access != 1 && res.can_access != true) {
									let conversation = self.findActiveConverstation();
									self.active_conversation.can_access = false;
									conversation.can_access = false;
								}

								// Update customer profile
								let customerProfile = res;
								self.updateCustomerProfile(customerProfile);

								// Success message
								Success.notify(app.vtranslate('JS_SOCIAL_CHATBOX_TRANSFER_CHAT_SUCCESS_MSG', replaceParams));
			
								// Close modal
								modal.find('[data-dismiss="modal"]').trigger('click');
							});;
						});
					});
				});

				modal.find('form').vtValidate({
					submitHandler: function () {
						setTimeout(() => datatable.ajax.reload(), 0);
					}
				});

				modal.find(':input').on('keydown', event => {
					if (event.keyCode == 13) {
						setTimeout(() => datatable.ajax.reload(), 0);
					}
				});

				modal.find('select, .dateField').on('change', event => {
					setTimeout(() => datatable.ajax.reload(), 0);
				});
			},
			transferChat: function (userId) {
				let params = {
					mode: 'transferChat',
					channel: this.active_social_page.channel,
					social_page_id: this.active_social_page.id,
					customer_social_id: this.active_conversation.customer_social_id,
					customer_id: this.active_conversation.customer_id,
					customer_type: this.active_conversation.customer_type,
					transfer_to: userId,
				}

				return Helper.ajax(params);
			},
			bindCommentAttachments: function (event) {
				if (event.target.files.length == 0) return;

				let files = event.target.files;

				for (let i = 0; i < files.length; i++) {
					let file = files[i];
					let nameParts = file.name.split('.');
					let ext = nameParts.pop();
					let baseName = nameParts.join('');
					
					files[i].ext = ext;
					files[i].base_name = baseName;
				}

				this.active_customer_comment_form.files = this.active_customer_comment_form.files.concat(...files);
				event.target.value = '';
			},
			removeCommentAttachment: function (file) {
				let files = this.active_customer_comment_form.files;
				let fileIndex = files.findIndex(single => single == file);

				if (fileIndex > -1) {
					files.splice(fileIndex, 1);
					this.active_customer_comment_form.files = files;
				}
			},
			saveComment: function () {
				let params = {
					module: 'ModComments',
					action: 'SaveAjax',
					is_private: 0,
					commentcontent: this.active_customer_comment_form.commentcontent,
					related_to: this.active_customer_profile.record_id,
				}

				if (!params.commentcontent) {
					Error.notify(app.vtranslate('JS_SOCIAL_CHATBOX_POST_COMMENT_CONTENT_REQUIRED_ERROR_MSG'));
					return;
				}

				let formData = new FormData();

				for (prop in params) {
					formData.append(prop, params[prop]);
				}

				this.active_customer_comment_form.files.forEach(file => {
					formData.append('filename[]', file);
				});

				this.posting_comment = true;

				Helper.ajax(formData).then((err, res) => {
					this.posting_comment = false;
					if (!Helper.resolveError(err, res)) return;

					Success.notify(app.vtranslate('JS_SOCIAL_CHATBOX_POST_COMMENT_SUCCESSFULLY'));

					this.resetCommentForm();
					
					setTimeout(() => this.loadCustomerComments(), 100);
				});
			},
			openTaggingModal: function () {
				let self = this;
				let idString = this.active_conversation.customer_social_id;

				// Load modal template
				const modal = $('#taggingModal').clone().attr('id', `tagging-modal-${idString}`);
				
				// Init DOM Elements
				modal.find('.select2-container').remove();

				// Init Dom Controller
				Vtiger_Edit_Js.getInstanceByModuleName('Vtiger').registerBasicEvents(modal.find('form'));
				
				// Register select2 element
				let params = {
					mode: 'getCustomerTags',
					customer_id: this.active_customer_profile.record_id,
					customer_type: this.active_customer_profile_mode.record_module,
				};

				app.helper.showProgress();

				Helper.ajax(params).then((err, res) => {
					app.helper.hideProgress();

					if (!Helper.resolveError(err, res)) return;

					let tagInput = modal.find('[name="tags"]');
					tagInput.select2({
						placeholder: tagInput.attr('placeholder'),
						minimumInputLength: 0,
						closeOnSelect: false,
						tags: [],
						tokenSeparators: [','],
						ajax: {
							url: `index.php?module=${self.module}&action=${self.action}&mode=getAssignableTags`,
							dataType: 'json',
							data: function (term, page) {
								term = term.trim();
	
								let data = {
									keyword: term
								}
	
								return data;
							},
							results: function (data) {
								return {results: data.result};
							},
							transport: function (params) {
								return jQuery.ajax(params);
							}
						},
						formatSelection: function (object, container) {
							if (object.id) {
								let template =  `<span title="${object.text}">${object.text}</span>`;
			
								// Process item type
								container
									.closest('.select2-search-choice')
									.attr('data-type', object.type)
									.addClass('tag')
		
								return template;
							}
		
							return object.text;
						},
						formatResult: function (object, container) {
							if (object.id) {
								let template =  `<span title="${object.text}">${object.text}</span>`;
			
								// Process item type
								container
									.attr('data-type', object.type)
									.addClass('tag-option')
		
								return template;
							}
		
							return object.text;
						}
					});

					if (res) tagInput.select2('data', res).trigger('change');
					let preserveTags = tagInput.select2('data').map(single => single.id) || [];

					// Register create new tag button
					let selectInput = tagInput.select2('container').find('.select2-input');
					
					selectInput.on('keydown', e => {
						if (e.keyCode == 13) {
							// TODO Find another way to check is result empty
							let isResultEmpty = $('.select2-results:visible').find('.select2-no-results').length >= 1;
							let newTag = $(e.target).val();
							let maxLength = 25;
							
							if (isResultEmpty) {
								if (newTag.length > maxLength) {
									Error.notify(app.vtranslate('JS_SOCIAL_CHATBOX_CREATE_TAG_LIMIT_ERROR_MSG', { limit: maxLength }));
									return;
								}

								// Create new Tag
								let requestParams = {
									module: self.active_customer_profile.record_module,
									action: 'TagCloud',
									mode: 'saveTags',
									tagsList: { new: [newTag] },
									newTagType: 'private',
								}

								Helper.ajax(requestParams).then((err, res) => {
									if (!Helper.resolveError(err, res)) return;
									
									let currentTags = tagInput.select2('data') || [];
									let newTags = res.new || {};
									
									newTags = self.formatTagsList(newTags);

									currentTags = currentTags.concat(...newTags);
									tagInput.select2('data', currentTags).trigger('change');
									tagInput.data('select2').blur();
									tagInput.data('select2').search.trigger('keydown');
								});
							}

							e.preventDefault();

							return false;
						}
					});

					// Register add tag button
					modal.find('.addTag').on('click', event => {
						event.preventDefault();
						let callBack = res => {
							let currentTags = tagInput.select2('data') || [];
							let newTags = res.new || {};
							
							newTags = this.formatTagsList(newTags);

							currentTags = currentTags.concat(...newTags);
							tagInput.select2('data', currentTags).trigger('change');
						};

						this.openCreateTagModal(callBack);
					});

					// Register save button
					modal.find('form').vtValidate({
						submitHandler: () => {
							let currentTags = tagInput.select2('data').map(single => single.id) || [];
							let existingTags = currentTags.filter(single => !preserveTags.includes(single));
							let deletedTags = preserveTags.filter(single => !currentTags.includes(single));
							
							let requestParams = {
								module: self.active_customer_profile.record_module,
								action: 'TagCloud',
								mode: 'saveTags',
								record: self.active_customer_profile.record_id,
								tagsList: {
									existing: existingTags,
									deleted: deletedTags
								}
							}

							Helper.ajax(requestParams).then((err, res) => {
								if (!Helper.resolveError(err, res)) return;
								
								this.loadCustomerTagList(this.active_customer_profile.record_id, this.active_customer_profile.record_module);

								Success.notify(app.vtranslate('JS_SOCIAL_CHATBOX_UPDATE_TAG_SUCCESSFUL_MSG'));

								modal.find('.close').trigger('click');
							});

							return false;
						}
					});

					// Modal shown call back
					let callBack = () => {
						modal.find('button').attr('disabled', false);
					}
				
					// Show modal
					app.helper.showModal(modal, { cb: callBack });
				});
			},
			replayGifImage: function (event) {
				$(event.target).attr('src', $(event.target).attr('src'));
			},
			openPlayVideoModal: function (fileInfo) {
				let url = this.getMediaUrl(fileInfo.url);
				let idString = this.active_conversation.customer_social_id;

				if (!url) {
					Error.notify(app.vtranslate('JS_SOCIAL_CHATBOX_UNABLE_TO_PLAY_VIDEO'));
					return;
				}

				// Load modal template
				const modal = $('#play-video').clone().attr('id', `play-video-${idString}`);
				
				// Load url to modal
				modal.find('source.video-url').attr('src', url);
				
				// Show modal
				app.helper.showModal(modal);
			},
			openCreateTagModal: function (callBack = null) {
				let self = this;
				let idString = this.active_conversation.customer_social_id;

				// Load modal template
				const modal = $('#createTagModal').clone().attr('id', `create-tag-${idString}`);
				
				// Init DOM Elements
				modal.find('.select2-container').remove();

				// Init Dom Controller
				Vtiger_Edit_Js.getInstanceByModuleName('Vtiger').registerBasicEvents(modal.find('form'));

				// Register submit event
				modal.find('form').vtValidate({
					submitHandler: form => {
						form = $(form);
						let newTag = form.find(':input[name="tag_name"]').val();
						let type = form.find(':input[name="visibility"]').is(':checked') ? 'public' : 'private';
							
						let requestParams = {
							module: self.active_customer_profile.record_module,
							action: 'TagCloud',
							mode: 'saveTags',
							tagsList: { new: [newTag] },
							newTagType: type,
						}

						Helper.ajax(requestParams).then((err, res) => {
							if (!Helper.resolveError(err, res)) return;

							if (typeof callBack == 'function') callBack(res);

							Success.notify(app.vtranslate('JS_SOCIAL_CHATBOX_CREATE_TAG_SUCCESSFUL_MSG'));

							modal.find('.close').trigger('click');
						});

						return false;
					}
				});
				
				// Show modal
				app.helper.showPopup(modal);
			},
			updateCustomerType: function (customerType) {
				if (this.active_customer_profile.leads_business_type == customerType) return;
				
				let params = {
					mode: 'updateCustomerType',
					module: this.module,
					action: this.action,
					business_type: customerType,
					customer_id: this.active_customer_profile.record_id,
					customer_type: this.active_customer_profile.record_module,
				}

				app.helper.showProgress();

				Helper.ajax(params).then((err, res) => {
					app.helper.hideProgress();

					if (!Helper.resolveError(err, res)) return;

					let customerProfile = Object.assign(res);
					this.updateCustomerProfile(customerProfile);
				});
			},
			
			openMessageTemplateModal: function () {
				let self = this;
				let idString = this.active_conversation.customer_social_id;
				// Load modal template
				const modal = $('#messageTemplate').clone().attr('id', `message-template-${idString}`);
	
				// Init DOM Elements
				modal.find('.select2-container').remove();
				vtUtils.applyFieldElementsView(modal);

				// Init Dom Controller
				Vtiger_Edit_Js.getInstanceByModuleName('Vtiger').registerBasicEvents(modal.find('form'));

				// Init events
				let messageTable = modal.find('.message-template-table');
				let dataTable = messageTable.DataTable({
					ordering: false,
					searching: false,
					processing: true,
					serverSide: true,
					language: DataTableUtils.languages,
					columns: [
						{
							data: 'select',
							name: 'select',
							render: (data, type, row) => {
								let rowString = JSON.stringify(row);
								let input =  `<div class="text-center"><input type="radio" data-row='${rowString}' name="select" value="${row.crmid}" /></div>`;
								return input;
							},
						},
						{
							data: 'question',
							name: 'question',
							render: (data, type, row) => {
								return `<div class="data-table-content">${data}</div>`;
							},
						},
						{
							data: 'faq_answer',
							name: 'faq_answer',
							render: (data, type, row) => {
								return `<div class="data-table-content">${data}</div>`;
							},
						},
						{ data: 'faqcategories', name: 'faqcategories' },
						{ data: 'createdtime', name: 'createdtime' },
					],
					ajax: {
						url: 'index.php',
						type: 'POST',
						dataType: 'JSON',
						data: function (data) {
							const filterFormData = modal.find('form[name="message_template"]').serializeFormData();
							return $.extend({}, data, {
								module: self.module,
								action: self.action,
								mode: 'getFaqMessageTemplates',
								filters: filterFormData,
							});
						},
					},
					initComplete: function () {
						const table = this;
	
						table.find('.clearFilters').on('click', function () {
							table.find(':input.form-search').val('').trigger('change');
							setTimeout(() => table.api().ajax.reload(), 0);
						});
	
						table.find('.trigerSearch').on('click', function () {
							setTimeout(() => table.api().ajax.reload(), 0);
						});
					}
				});

				modal.find('.saveButton').on('click', () => {
					form = modal.find('form');
					let formData = form.serializeFormData();

					if (!formData.select) {
						app.helper.showErrorNotification({ message: app.vtranslate('JS_SOCIAL_CHATBOX_QUICK_REPLY_SELECT_ERROR_MSG')});
						return;
					}
					
					let rowData = $(form).find('[name="select"]:checked').data('row');
					let messageContent = rowData.answer;

					self.active_conversation.message = messageContent;
					modal.find('.close').trigger('click');
					return false;
				});

				// Register save button
				modal.find('form').vtValidate({
					submitHandler: form => {
						setTimeout(() => dataTable.ajax.reload(), 0);
						return false;
					}
				});

				modal.find(':input').on('keydown', event => {
					if (event.keyCode == 13) {
						setTimeout(() => dataTable.ajax.reload(), 0);
					}
				});

				modal.find('select, .dateField').on('change', event => {
					setTimeout(() => dataTable.ajax.reload(), 0);
				});
	
				app.helper.showModal(modal);
			},
			scrollMessagesToBottom: function () {
				let scrollContainer = $(this.$el).find('.messages-container .scrollable-container');
				scrollContainer.animate({scrollTop: scrollContainer[0].scrollHeight}, 500, 'swing');
			},
			saveLocalStorageCache: function () {
				if (!this.active_conversation || !this.active_conversation.customer_social_id) return;
				
				let cache = {
					active_channel: this.active_social_page.channel,
					active_customer_id: this.active_conversation.customer_id,
					active_customer_social_id: this.active_conversation.customer_social_id,
				};

				setTimeout(() => LocalStorage.set('data', cache));
			},
		},
		mounted: function () {
			let self = this;
			$(this.$el).find('.scrollable-container').perfectScrollbar();
			vtUtils.showSelect2ElementView($(this.$el).find('.inputSelect'));
			
			GoogleMaps.initAutocomplete($(this.$el).find(':input[name="lane"]'), {
				city: $(this.$el).find(':input[name="city"]'), 
				state: $(this.$el).find(':input[name="state"]'), 
				zip: $(this.$el).find(':input[name="code"]'), 
				country: $(this.$el).find(':input[name="country"]'),
			});

			$(this.$el).on('change', ':input[name="lane"]', event => {
				this.customer_profile_form.lane = event.target.value;
			});

			CustomOwnerField.initCustomOwnerFields($(this.$el).find(':input[name="main_owner_id"]'));

			$(this.$el).find(':input[name="main_owner_id"]').on('change', event => {
				let data = $(event.target).select2('data');
				this.customer_profile_form.owner_id = data.id;
				this.customer_profile_form.owner_name = data.text;
			});

			$(this.$el).find(':input[name="leadsource"]').on('change', event => {
				this.customer_profile_form.leadsource = event.target.value;
			});

			$(this.$el).on('change', '.changeAvatarInput', event => {
				if (!event.target.value) return;

				let files = event.target.files;

				if (FileReader && files && files.length) {
					let fileReader = new FileReader();
					fileReader.onload = () => {
						this.customer_profile_form.avatar = fileReader.result;
					}
					fileReader.readAsDataURL(files[0]);
				}
			});

			CKEDITOR.disableAutoInline = true;
			mentionHandler.attach($(this.$el).find('#addCommentTextArea'));
			
			$(this.$el).find('#addCommentTextArea').on('input', function () {
				self.active_customer_comment_form.commentcontent = mentionHandler.getDbFormat($(this));
			});

			$(this.$el).on('change', ':input[name="salutationtype"]', event => {
				this.customer_profile_form.salutationtype = event.target.value;
			});

			$('body').on('click', event => {
			   this.handleContainerClicked(event); 
			});

			// Added by Vu Mai on 2022-10-21 to support message textarea dynamic height
			$(this.$el).on('input', 'textarea[name="message"]', event => {
				if (event.target.value) {
					event.target.style.height = 'auto';
					event.target.style.height = (event.target.scrollHeight) + 'px';
				}
				else {
					event.target.style.height = '34px';
				}
			});
			// End Vu Mai
		},
		updated: function () {
			$(this.$el).find('.scrollable-container').perfectScrollbar('update');

			if (this.size != 'NONE') {
				this.saveLocalStorageCache();
			}
			else {
				setTimeout(() => LocalStorage.flush());
			}
		},
	};

	$(function () {
		window.SocialChatboxPopup = new Vue(popupOptions);
		window.SocialChatboxPopup.LocalStorage = LocalStorage;
	});
})();