
/**
 * Author: Phu Vo
 * Date: 2019.07.17
 * Description: Edit View UI logic handler
 */

jQuery($ => {
    let zaloFilter = $(`[data-block="LBL_ZALO_USER_FILTER"]`);

    // Display Zalo User filter base on target list type
    $('#EditView').find('[name="cptargetlist_type"]').on('change', e => {
        let target = $(e.target);

        if (target.val() === 'Zalo') {
            zaloFilter.show();
            zaloFilter.find('input, select, button').prop('disabled', false);
        }
        else {
            zaloFilter.hide();
            zaloFilter.find('input, select, button').prop('disabled', true);
        }
    }).trigger('change');

    // Added by Phu Vo on 2019.08.21 to validate zalo filters
    jQuery.validator.addMethod('zalo-filters', function(value, element, params) {
        try {
            if ($('[name="cptargetlist_type"]')[0] == null || $('[name="cptargetlist_type"]').val() !== 'Zalo') return true;
            if (value) return true;
    
            if (
                !$('#CPTargetList_Edit_fieldName_cptargetlist_zalo_city').val()
                && !$('[name="cptargetlist_zalo_gender"]').val()
                && !$('#CPTargetList_Edit_fieldName_cptargetlist_zalo_age').val()
                && !$('#CPTargetList_Edit_fieldName_cptargetlist_zalo_platform').val()
            ) {
                return false;
            }

            return true;
        }
        catch (err) {
            console.log(err);
            return false;
        }
    }, jQuery.validator.format(app.vtranslate('JS_ZALO_FILTERS_ERROR_MESSAGE')));
    // End validate zalo filters
    
    setTimeout(() => {
        // Validate zalo filters
        let filters = $(
            '#CPTargetList_Edit_fieldName_cptargetlist_zalo_city' + 
            ', [name="cptargetlist_zalo_gender"]' + 
            ', #CPTargetList_Edit_fieldName_cptargetlist_zalo_age' + 
            ', #CPTargetList_Edit_fieldName_cptargetlist_zalo_platform'
        );
        
        filters.each((index, target) => {
            $(target).rules('add', { 'zalo-filters': true });
        });

        filters.on('change', e => {
            filters.not(e.target).valid();
        });
    }, 100);

    // Added by Hieu Nguyen on 2021-11-23 to disable checkbox Sync To Mautic if it is already checked when edit existing target list
    let syncToMauticInput = $('[name="sync_to_mautic"]:checkbox');

    if (syncToMauticInput.is(':checked')) {
        syncToMauticInput.attr('disabled', 'true');
        syncToMauticInput.closest('td').find('[name="sync_to_mautic"]:hidden').val(1);  // To prevent submit value switch to 0 when the main checkbox is disabled
    }
    // End Hieu Nguyen
});