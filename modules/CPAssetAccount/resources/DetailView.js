
/*
    DetailView.js
    Author: Phuc Lu
    Date: 2019.08.16
    Purpose: to handle logic on the UI
*/

function reportDetails() {   
    var declareCoverModal = jQuery('#report_detail_container').clone(true, true);

    var callBackFunction = function(data){       
        data.find('#report_detail_container').removeClass('hide');        
        var form = data.find('.formReportDetail'); 

        var controller = Vtiger_Edit_Js.getInstance();
        controller.registerBasicEvents(form);
        vtUtils.applyFieldElementsView(form);
        vtUtils.initDatePickerFields(form);
        
        // Form validation
        form.vtValidate();

        // Prevent submit action
        jQuery(form).submit(function(e) {
            e.preventDefault();
        })
    }

    var modalParams = {
        cb: callBackFunction
    }

    app.helper.showModal(declareCoverModal, modalParams);
}

function getReportDetails() {
    app.helper.showProgress();
    
    jQuery('#lbl_opening_balance span').html('');
    jQuery('#lbl_closing_balance span').html('');
    jQuery('#tbl_transactions tbody').html('');

    var newParams = {};
    newParams['module'] = 'CPAssetAccount';
    newParams['action'] = 'GetReportDetails';
    newParams['from_date'] = jQuery('[name="report_from_date"]:visible').val();
    newParams['to_date'] = jQuery('[name="report_to_date"]:visible').val();
    newParams['record_id'] = jQuery('[name="record_id"]').val();

    app.request.post({data:newParams}).then(function(error, data){
        app.helper.hideProgress();
        if(error || (error == null && data.success != '1')){
            app.helper.showErrorNotification({message:app.vtranslate('JS_ERROR', 'CPAssetAccount')});
            return;
        }

        var result = data.result;
        jQuery('#lbl_opening_balance span').html(result.opening_balance);
        jQuery('#lbl_closing_balance span').html(result.closing_balance);
        var number = result.transactions.length;

        $.each(result.transactions, function(key, transaction) {
            let newRow = "<tr><td>" + (number--) + "</td><td>" + transaction.transferred_date + "</td><td class='right'>" + transaction.type + transaction.amount + "</td><td  class='left'>" + transaction.code + (transaction.description != '' ? " - " + transaction.description : '') + "</td></tr>";
            jQuery('#tbl_transactions tbody').append(newRow);
        })
    })
}

function saveAsDefaultAssetAccount() {
    app.helper.showConfirmationBox({message:app.vtranslate('JS_CONFIRM_SAVE_AS_DEFAULT_ASSET_ACCOUNT', 'CPAssetAccount')}).then(function() {
        app.helper.showProgress();
        var params = {
            'module': 'CPAssetAccount',
            'action': 'SaveAccountAsDefaultAjax',
            'record': jQuery('input[name="record_id"]').val()
        };

        app.request.post({ data: params }).then(function(error, data) {
            app.helper.hideProgress();

            if (error) {
                app.helper.showErrorNotification({message:app.vtranslate('JS_ERROR', 'CPAssetAccount')});
                return;
            }

            app.helper.showSuccessNotification({message:app.vtranslate('JS_SAVE_SUCCESSFULLY', 'CPAssetAccount')});

            window.location = window.location;
        });
    });
}