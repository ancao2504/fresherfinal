/*
    Relationship.js
    Author: Hieu Nguyen
    Date: 2018-08-30
    Purpose: handle logic on the UI to add a new relationship
*/

jQuery(function($) {
    $('#btnAddRelationship').click(function (e) {
        var relationshipModal = $('#relationshipEditorModal').clone(true, true);

        var callBackFunction = function (data) {
            data.find('#relationshipEditorModal').removeClass('hide');
            var form = data.find('.relationshipEditorForm');
            var leftSideModule = form.find('[name="leftSideModule"]');
            var rightSideModule = form.find('[name="rightSideModule"]');
            var relationType = form.find('[name="relationType"]');
            var listingFunctionName = form.find('[name="listingFunctionName"]');
            var leftSideReferenceField = form.find('[name="leftSideReferenceField"]');
            var rightSideReferenceField = form.find('[name="rightSideReferenceField"]');
            var relationLabelKey = form.find('[name="relationLabelKey"]');
            var relationLabelDisplayEn = form.find('[name="relationLabelDisplayEn"]');
            var relationLabelDisplayVn = form.find('[name="relationLabelDisplayVn"]');

            // Populate default values
            relationType.change(function() {
                var leftSideModuleName = leftSideModule.val();
                var rightSideModuleName = $(this).val();
                var relationTypeValue = $(this).val();

                if (relationTypeValue == '1:1') {
                    listingFunctionName.val('');
                    listingFunctionName.closest('.form-group').hide();
                    leftSideReferenceField.val('related_' + getModuleNameInSingle(rightSideModuleName.toLowerCase()));
                    leftSideReferenceField.closest('.form-group').show();
                    rightSideReferenceField.val('related_' + getModuleNameInSingle(leftSideModuleName.toLowerCase()));
                    rightSideReferenceField.closest('.form-group').show();
                }

                if (relationTypeValue == '1:N') {
                    listingFunctionName.val('get_dependents_list');
                    listingFunctionName.closest('.form-group').show();
                    leftSideReferenceField.val('');
                    leftSideReferenceField.closest('.form-group').hide();
                    rightSideReferenceField.val('related_' + getModuleNameInSingle(leftSideModuleName.toLowerCase()));
                    rightSideReferenceField.closest('.form-group').show();
                }

                if (relationTypeValue == 'N:N') {
                    listingFunctionName.val('get_related_list');
                    listingFunctionName.closest('.form-group').show();
                    leftSideReferenceField.val('');
                    leftSideReferenceField.closest('.form-group').hide();
                    rightSideReferenceField.val('');
                    rightSideReferenceField.closest('.form-group').hide();
                }
            });

            rightSideModule.change(function() {
                var leftSideModuleName = leftSideModule.val();
                var rightSideModuleName = $(this).val();

                leftSideReferenceField.val('related_' + getModuleNameInSingle(rightSideModuleName.toLowerCase()));
                rightSideReferenceField.val('related_' + getModuleNameInSingle(leftSideModuleName.toLowerCase()));
                relationLabelKey.val('LBL_' + getModuleNameInSingle(rightSideModuleName.toUpperCase()) + '_LIST');
                relationLabelDisplayEn.val(rightSideModule.val());

                if (app.getUserLanguage() == 'vn_vn') {
                    relationLabelDisplayVn.val(rightSideModule.find('option:selected').text());
                }
            });

            relationLabelKey.on('keyup', function(e) {
                var value = relationLabelKey.val().trim().toUpperCase().replace(/\s/g, '')
                relationLabelKey.val(value);
            });

            // Form validation
            var params = {
                submitHandler: function (form) {
                    var form = $(form);

                    // Clear all errors
                    form.find(':input').each(function() {
                        vtUtils.hideValidationMessage($(this));
                    });

                    // Validate form
                    if(leftSideReferenceField.is(':visible') && !validateSpecialChars(listingFunctionName)) {
                        return false;
                    }

                    if(leftSideReferenceField.is(':visible') && !validateSpecialChars(leftSideReferenceField)) {
                        return false;
                    }

                    if(leftSideReferenceField.is(':visible') && !validateSpecialChars(rightSideReferenceField)) {
                        return false;
                    }

                    if(!validateSpecialChars(relationLabelKey)) {
                        return false;
                    }

                    var params = form.serializeFormData();
                    params['module'] = app.getModuleName();
                    params['parent'] = app.getParentModuleName();
                    params['action'] = 'Relation';
                    params['mode'] = 'add';

                    // Submit form
                    app.request.post({data: params})
                    .then(function (error, data) {
                        app.helper.hideProgress();
                        
                        if (error) {
                            var errorMsg = app.vtranslate('JS_RELATIONSHIP_EDITOR_CREATE_RELATIONSHIP_ERROR_MSG');
                            app.helper.showErrorNotification({ 'message': errorMsg });
                            return;
                        }

                        if (error == null && data.success != '1') {
                            if (data.message == 'MODULE_NOT_EXISTS') {
                                var errorMsg = app.vtranslate('JS_RELATIONSHIP_EDITOR_MODULE_NOT_EXISTS_ERROR_MSG');
                                showValidationMessage(form, rightSideModule, errorMsg);
                                return;
                            }

                            if (data.message == 'RELATIONSHIP_N2N_EXISTS') {
                                var errorMsg = app.vtranslate('JS_RELATIONSHIP_EDITOR_RELATIONSHIP_N2N_EXISTS_ERROR_MSG');
                                showValidationMessage(form, rightSideModule, errorMsg);
                                return;
                            }
                            
                            if (data.message == 'RELATIONSHIP_EXISTS') {
                                var errorMsg = app.vtranslate('JS_RELATIONSHIP_EDITOR_RELATIONSHIP_EXISTS_ERROR_MSG');
                                showValidationMessage(form, listingFunctionName, errorMsg);
                                return;
                            }                            

                            if (data.message == 'LEFT_SIDE_REFERENCE_FIELD_EXISTS') {
                                var errorMsg = app.vtranslate('JS_RELATIONSHIP_EDITOR_LEFT_SIDE_REFERENCE_FIELD_EXISTS_ERROR_MSG');
                                showValidationMessage(form, leftSideReferenceField, errorMsg);
                                return;
                            }

                            if (data.message == 'RIGHT_SIDE_REFERENCE_FIELD_EXISTS') {
                                var errorMsg = app.vtranslate('JS_RELATIONSHIP_EDITOR_RIGHT_SIDE_REFERENCE_FIELD_EXISTS_ERROR_MSG');
                                showValidationMessage(form, rightSideReferenceField, errorMsg);
                                return;
                            }

                            var errorMsg = app.vtranslate('JS_RELATIONSHIP_EDITOR_CREATE_RELATIONSHIP_ERROR_MSG');
                            app.helper.showErrorNotification({ 'message': errorMsg });
                        }

                        app.helper.hideModal();

                        var message = app.vtranslate('JS_RELATIONSHIP_EDITOR_CREATE_RELATIONSHIP_SUCCESS_MSG');
                        app.helper.showSuccessNotification({ 'message': message });

                        location.reload();
                    });
                }
            };

            form.vtValidate(params);
        };

        var modalParams = {
            cb: callBackFunction
        };

        app.helper.showModal(relationshipModal, modalParams);
    });

    function getModuleNameInSingle(moduleName) {
        if (moduleName.slice(-1).toLowerCase() == 's') {
            return moduleName.slice(0, -1);
        }

        return moduleName;
    }

    function validateSpecialChars(inputElement) {
        var form = inputElement.closest('form');
        var value = inputElement.val().trim();
        var pattern = /^[a-zA-Z_][a-zA-Z0-9_]*$/;

        if (!pattern.test(value)) {
            var errorMsg = app.vtranslate('JS_SPECIAL_CHARACTERS') + ' ' + app.vtranslate('JS_NOT_ALLOWED');
            showValidationMessage(form, inputElement, errorMsg);
            return false;
        }

        return true;
    }

    function showValidationMessage(form, inputElement, message) {
        vtUtils.showValidationMessage(inputElement, message, {
            position: {
                my: 'bottom left',
                at: 'top left',
                container: form
            }
        });
    }
});