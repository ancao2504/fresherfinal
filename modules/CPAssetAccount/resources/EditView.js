/*
    EditView.js
    Author: Phuc Lu
    Date: 2019.07.18
    Purpose: to handle logic on the UI
*/

jQuery(function($) {
    $('[name="cpassetaccount_currency"]').change(function() {
        var currency = $(this).find('option:selected').val();
        var label = '';

        if(typeof currencies != 'undefined') {
            label = currencies[currency].currencysymbol;
        }

        $('[name="orginal_balance"]').prev().html(label);
    });

});

