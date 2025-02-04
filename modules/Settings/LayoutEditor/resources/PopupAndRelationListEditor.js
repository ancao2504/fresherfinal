/*
    PopupAndRelationListEditor.js
    Author: Hieu Nguyen
    Date: 2019-10-09
    Purpose: handle logic on the UI to modify popup and relation list layouts
*/

const POPUP_MIN_FIELDS = 3;
const RELATION_LIST_MIN_FIELDS = 5;

jQuery(function ($) {
    var form = $('#popupAndRelationListLayoutForm');
    var popupFieldsInput = form.find('[name="popup_fields"]');
    var relationListFieldsInput = form.find('[name="relation_list_fields"]');

    initTaggingField(popupFieldsInput, _MODULE_FIELDS);
    initTaggingField(relationListFieldsInput, _MODULE_FIELDS);

    // Handle sort field change
    form.find('.sort-field').on('change', function () {
        var sortOrderInput = $(this).closest('.row').find('.sort-order');

        if ($(this).val() == '') {
            sortOrderInput.hide();
        }
        else {
            sortOrderInput.show();
        }
    });

    // Handle submit button
    form.find('.btnSaveLayout').on('click', function () {
        var popupFields = popupFieldsInput.val().split(',');
        var relationListFields = relationListFieldsInput.val().split(',');

        if (popupFields.length < POPUP_MIN_FIELDS) {
            app.helper.showErrorNotification({ 'message': app.vtranslate('LBL_POPUP_AND_RELATION_LIST_LAYOUT_POPUP_FIELDS_VALIDATION_MIN_LENGTH_ERROR_MSG', POPUP_MIN_FIELDS) });
            popupFieldsInput.select2('open');
            return;
        }

        if (relationListFields.length < RELATION_LIST_MIN_FIELDS) {
            app.helper.showErrorNotification({ 'message': app.vtranslate('LBL_POPUP_AND_RELATION_LIST_LAYOUT_RELATION_LIST_FIELDS_VALIDATION_MIN_LENGTH_ERROR_MSG', RELATION_LIST_MIN_FIELDS) });
            relationListFieldsInput.select2('open');
            return;
        }

        if (!confirm(app.vtranslate('LBL_POPUP_AND_RELATION_LIST_LAYOUT_SAVE_CONFIRM_MSG'))) {
            return;
        }

        app.helper.showProgress();

        var params = form.serializeFormData();
        var layout = {
            'popupLayout': {
                'display_fields': popupFields,
                'sort_field': form.find('[name="popup_sort_field"]').val(),
                'sort_order': form.find('[name="popup_sort_order"]').val(),
            },
            'relationListLayout': {
                'display_fields': relationListFields,
                'sort_field': form.find('[name="relation_list_sort_field"]').val(),
                'sort_order': form.find('[name="relation_list_sort_order"]').val(),
            }
        };

        params['module'] = 'LayoutEditor';
        params['parent'] = app.getParentModuleName();
        params['sourceModule'] = jQuery('#selectedModuleName').val();
        params['action'] = 'Module';
        params['mode'] = 'savePopupAndRelationListLayout';
        params['layout'] = layout;

        app.request.post({ 'data': params })
            .then(function (err, res) {
                app.helper.hideProgress();

                if (err || !res) {
                    app.helper.showErrorNotification({ 'message': app.vtranslate('LBL_POPUP_AND_RELATION_LIST_LAYOUT_ERROR_MSG') });
                    return;
                }

                app.helper.showSuccessNotification({ 'message': app.vtranslate('LBL_POPUP_AND_RELATION_LIST_LAYOUT_SAVE_SUCCESS_MSG') });
            });
    });
});

function initTaggingField(input, selections) {
    // Init select2 with autocomplete
    input.select2({
        closeOnSelect: false,
        tags: selections,
        tokenSeparators: [','],
        createSearchChoice: false
    });

    // Init selected tags
    var selectedTags = input.data('selectedTags');

    if (selectedTags) {
        input.select2('data', selectedTags).trigger('change');
    }

    // Init sortable
    input.select2('container').find('ul.select2-choices').sortable({
        containment: 'parent',
        start: () => {
            input.select2('onSortStart');
        },
        update: () => {
            input.select2('onSortEnd');
        }
    });

    input.addClass('select2-bound');
}