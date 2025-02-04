/*
    VTZaloOTTMessageTask.js
    Author: Hieu Nguyen
    Date: 2020-11-23
    Purpose: handle logic on the UI for VTZaloOTTMessageTask workflow task settings
*/

jQuery(function ($) {
    var formModelContent = $('.editTaskBody');
    var messageInput = formModelContent.find('[name="message"]');

    // Insert variable
    formModelContent.find('#btnInsertVariable').on('click', function () {
        var variable = formModelContent.find('#variable').val();
        if (variable == '') return;

        UIUtils.insertAtCursor(variable, messageInput);
    });
});