/*
    ModuleBuilder.js
    Author: Hieu Nguyen
    Date: 2018-08-17
    Purpose: handle logic on the UI to create a new module
*/

jQuery(function($) {
    var prefix = 'CP';

    $('#btnCreateModule').click(function(e) {
        var moduleBuilderContainer = $('#moduleBuilderModal').clone(true, true);

        var callBackFunction = function(data) {
            data.find('#moduleBuilderModal').removeClass('hide');

            var form = data.find('.moduleBuilderForm');
            var moduleName = form.find('[name="moduleName"]');
            moduleName.val(prefix);
            moduleName.data('value', prefix);

            moduleName.on('keyup', function (e) {
                var value = moduleName.val().trim();
                
                // Module prefix must be CP_
                if(value.indexOf(prefix) !== 0) {
                    moduleName.val(moduleName.data('value'));
                    return;
                }

                var moduleNameValue = value.replace(/\s/g, '');
                moduleName.val(moduleNameValue);
                moduleName.data('value', moduleNameValue);
            });

            // BEGIN-- Added by Phu Vo on 2020.08.11 -- Handle checkbox extension and person
            const radionCheckboxs = form.find(':input[name="isExtension"], :input[name="isPerson"]');
            radionCheckboxs.on('click', function() {
                if (!$(this).is(':checked')) return;
                if ($(this).is(':checked')) {
                    radionCheckboxs.not($(this)).attr('checked', false);
                }
            });
            // END-- Added by Phu Vo on 2020.08.11 -- Handle checkbox extension and person

            var params = {
                submitHandler: function(form) {
                    var form = $(form);

                    // Validate form
                    var moduleNameValue = moduleName.val().trim();
                    var pattern = /^[a-zA-Z_][a-zA-Z0-9_]*$/;

                    if(!pattern.test(moduleNameValue)) {
                        var errorMsg = app.vtranslate('JS_SPECIAL_CHARACTERS') + ' ' + app.vtranslate('JS_NOT_ALLOWED');

                        vtUtils.showValidationMessage(moduleName, errorMsg, {
                            position: {
                                my: 'bottom left',
                                at: 'top left',
                                container: form
                            }
                        });

                        return false;
                    }

                    var params = form.serializeFormData();
                    params['module'] = app.getModuleName();
                    params['parent'] = app.getParentModuleName();
                    params['action'] = 'Basic';
                    params['mode'] = 'createModule';

                    // Submit form
                    app.request.post({ 'data': params }).then(function(error, data) {
                        app.helper.hideProgress();

                        if(error == null && data.success == '1') {
                            app.helper.hideModal();

                            var message = app.vtranslate('JS_MODULE_BUILDER_CREATE_MODULE_SUCCESS_MSG');
                            app.helper.showSuccessNotification({'message': message});

                            location.reload();
                        } 
                        else {
                            if(data.success == 0) {
                                var errorMsg = app.vtranslate('JS_MODULE_BUILDER_CREATE_MODULE_ERROR_MSG');

                                if(data.message == 'MODULE_EXISTS') {
                                    errorMsg = app.vtranslate('JS_MODULE_BUILDER_MODULE_NAME_EXISTS_ERROR_MSG');
                                }
                                else if (data.message == 'MODULE_EXISTS_IN_REGISTER_FILE') {
                                    errorMsg = app.vtranslate('JS_MODULE_BUILDER_MODULE_NAME_EXISTS_IN_REGISTER_FILE_ERROR_MSG');
                                }

                                vtUtils.showValidationMessage(moduleName, errorMsg, {
                                    position: {
                                        my: 'bottom left',
                                        at: 'top left',
                                        container: form
                                    }
                                });
                            }
                            else {
                                var errorMsg = app.vtranslate('JS_MODULE_BUILDER_CREATE_MODULE_ERROR_MSG');
                                app.helper.showErrorNotification({'message': errorMsg});
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

        app.helper.showModal(moduleBuilderContainer, modalParams);
    });
});