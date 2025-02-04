// Create by Phu vo on 2018.12.21
jQuery(function() {
    let initSMSValidator = function() {
        let message = $("#message");
        let limit = message.attr("maxlength");
        let SMSCountRemain = $("#SMSCountRemain");
        let SMSCountDownContainer = $("#SMSCountDownContainer");

        $("#SMSCountLimit").text(limit);
        SMSCountRemain.text(limit - message.val().length);

        message.on('input propertychange', function() {
            let str = message.val();

            SMSCountRemain.text(limit - str.length); // Update character remain
            SMSCountDownContainer.toggleClass("text-danger", str.length >= limit);
        });
    }

    let initSMSTemplateSelector = function() {
        let select = $('#smsTemplate');
        let message = $("#message");

        select.on('change', function() {
            message.val($(this).val()).trigger('propertychange');
        });
    }

    initSMSValidator();
    initSMSTemplateSelector();
});