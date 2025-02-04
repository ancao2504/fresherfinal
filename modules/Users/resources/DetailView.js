/*
    DetailView.js
    Author: Hieu Nguyen
    Date: 2019-03-13
    Purpose: to handle logic in DetailView form
*/

jQuery(function($) {
    // Check for duplicate PBX extension number
    vtUtils.doWhen(
        function() {
            return $('input[name="phone_crm_extension"]')[0] != null;
        },
        function() {
            $('input[name="phone_crm_extension"]').attr('data-rule-remote-check-duplicate', 'index.php?module=Users&action=CheckDuplicateAjax');
        }
    );

    // Prevent saving if the PBX extension number is duplicated
    app.event.on(Vtiger_Detail_Js.PreAjaxSaveEvent, function (e) {
        var extInput = $('input[name="phone_crm_extension"]');

        if (extInput.target != null && !extInput.valid()) {
            e.preventDefault();
        }
    });

    // Handle click event for button copy acces key
    $('#copy-access-key').on('click', function () {
        var input = document.getElementById('access-key');
        input.select();
        document.execCommand('copy');
        app.helper.showSuccessNotification({ message: app.vtranslate('JS_COPIED_TO_CLIPBOARD_SUCCESS_MSG') });
    });
});