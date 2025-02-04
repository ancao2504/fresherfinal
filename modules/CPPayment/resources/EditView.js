/*
    EditView.js
    Author: Phuc Lu
    Date: 2019.08.15
    Purpose: to handle logic on the UI
*/

jQuery(function($) {
    $('[name="cppayment_currency"]').change(function(e, changeConversionRate = 1) {
        var currency = $(this).find('option:selected').val();
        var label = '';
        var conversionRate = 1;

        if (typeof currencies != 'undefined' && currency != '') {
            label = currencies[currency].currencysymbol;
            conversionRate =  currencies[currency].conversionrate;
        }

        conversionRate = customFormatNumber(String(conversionRate), 1);

        $('[name="amount"]').prev().html(label);
        if (changeConversionRate) $('[name="currency_ratio"]').val(conversionRate);

        calculateTotalAmountVND();                
        distributePaid(changeConversionRate);
    });

    // Trigger change to apply default ratio at the loading page
    if ($('[name="record"]').val() == '') {
        $('[name="cppayment_currency"]').trigger('change', 1);
    }
    else {
        $('[name="cppayment_currency"]').trigger('change', 0);
    }

    // When update amount, calculte vnd again
      $('[name="amount"]').keyup(function() {
        calculateTotalAmountVND();
    })
    
    $('[name="amount"]').blur(function() {       
        distributePaid();
    })

    $('[name="currency_ratio"]').keyup(function() {
        calculateTotalAmountVND(); 
    })
    
    $('[name="currency_ratio"]').blur(function() {        
        distributePaid();
    })

    // Validate between account and vendor
    $('[name="vendor_id"]').on(Vtiger_Edit_Js.postReferenceSelectionEvent, function() {
        var accountIdParentField =  $('.fieldValue.account_id');
        
        accountIdParentField.find('.sourceField').val('');
        accountIdParentField.find('.inputElement').val('');        
        accountIdParentField.find('.inputElement').attr('readonly', true);
        accountIdParentField.find('.clearReferenceSelection').addClass('hide');
        accountIdParentField.find('.relatedPopup').addClass('hide');
        accountIdParentField.find('.createReferenceRecord').addClass('hide');

        accountIdParentField.find('.inputElement').attr('oldplaceholder', accountIdParentField.find('.inputElement').attr('placeholder'));
        accountIdParentField.find('.inputElement').attr('placeholder', '');
        accountIdParentField.find('.inputElement').css('background', '#fff2db');
    })
    
    $('[name="account_id"]').on(Vtiger_Edit_Js.postReferenceSelectionEvent, function() {  
        var vendorIdParentField = $('.fieldValue.vendor_id');

        vendorIdParentField.find('.sourceField').val('');
        vendorIdParentField.find('.inputElement').val('');        
        vendorIdParentField.find('.inputElement').attr('readonly', true);
        vendorIdParentField.find('.clearReferenceSelection').addClass('hide');
        vendorIdParentField.find('.relatedPopup').addClass('hide');
        vendorIdParentField.find('.createReferenceRecord').addClass('hide');

        vendorIdParentField.find('.inputElement').attr('oldplaceholder', vendorIdParentField.find('.inputElement').attr('placeholder'));
        vendorIdParentField.find('.inputElement').attr('placeholder', '');   
        vendorIdParentField.find('.inputElement').css('background', '#fff2db');
    })

    $('.fieldValue.vendor_id .clearReferenceSelection, .fieldValue.account_id .clearReferenceSelection').on('click', function() {
        var accountIdParentField =  $('.fieldValue.account_id');
        var vendorIdParentField = $('.fieldValue.vendor_id');

        vendorIdParentField.find('.sourceField').val('');
        vendorIdParentField.find('.inputElement').val('');        
        vendorIdParentField.find('.inputElement').attr('readonly', false);
        vendorIdParentField.find('.clearReferenceSelection').addClass('hide');
        vendorIdParentField.find('.relatedPopup').removeClass('hide');
        vendorIdParentField.find('.createReferenceRecord').removeClass('hide');
        
        accountIdParentField.find('.sourceField').val('');
        accountIdParentField.find('.inputElement').val('');        
        accountIdParentField.find('.inputElement').attr('readonly', false);
        accountIdParentField.find('.clearReferenceSelection').addClass('hide');
        accountIdParentField.find('.relatedPopup').removeClass('hide');
        accountIdParentField.find('.createReferenceRecord').removeClass('hide');

        vendorIdParentField.find('.inputElement').attr('placeholder', vendorIdParentField.find('.inputElement').attr('oldplaceholder'));
        accountIdParentField.find('.inputElement').attr('placeholder', accountIdParentField.find('.inputElement').attr('oldplaceholder'));
        
        vendorIdParentField.find('.inputElement').css('background', 'white');
        accountIdParentField.find('.inputElement').css('background', 'white');

        $('#tbl-invoices tbody').html('');
    })

    // Trigger at the first time
    if ($('[name="vendor_id"]').val() != '') {
        $('[name="vendor_id"]').trigger(Vtiger_Edit_Js.postReferenceSelectionEvent);
    }

    if ($('[name="account_id"]').val() != '') {
        $('[name="account_id"]').trigger(Vtiger_Edit_Js.postReferenceSelectionEvent);
    }

    // Validate between PO and SO
    $('[name="related_salesorder"]').on(Vtiger_Edit_Js.postReferenceSelectionEvent, function() {
        var purchaseOrderParentField = $('.fieldValue.related_purchaseorder');

        purchaseOrderParentField.find('.sourceField').val('');
        purchaseOrderParentField.find('.inputElement').val('');        
        purchaseOrderParentField.find('.inputElement').attr('readonly', true);
        purchaseOrderParentField.find('.clearReferenceSelection').addClass('hide');
        purchaseOrderParentField.find('.relatedPopup').addClass('hide');

        purchaseOrderParentField.find('.inputElement').attr('oldplaceholder', purchaseOrderParentField.find('.inputElement').attr('placeholder'));
        purchaseOrderParentField.find('.inputElement').attr('placeholder', '');
        purchaseOrderParentField.find('.inputElement').css('background', '#fff2db');
    })

    $('[name="related_purchaseorder"]').on(Vtiger_Edit_Js.postReferenceSelectionEvent, function() { 
        var salesOrderParentField = $('.fieldValue.related_salesorder');

        salesOrderParentField.find('.sourceField').val('');
        salesOrderParentField.find('.inputElement').val('');        
        salesOrderParentField.find('.inputElement').attr('readonly', true);
        salesOrderParentField.find('.clearReferenceSelection').addClass('hide');
        salesOrderParentField.find('.relatedPopup').addClass('hide');

        salesOrderParentField.find('.inputElement').attr('oldplaceholder', salesOrderParentField.find('.inputElement').attr('placeholder'));
        salesOrderParentField.find('.inputElement').attr('placeholder', '');   
        salesOrderParentField.find('.inputElement').css('background', '#fff2db');
    })

    $('.fieldValue.related_salesorder .clearReferenceSelection, .fieldValue.related_purchaseorder .clearReferenceSelection').on('click', function() {
        var purchaseOrderParentField = $('.fieldValue.related_purchaseorder');
        var salesOrderParentField = $('.fieldValue.related_salesorder');

        salesOrderParentField.find('.sourceField').val('');
        salesOrderParentField.find('.inputElement').val('');        
        salesOrderParentField.find('.inputElement').attr('readonly', false);
        salesOrderParentField.find('.clearReferenceSelection').addClass('hide');
        salesOrderParentField.find('.relatedPopup').removeClass('hide');
        
        purchaseOrderParentField.find('.sourceField').val('');
        purchaseOrderParentField.find('.inputElement').val('');        
        purchaseOrderParentField.find('.inputElement').attr('readonly', false);
        purchaseOrderParentField.find('.clearReferenceSelection').addClass('hide');
        purchaseOrderParentField.find('.relatedPopup').removeClass('hide');

        salesOrderParentField.find('.inputElement').attr('placeholder', salesOrderParentField.find('.inputElement').attr('oldplaceholder'));
        purchaseOrderParentField.find('.inputElement').attr('placeholder', purchaseOrderParentField.find('.inputElement').attr('oldplaceholder'));
        
        salesOrderParentField.find('.inputElement').css('background', 'white');
        purchaseOrderParentField.find('.inputElement').css('background', 'white');
    })

    // Trigger at the first time
    if ($('[name="related_salesorder"]').val() != '') {
        $('[name="related_salesorder"]').trigger(Vtiger_Edit_Js.postReferenceSelectionEvent);
    }

    if ($('[name="related_purchaseorder"]').val() != '') {
        $('[name="related_purchaseorder"]').trigger(Vtiger_Edit_Js.postReferenceSelectionEvent);
    }

    // Custom code before submit
    $('#EditView').on('submit', function(e) {
        // Check validation before saving
        var invoiceValidate = validateInvoicesTotal();
        if (!invoiceValidate) {
            app.helper.showErrorNotification({message: app.vtranslate('JS_TOTAL_NOT_MATCH_TO_INVOICE', 'CPPayment')});
            
            setTimeout(function() {
                $('.btn.btn-success.saveButton').attr('disabled', false);
            }, 10);

            return false;
        }

        var invoices_detail = [];

        $('#tbl-invoices tbody tr:visible').each(function() {
            tempValue = {
                'invoice_id' : $(this).find('input[type="hidden"]').eq(0).val(),                
                'invoice_total' : app.unformatCurrencyToUser($(this).find('input[type="hidden"]').eq(1).val()),                
                'invoice_received' : app.unformatCurrencyToUser($(this).find('input[type="hidden"]').eq(2).val()),
                'invoice_balance' : app.unformatCurrencyToUser($(this).find('input[type="hidden"]').eq(3).val()),
                'invoice_paid' : app.unformatCurrencyToUser($(this).find('input[type="text"]').eq(0).val()),
            };

            invoices_detail.push(tempValue);
        })

        invoices_detail = JSON.stringify(invoices_detail);
        $('[name="invoices_detail"]').val(invoices_detail);

       return true;
    })

    $('[name="account_id"]').on(Vtiger_Edit_Js.preReferencePopUpOpenEvent, function(e, removeInvoice = true) {
        if (removeInvoice) {
            $('#tbl-invoices').find('tbody').html('');
        }
    })

    $('[name="vendor_id"]').on(Vtiger_Edit_Js.preReferencePopUpOpenEvent, function(e, removeInvoice = true) {
        if (removeInvoice) {
            $('#tbl-invoices').find('tbody').html('');
        }
    })
});

// Display for invoices
invoiceInterval = setInterval(function() {
    if ($('.fieldValue.invoices_detail').length > 0) {
        $('.fieldValue.invoices_detail').next().next().remove();
        $('.fieldValue.invoices_detail').next().remove();
        $('.fieldValue.invoices_detail').prev().remove();
        $('.fieldValue.invoices_detail').attr('colspan', 4);
        clearInterval(invoiceInterval);
    }
}, 100);

// Add save and completed button
if (allowToComplete) {
    addButtonInterval = setInterval(function() {
        if ($('.modal-overlay-footer .saveButton').length > 0 && typeof app != 'undefined') {
            var button = '&nbsp;&nbsp;&nbsp;&nbsp;<span class="btn btn-success saveButton" onclick="submitFormWithInvoiceValidation(1)">' + app.vtranslate('JS_SAVE_AND_COMPLETED', 'CPPayment') +'</span>';
            $(button).insertAfter($('.modal-overlay-footer .saveButton'));
            clearInterval(addButtonInterval);
        }
    }, 100);
}

// Add save and propose button
if (allowToPropose) {
    addButtonInterval = setInterval(function() {
        if ($('.modal-overlay-footer .saveButton').length > 0 && typeof app != 'undefined') {
            var button = '&nbsp;&nbsp;&nbsp;&nbsp;<span class="btn btn-success saveButton" onclick="submitFormWithInvoiceValidation(2)">' + app.vtranslate('JS_SAVE_AND_PROPOSED', 'CPPayment') +'</span>';
            $(button).insertAfter($('.modal-overlay-footer .saveButton'));
            clearInterval(addButtonInterval);
        }
    }, 100);
}

// Set action for related
paymentInterval = setInterval(function() {
    if (typeof Vtiger_Edit_Js != "undefined") {
        Vtiger_Edit_Js('CPPayment_Edit_Js', {}, {
            fillReferenceFieldValue : function(data, container) {
                Vtiger_Edit_Js.prototype.fillReferenceFieldValue.call(this, data, container);
                
                if (container.find('[name="vendor_id"]').length) {
                    container.find('[name="vendor_id"]').trigger(Vtiger_Edit_Js.postReferenceSelectionEvent);
                }

                if (container.find('[name="account_id"]').length) {
                    container.find('[name="account_id"]').trigger(Vtiger_Edit_Js.postReferenceSelectionEvent);
                }
            }
        })
        clearInterval(paymentInterval);
    }
},100);

// Calculate total amount in VNĐ
function calculateTotalAmountVND() {
    var amount = $('[name="amount"]').val();
    var ratio = $('[name="currency_ratio"]').val();
    var amountVnd = 0;
    
    amount = customUnformatNumber(amount);
    ratio = customUnformatNumber(ratio);

    amount = parseFloat(amount);
    ratio = parseFloat(ratio);

    if (isNaN(amount)) {
        amount = 0;
    }

    if (isNaN(ratio)) {
        ratio = 0;
    }

    amountVnd = amount * ratio;
    amountVnd = customFormatNumber(String(amountVnd), 0);

    $('[name="amount_vnd"]').val(amountVnd);
    $('[name="amount_vnd"]').prev().html(amountVnd);
}

// Return string value
function customUnformatNumber(number) {
    var decimalSeparator = app.getDecimalSeparator();
    var thousandSeparator = '';

    if (decimalSeparator == ',') {
        thousandSeparator = '.';
    }
    else {
        thousandSeparator = ',';
    }

    number = String(number);
    number = number.split(thousandSeparator).join('')

    return number;
}

// Return string
function customFormatNumber(number, toDecimal = 0) {
    var decimalSeparator = app.getDecimalSeparator();
    var decimalNumber = app.getNumberOfDecimals();
    var thousandSeparator = '';
    var thousandPart = '';
    var decimalPart = '';
    var hasDecimal = false;

    if (decimalSeparator == ',') {
        thousandSeparator = '.';
    }
    else {
        thousandSeparator = ',';
    }

    if (number.indexOf(decimalSeparator) > 0) {
        number = number.split(decimalSeparator);
        thousandPart = number[0];
        decimalPart = number[1];
        decimalPart = decimalPart.substring(0, decimalNumber);
        hasDecimal = true;
    }
    else {
        thousandPart = number;
    }
    
    thousandPart = thousandPart.split("").reverse().join("").replace(/(.{3})/g,"$1" + thousandSeparator).split("").reverse().join("");

    if (thousandPart[0] == thousandSeparator) {
        thousandPart=thousandPart.slice(1);
    }
    
    if (hasDecimal) {
        decimalPart = decimalPart.replace(/(.{3})/g,"$1" + thousandSeparator);
    }
    else {
        decimalPart = new Array(decimalNumber + 1).join('0');
    }

    if (toDecimal) {
        number = thousandPart + decimalSeparator + decimalPart;
    }
    else{
        number = thousandPart;
    }

    return number;
}

// Added by Phuc on 2019.08.18 to display popup of invoice
function selectInvoices() {
    var popupInstance = Vtiger_Popup_Js.getInstance();
    var searchParams= [];
    searchParams[0] = [];

    searchParams[0].push(['invoice_type', 'e', 'buy']);
    searchParams[0].push(['invoice_paid_status', 'e', 'partial,no_paid,null']);

    if ($('[name="account_id_display"]').val() != '') {
        searchParams[0].push(['account_id', 'e', $('[name="account_id_display"]').val()]);
    }
    else if ($('[name="vendor_id_display"]').val() != '') {
        searchParams[0].push(['related_vendor', 'e', $('[name="vendor_id_display"]').val()]);
    }
    else if ($('[name="contact_id_display"]').val() != '') {
        searchParams[0].push(['contact_id', 'e', $('[name="contact_id_display"]').val()]);
    }

    var parameters = {
        'module' : 'Invoice',
        'src_module' : 'CPPayment',
        'src_record' : '',
        'multi_select' : true,
        'search_params': searchParams,
    }

    popupInstance.show(parameters, addToDetail);

    vtUtils.doWhen(function() {
        return $('[name="invoice_paid_status"]').length > 0;
   }, function() {
      $('[name="invoice_type"]').closest('td').find('div:first').hide();
      $('[name="invoice_paid_status"]').closest('td').find('div:first').hide();
   })
}

function addToDetail(data) {
    data = JSON.parse(data);
    data = Object.keys(data);

    app.helper.showProgress();
    var params = {
        'module': 'CPReceipt',
        'action': 'GetInvoicesDetail',
        'data': data
    };

    app.request.post({ data: params }).then(function(error, data) {
        app.helper.hideProgress();

        if (error) {
            app.helper.showErrorNotification({message: app.vtranslate('JS_ERROR', 'CPPayment')});
            return;
        }

        var result = data.result;
        var addedInvoice = [];

        $('#tbl-invoices tbody [name="invoice-id[]"]').each(function() {
            addedInvoice.push($(this).val());
        })

        var accountId = $('[name="account_id"]').val();
        var contactId = $('[name="contact_id"]').val();
        var vendorId = $('[name="vendor_id"]').val();

        if (accountId == '' && contactId == '' && vendorId == '') {
            app.helper.showConfirmationBox({message: app.vtranslate('JS_APPLY_RELATED_FROM_THE_FIRST_INVOICE', 'CPPayment')}).then(function() {
                let first = true;
                
                $.each(result, function (key, data) {  
                    if (first) {
                        if (data.account_id != '') {
                            $('[name="account_id"]').val(data.account_id);
                            $('[name="account_id_display"]').val(data.account_name);
                            $('[name="account_id_display"]').next().removeClass('hide');                            
                            $('[name="account_id"]').trigger(Vtiger_Edit_Js.postReferenceSelectionEvent, false);
                        }
                        else {
                            $('[name="vendor_id"]').val(data.related_vendor);                            
                            $('[name="vendor_id_display"]').val(data.vendor_name);
                            $('[name="vendor_id_display"]').next().removeClass('hide');                         
                            $('[name="vendor_id"]').trigger(Vtiger_Edit_Js.postReferenceSelectionEvent, false);
                        }
                        
                        if (data.contact_id != '') {
                            $('[name="contact_id"]').val(data.contact_id);
                            $('[name="contact_id_display"]').val(data.contact_name);
                            $('[name="contact_id_display"]').next().removeClass('hide');
                        }

                        accountId = data.account_id;
                        contactId = data.contact_id;
                        vendorId = data.related_vendor;
                        first  = false;
                    }
                    
                    if (addedInvoice.indexOf(data.crmid) < 0 ) {
                        if ( (accountId == '' && vendorId == '' && contactId == '')
                        || (accountId != '' && accountId == data.account_id && data.related_vendor == '')
                        || (vendorId != '' && vendorId == data.related_vendor && data.account_id == '')
                        || (vendorId == '' && data.related_vendor == '' && accountId == '' && data.account_id == '' && vendorId != '' && vendorId == data.related_vendor)) {
                            let cloneTr = $('#tbl-invoices tfoot tr').eq(0).clone();
                            $(cloneTr).removeClass('hide');
        
                            $(cloneTr).find('.invoice-code a').html(data.invoice_no);    
                            $(cloneTr).find('.invoice-code a').attr('href', $(cloneTr).find('.invoice-code a').attr('href') + data.crmid);            
                            $(cloneTr).find('.invoice-code').next().val(data.crmid);
                            $(cloneTr).find('.invoice-subject').html(data.subject); 
                            $(cloneTr).find('.invoice-total').html(data.total); 
                            $(cloneTr).find('.invoice-total').next().val(data.total); 
                            $(cloneTr).find('.invoice-received').html(data.received); 
                            $(cloneTr).find('.invoice-received').next().val(data.received); 
                            $(cloneTr).find('.invoice-balance').html(data.balance); 
                            $(cloneTr).find('.invoice-balance').next().val(data.balance); 
        
                            $('#tbl-invoices tbody').append(cloneTr);
                        }
                    }
                })

                distributePaid();
            });
        }
        else {
            $.each(result, function (key, data) {   
                if (addedInvoice.indexOf(data.crmid) < 0) {
                    if ( (accountId == '' && vendorId == '' && contactId == '')
                    || (accountId != '' && accountId == data.account_id && data.related_vendor == '')
                    || (vendorId != '' && vendorId == data.related_vendor && data.account_id == '')
                    || (vendorId == '' && data.related_vendor == '' && accountId == '' && data.account_id == '' && vendorId != '' && vendorId == data.related_vendor)) {
                        let cloneTr = $('#tbl-invoices tfoot tr').eq(0).clone();
                        $(cloneTr).removeClass('hide');

                        $(cloneTr).find('.invoice-code a').html(data.invoice_no);    
                        $(cloneTr).find('.invoice-code a').attr('href', $(cloneTr).find('.invoice-code a').attr('href') + data.crmid);            
                        $(cloneTr).find('.invoice-code').next().val(data.crmid);
                        $(cloneTr).find('.invoice-subject').html(data.subject); 
                        $(cloneTr).find('.invoice-total').html(data.total); 
                        $(cloneTr).find('.invoice-total').next().val(data.total); 
                        $(cloneTr).find('.invoice-received').html(data.received); 
                        $(cloneTr).find('.invoice-received').next().val(data.received); 
                        $(cloneTr).find('.invoice-balance').html(data.balance); 
                        $(cloneTr).find('.invoice-balance').next().val(data.balance); 

                        $('#tbl-invoices tbody').append(cloneTr);
                    }
                }

                distributePaid();
            })
        }
    });
}

function removeRow(_this) {
    app.helper.showConfirmationBox({message: app.vtranslate('JS_REMOVE_ROW_CONFIRM', 'CPPayment')}).then(function() { 
        $(_this).closest('tr').remove();
    });
}

function formatNumberWithMaxValue(_this, type) {
    // Get max value
    var maxValue = $(_this).closest('td').prev().find('input').val();
    maxValue = parseFloat(customUnformatNumber(maxValue));
    var thisValue = $(_this).val();
    thisValue = parseFloat(customUnformatNumber(thisValue));
    var totalAmount = $('[name="amount_vnd"]').val();
    totalAmount = parseFloat(customUnformatNumber(totalAmount));

    if(isNaN(totalAmount)) {
        totalAmount = 0;
    }

    if(isNaN(maxValue)) {
        maxValue = 0;
    }

    if(isNaN(thisValue)) {
        thisValue = 0;
    }

    let tempTotal = 0;
    // Caculate total in others
    $(_this).closest('tbody').find('tr .inputElement:visible').each(function() { 
        var tempValue = $(this).val();
        tempValue = parseFloat(customUnformatNumber(tempValue));

        if(isNaN(tempValue)) {
            tempValue = 0;
        }

        tempTotal += tempValue;
    })

    tempTotal -= thisValue;

    // Caculate for max value
    if (totalAmount - tempTotal < maxValue) {
        maxValue = totalAmount - tempTotal;
    }
    
    if (maxValue < thisValue) {
        $(_this).val(maxValue);
    }

    formatNumber(_this, type);
}

function distributePaid(active = 1) {
    if (!active)
        return;

    var totalAmount = $('[name="amount_vnd"]').val();
    totalAmount = parseFloat(customUnformatNumber(totalAmount));

    if(isNaN(totalAmount)) {
        totalAmount = 0;
    }

    $('#tbl-invoices tbody tr .inputElement:visible').each(function() {
        $(this).val(0);
    });

    $('#tbl-invoices tbody tr .inputElement:visible').each(function() {
        var maxValue = $(this).closest('td').prev().find('input').val();
        maxValue = parseFloat(customUnformatNumber(maxValue));

        if(isNaN(maxValue)) {
            maxValue = 0;
        }

        if (totalAmount > maxValue) {
            totalAmount -= maxValue;
            $(this).val(maxValue);
            $(this).trigger('keyup');
        }
        else {
            $(this).val(totalAmount);
            $(this).trigger('keyup');
            totalAmount = 0;
        }
    })
}

function validateInvoicesTotal() {
    if ($('#tbl-invoices tbody tr .inputElement:visible').length <= 0) {
        return true;
    }

    var tempTotal = 0;
    var totalAmount = $('[name="amount_vnd"]').val();
    totalAmount = parseFloat(customUnformatNumber(totalAmount));

    if(isNaN(totalAmount)) {
        totalAmount = 0;
    }

    $('#tbl-invoices tbody tr .inputElement:visible').each(function() {
        tempValue = $(this).val();
        tempValue = parseFloat(customUnformatNumber(tempValue));

        if(isNaN(tempValue)) {
            tempValue = 0;
        }

        tempTotal += tempValue;
    })

    // If the total values in invoice is less than the total of receipt, return false
    if (tempTotal < totalAmount) {
        return false;
    }

    return true;
}

function submitFormWithInvoiceValidation(type) {
    if (type === 1) {
        label = 'JS_CONFIRM_COMPLETE';
    }
    else {
        label = 'JS_CONFIRM_PROPOSE';
    }

    app.helper.showConfirmationBox({message: app.vtranslate(label, 'CPPayment')}).then(function() { 
        if (type === 1) {
            $('[name="cppayment_status"]').val('completed');
        }
        else if (type === 2) {
            $('[name="cppayment_accountant_status"]').val('proposed');
        }

        $('button.saveButton').trigger('click');
    });
}