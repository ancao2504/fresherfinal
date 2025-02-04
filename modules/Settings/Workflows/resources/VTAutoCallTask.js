/*
    VTAutoCallTask.css
    Author: Hieu Nguyen
    Date: 2020-07-23
    Purpose: handle logic on the UI for VTAutoCallTask workflow task settings
*/

jQuery(function ($) {
    var formModelContent = $('.editTaskBody');
    var textToCallInput = formModelContent.find('[name="text_to_call"]');
    var handleResponseInput = formModelContent.find('[name="handle_response"]');
    var confimKeyInput = formModelContent.find('[name="confirm_value"]');
    var cancelKeyInput = formModelContent.find('[name="cancel_value"]');
    var targetFieldInput = formModelContent.find('[name="target_field"]');
    var confimedValueInput = formModelContent.find('[name="confirmed_value"]');
    var cancelledValueInput = formModelContent.find('[name="cancelled_value"]');

    // Init validation
    initValidation(handleResponseInput.is(':checked'));

    // Re-init validation after handle response checkbox value changed
    handleResponseInput.on('change', function () {
        var mustHandleResponse = $(this).is(':checked');
        initValidation(mustHandleResponse);
    });

    // Insert variable
    formModelContent.find('#btnInsertVariable').on('click', function () {
        var variable = formModelContent.find('#variable').val();
        if (variable == '') return;

        UIUtils.insertAtCursor(variable, textToCallInput);
    });

    // Update options after target field value changed
    targetFieldInput.on('change', function () {
        var selectedField = $(this).val();
        var fieldOptions = _PICKLIST_FIELDS[selectedField].options;
        var htmlOtions = '';

        $.each(fieldOptions, function (key, item) {
            htmlOtions += `<option value="${key}">${item.label}</option>`;
        });

        confimedValueInput.select2('destroy');
        cancelledValueInput.select2('destroy');
        confimedValueInput.html(htmlOtions).select2();
        cancelledValueInput.html(htmlOtions).select2();
    });

    function initValidation(handleResponse) {
        $.validator.setDefaults({
            ignore: ':disabled'
        });

        if (handleResponse) {
            formModelContent.find('.toggleRequired').find('.redColor').show();
            formModelContent.find('.toggleRequired').find(':input').attr('data-rule-required', 'true').attr('disabled', false);
        }
        else {
            formModelContent.find('.qtip').remove();
            formModelContent.find('.input-error').removeClass('input-error');
            formModelContent.find('.toggleRequired').find('.redColor').hide();
            formModelContent.find('.toggleRequired').find(':input').attr('disabled', true);
        }
    }
});