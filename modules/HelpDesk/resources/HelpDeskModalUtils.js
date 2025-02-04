/**
 * @author Tin Bui
 * @email tin.bui@onlinecrm.vn
 * @create date 2022.03.16
 * @desc Modal utils js for HelpDesk
 */

let HelpDeskModalUtils = new class {
    openStatusModal(ticket_status = '') {
        let self = this;
        
        let params = {
            module: 'HelpDesk',
            view: 'ModalAjax',
            mode: 'openStatusModal',
            record: app.getRecordId(),
        };

        app.helper.showProgress();

        app.request.get({ data: params }).then((err, html) => {
            app.helper.hideProgress();
            
            app.helper.showModal(html, {
                preShowCb: function () {
                    let form = $('.statusForm');
                    let editJs = Vtiger_Edit_Js.getInstance();
                    editJs.registerBasicEvents(form);
                    vtUtils.applyFieldElementsView(form);
                    CustomOwnerField.initCustomOwnerFields();
                    form.vtValidate();

                    form.find('[name="ticketstatus"]').on('change', function () {
                        let status = $(this).val();
                        
                        let hideBeforeWaitCloseFields = {
                            related_cpslacategory: ['Wait Close', 'Closed'],
                            is_send_survey: ['Closed'],
                            helpdesk_over_sla_reason: ['Closed'],
                            over_sla_note: ['Closed']
                        }

                        for (const fieldName in hideBeforeWaitCloseFields) {
                            form.find(`.${fieldName}`).toggle(hideBeforeWaitCloseFields[fieldName].includes(status));
                        }
                    }).trigger('change');

                    if (ticket_status) {
                        form.find('[name="ticketstatus"]').val(ticket_status).trigger('change');
                    }
                },
                cb: function () {
                    let form = $('.statusForm');
                    
                    form.find('.js-save').on('click', function () {
                        if (form.valid()) {
                            let formData = form.serializeFormData();

				            app.helper.showProgress();
                            
                            app.request.post({data: formData}).then(function (err, res) {
                                app.helper.hideProgress();
                                
                                if (res && !err) {
                                    app.helper.hideModal();
                                    app.helper.showSuccessNotification({message: app.vtranslate('JS_SUCCESS')});

                                    setTimeout(() => {
                                        // Check over sla
                                        let editJs = Vtiger_Edit_Js.getInstance();
                                        
                                        editJs.getRecordDetails({source_module: 'HelpDesk', record: app.getRecordId()}).then(function(res){
                                            let ticket = res.data;
                                            
                                            if (ticket.over_sla == 1 && ticket.helpdesk_over_sla_reason == '')  {
                                                self.openOverSLAModal();
                                            }
                                            else {
                                                $('.tab-item.active').trigger('click');
                                            }
                                        });
                                    }, 100);
                                }
                                else {
                                    app.helper.showErrorNotification({message: app.vtranslate('JS_FAILED')});
                                }
                            });
                        }
                    });
                }
            });
        });
    }

    openOverSLAModal() {
        let params = {
            module: 'HelpDesk',
            view: 'ModalAjax',
            mode: 'openSLAModal',
            record: app.getRecordId(),
        };

        app.helper.showProgress();

        app.request.get({ data: params }).then((err, html) => {
            app.helper.hideProgress();
            
            app.helper.showModal(html, {
                preShowCb: function () {
                    let form = $('.overSLAForm');
                    let editJs = Vtiger_Edit_Js.getInstance();
                    editJs.registerBasicEvents(form);
                    vtUtils.applyFieldElementsView(form);
                    CustomOwnerField.initCustomOwnerFields();
                    form.vtValidate();
                },
                cb: function () {
                    $('.modal-backdrop').off('click');
                    let form = $('.overSLAForm');
                    
                    form.find('.js-save').on('click', function () {
                        if (form.valid()) {
                            var formData = form.serializeFormData();
				            app.helper.showProgress();
                            
                            app.request.post({data: formData}).then(function (err, res) {
                                app.helper.hideProgress();
                                if (res && !err) {
                                    app.helper.showSuccessNotification({message: app.vtranslate('JS_SUCCESS')});
                                    app.helper.hideModal();
                                    $('.tab-item.active').trigger('click');
                                }
                                else {
                                    app.helper.showErrorNotification({message: app.vtranslate('JS_FAILED')});
                                }
                            });
                        }
                    });
                }
            });
        });
    }
}