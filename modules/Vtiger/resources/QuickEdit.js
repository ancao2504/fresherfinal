/*
	QuickEdit.js
	Author: Vu Mai
	Date: 2022-09-27
	Purpose: to handle logic on the UI
*/

class BaseQuickEdit {
	
	constructor () {
		this.form;
	}

	registerEvent (form) {
		this.form = form;

		// Init form field
		vtUtils.applyFieldElementsView(form);
		Vtiger_Edit_Js.getInstanceByModuleName('Vtiger').registerBasicEvents(form);
		CustomOwnerField.initCustomOwnerFields(form.find('input[name="assigned_user_id"]'));

		let moduleName = form.find('input[name="module"]').val();

		if (moduleName == 'Documents') {
			form.documentsJS = new Documents_Index_Js();
			form.documentsJS.registerFileHandlingEvents(form);
			
			let noteContentInput =  form.find('#Documents_editView_fieldName_notecontent');

			if (noteContentInput.length > 0) {
				form.documentsJS.applyEditor (
					noteContentInput.attr('id', 'Documents_editView_fieldName_notecontent_popup')
				);
			}
		}

		// Register event for button go to full edit form
		form.on('click', '.btn-show-full-form' , function (e) {
			var editViewUrl = jQuery(e.currentTarget).data('href');
			window.open(editViewUrl);
		});
	}

	registerSubmitEvent (submitCallBack = null, saveCallBack = null) {
		let self = this;

		self.form.vtValidate({
			submitHandler: function () {
				if (typeof submitCallBack == 'function') {
					submitCallBack();
				}

				var noteContentElement = self.form.find('#Documents_editView_fieldName_notecontent_popup');
						
				if (noteContentElement.length) {
					var noteContent = CKEDITOR.instances.Documents_editView_fieldName_notecontent_popup.getData()
					noteContentElement.val(noteContent);
				}
				
				var formData = self.form.serializeFormData();

				if (formData.record) {
					formData.id = formData.record;
				}

				let moduleName = self.form.find('input[name="module"]').val();
				let documentType = self.form.find('input[name="document_type"]').val();
		
				if (moduleName == 'Documents' && documentType == 'I') {
					let file = self.form.documentsJS.getFile();

					if (file) {
						var extraData = {};
						extraData['notes_title'] = self.form.find('[name="notes_title"]').val();

						self.form.documentsJS._upload(self.form, extraData).then(function(data) {
							app.helper.showSuccessNotification({
								'message' : app.vtranslate('JS_UPLOAD_SUCCESSFUL')
							});

							app.helper.hideModal();
						},
						function(e) {
							app.helper.showErrorNotification({'message' : app.vtranslate('JS_UPLOAD_FAILED')});
						});

						return;
					}
				}	

				// Format data form Form Data
				if ($.inArray(formData['module'], _INVENTORY_MODULES) != -1) {
					formData = self.formatInventoryDataFromFormData(formData);
				}

				app.helper.showProgress();

				app.request.post({ data: formData })
				.then(function (err, data) {
					app.helper.hideProgress();
					
					if (typeof saveCallBack == 'function') {
						saveCallBack(err, data);
					}

					if (err) {
						app.helper.showErrorNotification({ message: err.message });
						return;
					}

					//Auto close modal
					if (self.form.closest('.modal')[0] != null) {
						self.form.closest('.modal').find('.cancelLink').trigger('click');
					}

					if (formData.id) {
						app.helper.showSuccessNotification({ message: app.vtranslate('JS_RECORD_UPDATED') });
					}
					else {
						app.helper.showSuccessNotification({ message: app.vtranslate('JS_RECORD_CREATED') });

						// Assign id to field record after create
						if (data) {
							self.form.find('input[name="record"]').val(data).attr('value', data);
							self.id = data;
						}

						// Update content button from create to update
						self.form.find('button[name="saveButton"]').html(BUTTON_UPDATE_LABEL);

						// Update href for btn go to full form
						let ediViewUrl = self.form.find('.btn-show-full-form').attr('data-href');
						ediViewUrl += `&record=${data}`;
						self.form.find('.btn-show-full-form').attr('data-href', ediViewUrl);
					}

					self.form.find('button[name="saveButton"]').removeAttr('disabled');
				});
			}
		});
	}
}

class InventoryQuickEdit extends BaseQuickEdit {

	form = null;
	type = '';	// Ex: SalesOrder, Invoice, ...
	id = '';
	customerId = '';
	customerType = '';
	data = {
		'items': [],
		'total': 0,
		'discount_type':  'zero',
		'discount_amount': 0,
		'discount_percent': 0,
		'discount_total':  0,
		'charge_info': [],
		'charge_total': 0,
		'pre_tax_total': 0,
		'tax_type': 'group',
		'taxes': [],
		'tax_total': 0,
		'taxes_charge_total': 0,
		'adjustment_total': 0,
		'grand_total': 0
	};
	statusFieldName = '';

	registerEvent (form) {
		let self = this;
		this.form = form;
		this.type = form.find('input[name="module"]').val();
		this.id = form.find('input[name="record"]').val();
		this.customerId = form.find('input[name="customer_id"]').val();
		this.customerType = form.find('input[name="customer_type"]').val();
		this.statusFieldName = form.find('input[name="status_field_name"]').val();

		// Init form field
		vtUtils.applyFieldElementsView(form);
		Vtiger_Edit_Js.getInstanceByModuleName('Vtiger').registerBasicEvents(form);
		form.find('.custom-select').select2();

		// Init field select item
		form.find('.select-item').each(function () { 
			self.initSelectItemField($(this));
		});

		// Init value
		let item = this.form.find('input[name="line_items"]').val();
		let taxes = this.form.find('input[name="taxes"]').val();
		this.data.items = item != '' ? jQuery.parseJSON(item) : [];
		this.data.total = this.form.find('input[name="total"]').val();
		this.data.discount_type = this.form.find('input[name="final_discount_type"]').val();
		this.data.discount_amount = this.form.find('input[name="final_discount_amount"]').val();
		this.data.discount_percent = this.form.find('input[name="final_discount_percent"]').val();
		this.data.discount_total = this.form.find('input[name="final_discount_total"]').val();
		this.data.charge_total = this.form.find('input[name="charges"]').val();
		this.data.taxes = taxes != '' ? jQuery.parseJSON(taxes) : [];
		this.data.pre_tax_total = this.form.find('input[name="pre_tax_total"]').val();
		this.data.tax_total = this.form.find('input[name="tax_total"]').val();
		this.data.taxes_charge_total = this.form.find('input[name="taxes_charge_total"]').val();
		this.data.adjustment_total = this.form.find('input[name="adjustment_total"]').val();
		this.data.grand_total = this.form.find('input[name="grand_total"]').val();

		// Trigger togger no item msg
		this.toggleNoItemMessage();

		// Register event change tax type
		form.find('.tax-type').on('change.select2', function () {
			let type = form.find('select[name="tax_type"]').val();
			self.changeTaxType(type);
		});

		// Trigger event change tax type
		form.find('.tax-type').trigger('change.select2');

		// Register event remove item
		form.on('click', '.remove-item', function () {
			self.removeItem($(this).closest('tr').attr('data-id'));
			$(this).closest('tr').remove();
		});

		// Register event quantity change
		form.on('change', '.quantity-input', function () {
			self.reCalculateItemAmount(this);
		});

		// Register event item price change
		form.on('change', '.item-price', function () {
			self.reCalculateItemAmount(this);
		});

		// Regiter event item price input
		form.on('input', '.item-price', function () {
			$(this).val(self.formatInputCurrency($(this).val()));
		});

		// Regiter event discount amount input
		form.on('input', '.discount-amount', function () {
			$(this).val(self.formatInputCurrency($(this).val()));
		});

		// Regiter event discount amount change
		form.find('.tax-percent').on('change.select2', function () {
			self.data.tax_percent = Number.parseInt($(this).select2('data').text);
			self.calcTaxAmount();
		});

		// Register event show item discount popup
		form.on('click', '.individual-discount', function () {
			self.showItemDiscountPopup($(this));
		});

		// Register event show item tax popup
		form.on('click', '.individual-tax', function () {
			self.showItemTaxPopup($(this));
		});

		// Register event show final discount popup
		form.on('click', '.final-discount', function () {
			self.showFinalDiscountPopup($(this));
		});

		// Register event show final charges popup
		form.on('click', '.final-charges', function () {
			self.showFinalChargesPopup($(this));
		});

		// Register event show final tax popup
		form.on('click', '.group-tax', function () {
			self.showFinalTaxPopup($(this));
		});

		// Register event show final tax charge popup
		form.on('click', '.final-taxes-charges', function () {
			self.showFinalTaxChargePopup($(this));
		});

		// Register event show final tax charge popup
		form.on('change', 'input[name="adjustment_type"]', function () {
			self.calcAdjustmentTotal();
		});

		// Register event show final tax charge popup
		form.on('focusout', '.adjustment-total', function () {
			self.form.find('input[name="adjustment_total"]').attr('value', $(this).val());
			self.calcAdjustmentTotal();
		});

		// Regiter event go to full form
		form.on('click', '.btn-show-full-form' , function () {
			var editViewUrl = $(this).data('href');
			window.open(editViewUrl);
		});
	}

	initSelectItemField (element) {
		let self = this;

		element.select2({
			placeholder: app.vtranslate('JS_TYPE_TO_SEARCH'),
			minimumInputLength: _VALIDATION_CONFIG.autocomplete_min_length,
			ajax: {
				type: 'POST',
				dataType: 'json',
				cache: true,
				data: function (term, page) {
					term = term.trim();

					if (term.length == 0) {
						userSelector.select2('close');
						userSelector.select2('open');
						return null;
					}

					var data = {
						module: 'Vtiger',
						action: 'InventoryQuickEditAjax',
						mode: 'searchItems',
						keyword: term,
						ignore_ids: self.data.items.map((item) => item.id),
					};

					return data;
				},
				results: function (data) {
					return { results: data.result.map((single) => ({ id: single.id, text: single.label, data: single })) };
				},
				transport: function (params) {
					return jQuery.ajax(params);
				},
			},
		});

		// Init events
		$(element).on('change.select2', (e) => {
			self.selectItems($(element).select2('data'));
			$(element).select2('data', '');
		});
	}

	changeTaxType (type) {
		let self = this;
		self.data.tax_type = type;

		if (self.data.items == [null]) {
			return;
		}

		if (type == 'individual') {
			self.form.find('.item-tax-container').show();
			self.form.find('.group-tax-container').hide();

			// Calculate tax total for each item tax
			self.form.find('tbody .item-tax-container .individual-tax').each(function () {
				let lineItemRow = $(this).closest('tr');
				let itemIndex = self.data.items.findIndex((item) => item.id == lineItemRow.attr('data-id'));

				// Trigger calculate item tax total
				self.calcItemTaxTotal (self.data.items[itemIndex], $(this));
			});
		}

		if (type == 'group') {
			self.form.find('.item-tax-container').hide();
			self.form.find('.group-tax-container').show();

			// Recalculate total amout
			self.form.find('tbody .item-tax-container .individual-tax').each(function () {
				let lineItemRow = $(this).closest('tr');

				let itemIndex = self.data.items.findIndex((item) => item.id == lineItemRow.attr('data-id'));
				self.data.items[itemIndex].net_price = Number.parseInt(self.data.items[itemIndex].total_after_discount);

				// Trigger calculate total amount
				self.calcTotalAmount();
			});
		}
	}

	selectItems (inputData) {
		const data = inputData.data;

		// Added item selected to array
		this.data.items.push({
			id: data.id,
			module: data.module,
			item_no: data.item_no,
			label: data.label,
			quantity: 1,
			price: Number.parseInt(data.price),
			total: Number.parseInt(data.price),
			discount_type:  'zero',
			discount_percent:  0,
			discount_amount:  0,
			discount_total:  0,
			total_after_discount: Number.parseInt(data.price),
			tax_total: 0,
			net_price: Number.parseInt(data.price),
			purchase_cost: Number.parseInt(data.purchase_cost),
		});

		let itemIndex = this.data.items.findIndex((item) => item.id == data.id);

		// Append item selected to table list 
		this.insertItem(this.data.items[itemIndex]);
	}

	insertItem (data) {
		let item = this.form.find('#item-list #template').clone();
		item.find('tr').attr('data-id', data.id);
		item.find('.inline-product-name').text(data.label);
		item.find('input[name="item_price"]').attr('value', app.convertCurrencyToUserFormat(data.price, false));
		item.find('.total-value').html(app.convertCurrencyToUserFormat(data.price, false));
		item.find('.item-total-after-discount').html(app.convertCurrencyToUserFormat(data.price, false));
		this.form.find('#item-list tbody#line-items').append(item.html());

		// Trigger event toggle no item msg
		this.toggleNoItemMessage();

		let lineItemRow = this.form.find(`#item-list tbody#line-items tr[data-id="${data.id}"] .price`);

		if (this.data.tax_type == 'group') {
			// Trigger calculate total amount
			this.calcTotalAmount();
		}
		else {
			// Trigger calculate item tax total
			this.calcItemTaxTotal(data, lineItemRow);
		}

		vtUtils.enableTooltips();
	}

	removeItem (itemId) {
		let itemIndex = this.data.items.findIndex((item) => item.id == itemId);

		if (itemIndex > -1) {
			this.data.items.splice(itemIndex, 1);
			this.calcTotalAmount();
		}

		this.toggleNoItemMessage();
	}

	toggleNoItemMessage () {
		let self = this;

		if (self.data.items.length > 0) {
			this.form.find('table tbody .no-item-msg').hide();
		}
		else {
			this.form.find('table tbody .no-item-msg').show();
		}
	}

	reCalculateItemAmount (target) {
		let itemId = $(target).closest('tr').attr('data-id');
		let targetName = $(target).attr('name');
		let itemIndex = this.data.items.findIndex((item) => item.id == itemId);
		let totalPrice = 0;

		// Update quantity or price and total price of item in array
		if (itemIndex > -1) {
			if (targetName == 'quantity') {
				this.data.items[itemIndex].quantity = $(target).val();
			}
			else {
				this.data.items[itemIndex].price = this.parseCurrency($(target).val());
			}

			totalPrice = this.data.items[itemIndex].quantity * this.data.items[itemIndex].price;
			this.data.items[itemIndex].total = Number.parseInt(totalPrice);

			// Trigger calculate discount total of each item
			this.calcItemDiscountTotal(this.data.items[itemIndex], target);

			// Change total price of each item in UI and discountUI popover title
			$(target).closest('tr').find('.total-value').html(app.convertCurrencyToUserFormat(totalPrice, false));
			$(target).closest('tr').find('.discountUI .sub-total-val').html(app.convertCurrencyToUserFormat(totalPrice, false));
		}
	}

	triggerOptionDiscountChangeEvent (discountDiv) {
		let selectedDiscountType = discountDiv.find('input.discounts').filter(':checked');
		let discountType = selectedDiscountType.data('discountType');

		let rowAmountField = jQuery('input.discount-amount', discountDiv);
		let rowPercentageField = jQuery('input.discount-percentage', discountDiv);

		rowAmountField.hide();
		rowPercentageField.hide();

		if (discountType == Inventory_Edit_Js.percentageDiscountType) {
			rowPercentageField.show().removeClass('hide').focus();
		} 
		else if (discountType == Inventory_Edit_Js.directAmountDiscountType) {
			rowAmountField.show().removeClass('hide').focus();
		}
	};

	showItemDiscountPopup (element) {
		let self = this;
		element.popover('destroy');
		let lineItemRow = element.closest('tr');
		self.form.find('.popover.lineItemPopover.discount-form').css('opacity', 0).css('z-index', '-1');

		let callBackFunction = function (element, data) {
			let discountDiv = jQuery('div.discountUI', data);
			// Trigger discount item change
			self.triggerOptionDiscountChangeEvent(discountDiv);

			data.on('change', '.discounts', function (e) {
				let target = jQuery(e.currentTarget);
				let discountDiv = target.closest('div.discountUI');
				// Trigger discount item change
				self.triggerOptionDiscountChangeEvent(discountDiv);
			});

			// Handle click save button event on popup item discount
			data.find('.popoverButton').on('click', function (e) {
				let validate = data.find('input').valid();

				if (validate) {
					let selectedDiscountType = discountDiv.find('input.discounts').filter(':checked');
					let discountType = selectedDiscountType.data('discountType');
					let discountRow = selectedDiscountType.closest('tr');
					let discountValue = app.unformatCurrencyToUser(discountRow.find('.discount-value').val());

					if (discountValue == "" || isNaN(discountValue) || discountValue < 0) {
						discountValue = 0;
					}

					let discountDivId = discountDiv.attr('id');
					let oldDiscountDiv = $('#' + discountDivId, lineItemRow);
					discountValue = app.convertCurrencyToUserFormat(discountValue, false);

					// Update discount type and discount total value for hidden input of item
					lineItemRow.find('input[name="item_discount_type"]').val(discountType);
					lineItemRow.find('input[name="item_discount_total"]').val(discountValue);

					// Update discount type and discount total value of item in line items
					let itemIndex = self.data.items.findIndex((item) => item.id == lineItemRow.attr('data-id'));
					self.data.items[itemIndex].discount_type = discountType;
					
					if (discountType == Inventory_Edit_Js.percentageDiscountType) {
						self.data.items[itemIndex].discount_percent = discountValue;
						self.data.items[itemIndex].discount_amount = 0;
						$('input.discount-percentage', oldDiscountDiv).val(discountValue);
					} 
					else if (discountType == Inventory_Edit_Js.directAmountDiscountType) {
						self.data.items[itemIndex].discount_amount = self.parseCurrency(discountValue);
						self.data.items[itemIndex].discount_percent = 0;
						$('input.discount-amount', oldDiscountDiv).val(discountValue);
					}

					element.popover('destroy');

					// Trigger calculate discount total of each item 
					self.calcItemDiscountTotal(self.data.items[itemIndex], element);
				}
			});

			// Register cancel popup event
			data.find('.popoverCancel').on('click', function (e) {
				self.form.find('div[id^=qtip-]').qtip('destroy');
				element.popover('destroy');
			});
		}

		let parentElem = element.closest('td');
		let discountUI = parentElem.find('div.discountUI').clone(true, true).removeClass('hide').addClass('show');
		let discountType = discountUI.find('input[name="item_discount_type"]').val();

		// Checked input discount type before popup show
		discountUI.find(`input[data-discount-type="${discountType}"]`).prop("checked", true);
		
		let template = jQuery(Inventory_Edit_Js.lineItemPopOverTemplate);
		template.addClass('discount-form');
		let popOverTitle = discountUI.find('.popover-title').text();

		element.popover({
			'content': discountUI,
			'html': true,
			'placement': 'bottom',
			'title': popOverTitle,
			'animation': true,
			'trigger': 'manual',
			'template': template,
			'container': lineItemRow

		});

		// Handle event show popup
		element.one('shown.bs.popover', function (e) {
			callBackFunction(element, jQuery('.discount-form'));

			if (element.next('.popover').find('.popover-content').height() > 250) {
				app.helper.showScroll(element.next('.popover').find('.popover-content'), {'height': '250px'});
			}
		})

		element.popover('toggle');
	}

	calcItemDiscountTotal (item, target) {
		let total = Number.parseInt(item.total);

		if (item.discount_type == 'percentage') {
			item.discount_total = Number.parseInt((item.discount_percent / 100) * total);
			item.total_after_discount = total - item.discount_total;
			$(target).closest('tr').find('.item_discount').html(`(${item.discount_percent}%)`);
		}

		if (item.discount_type == 'amount') {
			item.discount_total = Number.parseInt(item.discount_amount);
			item.total_after_discount = total - item.discount_total;
			$(target).closest('tr').find('.item_discount').html(`(${app.convertCurrencyToUserFormat(item.discount_amount, false)})`);
		}

		if (item.discount_type == 'zero') {
			item.discount_total = 0;
			item.total_after_discount = total;
			$(target).closest('tr').find('.item_discount').html('0');
		} 

		// Change value in UI
		$(target).closest('tr').find('.item-discount-total').html(app.convertCurrencyToUserFormat(item.discount_total, false));
		$(target).closest('tr').find('.item-total-after-discount').html(app.convertCurrencyToUserFormat(item.total_after_discount, false));

		if (this.data.tax_type == 'group') {
			item.net_price = Number.parseInt(item.total_after_discount);
			// Trigger calculate total amount
			this.calcTotalAmount();
		}
		else {
			// Trigger calculate item tax total
			this.calcItemTaxTotal(item, target);
		}
	}

	showItemTaxPopup (element) {
		let self = this;
		element.popover('destroy');
		let lineItemRow = element.closest('tr');
		let itemIndex = self.data.items.findIndex((item) => item.id == lineItemRow.attr('data-id'));
		self.form.find('.popover.lineItemPopover.individual-tax-form').css('opacity', 0).css('z-index', '-1');

		let callBackFunction = function (element, data) {
			// Handle event change tax percent in popup
			data.on('focusout', '.tax-percentage', function(e) {
				let currentTaxElement = $(e.currentTarget);
				let individualTaxTotal = 0;

				if (currentTaxElement.valid()) {
					let individualTaxPercentage = app.unformatCurrencyToUser(currentTaxElement.val());

					if (individualTaxPercentage == '') {
						individualTaxPercentage = 0;
					}

					if (isNaN(individualTaxPercentage)) {
						individualTaxTotal = 0;
					} 
					else {
						individualTaxPercentage = parseFloat(individualTaxPercentage);
						individualTaxTotal = Math.abs(individualTaxPercentage * self.data.items[itemIndex].total_after_discount) / 100;
						individualTaxTotal = individualTaxTotal.toFixed(self.numOfCurrencyDecimals);
					}

					currentTaxElement.closest('tr').find('.tax-total').val(app.convertCurrencyToUserFormat(individualTaxTotal, false));
				}
			});

			// Calculate tax total 
			data.find('.popoverButton').on('click', function(e) {
				let validate = data.find('input').valid();

				if (validate) {
					element.closest('tr').popover('destroy');
					self.calcItemTaxTotal (self.data.items[itemIndex], element);
				}
			});

			// Cancel item tax popup
			data.find('.popoverCancel').on('click', function(e) {
				self.form.find('div[id^=qtip-]').qtip('destroy');
				element.closest('tr').popover('destroy');
			});
		};

		let parentElem = element.closest('td');
		let taxUI = parentElem.find('div.taxUI').clone(true, true).removeClass('hide').addClass('show');
		taxUI.find('div.individualTaxDiv').removeClass('hide').addClass('show');
		let template = $(Inventory_Edit_Js.lineItemPopOverTemplate);
		template.addClass('individual-tax-form');
		let popOverTitle = taxUI.find('.popover-title').text();
		
		element.closest('tr').popover({
			'content' : taxUI,
			'html' : true,
			'placement' : 'top',
			'title': popOverTitle,
			'animation' : true,
			'trigger' : 'manual',
			'template' : template,
			'container' : lineItemRow
			
		});

		element.closest('tr').one('shown.bs.popover', function(e) {
			callBackFunction(element, jQuery('.individual-tax-form'));

			if(element.closest('tr').next('.popover').find('.popover-content').height() > 250) {
				element.closest('tr').next('.popover').find('.popover-content').addClass('fancyScrollbar');
				element.closest('tr').next('.popover').find('.popover-content').attr('style', 'max-height: 200px;overflow-y: scroll;overflow-x: hidden;');
			}
		})

		element.closest('tr').popover('toggle');
	}

	calculateTaxForItem (lineItemRow, item) { 
		var self = this;
		var totalAfterDiscount = item.total_after_discount;
		var taxPercentages = $('.individual-tax-form .tax-percentage', lineItemRow);

		// Get element from taxUI in table if item popup is not exist
		if (taxPercentages.length == 0) {
			taxPercentages = $('.taxDivContainer .taxUI .tax-percentage', lineItemRow);
		}

		// Update tax percent and tax amount value for each tax in table and popup
		jQuery.each(taxPercentages, function (index, domElement) {
			var taxPercentage = $(domElement);
			var individualTaxRow = taxPercentage.closest('tr');
			var individualTaxPercentage = app.unformatCurrencyToUser(taxPercentage.val());

			if (individualTaxPercentage == '') {
				individualTaxPercentage = 0;
			}

			if (isNaN(individualTaxPercentage)) {
				var individualTaxTotal = 0;
			} 
			else {
				var individualTaxPercentage = parseFloat(individualTaxPercentage);
				var individualTaxTotal = Math.abs(individualTaxPercentage * totalAfterDiscount) / 100;
				individualTaxTotal = individualTaxTotal.toFixed(self.numOfCurrencyDecimals);
			}

			individualTaxRow.find('.tax-total').val(app.convertCurrencyToUserFormat(individualTaxTotal, false));
			individualTaxRow.find('.tax-total').attr('value', app.convertCurrencyToUserFormat(individualTaxTotal, false));

			var taxIdAttr = taxPercentage.attr('id');
			var taxElement = $(lineItemRow).find('.taxUI').find('#' + taxIdAttr);
			taxElement.attr('value', taxPercentage.val());
			taxElement.closest('tr').find('.tax-total').attr('value', app.convertCurrencyToUserFormat(individualTaxTotal));

			// Update item tax detail in array line item
			let taxName = taxPercentage.attr('data-name');

			if (item.taxes == undefined) {
				item.taxes = [];
				item.taxes.push({
					taxname: taxName,
					percentage: individualTaxPercentage,
					amount: individualTaxTotal,
				});
			}
			else {
				let taxIndex = item.taxes.findIndex((item) => item.taxname == taxName);

				if (taxIndex == -1) {
					item.taxes.push({
						taxname: taxName,
						percentage: individualTaxPercentage,
						amount: individualTaxTotal,
					});
				}
				else {
					item.taxes[taxIndex].percentage = individualTaxPercentage;
					item.taxes[taxIndex].amount = individualTaxTotal;
				}
			}
		});

		// Calculation compound taxes
		var taxTotal = 0;
		var taxPercentages = $('.taxDivContainer .taxUI .tax-percentage', lineItemRow);

		jQuery.each(taxPercentages, function(index, domElement) {
			var taxElement = $(domElement);
			var taxRow = taxElement.closest('tr');
			var total = self.parseCurrency($('.tax-total', taxRow).val());
			var compoundOn = taxElement.data('compoundOn');

			if (compoundOn) {
				var amount = parseFloat(totalAfterDiscount);

				jQuery.each(compoundOn, function(index, id) {
					if (!isNaN($('.tax-total' + id, lineItemRow).val())) {
						amount = parseFloat(amount) + parseFloat(app.unformatCurrencyToUser(jQuery('.tax-total' + id, lineItemRow).val()));
					}
				});

				if (isNaN(taxElement.val())) {
					var total = 0;
				} 
				else {
					var total = Math.abs(amount * app.unformatCurrencyToUser(taxElement.val())) / 100;
				}

				taxRow.find('.tax-total').val(app.convertCurrencyToUserFormat(total, false));
				taxRow.find('.tax-total').attr('value', app.convertCurrencyToUserFormat(total, false));
			}

			taxTotal += parseFloat(total);
		});

		item.tax_total = taxTotal;
	}

	calcItemTaxTotal (item, target) {
		let lineItemRow = target.closest('tr');
		this.calculateTaxForItem(lineItemRow, item);

		// Update item net price
		item.net_price = Number.parseInt(item.total_after_discount) + Number.parseInt(item.tax_total);

		// Change value in UI
		$(target).closest('tr').find('.item-tax-total').html(app.convertCurrencyToUserFormat(item.tax_total, false));
		$(target).closest('tr').find('.taxUI .sub-total-val').html(app.convertCurrencyToUserFormat(item.total_after_discount, false));

		// Trigger calculate total amount
		this.calcTotalAmount();
	}

	calcTotalAmount () {
		let self = this;
		let total = 0;

		self.data.items.forEach(item => {
			total += Number.parseInt(item.net_price);
		});

		self.form.find('.temp-price label').html(app.convertCurrencyToUserFormat(total, false));
		self.form.find('.total h5').html(app.convertCurrencyToUserFormat(total, false));
		self.data.total = total;

		// Change total price in finalDiscountUI popover title
		self.form.find('#final-discount-ui .popover-title .sub-total-val').html(app.convertCurrencyToUserFormat(total, false));

		// Trigger calculate final discount amount
		self.calcFinalDiscountAmount();
	}

	showFinalDiscountPopup (element) {
		var self = this;
		var finalDiscountUI = self.form.find('#final-discount-ui').clone(true, true).removeClass('hide');
		var popOverTemplate = $(Inventory_Edit_Js.lineItemPopOverTemplate);
		popOverTemplate.addClass('final-discount-form');
		finalDiscountUI.addClass('final-discount-popover-ui');
		var popOverTitle = finalDiscountUI.find('.popover-title').text();

		element.closest('.final-discount-container').popover({
			'content' : finalDiscountUI,
			'html' : true,
			'placement' : 'top',
			'title': popOverTitle,
			'animation' : true,
			'trigger' : 'manual',
			'template' : popOverTemplate
		});

		element.closest('.final-discount-container').on('shown.bs.popover', function() {
				if ($(element).closest('.final-discount-container').next('.popover').find('.popover-content').height() > 300) {
					app.helper.showScroll($(element).closest('.final-discount-container').next('.popover').find('.popover-content'), {'height': '300px'});
				}

				var finalDiscountUI = $('.final-discount-popover-ui');
				var finalDiscountPopOver = finalDiscountUI.closest('.popover');

				finalDiscountPopOver.on('change', '.discounts', function (e) {
					var target = $(e.currentTarget);
					var discountDiv = target.closest('div.finalDiscountUI');

					// Trigger discount item change
					self.triggerOptionDiscountChangeEvent(discountDiv);
				});

				finalDiscountPopOver.find('.popoverButton').on('click', function (e) {
					var discountDiv = $('div.finalDiscountUI', '.final-discount-form');
					var validate = finalDiscountUI.find('input').valid();

					if (validate) {
						var selectedDiscountType = discountDiv.find('input.discounts').filter(':checked');
						var discountType = selectedDiscountType.data('discountType');
						var discountRow = selectedDiscountType.closest('tr');

						var discountValue = app.unformatCurrencyToUser(discountRow.find('.discount-value').val());

						if (discountValue == '' || isNaN(discountValue) || discountValue < 0) {
							discountValue = 0;
						}

						// Update final discount type value
						self.data.discount_type = discountType;
						
						if (discountType == Inventory_Edit_Js.percentageDiscountType) {
							self.data.discount_percent = discountValue;
							self.data.discount_amount = 0;
							$('div.finalDiscountUI').find('.discount-percentage').val(discountValue);
						} 
						else if (discountType == Inventory_Edit_Js.directAmountDiscountType) {
							self.data.discount_amount = self.parseCurrency(discountValue);
							self.data.discount_percent = 0;
							$('div.finalDiscountUI').find('.discount-amount').val(app.convertCurrencyToUserFormat(discountValue, false));
						}

						element.closest('.final-discount-container').popover('destroy');
						
						// Trigger calculate item discount amount 
						self.calcFinalDiscountAmount();
					}
				});

				// Checked input discount type before popup show
				finalDiscountUI.find(`input[data-discount-type="${self.data.discount_type}"]`).prop('checked', true);
				finalDiscountUI.find(`input[data-discount-type="${self.data.discount_type}"]`).trigger('change');

				// Register cancel popup event
				finalDiscountPopOver.find('.popoverCancel').on('click', function (e) {
					self.form.find('div[id^=qtip-]').qtip('destroy');
					element.closest('.final-discount-container').popover('destroy');
				});
		});

		element.closest('.final-discount-container').popover('toggle');
	}

	calcFinalDiscountAmount () {
		let total = Number.parseInt(this.data.total) || 0;
		
		if (this.data.discount_type == 'percentage') {
			this.data.discount_total = Number.parseInt((this.data.discount_percent / 100) * total);
			this.data.pre_tax_total = total - this.data.discount_total;

			this.form.find('.discount-detail').html(`(${this.data.discount_percent}%)`);
		}

		if (this.data.discount_type == 'amount') {
			this.data.discount_total = Number.parseInt(this.data.discount_amount);
			this.data.pre_tax_total = total - this.data.discount_total;

			this.form.find('.discount-detail').html(`(${app.convertCurrencyToUserFormat(this.data.discount_amount, false)})`);
		}

		if (this.data.discount_type == 'zero') {
			this.data.discount_total = 0;
			this.data.pre_tax_total = total;
			this.form.find('.discount-detail').html('(0)');
		} 

		// Change value in UI
		this.form.find('.discount-total').html(app.convertCurrencyToUserFormat(this.data.discount_total, false));

		// Trigger calculate charges amount
		this.calcChargesTotal();
	}

	showFinalChargesPopup (element) {
		var self = this;
		var chargesUI = element.closest('.final-charge-container').find('#charges-block').clone(true, true).removeClass('hide');
		var popOverTemplate = $(Inventory_Edit_Js.lineItemPopOverTemplate);
		popOverTemplate.addClass('final-charges-form');
		chargesUI.addClass('final-charges-popover-ui');
		var popOverTitle = element.closest('.final-charge-container').find('.final-charges').text();

		element.closest('.final-charge-container').popover({
			'content' : chargesUI,
			'html' : true,
			'placement' : 'top',
			'title': popOverTitle,
			'animation' : true,
			'trigger' : 'manual',
			'template' : popOverTemplate
		});

		element.closest('.final-charge-container').on('shown.bs.popover', function() {
			if ($(element).closest('.final-charge-container').next('.popover').find('.popover-content').height() > 300) {
				(element).closest('.final-charge-container').next('.popover').find('.popover-content').addClass('fancyScrollbar');
				(element).closest('.final-charge-container').next('.popover').find('.popover-content').attr('style', 'max-height: 300px;overflow-y: scroll;overflow-x: hidden;');
			}
			
			var chargesUI = $('.final-charges-popover-ui');
			var finalChargesPopOver = chargesUI.closest('.popover');

			finalChargesPopOver.on('focusout', '.charge-percent', function(e) {
				var element = $(e.currentTarget);

				if (element.closest('form').valid()) {
					var amount = Number.parseInt(self.data.total - self.data.discount_total);
		
					if (isNaN(element.val())) {
						var value = 0;
					} 
					else {
						var value = Math.abs(amount * element.val()) / 100;
					}

					element.closest('tr').find('.charge-value').val(app.convertCurrencyToUserFormat(value, false));
				}
			});
			
			finalChargesPopOver.find('.popoverButton').on('click', function (e) {
				var validate = chargesUI.find('input').valid();

				if (validate) {
					element.closest('.final-charge-container').popover('destroy');

					// Trigger calculate charges total 
					self.calcChargesTotal();
				}
			});

			// Register cancel popup event
			finalChargesPopOver.find('.popoverCancel').on('click', function (e) {
				self.form.find('div[id^=qtip-]').qtip('destroy');
				element.closest('.final-charge-container').popover('destroy');
			});
		});

		element.closest('.final-charge-container').popover('toggle');
	}

	calcChargesTotal () {
		var self = this;
		var amount = self.data.total - self.data.discount_total;
		var chargesPercents = $('.final-charges-form .charge-value', self.form);

		// Get element from charges block in table if item popup is not exist
		if (chargesPercents.length == 0) {
			chargesPercents = $('.charges-block-container .charges-block .charge-value', self.form);
		}

		jQuery.each(chargesPercents, function (index, domElement) {
			var chargeValueInput = $(domElement);
			var chargeValue = app.unformatCurrencyToUser($(domElement).val());

			if ($(domElement).closest('tr').find('.charge-percent')[0] != null) {
				var chargesPercentInput = $(domElement).closest('tr').find('.charge-percent');
				var chargesPercent = app.unformatCurrencyToUser(chargesPercentInput.val());

				if (chargesPercent == '') {
					chargesPercent = 0;
				}

				if (isNaN(chargesPercent)) {
					chargeValue = 0;
				} 
				else {
					chargesPercent = parseFloat(chargesPercent);
					chargeValue = Math.abs(chargesPercent * amount) / 100;
					chargeValue = chargeValue.toFixed(self.numOfCurrencyDecimals);
				}

				self.form.find(`input[name="${chargesPercentInput.attr('name')}"]`).val(chargesPercent);
				self.form.find(`input[name="${chargesPercentInput.attr('name')}"]`).attr('value', chargesPercent);
			}

			self.form.find(`input[name="${chargeValueInput.attr('name')}"]`).val(app.convertCurrencyToUserFormat(chargeValue, false));
			self.form.find(`input[name="${chargeValueInput.attr('name')}"]`).attr('value', app.convertCurrencyToUserFormat(chargeValue, false));
		});

		var chargesTotal = 0;

		self.form.find('.charges-block-container .charges-block .charge-value').each(function(index, domElement){
			// Unformat data Currency To User Format
			var chargeElementValue = app.unformatCurrencyToUser($(domElement).val());

			if (!chargeElementValue) {
				$(domElement).val(0);
				chargeElementValue = 0;
			}

			var chargeId = $(domElement).attr('data-charge-id');
			var chargeIndex = self.data.charge_info.findIndex((item) => item.charge_id == chargeId);

			if ($(domElement).closest('tr').find('.charge-percent')[0] != null) {
				if (chargeIndex == -1) {
					self.data.charge_info.push({
						'charge_id': chargeId,
						'percent': $(domElement).closest('tr').find('.charge-percent').val(),
						'value': Number.parseInt(chargeElementValue),
						'taxes': [],
					});
				}
				else {
					self.data.charge_info[chargeIndex].percent = $(domElement).closest('tr').find('.charge-percent').val();
					self.data.charge_info[chargeIndex].value = Number.parseInt(chargeElementValue);
				}
			}
			else {
				if (chargeIndex == -1) {
					self.data.charge_info.push({
						'charge_id': chargeId,
						'value': Number.parseInt(chargeElementValue),
						'taxes': [],
					});
				}
				else {
					self.data.charge_info[chargeIndex].value = Number.parseInt(chargeElementValue);
				}
			}

			chargesTotal = Number.parseInt(chargesTotal) + Number.parseInt(chargeElementValue);
		});

		self.data.pre_tax_total = amount + chargesTotal;
		self.data.charge_total = chargesTotal;
		self.form.find('.charges-total').html(app.convertCurrencyToUserFormat(chargesTotal, false));
		self.form.find('.pre-tax-total').html(app.convertCurrencyToUserFormat(this.data.pre_tax_total, false));

		if (self.data.tax_type == 'group') {
			self.calcGroupTaxTotal();
		}
		else {
			self.calcTaxChargeTotal();
		}
	}

	showFinalTaxPopup (element) {
		var self = this;
		var finalTaxUI = element.closest('.group-tax-container').find('.finalTaxUI').clone(true, true).removeClass('hide');
		var popOverTemplate = $(Inventory_Edit_Js.lineItemPopOverTemplate);
		popOverTemplate.addClass('group-tax-form');
		finalTaxUI.addClass('group-tax-popover-ui');
		var popOverTitle = finalTaxUI.find('.popover_title').val();

		element.closest('.group-tax-container').popover({
			'content' : finalTaxUI,
			'html' : true,
			'placement' : 'top',
			'title': popOverTitle,
			'animation' : true,
			'trigger' : 'manual',
			'template' : popOverTemplate
		});

		element.closest('.group-tax-container').on('shown.bs.popover', function() {
			if ($(element).closest('.group-tax-container').next('.popover').find('.popover-content').height() > 300) {
				(element).closest('.group-tax-container').next('.popover').find('.popover-content').addClass('fancyScrollbar');
				(element).closest('.group-tax-container').next('.popover').find('.popover-content').attr('style', 'max-height: 300px;overflow-y: scroll;overflow-x: hidden;');
			}
			
			var finalTaxUI = $('.group-tax-popover-ui');
			var finalTaxPopOver = finalTaxUI.closest('.popover');

			finalTaxPopOver.on('focusout', '.group-tax-percentage', function(e) {
				var currentTaxElement = $(e.currentTarget);
				var amount = self.data.total - self.data.discount_total;

				if (currentTaxElement.valid()) {
					var taxPercentage = app.unformatCurrencyToUser(currentTaxElement.val());

					if (taxPercentage == '') {
						taxPercentage = 0;
					}

					if (isNaN(taxPercentage)) {
						var taxTotal = 0;
					} 
					else {
						var taxPercentage = parseFloat(taxPercentage);
						var taxTotal = Math.abs(taxPercentage * amount) / 100;
						taxTotal = taxTotal.toFixed(self.numOfCurrencyDecimals);
					}

					currentTaxElement.closest('tr').find('.group-tax-total').val(app.convertCurrencyToUserFormat(taxTotal, false));
				}
			});
			
			finalTaxPopOver.find('.popoverButton').on('click', function (e) {
				var validate = $('.group-tax-form').find('input').valid();

				if (validate) {
					element.closest('.group-tax-container').popover('destroy');

					// Trigger calculate charges total 
					self.calcGroupTaxTotal();
				}
			});

			// Register cancel popup event
			finalTaxPopOver.find('.popoverCancel').on('click', function (e) {
				self.form.find('div[id^=qtip-]').qtip('destroy');
				element.closest('.group-tax-container').popover('destroy');
			});
		});

		element.closest('.group-tax-container').popover('toggle');
	}

	calcGroupTaxTotal () { 
		var self = this;
		var preTaxTotal = self.data.total - self.data.discount_total;
		var taxPercentages = $('.group-tax-form .group-tax-percentage', self.form);

		// Get element from finalTaxUI in table if item popup is not exist
		if (taxPercentages.length == 0) {
			taxPercentages = $('.group-tax-container .finalTaxUI .group-tax-percentage', self.form);
		}

		// Update tax percent and tax amount value for each tax in table and popup
		jQuery.each(taxPercentages, function (index, domElement) {
			var taxPercentageInput = $(domElement);
			var taxRow = taxPercentageInput.closest('tr');
			var taxPercentage = app.unformatCurrencyToUser(taxPercentageInput.val());

			if (taxPercentage == '') {
				taxPercentage = 0;
			}

			if (isNaN(taxPercentage)) {
				var taxTotal = 0;
			} 
			else {
				var taxPercentage = parseFloat(taxPercentage);
				var taxTotal = Math.abs(taxPercentage * preTaxTotal) / 100;
				taxTotal = taxTotal.toFixed(self.numOfCurrencyDecimals);
			}

			taxRow.find('.group-tax-total').val(app.convertCurrencyToUserFormat(taxTotal, false));
			taxRow.find('.group-tax-total').attr('value', app.convertCurrencyToUserFormat(taxTotal, false));

			var taxIdAttr = taxPercentageInput.attr('id');
			var taxElement = $(self.form).find('.finalTaxUI').find('#' + taxIdAttr);
			taxElement.attr('value', taxPercentageInput.val());
			taxElement.closest('tr').find('.group-tax-total').attr('value', app.convertCurrencyToUserFormat(taxTotal));

			// Update array taxes
			let taxName = taxPercentageInput.attr('data-name');

			if (self.data.taxes == undefined) {
				self.data.taxes.push({
					taxname: taxName,
					percentage: taxPercentage,
					amount: taxTotal,
				});
			}
			else {
				let taxIndex = self.data.taxes.findIndex((item) => item.taxname == taxName);

				if (taxIndex == -1) {
					self.data.taxes.push({
						taxname: taxName,
						percentage: taxPercentage,
						amount: taxTotal,
					});
				}
				else {
					self.data.taxes[taxIndex].percentage = taxPercentage;
					self.data.taxes[taxIndex].amount = taxTotal;
				}
			}
		});

		// Calculation compound taxes
		var taxTotal = 0;
		var taxPercentages = $('.group-tax-container .finalTaxUI .group-tax-percentage', self.form);

		jQuery.each(taxPercentages, function(index, domElement) {
			var taxElement = $(domElement);
			var taxRow = taxElement.closest('tr');
			var total = self.parseCurrency($('.group-tax-total', taxRow).val());
			var compoundOn = taxElement.data('compoundOn');

			if (compoundOn) {
				var amount = parseFloat(preTaxTotal);

				jQuery.each(compoundOn, function(index, id) {
					if (!isNaN($('.group-tax-total' + id, self.form).val())) {
						amount = parseFloat(amount) + parseFloat(app.unformatCurrencyToUser(jQuery('.group-tax-total' + id, self.form).val()));
					}
				});

				if (isNaN(taxElement.val())) {
					var total = 0;
				} 
				else {
					var total = Math.abs(amount * app.unformatCurrencyToUser(taxElement.val())) / 100;
				}

				taxRow.find('.group-tax-total').val(app.convertCurrencyToUserFormat(total, false));
				taxRow.find('.group-tax-total').attr('value', app.convertCurrencyToUserFormat(total, false));
			}

			taxTotal += parseFloat(total);
		});

		self.data.tax_total = taxTotal;
		
		// Change value in UI
		self.form.find('.final-tax-total').html(app.convertCurrencyToUserFormat(self.data.tax_total));

		// Trigger calculate grand total
		self.calcAdjustmentTotal();
	}

	// showFinalTaxChargePopup (element) {
	// 	var self = this;
	// 	var taxChargesUI = element.closest('.final-taxes-charges-container').find('#chargeTaxesBlock').clone(true, true).removeClass('hidden');
	// 	var popOverTemplate = $(Inventory_Edit_Js.lineItemPopOverTemplate);
	// 	popOverTemplate.addClass('final-tax-charges-form');
	// 	taxChargesUI.addClass('final-tax-charges-popover-ui');
	// 	var popOverTitle = element.closest('.final-taxes-charges-container').find('.popover-title').text();

	// 	element.closest('.final-taxes-charges-container').popover({
	// 		'content' : taxChargesUI,
	// 		'html' : true,
	// 		'placement' : 'bottom',
	// 		'title': popOverTitle,
	// 		'animation' : true,
	// 		'trigger' : 'manual',
	// 		'template' : popOverTemplate
	// 	});

	// 	element.closest('.final-taxes-charges-container').on('shown.bs.popover', function() {
	// 		if ($(element).closest('.final-taxes-charges-container').next('.popover').find('.popover-content').height() > 200) {
	// 			(element).closest('.final-taxes-charges-container').next('.popover').find('.popover-content').addClass('fancyScrollbar');
	// 			(element).closest('.final-taxes-charges-container').next('.popover').find('.popover-content').attr('style', 'max-height: 200px;overflow-y: scroll;overflow-x: hidden;');
	// 		}
			
	// 		var taxChargesUI = $('.final-tax-charges-popover-ui');
	// 		var finalTaxChargesPopOver = taxChargesUI.closest('.popover');

	// 		finalTaxChargesPopOver.on('focusout', '.charge-tax-percentage', function(e) {
	// 			var element = $(e.currentTarget);
	// 			var chargeId = element.attr('data-charge-id');
	// 			var chargeAmount = self.form.find('[name="charges['+chargeId+'][value]"]').val();
				
	// 			if (element.closest('form').valid()) {
	// 				if (isNaN(element.val())) {
	// 					var value = 0;
	// 				} 
	// 				else {
	// 					var value = Math.abs(self.parseCurrency(chargeAmount) * element.val()) / 100;
	// 				}

	// 				element.closest('tr').find('.chargeTaxValue').val(app.convertCurrencyToUserFormat(value, false));
	// 			}
	// 		});
			
	// 		finalTaxChargesPopOver.find('.popoverButton').on('click', function (e) {
	// 			var validate = taxChargesUI.find('input').valid();

	// 			if (validate) {
	// 				element.closest('.final-taxes-charges-container').popover('destroy');

	// 				// Trigger calculate charges total 
	// 				self.calcChargesTotal();
	// 			}
	// 		});

	// 		// Register cancel popup event
	// 		finalTaxChargesPopOver.find('.popoverCancel').on('click', function (e) {
	// 			self.form.find('div[id^=qtip-]').qtip('destroy');
	// 			element.closest('.final-taxes-charges-container').popover('destroy');
	// 		});
	// 	});

	// 	element.closest('.final-taxes-charges-container').popover('toggle');
	// }

	// calcTaxChargeTotal () {
	// 	var self = this;
	// 	var taxChargePercentages = $('.final-tax-charges-form .charge-tax-percentage', self.form);

	// 	// Get element from chargeTaxesBlock in table if item popup is not exist
	// 	if (taxChargePercentages.length == 0) {
	// 		taxChargePercentages = $('.final-taxes-charge .chargeTaxesBlock .charge-tax-percentage', self.form);
	// 	}

	// 	// Update tax percent and tax amount value for each tax in table and popup
	// 	jQuery.each(taxChargePercentages, function (index, domElement) {
	// 		var taxChargePercentageInput = $(domElement);
	// 		var taxChargeRow = taxChargePercentageInput.closest('tr');
	// 		var taxChargePercentage = app.unformatCurrencyToUser(taxChargePercentageInput.val());
	// 		var chargeId = taxChargePercentageInput.attr('data-charge-id');
	// 		var chargeAmount = self.form.find('input[name="charges['+chargeId+'][value]"]').val();

	// 		if (taxChargePercentage == '') {
	// 			taxChargePercentage = 0;
	// 		}

	// 		if (isNaN(taxChargePercentage)) {
	// 			var taxChargeTotal = 0;
	// 		} 
	// 		else {
	// 			var taxChargePercentage = parseFloat(taxChargePercentage);
	// 			var taxChargeTotal = Math.abs(taxChargePercentage * self.parseCurrency(chargeAmount)) / 100;
	// 			taxChargeTotal = taxChargeTotal.toFixed(self.numOfCurrencyDecimals);
	// 		}

	// 		taxChargeRow.find('.group-tax-total').val(app.convertCurrencyToUserFormat(taxChargeTotal, false));
	// 		taxChargeRow.find('.group-tax-total').attr('value', app.convertCurrencyToUserFormat(taxChargeTotal, false));

	// 		var taxChargeName = taxChargePercentageInput.attr('name');
	// 		var taxElement = $(self.form).find('.chargeTaxesBlock').find(`input[name="${taxChargeName}"]`);
	// 		taxElement.attr('value', taxChargePercentageInput.val());
	// 		taxElement.closest('tr').find('.chargeTaxValue').attr('value', app.convertCurrencyToUserFormat(taxChargeTotal));
	// 	});

	// 	// Calculation compound taxes
	// 	var taxChargeTotal = 0;
	// 	var taxPercentages = $('.final-taxes-charge .chargeTaxesBlock .charge-tax-percentage', self.form);

	// 	jQuery.each(taxPercentages, function(index, domElement) {
	// 		var taxChargeElement = $(domElement);
	// 		var taxChargeRow = taxChargeElement.closest('tr');

	// 		let chargeId = taxChargeElement.attr('data-charge-id');
	// 		let chargeTaxId = taxChargeElement.attr('data-charge-tax-id');
	// 		let chargeIndex = self.data.charge_info.findIndex((item) => item.charge_id == chargeId);
	// 		let chargeTaxIndex = self.data.charge_info[chargeIndex].taxes.findIndex((item) => item.tax_id == chargeTaxId);
			
	// 		if (chargeTaxIndex == -1) {
	// 			self.data.charge_info[chargeIndex].taxes.push({
	// 				'tax_id': chargeTaxId,
	// 				'value': $(domElement).val(),
	// 			});
	// 		}
	// 		else {
	// 			self.data.charge_info[chargeIndex].taxes[chargeTaxIndex].value = $(domElement).val();
	// 		}

	// 		var total = self.parseCurrency($('.chargeTaxValue', taxChargeRow).val());
	// 		taxChargeTotal += parseFloat(total);
	// 	});

	// 	self.data.taxes_charge_total = taxChargeTotal;
		
	// 	// Change value in UI
	// 	self.form.find('.charges-taxes').html(app.convertCurrencyToUserFormat(self.data.taxes_charge_total));

	// 	// Trigger calculate adjustment total
	// 	self.calcAdjustmentTotal();
	// }

	calcAdjustmentTotal () {
		let type = this.form.find('input[name="adjustment_type"]').filter(':checked').val();
		let total = this.form.find('input[name="adjustment_total"]').val();
		this.data.adjustment_total = this.parseCurrency(total);

		if (type == '-') {
			this.data.adjustment_total = -this.parseCurrency(total);
		}

		// Trigger calculate grand total
		this.calcGrandTotal();
	}

	calcGrandTotal () {
		let preTaxTotal = Number.parseInt(this.parseCurrency(this.data.pre_tax_total));
		let taxTotal = this.data.tax_type == 'group' ? Number.parseInt(this.parseCurrency(this.data.tax_total)) : 0;
		let taxChargeTotal = Number.parseInt(this.parseCurrency(this.data.taxes_charge_total));
		let adjustmentTotal = Number.parseInt(this.data.adjustment_total);
		let grandTotal = (preTaxTotal + taxTotal + taxChargeTotal + adjustmentTotal) || 0;

		this.data.grand_total = grandTotal;
		this.form.find('.total h5').html(app.convertCurrencyToUserFormat(grandTotal, false));
	}

	parseCurrency (value) {
		if (!value) {
			return '0';
		}

		let separator = app.getDecimalSeparator();
		let separatorIndex = value.toString().indexOf(separator);

		if (separatorIndex > -1) {
			value = value.slice(0, separatorIndex);
		}

		value = value.toString().replace(/[^0-9]/g, '');
		value = Number.parseInt(value);

		return value;
	}

	formatInputCurrency (number) {
		let parsedNumber = this.parseCurrency(number);
		return this.formatCurrency(parsedNumber);
	}

	formatCurrency (number) {
		let amount = app.convertCurrencyToUserFormat(number, false);
		let separator = app.getDecimalSeparator();
		let separatorIndex = amount.indexOf(separator);
		
		if (separatorIndex > -1) {
			value = value.slice(0, separatorIndex);
		}

		return amount;
	}

	formatInventoryDataFromFormData (formData) {
		let inventoryFormData = {};
		inventoryFormData['module'] = this.type;
		inventoryFormData['action'] = 'InventoryQuickEditAjax';
		inventoryFormData['mode'] = 'saveAjax';
		inventoryFormData['id'] = this.id;
		inventoryFormData['customer_id'] = this.customerId;
		inventoryFormData['customer_type'] = this.customerType;

		for (let key in this.data) {
			inventoryFormData[key] = this.data[key];
		}

		inventoryFormData[this.statusFieldName] = formData[this.statusFieldName];
		inventoryFormData['description'] = formData['description'];

		if (this.type == 'SalesOrder') {
			inventoryFormData['ship_street'] = formData['ship_street'];
			inventoryFormData['receiver_name'] = formData['receiver_name'];
			inventoryFormData['receiver_phone'] = formData['receiver_phone'];
			inventoryFormData['has_invoice'] = formData['has_invoice'];
		}

		if (formData['campaign_id']) {
			inventoryFormData['campaign_id'] = formData['campaign_id'];
		}
		
		return inventoryFormData;
	}
}

// Define quick edit helper 
window.QuickEditHelper = {
	openQuickEditModal: function (moduleName, recordId, submitCallBack = null, saveCallBack = null) { 
		app.helper.showProgress();
		let params = {
			module: moduleName,
			view: 'QuickEditAjax',
			mode: 'edit',
			record: recordId
		};

		app.request.post({ data: params })
		.then(function (err, data) {
			app.helper.hideProgress();

			if (err) {					
				app.helper.showErrorNotification({ message: err.message });
				return;
			}

			// Display modal
			app.helper.showModal(data, {
				preShowCb: function (modal) {
					let form = modal.find('form[name="quick_edit"]');
					let quickEdit = new BaseQuickEdit();
					let isInventory = form.find('input[name="is_inventory"]')[0] != null ? true : false;
					
					if (isInventory) {
						quickEdit = new InventoryQuickEdit();
					}

					// Load custom script
					let scripts = modal.find('.custom-scripts script');

					if (scripts.length > 0) {
						scripts.each(function (index, script) {
							if (script.src != null) {
								jQuery.getScript(script.src, () => { console.log('Script ' + script.src + ' loaded') });
							}
						});
					}

					// Register basic event
					var moduleInstance = Vtiger_Edit_Js.getInstanceByModuleName(moduleName);

					if (typeof(moduleInstance.quickCreateSave) == 'function' && moduleName != 'Documents') {
						targetInstance = moduleInstance;
						targetInstance.registerBasicEvents(form);
					}
					
					quickEdit.registerEvent(form);
					quickEdit.registerSubmitEvent(submitCallBack, saveCallBack);
				},
				cb: function (modal) {
					app.event.trigger('post.quickEditModal.open', modal);
			
					// Set the same height for label and input in the same row after modal loaded
					modal.find('.left').each(function (index, leftColumn) {
						let leftColumnHeight = parseFloat($(this).outerHeight());
						let rightColumn = $(this).next('.right');
						let rightColumnHeight = parseFloat(rightColumn.outerHeight());

						if (leftColumnHeight == 0 || rightColumnHeight == 0) {
							return;
						}

						if (leftColumnHeight == rightColumnHeight ) {
							return;
						}

						if (leftColumnHeight > rightColumnHeight) {
							$(this).css('height', leftColumnHeight);
							rightColumn.css('height', leftColumnHeight);
						}
						else {
							$(this).css('height', rightColumnHeight);
							rightColumn.css('height', rightColumnHeight);
						}
					})
				}
			});
		});
	},
}