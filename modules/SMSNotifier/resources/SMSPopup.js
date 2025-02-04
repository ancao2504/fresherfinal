/*
    SMSPopup.js
    Author: Hieu Nguyen
    Date: 2020-10-19
    Purpose: handle logic on the UI for SMS Popup
*/

$(function () {
    var formContainer = $('#sendSmsContainer');
    var messageInput = formContainer.find('#message');

    // Handle button insert variable
    formContainer.find('#btnInsertVariable').on('click', function () {
        var variable = formContainer.find('#variable').val();
        if (variable == '') return;

        UIUtils.insertAtCursor(variable, messageInput);
        messageInput.trigger('change');
    });
});