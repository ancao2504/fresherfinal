/**
 * ChatbotIframe.js
 * Author: Phu Vo
 * Date: 2020.09.11
 * Description: UI handler for chatbot iframe
 */

(() => {
	jQuery.validator.addMethod('optional-less-than-or-equal', function (value, element, params) {
		if (value && parseInt(value) > params) return false;
		return true;
	}, function (params, element) {
		return app.vtranslate('CPChatBotIntegration.JS_LESS_THAN_OR_EQUAL_ERROR', { number: params});
	});

	Vue.component('select-product', {
		props: ['placeholder', 'module', 'ignores'],
		template: `
			<b-form-input></b-form-input>
		`,
		mounted () {
			const self = this;
			let urlParams = window._IFRAME_DATA.url_params;

			$(this.$el).select2({
				url: 'webhook.php',
				placeholder: self.placeholder || app.vtranslate('CPChatBotIntegration.JS_TYPE_TO_SEARCH'),
				minimumInputLength: _VALIDATION_CONFIG ? _VALIDATION_CONFIG.autocomplete_min_length : 2,
				ajax: {
					url: 'entrypoint.php',
					type: 'POST',
					dataType: 'JSON',
					data: function (term, page) {
						// Skip ajax request when user enter only spaces
						if (term.length == 0) {
							$(self.$el).select2('close');
							$(self.$el).select2('open');
							return null;
						}

						const data = {
							name: urlParams.name,
							bot_name: window._IFRAME_DATA.bot_name,
							access_token: urlParams.access_token,
							action: 'ChatbotIframeAjax',
							mode: 'searchProduct',
							search_module: self.module,
							search_value: term,
							ignores: self.ignores,
						}

						return data;
					},
					results: function (data) {
						return { results: data.result.map((single) => ({ id: single.id, text: single.label, data: single })) };
					},
					transport: function (params) {
						return jQuery.ajax(params);
					}
				}
			});

			// Init events
			$(this.$el).on('change.select2', (e) => {
				self.$emit('input', $(self.$el).select2('data'));
				$(self.$el).select2('data', '');
			});
		},
		destroyed: function () {
			$(this.$el).off().select2('destroy');
		}
	});

	Vue.component('multiple-product', {
		props: ['placeholder', 'module', 'dataRuleRequired'],
		template: `
			<b-form-input :data-rule-required="dataRuleRequired"></b-form-input>
		`,
		mounted () {
			const self = this;
			let urlParams = window._IFRAME_DATA.url_params;

			$(this.$el).select2({
				placeholder: self.placeholder || app.vtranslate('JS_TYPE_TO_SEARCH'),
				minimumInputLength: _VALIDATION_CONFIG ? _VALIDATION_CONFIG.autocomplete_min_length : 2,
				closeOnSelect: false,
				tags: [],
				tokenSeparators: [','],
				ajax: {
					url: 'entrypoint.php',
					type: 'POST',
					dataType: 'JSON',
					data: function (term, page) {
						// Skip ajax request when user enter only spaces
						if (term.length == 0) {
							$(self.$el).select2('close');
							$(self.$el).select2('open');
							return null;
						}

						const data = {
							name: urlParams.name,
							bot_name: window._IFRAME_DATA.bot_name,
							access_token: urlParams.access_token,
							action: 'ChatbotIframeAjax',
							mode: 'searchProduct',
							search_module: self.module,
							search_value: term,
						}

						return data;
					},
					results: function (data) {
						return { results: data.result.map((single) => ({ id: single.id, text: single.label, data: single })) };
					},
					transport: function (params) {
						return jQuery.ajax(params);
					}
				}
			});

			// Init events
			$(this.$el).on('change.select2', (e) => {
				self.$emit('input', $(self.$el).val());
			});
		},
		destroyed: function () {
			$(this.$el).off().select2('destroy');
		}
	});

	// Init app state
	const initData = {
		overlay: false,
		uploading_avatar: false,
		modes: {
			'customer': 'detail',
			'Calendar': 'list',
			'SalesOrder': 'list',
			'Invoice': 'list',
			'HelpDesk': 'list',
			'Products': 'list',
			'Services': 'list',
			'CPEventRegistration': 'list',
		},
		customer_data: {},
		customer_display: {},
		form_data: {
			'customer': {},
			'comment': {},
			'Calendar': {},
			'SalesOrder': {
				items: [],
				total: 0,
				discount_percent: 0,
				discount_amount: 0,
				tax_percent: 0,
				tax_amount: 0,
				grand_total: 0,
			},
			'Invoice': {},
			'HelpDesk': {},
			'Products': {},
			'Services': {},
			'Avatar': {
				imagename: ''
			},
		},
		meta_data: {},
		counters: {},
		data: {
			Calendar: [],
			SalesOrder: [],
			Invoice: [],
			HelpDesk: [],
			Products: [],
			Services: [],
		},
		fields: {
			Calendar: [
				{
					key: 'subject',
					label: ChatbotHelper.getFieldLabel('Calendar', 'subject'),
					class: 'subject title',
				},
				{
					key: 'activitytype',
					label: ChatbotHelper.getFieldLabel('Calendar', 'activitytype'),
					class: 'activitytype',
				},
				{
					key: 'date_start',
					label: ChatbotHelper.getFieldLabel('Calendar', 'date_start'),
					class: 'date_start',
				},
			],
			SalesOrder: [
				{
					key: 'salesorder_no',
					label: ChatbotHelper.getFieldLabel('SalesOrder', 'salesorder_no'),
					class: 'salesorder_no',
				},
				{
					key: 'sostatus',
					label: ChatbotHelper.getFieldLabel('SalesOrder', 'sostatus'),
					class: 'sostatus',
				},
				{
					key: 'createdtime',
					label: ChatbotHelper.getFieldLabel('SalesOrder', 'createdtime'),
					class: 'createdtime',
				},
				{
					key: 'hdnGrandTotal',
					label: ChatbotHelper.getFieldLabel('SalesOrder', 'hdnGrandTotal'),
					class: 'hdnGrandTotal',
				},
			],
			Invoice: [
				{
					key: 'invoice_no',
					label: ChatbotHelper.getFieldLabel('Invoice', 'invoice_no'),
					class: 'invoice_no',
				},
				{
					key: 'createdtime',
					label: ChatbotHelper.getFieldLabel('SalesOrder', 'createdtime'),
					class: 'createdtime',
				},
				{
					key: 'hdnGrandTotal',
					label: ChatbotHelper.getFieldLabel('Invoice', 'hdnGrandTotal'),
					class: 'hdnGrandTotal',
				}
			],
			HelpDesk: [
				{
					key: 'ticket_no',
					label: ChatbotHelper.getFieldLabel('HelpDesk', 'ticket_no'),
					class: 'ticket_no',
				},
				{
					key: 'ticket_title',
					label: ChatbotHelper.getFieldLabel('HelpDesk', 'ticket_title'),
					class: 'ticket_title title',
				},
				{
					key: 'createdtime',
					label: ChatbotHelper.getFieldLabel('HelpDesk', 'createdtime'),
					class: 'createdtime',
				},
			],
			Products: [
				{
					key: 'product_no',
					label: ChatbotHelper.getFieldLabel('Products', 'product_no'),
					class: 'product_no',
				},
				{
					key: 'productname',
					label: ChatbotHelper.getFieldLabel('Products', 'productname'),
					class: 'productname title',
				},
				{
					key: 'unit_price',
					label: ChatbotHelper.getFieldLabel('Products', 'unit_price'),
					class: 'unit_price',
				},
			],
			Services: [
				{
					key: 'service_no',
					label: ChatbotHelper.getFieldLabel('Services', 'service_no'),
					class: 'service_no',
				},
				{
					key: 'servicename',
					label: ChatbotHelper.getFieldLabel('Services', 'servicename'),
					class: 'servicename title',
				},
				{
					key: 'unit_price',
					label: ChatbotHelper.getFieldLabel('Services', 'unit_price'),
					class: 'unit_price',
				},
			],
			CPEventRegistration: [
				{
					key: 'cpeventregistration_no',
					label: ChatbotHelper.getFieldLabel('CPEventRegistration', 'cpeventregistration_no'),
					class: 'cpeventregistration_no',
				},
				{
					key: 'cpeventregistration_status',
					label: ChatbotHelper.getFieldLabel('CPEventRegistration', 'cpeventregistration_status'),
					class: 'cpeventregistration_status',
				},
				{
					key: 'createdtime',
					label: ChatbotHelper.getFieldLabel('CPEventRegistration', 'createdtime'),
					class: 'createdtime',
				},
			],
		},
		selected_tags: [
			{
				id: 'Users:' + _CURRENT_USER_META.id,
				text: `${_CURRENT_USER_META.name} (${_CURRENT_USER_META.email})`,
			}
		],
		datepicker_options: {
			format: window.vtUtils.getMomentDateFormat(),
		},
		// cache: {},
	};

	window.Chatbot = new Vue({
		// App Element
		el: '#app',

		// App Data
		data: Object.assign(initData, window._IFRAME_DATA),

		watch: {
			'form_data.SalesOrder.select_product' (val, pre) {
				if (val) {
					this.selectProduct(val);
					this.form_data.SalesOrder.select_product = '';
				}
			},
			'form_data.Calendar.events_call_direction' (val, pre) {
				if (val && this.form_data.Calendar.activitytype == 'Call') {
					const options = this.meta_data.Events.picklist_fields.events_call_direction;
					const selected = options.find((option) => option.value == val);
					this.form_data.Calendar.subject = selected.text;
				}
			},
			'form_data.SalesOrder.discount_percent' (val, pre) {
				if (val && parseInt(val) > 100) {
					const self = this;

					setTimeout(() => {
						self.form_data.SalesOrder.discount_percent = '100';
					}, 100);
				}
			},
			'form_data.Calendar.date_start' (val, pre) {
				this.calcEndDate(null);
			}
		},

		// App methods
		methods: {
			toggleMode (element, value = 'detail', modifier = '') {
				if (value == 'detail') {
					$('.error:not(:input)').remove();
					$(':input').removeClass('error');

					this.form_data[element] = {};
					this.modes[element] = 'detail';
				}
				if (value == 'list') {
					this.form_data[element] = {};
					this.modes[element] = 'list';
				}
				if (value == 'edit') {
					this.selected_tags = [ ...[], ...initData.selected_tags ]; // Reset selected tags to current user
	
					if (element == 'customer') {
						this.form_data[element] = Object.assign({}, this[`${element}_data`]);
					}
					else {
						this.form_data[element] = this.getDefaultFormData(element, modifier);
					}

					this.modes[element] = 'edit';
				}
				if (value == 'salesorder') {
					this.form_data['SalesOrder'] = this.getDefaultFormData('SalesOrder', modifier);
					this.modes['customer'] = 'salesorder';
					this.loadLeadRelatedProducts();
				}
			},

			getCounter (element) {
				return this.counters[element] || 0;
			},

			isRequired (module, fieldName) {
				if (this.meta_data[module] && this.meta_data[module].all_fields && this.meta_data[module].all_fields[fieldName]) {
					return this.meta_data[module].all_fields[fieldName].required == true;
				}

				return false;
			},

			openCreateCustomerWindow () {
				const urlParams = Object.assign({}, this.url_params);
				urlParams.name = this.url_params.name;
				urlParams.view = 'ChatbotIframeCreateCustomerPopup';
				urlParams.bot_name = window._IFRAME_DATA.bot_name;

				const callback = (data) => {
					this.customer_data = data.customer_data;
					this.customer_display = data.customer_display;
				};

				ChatbotHelper.popupCenter('entrypoint.php?' + $.param(urlParams), app.vtranslate('CPChatBotIntegration.JS_HANA_ADD_TO_CRM'), 800, 480, callback);
			},

			getOptions (module, field) {
				if (!this.meta_data[module]) return [];
				if (!this.meta_data[module].picklist_fields[field]) return [];

				const options = Object.values(this.meta_data[module].picklist_fields[field]).map((single) => ({ value: single.key, text: single.label }));
				options.unshift({ value: '', text: app.vtranslate('JS_SELECT_OPTION')});

				return options;
			},

			getDefaultFormData (element, mode = '') {
				if (element == 'Calendar') {
					const momentNow = moment().add(30 - moment().minute() % 30, 'm');

					const defaultFormData = {
						activitytype: 'Call',
						eventstatus: 'Planned',
						taskstatus: 'Planned',
						events_call_direction: 'Outbound',
						date_start: MomentHelper.getDisplayDate(),
						time_start: momentNow.format('HH:mm'),
						due_date: MomentHelper.getDisplayDate(),
						time_end: momentNow.add(30, 'm').format('HH:mm'),
						visibility: 'Public', // Some db throw error on null visibility
					}

					if (mode == 'Call') defaultFormData.activitytype = 'Call';
					if (mode == 'Meeting') defaultFormData.activitytype = 'Meeting';
					if (mode == 'Task') defaultFormData.activitytype = 'Task';

					return defaultFormData;
				}
				if (element == 'HelpDesk') {
					return {
						ticketstatus: 'In Progress',
						ticketpriorities: 'Normal',
					};
				}
				if (element == 'SalesOrder') {
					return {
						sostatus: 'Created',
						receiver_name: this.customer_data.full_name,
						receiver_phone: this.customer_data.mobile,
						company: this.customer_data.company,
						items: [],
						total: 0,
						discount_percent: 0,
						discount_amount: this.formatInputCurrency(0),
						tax_percent: 0,
						tax_amount: this.formatInputCurrency(0),
						grand_total: 0,
						bill_street: this.getCustomerAddress(),
						ship_street: this.getCustomerAddress(),
					};
				}
				if (element == 'Products') {
					return {};
				}
				if (element == 'Services') {
					return {};
				}

				return {};
			},

			saveCustomer () {
				if (!$('#customer').valid()) return;

				const self = this;
				const formData = Object.assign({}, this.url_params, this.form_data['customer'],
					{
						name: this.url_params.name,
						bot_name: window._IFRAME_DATA.bot_name,
						access_token: this.url_params.access_token,
						action: 'ChatbotIframeAjax',
						mode: 'saveCustomer',
					}
				);

				if (!formData || !formData.action) {
					return this.$bvToast.toast(app.vtranslate('JS_THERE_WAS_SOMETHING_ERROR'), {
						title: app.vtranslate('JS_ERROR'),
						variant: 'danger',
					});
				}

				this.overlay = true;

				app.request.post({ url: 'entrypoint.php', data: formData }).then((err, res) => {
					self.overlay = false;

					if (err) {
						return this.$bvToast.toast(err.message, {
							title: app.vtranslate('JS_ERROR'),
							variant: 'danger',
						});
					}

					if (!res) {
						return this.$bvToast.toast(app.vtranslate('JS_THERE_WAS_SOMETHING_ERROR'), {
							title: app.vtranslate('JS_ERROR'),
							variant: 'danger',
						});
					}

					self.customer_data = res.customer_data;
					self.customer_display = res.customer_display;

					self.toggleMode('customer', 'detail');

					return self.$bvToast.toast(app.vtranslate('JS_SAVE_SUCCESSFULLY'), {
						title: app.vtranslate('JS_SUCCESS'),
						variant: 'success',
					});
				});
			},

			getCustomerAddress () {
				let customerFullAddress = '';

				if (this.customer_data.record_module == 'Contacts') {
					if (this.customer_data.mailingstreet) customerFullAddress += this.customer_data.mailingstreet;
					if (this.customer_data.mailingstate) customerFullAddress += ', ' + this.customer_data.mailingstate;
					if (this.customer_data.mailingcity) customerFullAddress += ', ' +  this.customer_data.mailingcity;
				}
				else {
					if (this.customer_data.lane) customerFullAddress += this.customer_data.lane;
					if (this.customer_data.state) customerFullAddress += ', ' + this.customer_data.state;
					if (this.customer_data.city) customerFullAddress += ', ' +  this.customer_data.city;
				}

				return customerFullAddress;
			},

			saveComment () {
				if (!$('#comment').valid()) return;

				if (!this.form_data.comment.commentcontent) return;

				const formData = Object.assign({}, this.url_params, this.form_data.comment,
					{
						name: this.url_params.name,
						bot_name: window._IFRAME_DATA.bot_name,
						access_token: this.url_params.access_token,
						module: 'ModComments',
						action: 'SaveAjax',
						related_to: this.customer_data.record_id,
						is_private: 0,
						assigned_user_id: _CURRENT_USER_META.id,
						main_owner_id: _CURRENT_USER_META.id,
					}
				);

				this.overlay = true;

				app.request.post({ url: 'entrypoint.php', data: formData }).then((err, res) => {
					this.overlay = false;

					if (err) {
						return this.$bvToast.toast(err.message, {
							title: app.vtranslate('JS_ERROR'),
							variant: 'danger',
						});
					}

					if (!res) {
						return this.$bvToast.toast(app.vtranslate('JS_THERE_WAS_SOMETHING_ERROR'), {
							title: app.vtranslate('JS_ERROR'),
							variant: 'danger',
						});
					}

					this.form_data.comment = {};

					return this.$bvToast.toast(app.vtranslate('CPChatBotIntegration.JS_POST_COMMENT_SUCCESS'), {
						title: app.vtranslate('JS_SUCCESS'),
						variant: 'success',
					});
				});
			},

			loadRelatedList (element, silent = false) {
				if (this.modes[element] == 'edit') return;

				const params = Object.assign({}, this.url_params,
					{
						name: this.url_params.name,
						bot_name: window._IFRAME_DATA.bot_name,
						access_token: this.url_params.access_token,
						action: 'ChatbotIframeAjax',
						mode: 'getRelatedListForChatbotIframe',
						source_record: this.customer_data.record_id,
						source_module: this.customer_data.record_module,
						related_module: element,
						fields: this.fields[element].map((single) => single.key),
					}
				);

				if (!silent) this.overlay = true;

				app.request.post({ url: 'entrypoint.php', data: params }).then((err, res) => {
					if (!silent) this.overlay = false;

					if (err) {
						return this.$bvToast.toast(err.message, {
							title: app.vtranslate('JS_ERROR'),
							variant: 'danger',
						});
					}

					if (!res) {
						return this.$bvToast.toast(app.vtranslate('JS_THERE_WAS_SOMETHING_ERROR'), {
							title: app.vtranslate('JS_ERROR'),
							variant: 'danger',
						});
					}

					this.data[element] = res;
					this.loadRelatedCounters();
				});
			},

			loadRelatedCounters () {
				const params = Object.assign({}, this.url_params,
					{
						name: this.url_params.name,
						bot_name: window._IFRAME_DATA.bot_name,
						access_token: this.url_params.access_token,
						action: 'ChatbotIframeAjax',
						mode: 'getRelatedCountersForChatbotIframe',
						source_record: this.customer_data.record_id,
						source_module: this.customer_data.record_module,
					}
				);

				app.request.post({ url: 'entrypoint.php', data: params }).then((err, res) => {
					if (err) return;
					if (!res) return;
					
					this.counters = res;
				});
			},

			openChatbotIframeRelatedListPopup (element) {
				const params = Object.assign({}, this.url_params,
					{
						name: this.url_params.name,
						bot_name: window._IFRAME_DATA.bot_name,
						access_token: this.url_params.access_token,
						view: 'ChatbotIframeRelatedListPopup',
						source_record: this.customer_data.record_id,
						source_module: this.customer_data.record_module,
						related_module: element,
					}
				);

				ChatbotHelper.popupCenter(`entrypoint.php?` + $.param(params), 'Related ' + element, 800, 480);
			},

			submitSaveAjax (element) {
				if (!$(`#${element}`).valid()) return;

				const params = Object.assign({}, this.url_params, this.form_data[element],
					{
						name: this.url_params.name,
						bot_name: window._IFRAME_DATA.bot_name,
						access_token: this.url_params.access_token,
						module: element,
						action: 'SaveAjax',
					}
				);

				if (element == 'ModComments') {
					params.related_to = this.customer_data.record_id;
					params.relationOperation = true;
				}
				else if (element == 'Calendar') {
					if (this.customer_data.record_module == 'Accounts') {
						params.related_account = this.customer_data.record_id;
					}
					else if (this.customer_data.record_module == 'Contacts') {
						params.contact_id = this.customer_data.record_id;
					}
					else if (this.customer_data.record_module == 'Leads') {
						params.related_lead = this.customer_data.record_id;
					}
					else {
						params.parent_id = this.customer_data.record_id;
					}
					
					params.relationOperation = true;
					params.sourceRecord = this.customer_data.record_id;
					params.sourceModule = this.customer_data.record_module;
				}
				else {
					params.parent_id = this.customer_data.record_id;
					params.sourceRecord = this.customer_data.record_id;
					params.sourceModule = this.customer_data.record_module;
				}

				if (element == 'HelpDesk') {
					params.contact_id = this.customer_data.record_id;
				}

				if (element == 'Calendar') {
					let activityType = this.form_data.Calendar.activitytype;

					if (activityType == 'Task') {
						params.calendarModule = 'Calendar';
					}
					else {
						params.calendarModule = 'Events';
					}
				}

				this.overlay = true;

				app.request.post({ url: 'entrypoint.php', data: params }).then((err, res) => {
					this.overlay = false;

					if (err) {
						return this.$bvToast.toast(err.message, {
							title: app.vtranslate('JS_ERROR'),
							variant: 'danger',
						});
					}

					if (!res) {
						return this.$bvToast.toast(app.vtranslate('JS_THERE_WAS_SOMETHING_ERROR'), {
							title: app.vtranslate('JS_ERROR'),
							variant: 'danger',
						});
					}

					this.form_data[element] = {};
					this.modes[element] = 'list';
					this.loadRelatedList(element, true);

					return this.$bvToast.toast(app.vtranslate('CPChatBotIntegration.JS_CREATE_NEW_SUCCESS'), {
						title: app.vtranslate('JS_SUCCESS'),
						variant: 'success',
					});
				});
			},

			selectProduct (inputData) {
				const data = inputData.data;
				this.form_data.SalesOrder.items.push({
					id: data.id,
					module: data.module,
					product_no: data.product_no,
					label: data.label,
					quantity: 1,
					price: Number.parseInt(data.price),
					total: Number.parseInt(data.price),
					purchase_cost: Number.parseInt(data.purchase_cost),
				});

				this.form_data.SalesOrder.ignores = this.form_data.SalesOrder.items.map((item) => item.id);
				this.calcTotal();
			},

			removeProduct (productId) {
				let productIndex = this.form_data.SalesOrder.items.findIndex((item) => item.id == productId);
				if (productIndex > -1) this.form_data.SalesOrder.items.splice(productIndex, 1);

				this.form_data.SalesOrder.ignores = this.form_data.SalesOrder.items.map((item) => item.id);
				this.calcTotal();
			},

			updateQuantity (item) {
				item.total = item.price * item.quantity;
				this.calcTotal();
			},

			calcTotal () {
				let total = 0;

				this.form_data.SalesOrder.items.forEach(item => {
					total += item.total;
				});

				this.form_data.SalesOrder.total = total;
				this.calcDiscountAmount();
			},

			calcDiscountAmount () {
				let total = this.form_data.SalesOrder.total || 0;
				let discountPercent = this.form_data.SalesOrder.discount_percent || 0;
				let discountAmount = total / 100 * discountPercent;

				this.form_data.SalesOrder.discount_amount = this.formatInputCurrency(discountAmount);
				this.calcTaxAmount();
			},

			calcDiscountPercent () {
				let total = this.form_data.SalesOrder.total || 0;
				let discountAmount = this.parseCurrency(this.form_data.SalesOrder.discount_amount) || 0;
				let discountPercent = (discountAmount / total) * 100;

				this.form_data.SalesOrder.discount_percent = discountPercent;
				this.calcTaxAmount();
			},

			calcTaxAmount () {
				let total = this.form_data.SalesOrder.total || 0;
				let taxPercentValue = this.form_data.SalesOrder.tax_percent;
				let taxMeta = this.meta_data.SalesOrder.picklist_fields.tax.find((single) => single.value == taxPercentValue) || {};
				let taxPercent = taxMeta.text || 0;
				let discountAmount = this.parseCurrency(this.form_data.SalesOrder.discount_amount) || 0;
				let taxAmount = (total -discountAmount) / 100 * taxPercent;

				this.form_data.SalesOrder.tax_amount = this.formatInputCurrency(taxAmount);
				this.calcGrandTotal();
			},

			calcGrandTotal () {
				let total = this.form_data.SalesOrder.total;
				let discountAmount = this.parseCurrency(this.form_data.SalesOrder.discount_amount);
				let taxAmount = this.parseCurrency(this.form_data.SalesOrder.tax_amount);
				let grandTotal = (total - discountAmount + taxAmount) || 0;

				this.form_data.SalesOrder.grand_total = grandTotal;
				setTimeout(() => $('form:visible').valid(), 0);
			},

			submitSalesOrderForLead () {
				if (!$('#salesorder').valid()) return;

				const self = this;
				const formData = Object.assign({}, this.url_params, this.form_data['SalesOrder'],
					{
						name: this.url_params.name,
						bot_name: window._IFRAME_DATA.bot_name,
						access_token: this.url_params.access_token,
						action: 'ChatbotIframeAjax',
						mode: 'saveSalesOrder',
						customer_id: this.customer_data.record_id,
						customer_type: this.customer_data.record_module,
						birthday: this.url_params.baseBirthday,
					}
				);

				formData.discount_amount = this.parseCurrency(formData.discount_amount);
				formData.tax_amount = this.parseCurrency(formData.tax_amount);

				this.overlay = true;

				app.request.post({ url: 'entrypoint.php', data: formData }).then((err, res) => {
					self.overlay = false;

					if (err) {
						return this.$bvToast.toast(err.message, {
							title: app.vtranslate('JS_ERROR'),
							variant: 'danger',
						});
					}

					if (!res) {
						return this.$bvToast.toast(app.vtranslate('JS_THERE_WAS_SOMETHING_ERROR'), {
							title: app.vtranslate('JS_ERROR'),
							variant: 'danger',
						});
					}

					if (res == true) {
						return this.$bvToast.toast(app.vtranslate('CPChatBotIntegration.JS_ERROR_ON_CONVERT_LEAD_PROCESS'), {
							title: app.vtranslate('JS_ERROR'),
							variant: 'danger',
						});
					}

					self.modes.customer = 'detail';
					self.customer_data = res.customer_data;
					self.customer_display = res.customer_display;
					self.loadRelatedList('SalesOrder', true);

					return self.$bvToast.toast(app.vtranslate('JS_SAVE_SUCCESSFULLY'), {
						title: app.vtranslate('JS_SUCCESS'),
						variant: 'success',
					});
				});
			},

			submitSalesOrder () {
				if (!$('#SalesOrder').valid()) return;

				// User have to select at least one item
				if (this.form_data.SalesOrder.items.length == 0) {
					return this.$bvToast.toast(app.vtranslate('CPChatBotIntegration.JS_SELECT_AT_LEAST_ONE_PRODUCT'), {
						title: app.vtranslate('JS_ERROR'),
						variant: 'danger',
					});
				}

				const self = this;
				const formData = Object.assign({}, this.url_params, this.form_data['SalesOrder'],
					{
						name: this.url_params.name,
						bot_name: window._IFRAME_DATA.bot_name,
						access_token: this.url_params.access_token,
						action: 'ChatbotIframeAjax',
						mode: 'saveSalesOrder',
						customer_id: this.customer_data.record_id,
						customer_type: this.customer_data.record_module,
					}
				);

				formData.discount_amount = this.parseCurrency(formData.discount_amount);
				formData.tax_amount = this.parseCurrency(formData.tax_amount);

				this.overlay = true;

				app.request.post({ url: 'entrypoint.php', data: formData }).then((err, res) => {
					self.overlay = false;

					if (err) {
						return this.$bvToast.toast(err.message, {
							title: app.vtranslate('JS_ERROR'),
							variant: 'danger',
						});
					}

					if (!res) {
						return this.$bvToast.toast(app.vtranslate('JS_THERE_WAS_SOMETHING_ERROR'), {
							title: app.vtranslate('JS_ERROR'),
							variant: 'danger',
						});
					}

					self.modes.SalesOrder = 'list';
					self.loadRelatedList('SalesOrder', true);

					return self.$bvToast.toast(app.vtranslate('JS_SAVE_SUCCESSFULLY'), {
						title: app.vtranslate('JS_SUCCESS'),
						variant: 'success',
					});
				});
			},

			submitProduct (type) {
				if (!$(`#${type}`).valid()) return;

				const self = this;
				const params = Object.assign({}, this.url_params,
					{
						name: this.url_params.name,
						bot_name: window._IFRAME_DATA.bot_name,
						access_token: this.url_params.access_token,
						action: 'ChatbotIframeAjax',
						mode: 'saveRelatedProduct',
						customer_type: this.customer_data.record_module,
						customer_id: this.customer_data.record_id,
						target: type,
						product_ids: this.form_data[type].product_ids,
					}
				);

				this.overlay = true;

				app.request.post({ url: 'entrypoint.php', data: params }).then((err, res) => {
					self.overlay = false;

					if (err) {
						return this.$bvToast.toast(err.message, {
							title: app.vtranslate('JS_ERROR'),
							variant: 'danger',
						});
					}

					if (!res) {
						return this.$bvToast.toast(app.vtranslate('JS_THERE_WAS_SOMETHING_ERROR'), {
							title: app.vtranslate('JS_ERROR'),
							variant: 'danger',
						});
					}

					self.modes[type] = 'list';
					self.loadRelatedList(type, true);

					return self.$bvToast.toast(app.vtranslate('JS_SAVE_SUCCESSFULLY'), {
						title: app.vtranslate('JS_SUCCESS'),
						variant: 'success',
					});
				});
			},

			loadLeadRelatedProducts () {
				const self = this;

				const params = Object.assign({}, this.url_params,
					{
						name: this.url_params.name,
						bot_name: window._IFRAME_DATA.bot_name,
						access_token: this.url_params.access_token,
						action: 'ChatbotIframeAjax',
						mode: 'loadLeadRelatedProducts',
						customer_id: this.customer_data.record_id,
					}
				);

				this.overlay = true;

				app.request.post({ url: 'entrypoint.php', data: params }).then((err, res) => {
					self.overlay = false;

					if (err) {
						return this.$bvToast.toast(err.message, {
							title: app.vtranslate('JS_ERROR'),
							variant: 'danger',
						});
					}

					if (!res || !(res instanceof Array)) {
						return this.$bvToast.toast(app.vtranslate('JS_THERE_WAS_SOMETHING_ERROR'), {
							title: app.vtranslate('JS_ERROR'),
							variant: 'danger',
						});
					}

					res.forEach((product) => {
						product.data = product;
						self.selectProduct(product);
					});
				});
			},

			openSalesOrderDetail(salesOrderData) {
				const params = Object.assign({}, this.url_params,
					{
					name: this.url_params.name,
					bot_name: window._IFRAME_DATA.bot_name,
					access_token: this.url_params.access_token,
					view: 'ChatbotIframeSalesOrderDetailPopup',
					record_id: salesOrderData.id,
					salesorder_no: salesOrderData.salesorder_no,
					}
				);

				ChatbotHelper.popupCenter('entrypoint.php?' + $.param(params), 'Chatbot', 800, 480);
			},

			parseCurrency (value) {
				if (!value) return '0';

				let separator = app.getDecimalSeparator();
				let separatorIndex = value.toString().indexOf(separator);
				if (separatorIndex > -1) value = value.slice(0, separatorIndex);


				value = value.toString().replace(/[^0-9]/g, '');
				value = Number.parseInt(value);
				return value;
			},

			formatInputCurrency (number) {
				let parsedNumber = this.parseCurrency(number);
				return this.formatCurrency(parsedNumber);
			},

			formatDiscountAmount (e) {
				e.preventDefault();
				this.form_data.SalesOrder.discount_amount = this.formatInputCurrency(this.form_data.SalesOrder.discount_amount);
			},

			formatTaxAmount (e) {
				e.preventDefault();
				this.form_data.SalesOrder.tax_amount = this.formatInputCurrency(this.form_data.SalesOrder.tax_amount);
			},

			formatCurrency (number) {
				let amount = app.convertCurrencyToUserFormat(number, false);
				let separator = app.getDecimalSeparator();
				let separatorIndex = amount.indexOf(separator);
				if (separatorIndex > -1) amount = amount.slice(0, separatorIndex);

				return amount;
			},

			loadCounters () {
				const requestData = Object.assign({}, this.url_params,
					{
						name: this.url_params.name,
						bot_name: window._IFRAME_DATA.bot_name,
						access_token: this.url_params.access_token,
						action: 'ChatbotIframeAjax',
						mode: 'loadCounters',
						source_record: this.customer_data.record_id,
						source_module: this.customer_data.record_module,
					}
				);

				app.request.post({ url: 'entrypoint.php', data: requestData }).then((err, res) => {

					if (err) {
						return this.$bvToast.toast(err.message, {
							title: app.vtranslate('JS_ERROR'),
							variant: 'danger',
						});
					}

					if (!res) {
						return this.$bvToast.toast(app.vtranslate('JS_THERE_WAS_SOMETHING_ERROR'), {
							title: app.vtranslate('JS_ERROR'),
							variant: 'danger',
						});
					}

					this.counters = res.counters;
				});
			},

			openUploadAvatarModal () {
				this.form_data.Avatar.imagename = this.customer_data.avatar;
				this.$refs.uploadAvatarModal.show()
			},

			updateAvatarPreviewFile (event) {
				const reader = new FileReader();

				// Register event onload
				reader.onload = (event) => {
					this.form_data.Avatar.imagename = event.target.result;
				};

				// Trigger read data process
				reader.readAsDataURL(event.target.files[0]);
			},

			submitAvatar () {
				const self = this;
				const formData = new FormData($('form[name="avatar"]')[0]);
				const imageBlob = formData.get('imagename[]');

				// Validate image
				if (!imageBlob || !imageBlob.size) return;

				// Assign nesessary information
				formData.set('name', this.url_params.name);
				formData.set('access_token', this.url_params.access_token);
				formData.set('bot_name', window._IFRAME_DATA.bot_name);
				formData.set('action', 'ChatbotIframeAjax');
				formData.set('mode', 'saveCustomerAvatar');
				formData.set('customer_id', this.customer_data.record_id);
				formData.set('customer_type', this.customer_data.record_module);

				this.uploading_avatar = true;

				$.ajax('entrypoint.php', {
					cache: false,
					contentType: false,
					processData: false,
					method: 'POST',
					type: 'POST',
					data: formData,
				}).done((res) => {
					self.uploading_avatar = false;

					//  Handle saveing error
					if (res !== true && !res.result) {
						return self.$bvToast.toast(app.vtranslate('JS_THERE_WAS_SOMETHING_ERROR'), {
							title: app.vtranslate('JS_ERROR'),
							variant: 'danger',
						});
					}

					// Reload new image processing from here
					self.customer_data.avatar = res.result.avatar;
					self.customer_display.avatar = res.result.avatar;

					return self.$bvToast.toast(app.vtranslate('CPChatBotIntegration.JS_UPLOAD_IMAGE_SUCCESS'), {
						title: app.vtranslate('JS_SUCCESS'),
						variant: 'success',
					});
				}).fail((jqXHR) => {
					self.uploading_avatar = false;
					return self.$bvToast.toast(jqXHR.message, {
						title: app.vtranslate('JS_ERROR'),
						variant: 'danger',
					});
				});
			},

			getEntryPointUrl (url) {
				let params = app.convertUrlToDataParams(url);
				delete params['index.php'];
				params.name = this.url_params.name;
				params.access_token = this.url_params.access_token;

				return 'entrypoint.php?' + $.param(params);
			},

			calcEndTime (e) {
				let duration = 30;
				let startTime = moment(this.form_data.Calendar.time_start, 'HH:mm');
				let newEndTime = startTime.add(duration, 'minutes')
				
				this.form_data.Calendar.time_end = newEndTime.format('HH:mm');
			},

			calcEndDate (e) {
				if (this.form_data.Calendar.date_start) {
					this.form_data.Calendar.due_date = this.form_data.Calendar.date_start;
				}
			}
		},

		// Event will be triggered when app ready
		mounted() {
			$(this.$el).show();

			if (this.customer_data.record_id && this.customer_data.record_module) {
				this.loadCounters();
			}
		}
	});
})();