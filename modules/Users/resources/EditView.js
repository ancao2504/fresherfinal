/*
    DetailView.js
    Author: Hieu Nguyen
    Date: 2019-03-13
    Purpose: to handle logic in EditView form
*/

jQuery(function($) {
    // Fix app.getRecordId function
    app.getRecordId = function() {
        return $('input[name="record"]').val();
    }

    // Hide new user language + currency and format panel as it's handled by default user preferences in config_override.php already
    if (!app.getRecordId()) {
        $('td.fieldLabel.language').hide();
        $('td.fieldLabel.language').next('td.fieldValue').hide();
        $('div[data-block="LBL_CURRENCY_CONFIGURATION"]').hide();
        $('div[data-block="LBL_CURRENCY_CONFIGURATION"]').prev('br').hide();
        $('div[data-block="LBL_CURRENCY_CONFIGURATION"]').prev('br').hide();
    }

    // Check for duplicate PBX extension number
    $('input[name="phone_crm_extension"]').attr('data-rule-remote-check-duplicate', 'index.php?module=Users&action=CheckDuplicateAjax');
});