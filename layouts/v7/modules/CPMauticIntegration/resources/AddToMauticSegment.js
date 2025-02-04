/*
    File: AddToMauticSegment.js
    Author: Phuc
    Date: 2020.02.24
*/

function addToMauticSegment() {
    app.helper.showProgress();
    
    var params = {
        'module': 'CPMauticIntegration',
        'action': 'MauticAjax',
        'mode': 'getRecordsToSegmentView',
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

            var callBackFunction = function (data) {       
                data.find('#div-add-to-mautic-segment').removeClass('hide');        
                var form = data.find('.formAddToMauticSegment');
                data.find('.slc_segment').select2();
        
                var controller = Vtiger_Edit_Js.getInstance();
                controller.registerBasicEvents(form);
        
                var params = {
                    submitHandler : function (form) {
                        app.helper.showProgress();
        
                        var form = $(form);
                        var formData = form.serializeFormData();  
                        formData['module'] = 'CPMauticIntegration';
                        formData['action'] = 'MauticAjax';
                        formData['mode'] = 'addRecordsToSegment';
        
                        app.request.post({'data': formData}).then(
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

function addNewSegment(element){
    var element = jQuery(element);

    // Change div to form
    var declareModal = $('#div-add-new-mautic-segment').clone(true, true);
    var formHtml = '<form class="form-horizontal formAddNewMauticSegment" method="POST">' + declareModal.find('.formAddNewMauticSegment').html() + '</form>';  
    declareModal.find('.formAddNewMauticSegment').remove();
    declareModal.append(formHtml);

    var callBackFunction = function (data){       
        data.find('#div-add-new-mautic-segment').removeClass('hide');        
        var form = data.find('.formAddNewMauticSegment'); 

        var controller = Vtiger_Edit_Js.getInstance();
        controller.registerBasicEvents(form);

        var params = {
            submitHandler: function(form){               
                app.helper.showProgress();

                var form = $(form);
                var formData = form.serializeFormData();  
                formData['module'] = 'CPMauticIntegration';
                formData['action'] = 'MauticAjax';
                formData['mode'] = 'addNewSegment';

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