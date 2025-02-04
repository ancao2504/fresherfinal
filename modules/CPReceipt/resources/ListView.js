/*
    ListView.js
    Author: Phuc Lu
    Date: 2019.09.19
    Purpose: to handle logic on the UI
*/

function confirmComplete(recordId) {
    app.helper.showProgress();
    var params = {
        'module': 'CPReceipt',
        'action': 'ConfirmAjax',
        'mode': 'getAmountVND',
        'record': recordId
    };

    app.request.post({ data: params }).then(function(error, data) {
        app.helper.hideProgress();

        if (error) {
            app.helper.showErrorNotification({message: app.vtranslate('JS_ERROR', 'CPReceipt')});
            return;
        }

        var declareConfirmCompleteModal = $('#divConfirmCompleteModal').clone(true, true);

        // Change div to form
        var formHtml = "<form class='form-horizontal formDeclareConfirmCompleteForm' method='POST'>" + declareConfirmCompleteModal.find('.formDeclareConfirmCompleteForm').html() + '</form>';  
        declareConfirmCompleteModal.find('.formDeclareConfirmCompleteForm').remove();
        declareConfirmCompleteModal.append(formHtml);

        declareConfirmCompleteModal.find('.amountVnd').html(data.formattedAmountVnd);
        declareConfirmCompleteModal.find('[name="receivedAmountVND"]').val(data.formattedAmountVnd);
        declareConfirmCompleteModal.find('[name="record"]').val(recordId);

        var callBackFunction = function(data) {             
            data.find('#divConfirmCompleteModal').removeClass('hide');        
            var form = data.find('.formDeclareConfirmCompleteForm'); 
            
            var params = {
                submitHandler: function(form) {
                    var form = $(form);
                    var newParams = form.serializeFormData();

                    app.helper.showProgress();
                    app.request.post({data: newParams}).then(function(error, data) {
                        app.helper.hideProgress();
                        
                        if (error) {
                            app.helper.showErrorNotification({message: app.vtranslate('JS_ERROR', 'CPReceipt')});
                            return;
                        }
                        
                        app.helper.showSuccessNotification({message: app.vtranslate('JS_PAY_SUCCESSFULLY', 'Products')});
                        app.helper.hideModal();
                        window.location = window.location;
                    })
                }
            }

            // Form validation
            form.vtValidate();

            form.submit(function(e, isConfirm = false, confirmResult = false) {
                if (form.find('[name="receivedAmountVND"]').val() == '') {
                    e.preventDefault();
                    return;
                }
                
                if (form.find('[name="receivedAmountVND"]').val() == 0) {
                    e.preventDefault();
                    app.helper.showErrorNotification({message: app.vtranslate('JS_VALUE_MUST_BE_GREATER_THAN_0', 'CPReceipt')});
                    return;
                }

                // Ask for confirm a time
                if (!isConfirm) {                    
                    e.preventDefault();
                    app.helper.showConfirmationBox({message: app.vtranslate('JS_CONFIRM_COMPLETE', 'CPReceipt')}).then(
                        function(e) { 
                            // Form validation
                            form.vtValidate(params);
                            form.trigger('submit', [true, true]);
                            },
                        function() {
                            form.trigger('submit', [true, false]);
                        }
                    );
                }

                // If user doesn't choose yes, prevent action
                if (!confirmResult) {
                    e.preventDefault();
                }
            });
        };

        var modalParams = {
            cb: callBackFunction
        }
    
        app.helper.showModal(declareConfirmCompleteModal, modalParams);
    });
}

function validateAndCalculateRemaining(remainingElement) {
    var currentReceived = app.unformatCurrencyToUser(jQuery(remainingElement).val());
    var currentAmount = app.unformatCurrencyToUser(jQuery(remainingElement).closest('.form-group').prev().find('.amountVnd').html());

    if (currentAmount < currentReceived) {
        currentReceived = currentAmount;
        jQuery(remainingElement).val(app.convertCurrencyToUserFormat(currentReceived));
    }
    
    var currentRemaining = currentAmount - currentReceived;

    jQuery(remainingElement).closest('.form-group').next().find('.remainingAmountVND').html(app.convertCurrencyToUserFormat(currentRemaining));
}