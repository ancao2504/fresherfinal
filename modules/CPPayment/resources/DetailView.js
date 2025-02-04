/*
    DetailView.js
    Author: Phuc Lu
    Date: 2019.08.15
    Purpose: to handle logic on the UI
*/

// Added by Phuc on 2019.07.19 to create function to update status of receipt
function updateStatus(type, value) {
    var confirmLabel = '';

    switch(type) {
        case 'accountant_status':
            confirmLabel = 'JS_PROPOSE_CONFIRM';
            break;
        case 'leader_status':
        case 'manager_status':
            if (value == 'approved') {
                confirmLabel = 'JS_APPROVE_CONFIRM';
            }
            else {
                confirmLabel = 'JS_REJECT_CONFIRM';
            }

            break;
        case 'status':
            confirmLabel = 'JS_CONFIRM_COMPLETE';
            if (value == 'cancelled') {
                confirmLabel = 'JS_COMFIRM_CANCEL';
            }

            break;
    };
    
    app.helper.showConfirmationBox({message: app.vtranslate(confirmLabel, 'CPPayment')}).then(function() { 
        app.helper.showProgress();
        var params = {
            'module': 'CPPayment',
            'action': 'UpdateStatusAjax',
            'record': $('[name="record_id"]').val(),
            'type'  : type,
            'value' : value
        };

        app.request.post({ data: params }).then(function(error, data) {
            app.helper.hideProgress();

            if (error) {
                app.helper.showErrorNotification({message: app.vtranslate('JS_ERROR', 'CPPayment')});
                return;
            }

            infoLabel = '';

            switch(type) {
                case 'accountant_status':
                    infoLabel = 'JS_SUCCESS_AND_WAIT';
                    break;
                case 'leader_status':
                case 'manager_status':
                    if(value == 'approved') {
                        infoLabel = 'JS_APPROVED';
                    }
                    else {
                        infoLabel = 'JS_REJECTED';
                    }
                    break;
                case 'status':
                    infoLabel = 'JS_SAVE_SUCCESSFULLY';
                    break;
            };

            app.helper.showSuccessNotification({message: app.vtranslate(infoLabel, 'CPPayment')});

            // Reload the page
            window.location = window.location;
        });
    });
}

// Added by Phuc on 2019.09.27 to display for invoices
invoiceInterval = setInterval(function() {
    if ($('.fieldValue.invoices_detail').length > 0) {
        $('.fieldValue.invoices_detail').next().next().remove();
        $('.fieldValue.invoices_detail').next().remove();
        $('.fieldValue.invoices_detail').prev().remove();
        $('.fieldLabel.accountant_id').html('');
        $('.fieldValue.accountant_id').html('');
        $('.fieldValue.invoices_detail').attr('colspan', 4);
        clearInterval(invoiceInterval);
    }
}, 100);