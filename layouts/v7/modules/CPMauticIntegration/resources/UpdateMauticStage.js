/*
    File: UpdateMauticStage.js
    Author: Phuc
    Date: 2020.03.04
*/

function updateMauticStage() {
    app.helper.showProgress();
    
    var params = {
        'module': 'CPMauticIntegration',
        'action': 'MauticAjax',
        'mode': 'getUpdateMauticStageView',
        'related_module': app.getModuleName(),
    }

    app.request.post({'data': params}).then(
        function (error, data) {
            app.helper.hideProgress();

            if (error || !data.success) {
                app.helper.showErrorNotification({message: app.vtranslate('JS_ERROR')});
                return;
            }

            var listInstance = window.app.controller();
            var postData = listInstance.getDefaultParams();
            var listSelectAllParams = listInstance.getListSelectAllParams(true);

            listSelectAllParams['search_params'] = listInstance.getListSearchParams();
            postData = jQuery.extend(postData, listSelectAllParams);
            postData = JSON.stringify(postData);

            var declareModal = $(data.content);
            declareModal.find('#params').html(postData);

            var callBackFunction = function (data){       
                data.find('#div-update-mautic-stage').removeClass('hide');        
                var form = data.find('.formUpdateMauticStage');
                data.find('.slc_stage').select2();

                var controller = Vtiger_Edit_Js.getInstance();
                controller.registerBasicEvents(form);

                var params = {
                    submitHandler : function (form){
                        app.helper.showProgress();

                        var form = $(form);
                        var formData = form.serializeFormData();  
                        formData['module'] = 'CPMauticIntegration';
                        formData['action'] = 'MauticAjax';
                        formData['mode'] = 'updateRecordsStage';

                        app.request.post({'data':formData}).then(
                            function (error,data) {
                                app.helper.hideProgress();

                                if (error || !data.success) {
                                    app.helper.showErrorNotification({message: app.vtranslate('JS_ERROR')});
                                    return;
                                }

                                app.helper.showSuccessNotification({message: data.message});
                                app.helper.hideModal();
                            }
                        );
                    }
                };
                
                form.vtValidate(params);
            }

            var modalParams = {
                cb: callBackFunction
            }

            app.helper.showModal(declareModal, modalParams);
        }
    );
}

function addNewStage(element){
    var element = jQuery(element);

    // Change div to form
    var declareModal = $('#div-add-new-mautic-stage').clone(true, true);
    var formHtml = '<form class="form-horizontal formAddNewMauticStage" method="POST">' + declareModal.find('.formAddNewMauticStage').html() + '</form>';  
    declareModal.find('.formAddNewMauticStage').remove();
    
    declareModal.append(formHtml);

    var callBackFunction = function (data){       
        data.find('#div-add-new-mautic-stage').removeClass('hide');        
        var form = data.find('.formAddNewMauticStage'); 

        var controller = Vtiger_Edit_Js.getInstance();
        controller.registerBasicEvents(form);

        var params = {
            submitHandler: function(form){               
                app.helper.showProgress();

                var form = $(form);
                var formData = form.serializeFormData();  
                formData['module'] = 'CPMauticIntegration';
                formData['action'] = 'MauticAjax';
                formData['mode'] = 'addNewStage';

                app.request.post({'data':formData}).then(function (error, data) {
                        app.helper.hidePopup();
                        app.helper.hideProgress();

                        if (error || !data.success) {
                            app.helper.showErrorNotification({message: app.vtranslate('JS_ERROR')});
                            return;
                        }

                        app.helper.showSuccessNotification({message: app.vtranslate('JS_SUCCESS')});
                        element.prev().append("<option value='" + data.id + "'>" + data.name + "</option>");
                        element.prev().val(data.id);
                        element.prev().select2('destroy');
                        element.prev().select2();
                    }
                );
            }
        };

        // Form validation
        form.vtValidate(params);
    }

    var modalParams = {
        cb: callBackFunction
    }

    app.helper.showPopup(declareModal, modalParams);
}
// Ended by Phuc