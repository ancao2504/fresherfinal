/**
 * Name: EditView.js
 * Author: Phu Vo
 * Date: 2020.12.03
 * Description: Handle ui logic for SMS Template Edit View
 */

$(function () {
    var form = $('form#EditView');

    // Added by Hieu Nguyen on 2020-12-07 to insert variable
    form.find('#btnInsertVariable').on('click', function (event) {
        var variable = form.find('#variable').val();
        if (!variable) return;

        UIUtils.insertAtCursor(variable, form.find('#message'));
        form.find('#message').trigger('change');
    });
    // End Hieu Nguyen

    // Trigger update message counter
    form.find('#message').on('keyup change', function (event) {
        let target = $(event.target);
        let value = target.val() || '';
        form.find('#character-counter').text(value.length);
    }).trigger('change');

    // Validate message content base on message type
    form.find('[name="sms_ott_message_type"]').on('change', function (event) {
        let target = $(event.target);
        let value = target.val();

        if (value == 'SMS') {
            form.find('#message').removeClass('ignore-validation');

            // Added by Hieu Nguyen on 2021-11-16 to remove ascii rule when the provider support unicode SMS
            if (_PROVIDER_INFO && _PROVIDER_INFO['unicode_sms_supported']) {
                form.find('#message').removeAttr('data-rule-asciiOnly');
            }
            // End Hieu Nguyen
        }
        else {
            form.find('#message').addClass('ignore-validation');
            form.find('#message').removeClass('input-error');
        }
    }).trigger('change');
});