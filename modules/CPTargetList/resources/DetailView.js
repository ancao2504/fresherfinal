
/**
 * Author: Phu Vo
 * Date: 2019.07.17
 * Description: DetailView UI logic handler
 */

jQuery($ => {
    let type = $('#detailView').find('.fieldValue.cptargetlist_type');
    let typeValue = type.find('.value > span').text().trim();
    let zaloFilter = $('[data-block="LBL_ZALO_USER_FILTER"]');

    // Display Zalo User filter base on target list type
    if (typeValue === 'Zalo') {
        zaloFilter.show();
        zaloFilter.find('input, select, button').prop('disabled', false);
    }
    else {
        zaloFilter.hide();
        zaloFilter.find('input, select, button').prop('disabled', true);
    }

    // Added by Hieu Nguyen on 2019-07-24 to init select2 for custom filter at related list header
    app.event.on('post.relatedListLoad.click', function (event, data) {
        var customFilterInput = $('.relatedHeader').find('#customFilter');

        if (!customFilterInput.hasClass('select2-offscreen')) {
            customFilterInput.select2();
        }
    });
    // End Hieu Nguyen
});

// Implemented by Hieu Nguyen on 2019-07-23
function triggerAddRelationsFromFilter(input, filterId) {
    if(filterId == '') return;

    var relatedModuleName = $(input).closest('.relatedContainer').find('[name="relatedModuleName"]').val();
    var relatedModuleLabel = $('.tab-item.active').find('.tab-label').text().trim();

    var message = app.vtranslate('JS_APPENDED_TO_EXISTING_LIST', relatedModuleLabel)+ '<br><br>' + app.vtranslate('JS_WISH_TO_PROCEED');
    
    bootbox.confirm({
        message: message,
        callback: function (result) {
            if (!result) {
                // Reset value if user cancel this action
                $(input).val('').trigger('change');
                return;
            }

            // Call ajax to add relations from selected filter
            app.helper.showProgress();

            var params = {
                'module': 'CPTargetList',
                'action': 'RelationAjax',
                'mode' : 'addRelationsFromFilter',
                'target_list_id': app.getRecordId(),
                'related_module': relatedModuleName,
                'filter_id': filterId,
            };

            app.request.post({ data: params }).then(function (error, res) {
                    app.helper.hideProgress();

                    // Handle result
                    if (error || !res) {
                        app.helper.showErrorNotification({ message: app.vtranslate('JS_SOCIAL_INTEGRATION_UNKNOWN_ERROR_MSG') });
                        return;
                    }

                    if (res.records_count == 0) {
                        app.helper.showErrorNotification({ message: app.vtranslate('JS_NO_RECORDS_RELATED_TO_THIS_FILTER') });
                        return;
                    }

                    app.helper.showSuccessNotification({ message: app.vtranslate('JS_APPENDED_TO_EXISTING_LIST_SUCCESS_MSG', relatedModuleLabel) });

                    // Reset value for next actions
                    $(input).val('').trigger('change');

                    // Reload the list
                    var relatedListController = Vtiger_RelatedList_Js.getInstance();

                    relatedListController.loadRelatedList().then(function () {
                        relatedListController.triggerRelationAdditionalActions();
                    });
                }
            );
        }
    });
}

// Implemented by Hieu Nguyen on 2019-07-23
function triggerComposeSocialMessage(channel) {
    app.helper.showProgress();

    var params = {
        'module': 'CPSocialIntegration',
        'action': 'SocialMessageAjax',
        'mode': 'checkTargetList',
        'target_list_id': app.getRecordId()
    };

    app.request.post({ data: params }).then(function (error, data) {
        app.helper.hideProgress();

        if (error || !data) {
            app.helper.showErrorNotification({ message:app.vtranslate('CPSocialIntegration.JS_SOCIAL_INTEGRATION_UNKNOWN_ERROR_MSG') });
            return;
        }

        if(data.customers_count == 0) {
            app.helper.showErrorNotification({ message:app.vtranslate('CPSocialIntegration.JS_SOCIAL_MESSAGE_EMPTY_TARGET_LIST_ERROR_MSG') });
            return;
        }

        SocialHandler.composeSocialMessage(channel);
    });
}