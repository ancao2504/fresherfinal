/*
    TargetListHelper.js
    Author: Hieu Nguyen
    Date: 2021-07-16
    Purpose: handle client logic for Add To Target List and Remove From Target List buttons
*/

jQuery(function ($) {
    let reportDetailControler = window.app.controller();
    let advanceFilterInstance = reportDetailControler.advanceFilterInstance;
    window.last_advanced_filter = advanceFilterInstance.getValues();    // To track filter changes

    // Save filter values anytime the button submit filter is clicked
    $('button.generateReport').on('click', function () {
        window.last_advanced_filter = advanceFilterInstance.getValues();
    });

    // Handle button Add To / Remove From Target List
    $('#btn-add-to-target-list, #btn-remove-from-target-list').on('click', function () {
        let clickedButton = $(this);
        let advancedFilter = advanceFilterInstance.getValues();

        if (JSON.stringify(advancedFilter) != JSON.stringify(window.last_advanced_filter)) {
            bootbox.alert(app.vtranslate('Reports.JS_TARGET_LIST_HELPER_FILTER_CHANGES_NOT_SUBMITTED_ERROR_MSG'));
            return;
        }

        checkReportResult(function () {
            loadTargetLists(function (targetLists) {
                let title = clickedButton.text();
                let mode = clickedButton.data('mode');
                showModal(title, mode, targetLists, advancedFilter);
            });
        });

        return false;
    });

    function checkReportResult(callback) {
        app.helper.showProgress();
        let advancedFilter = advanceFilterInstance.getValues();

        var params = {
            'module': 'Reports',
            'advanced_filter': advancedFilter,
            'record': app.getRecordId(),
            'action': 'DetailAjax',
            'mode': 'getRecordsCount'
        };

        app.request.post({ data: params }).then(function (error, data) {
            app.helper.hideProgress();
            var count = parseInt(data);

            if (error) {
                bootbox.alert(app.vtranslate('Reports.JS_TARGET_LIST_HELPER_UNKOWN_ERROR_MSG'));
                return false;
            }

            if (count == 0) {
                bootbox.alert(app.vtranslate('Reports.JS_TARGET_LIST_HELPER_REPORT_NO_RECORD_ERROR_MSG'));
                return false;
            }

            callback();
        });
    }

    function loadTargetLists(callback) {
        app.helper.showProgress();

        let params = {
            module: 'Reports',
            action: 'TargetListHelperAjax',
            mode: 'loadTargetLists'
        };

        app.request.post({ data: params }).then(function (error, res) {
            app.helper.hideProgress();

            if (error) {
                bootbox.alert(app.vtranslate('Reports.JS_TARGET_LIST_HELPER_LOAD_TARGET_LIST_ERROR_MSG'));
                return false;
            }

            if (res.target_lists.length == 0) {
                bootbox.alert(app.vtranslate('Reports.JS_TARGET_LIST_HELPER_NO_TARGET_LIST_ERROR_MSG'));
                return false;
            }

            callback(res.target_lists);
        });
    }

    function showModal(title, mode, targetLists, advancedFilter) {
        let modal = $('#target-list-helper-modal').clone(true, true);

        // Display modal title
        modal.find('.modal-header .pull-left').text(title);

        // Display hint text
        if (mode == 'addToTargetList') {
            modal.find('.hint').text(app.vtranslate('Reports.JS_TARGET_LIST_HELPER_ADD_TO_TARGET_LIST_HINT'));
        }
        else {
            modal.find('.hint').text(app.vtranslate('Reports.JS_TARGET_LIST_HELPER_REMOVE_FROM_TARGET_LIST_HINT'));
        }

        // Setup inital values
        let form = modal.find('form');
        form.find('[name="mode"]').val(mode);
        form.find('[name="advanced_filter"]').val(JSON.stringify(advancedFilter));

        // Display submit button type
        form.find('[name="submit"]').addClass(mode == 'addToTargetList' ? 'btn-success' : 'btn-danger');

        let callBackFunction = function (data) {
            data.find('#target-list-helper-modal').removeClass('hide');
            let form = data.find('#target-list-helper-form');
            let targetListIdInput = form.find('[name="target_list_id"]');
            
            // Fill target lists
            for (var i = 0; i < targetLists.length; i++) {
                item = targetLists[i];
                targetListIdInput.append('<option value="'+ item.id +'">'+ item.name +'</option>');
            };

            // Init modal form
            let controller = Vtiger_Edit_Js.getInstance();
            controller.registerBasicEvents(form);
            vtUtils.applyFieldElementsView(form);
            targetListIdInput.select2({ width: 'resolve' });

            // Form validation
            var params = {
                submitHandler: function (form) {
                    var form = jQuery(form);

                    if (targetListIdInput.val() == '') {
                        bootbox.alert(app.vtranslate('Reports.JS_TARGET_LIST_HELPER_NO_TARGET_LIST_SELECTED_ERROR_MSG'));
                        return false;
                    }

                    var confirmMsg = app.vtranslate('Reports.JS_TARGET_LIST_HELPER_ADD_TO_TARGET_LIST_CONFIRM_MSG');

                    if (mode == 'removeFromTargetList') {
                        confirmMsg = app.vtranslate('Reports.JS_TARGET_LIST_HELPER_REMOVE_FROM_TARGET_LIST_CONFIRM_MSG');
                    }

                    bootbox.confirm({
                        message: confirmMsg,
                        callback: function (result) {
                            if (result) {
                                handleSubmitForm(form, mode);
                            }
                        }
                    });
                }
            };

            form.vtValidate(params);
        };

        var modalParams = {
            cb: callBackFunction
        };

        app.helper.showModal(modal, modalParams);
    }

    function handleSubmitForm(form, mode) {
        app.helper.showProgress();
        let params = form.serializeFormData();

        app.request.post({ data: params }).then(function (error, res) {
            app.helper.hideProgress();

            if (error) {
                bootbox.alert(app.vtranslate('Reports.JS_TARGET_LIST_HELPER_UNKOWN_ERROR_MSG'));
                return false;
            }

            if (!res.success && res.error_code == 'NO_RECORD') {
                bootbox.alert(app.vtranslate('Reports.JS_TARGET_LIST_HELPER_REPORT_NO_RECORD_ERROR_MSG'));
                return false;
            }

            let result = res.result;
            let btnDismiss = form.find('.cancelLink');
            result.target_list_name = form.find('[name="target_list_id"]').find('option:selected').text();

            if (mode == 'addToTargetList') {
                // All record failed
                if (result.error == result.total) {
                    bootbox.alert(app.vtranslate('Reports.JS_TARGET_LIST_HELPER_ADD_TO_TARGET_LIST_ERROR_MSG'));
                    return false;
                }

                // Display result summary
                let message = app.vtranslate('Reports.JS_TARGET_LIST_HELPER_ADD_TO_TARGET_LIST_SUCCESS_MSG', result);
                bootbox.alert(message);
                btnDismiss.trigger('click');
            }

            if (mode == 'removeFromTargetList') {
                // All record failed
                if (result.error == result.total) {
                    bootbox.alert(app.vtranslate('Reports.JS_TARGET_LIST_HELPER_REMOVE_FROM_TARGET_LIST_ERROR_MSG'));
                    return false;
                }

                // Display result summary
                let message = app.vtranslate('Reports.JS_TARGET_LIST_HELPER_REMOVE_FROM_TARGET_LIST_SUCCESS_MSG', result);
                bootbox.alert(message);
                btnDismiss.trigger('click');
            }
        });
    }
});