/*
    EditView.js
    Author: Hieu Nguyen
    Date: 2020-04-09
    Purpose: to handle logic on the UI
*/

jQuery(function ($) {
    // Fill Account when a Contact is selected
    $('.fieldValue.contact_id').find('[name="contact_id"]').on('Vtiger.PostReference.Selection Vtiger.PostReference.QuickCreateSave', function (e, res) {
        var contactId = $(this).val();
        if (contactId == '') return;

        // Get data from selected Contact
        var editViewController = Vtiger_Edit_Js.getInstance();
        var params = { 'source_module': 'Contacts', 'record': contactId };
        
        editViewController.getRecordDetails(params).then(function (res) {
            var contactAccountId = res.data.account_id;
            if (contactAccountId == '' || contactAccountId == '0') return;
            var accountInput = $('.fieldValue.related_to').find('[name="related_to"]');

            // In case related Account is not selected yet
            if (accountInput.val() == '' || accountInput.val() == '0') {
                // Get data from Contact's Account
                var params = { 'source_module': 'Accounts', 'record': contactAccountId };

                editViewController.getRecordDetails(params).then(function (res) {
                    var contactAccountName = res.data.accountname;
                    if (contactAccountName == '') return;

                    // Fill Account id and name into the related Account field
                    accountInput.val(contactAccountId);
                    accountInput.closest('td').find('[name="related_to_display"]').val(contactAccountName).attr('readonly', true);
                    accountInput.closest('td').find('.clearReferenceSelection').removeClass('hide');
                });
            }
        });
    });

    // Added by Phuc on 2020.03.18 to init lost reason field
    // Modified logic by Phu Vo on 2021.08.09 
    let currentSalesStage = $('.recordEditView [name="sales_stage"]').val();
    let currentResult = $('.recordEditView [name="potentialresult"]').val();

    $('.potentiallostreason.fieldLabel').append(' <span class="redColor">*</span>');
    
    $('.recordEditView [name="potentialresult"]').change(function (e, trigger = true) {
        if (!trigger) return;
        
        let lostReasonInput = $('.recordEditView .potentiallostreason');
        let lostReasonDescriptionInput = $('.recordEditView .lost_reason_description');
        let thisValue = $(this).val();

        if (thisValue == 'Closed Lost') {
            lostReasonInput.closest('tr').removeClass('hide');
            lostReasonInput.removeClass('hide');
            lostReasonInput.find('select').attr('data-rule-required', true);
            lostReasonDescriptionInput.removeClass('hide');
        }
        else {
            lostReasonInput.find('select').select2('val', '');
            lostReasonInput.find('select').removeAttr('data-rule-required');
            lostReasonInput.addClass('hide'); 
            lostReasonDescriptionInput.find('textarea').val('').trigger('change');
            lostReasonDescriptionInput.addClass('hide');

            // Hide tr if this row only have 1 field
            if (lostReasonInput.closest('tr').find('td:visible').html() == '') {
                lostReasonInput.closest('tr').addClass('hide');
            }
        }
    }).trigger('change');   // Added trigger change by Hieu Nguyen on 2022-04-01 to trigger re-display form when page load

    $('.recordEditView [name="sales_stage"]').change(function (e, trigger = true) {
        if (!trigger) return;
        
        let thisValue = $(this).val();
        
        if (['Closed Lost', 'Closed Won'].includes(currentSalesStage) && thisValue != currentSalesStage){
            let currentSalesStageLabel = $('.recordEditView [name="sales_stage"]').find(`option[value="${currentSalesStage}"]`).text();
            let replaceParams = { sales_stage: currentSalesStageLabel };
            let confirmationMessage = app.vtranslate('Potentials.JS_SALES_STAGE_REVERT_CONFIRMATION_MSG', replaceParams);

            app.helper.showConfirmationBox({ message: confirmationMessage }).then(
                () => {
                    currentSalesStage = '';
                    
                    if (['Closed Lost', 'Closed Won'].includes(thisValue)) {
                        $('.recordEditView [name="potentialresult"]').val(thisValue).trigger('change');
                        $('.recordEditView [name="potentialresult"]').select2('readonly', true);
                    }
                    else {
                        $('.recordEditView [name="potentialresult"]').val('').trigger('change');
                        $('.recordEditView [name="potentialresult"]').select2('readonly', false);
                    }
                },
                () => {
                    $('.recordEditView [name="sales_stage"]').val(currentSalesStage).trigger('change');
                },
            );
        }
        else {
            if (['Closed Lost', 'Closed Won'].includes(thisValue)) {
                $('.recordEditView [name="potentialresult"]').val(thisValue).trigger('change');
                $('.recordEditView [name="potentialresult"]').select2('readonly', true);
            }
            else if (currentResult != $('.recordEditView [name="potentialresult"]').val()) {
                $('.recordEditView [name="potentialresult"]').val('').trigger('change');
                $('.recordEditView [name="potentialresult"]').select2('readonly', false);
            }
        }
        
        setTimeout(() => currentResult = '');
    }).trigger('change');
    // Ended by Phuc
});