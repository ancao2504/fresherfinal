/*
*   EditView.js
*   Author: Phuc Lu
*   Date: 2019.08.01
*   Purpose: customize the layout
*/

jQuery(function($) {
    $('[name="invoice_type"]').change(function(e, deleteOldValue = 1) {

        if ($(this).val() == 'buy') {            
            $('.fieldLabel.salesorder_id').html(app.vtranslate('JS_PURCHASE_ORDER', 'Invoice'));
            $('.fieldLabel.account_id').html(app.vtranslate('JS_VENDOR', 'Invoice'));
            
            // Update for Order
            var parentTd =  $('.fieldValue.salesorder_id');

            if (deleteOldValue) parentTd. find('.clearReferenceSelection').trigger('click');

            parentTd. find('[name="popupReferenceModule"]').val('PurchaseOrder');
            parentTd.find('[type="hidden"].sourceField').attr('name', 'related_purchaseorder');
            parentTd.find('[data-fieldtype="reference"]').attr('name', 'related_purchaseorder_display');
            parentTd.find('[data-fieldtype="reference"]').attr('id', 'related_purchaseorder_display');
            parentTd.find('[data-fieldtype="reference"]').attr('data-fieldname', 'related_purchaseorder');    
            
            // Update for vendor/customer
            var parentTd =  $('.fieldValue.account_id');

            if (deleteOldValue) parentTd. find('.clearReferenceSelection').trigger('click');

            parentTd. find('[name="popupReferenceModule"]').val('Vendors');
            parentTd.find('[type="hidden"].sourceField').attr('name', 'related_vendor');
            parentTd.find('[data-fieldtype="reference"]').attr('name', 'related_vendor_display');
            parentTd.find('[data-fieldtype="reference"]').attr('id', 'related_vendor_display');
            parentTd.find('[data-fieldtype="reference"]').attr('data-fieldname', 'related_vendor');   
        }
        else {
            $('.fieldLabel.salesorder_id').html(app.vtranslate('JS_SALE_ORDER', 'Invoice'));
            $('.fieldLabel.account_id').html(app.vtranslate('JS_ACCOUNT', 'Invoice'));

            // Update for Order
            var parentTd =  $('.fieldValue.salesorder_id');

            if (deleteOldValue) parentTd. find('.clearReferenceSelection').trigger('click');

            parentTd. find('[name="popupReferenceModule"]').val('SalesOrder');            
            parentTd.find('[type="hidden"].sourceField').attr('name', 'salesorder_id');
            parentTd.find('[data-fieldtype="reference"]').attr('name', 'salesorder_id_display');
            parentTd.find('[data-fieldtype="reference"]').attr('id', 'salesorder_id_display');
            parentTd.find('[data-fieldtype="reference"]').attr('data-fieldname', 'salesorder_id'); 
            
            // Update for vendor/customer
            var parentTd =  $('.fieldValue.account_id');

            if (deleteOldValue) parentTd. find('.clearReferenceSelection').trigger('click');

            parentTd. find('[name="popupReferenceModule"]').val('Accounts');            
            parentTd.find('[type="hidden"].sourceField').attr('name', 'account_id');
            parentTd.find('[data-fieldtype="reference"]').attr('name', 'account_id_display');
            parentTd.find('[data-fieldtype="reference"]').attr('id', 'account_id_display');
            parentTd.find('[data-fieldtype="reference"]').attr('data-fieldname', 'account_id'); 
        }
    })

    $('[name="invoice_type"]').trigger('change', 0);

    // Hide field purchase order and vendor
    $('.fieldLabel.related_purchaseorder').html('');
    $('.fieldValue.related_purchaseorder').html('');

    if ($('.fieldLabel.related_purchaseorder').parent().find('.fieldLabel:visible').length < 1)
        $('.fieldLabel.related_purchaseorder').parent().remove();

    $('.fieldLabel.related_vendor').html('');
    $('.fieldValue.related_vendor').html('');
    
    if ($('.fieldLabel.related_vendor').parent().find('.fieldLabel:visible').length < 1)
        $('.fieldLabel.related_vendor').parent().remove();

    // Added by Hieu Nguyen on 2020-12-08 to show relate fields value when edit existing record
    if ($('[name="invoice_type"]').val() == 'buy') {    // Invoice for buying
        // Related vendor
        var hiddenRelatedVendor = $('#hidden_related_vendor');

        if (hiddenRelatedVendor.val() && hiddenRelatedVendor.val() != '0') {
            $('[name="related_vendor"]').closest('.input-group').find('.clearReferenceSelection').removeClass('hide');
            $('#related_vendor_display').val(hiddenRelatedVendor.data('name')).attr('disabled', true);
            $('[name="related_vendor"]').val(hiddenRelatedVendor.val());
        }
        
        // Related PO
        var hiddenRelatedPO = $('#hidden_related_purchaseorder');

        if (hiddenRelatedPO.val() && hiddenRelatedPO.val() != '0') {
            $('[name="related_purchaseorder"]').closest('.input-group').find('.clearReferenceSelection').removeClass('hide');
            $('#related_purchaseorder_display').val(hiddenRelatedPO.data('name')).attr('disabled', true);
            $('[name="related_purchaseorder"]').val(hiddenRelatedPO.val());
        }
    }
    else {  // Invoice for selling
        // Related account
        var hiddenRelatedAccount = $('#hidden_related_account');

        if (hiddenRelatedAccount.val() && hiddenRelatedAccount.val() != '0') {
            $('[name="account_id"]').closest('.input-group').find('.clearReferenceSelection').removeClass('hide');
            $('#account_id_display').val(hiddenRelatedAccount.data('name')).attr('disabled', true);
            $('[name="account_id"]').val(hiddenRelatedAccount.val());
        }

        // Related SO
        var hiddenRelatedSO = $('#hidden_related_salesorder');

        if (hiddenRelatedSO.val() && hiddenRelatedSO.val() != '0') {
            $('[name="salesorder_id"]').closest('.input-group').find('.clearReferenceSelection').removeClass('hide');
            $('#salesorder_id_display').val(hiddenRelatedSO.data('name')).attr('disabled', true);
            $('[name="salesorder_id"]').val(hiddenRelatedSO.val());
        }
    }
    // End Hieu Nguyen
})

