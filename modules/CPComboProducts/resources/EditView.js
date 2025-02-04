/*
    EditView.js
    Author: Kelvin Thang
    Date: 2020-02-29
    Purpose: to handle logic on the UI
*/

jQuery(function($) {
    vtUtils.doWhen(function () {
        return jQuery('.lineItemTabCloneCopy').length > 0;
    }, function () {
        registerRemoveItemResultAndSection();
    });
});

/**
 * Added by Kelvin Thang
 * Date: 2020-02-29
 * Function which will register Remove lineItem Result
 */
function registerRemoveItemResultAndSection() {
    $('#lineItemResult').remove();
    $('#addSection').closest('.btn-group').remove();
    $('.lineItemTax').closest('div').css('display', 'none');
    $('#currency_id').closest('div').css('display', 'none');
}
