/*
*   DetailView.js
*   Author: Phuc Lu
*   Date: 2019.08.02
*   Purpose: customize the layout
*/

jQuery(function($) {
    if ($('.fieldValue.invoice_type').find('span').attr('data-value') == 'buy') {
        if (typeof jsLanguageString != 'undefined') {
            $('.fieldLabel.salesorder_id').html(jsLanguageString['JS_PURCHASE_ORDER']);
            $('.fieldLabel.account_id').html(jsLanguageString['JS_VENDOR']);
            $('.details table.lineItemsTable').find('tr').eq($('.details table.lineItemsTable').find('tr').length - 2).find('td:first div').html(jsLanguageString['JS_PAID']);
        }
        else {
            $('.fieldLabel.salesorder_id').html(app.vtranslate('JS_PURCHASE_ORDER', 'Invoice'));
            $('.fieldLabel.account_id').html(app.vtranslate('JS_VENDOR', 'Invoice'));
            $('.details table.lineItemsTable').find('tr').eq($('.details table.lineItemsTable').find('tr').length - 2).find('td:first div').html(app.vtranslate('JS_PAID', 'Invoice'));
        }
    }

    // Hide field purchase order and vendor
    $('.fieldLabel.related_purchaseorder').html('');
    $('.fieldValue.related_purchaseorder').html('');

    if ($('.fieldLabel.related_purchaseorder').parent().find('.fieldLabel:visible').length < 1)
        $('.fieldLabel.related_purchaseorder').parent().remove();

    $('.fieldLabel.related_vendor').html('');
    $('.fieldValue.related_vendor').html('');
    
    if ($('.fieldLabel.related_vendor').parent().find('.fieldLabel:visible').length < 1)
        $('.fieldLabel.related_vendor').parent().remove();
})