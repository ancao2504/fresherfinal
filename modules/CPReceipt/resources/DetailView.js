/*
    DetailView.js
    Author: Phuc Lu
    Date: 2019.07.19
    Purpose: to handle logic on the UI
*/

// Added by Phuc on 2019.07.19 to create function to update status of receipt
function updateStatus(type, value) {
    var confirmLabel = 'JS_CONFIRM_COMPLETE';

    if (value == 'cancelled') {
        confirmLabel = 'JS_COMFIRM_CANCEL';
    }

    app.helper.showConfirmationBox({message: app.vtranslate(confirmLabel, 'CPReceipt')}).then(function() { 
        app.helper.showProgress();
        var params = {
            'module': 'CPReceipt',
            'action': 'UpdateStatusAjax',
            'record': jQuery('[name="record_id"]').val(),
            'type'  : type,
            'value' : value
        };

        app.request.post({ data: params }).then(function(error, data) {
            app.helper.hideProgress();

            if (error) {
                app.helper.showErrorNotification({message: app.vtranslate('JS_ERROR', 'CPReceipt')});
                return;
            }

            app.helper.showSuccessNotification({message: app.vtranslate("JS_SAVE_SUCCESSFULLY", 'CPReceipt')});

            // Reload the page
            window.location = window.location;
        });
    });
}

// Added by Phuc 0n 2019.09.27 to display for invoices
invoiceInterval = setInterval(function() {
    if ($('.fieldValue.invoices_detail').length > 0) {
        $('.fieldValue.invoices_detail').next().next().remove();
        $('.fieldValue.invoices_detail').next().remove();
        $('.fieldValue.invoices_detail').prev().remove();
        $('.fieldValue.invoices_detail').attr('colspan', 4);
        clearInterval(invoiceInterval);
    }
}, 100);