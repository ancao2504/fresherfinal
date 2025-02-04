/**
 * @author Tin Bui
 * @email tin.bui@onlinecrm.vn
 * @create date 2022.03.16
 * @desc Email reply form script
 */

class EmailReplyForm {
    constructor($el) {
        this.$el = $el;
        
        if (this.$el.length == 0) {
            return false;
        }
        
        this.initialize();
    }

    async initialize() {
        this.initCKEditor();
        this.registerEmailCCField();
        this.initAttachmentsField();
        this.registerEvents();
        this.loadTicketData();
    }

    registerEvents() {
        let self = this;

        this.$el.find('[name="ticketstatus"]').select2();

        this.$el.find('[name="ticketstatus"]').on('change', function () {
            if ($(this).val() != $(this).data('oldValue')) {
                self.$el.find('.btnSubmit').text(app.vtranslate('HelpDesk.JS_LBL_BTN_SEND_EMAIL_AND_UPDATE_STATUS'));
            }
            else {
                self.$el.find('.btnSubmit').text(app.vtranslate('HelpDesk.JS_LBL_BTN_SEND_EMAIL'));
            }
        });

        this.$el.vtValidate({
            submitHandler: function (form) {
                form = $(form);
                
                // Validate form data
                let isContentEmpty = self.ckInstance.getPlainText().replace('\n', '').length == 0;
                
                if (isContentEmpty) {
                    app.helper.showAlertNotification({
						message: app.vtranslate('HelpDesk.JS_EMAIL_CONTENT_WAS_EMPTY')
					});

                    return false;
                }

                // Handle send reply
                let confirmMessage = app.vtranslate('HelpDesk.JS_CONFIRM_SEND_REPLY');
                let tkOldStatus = form.find('[name="ticketstatus"]').data('oldValue');
                let tkNewStatus = form.find('[name="ticketstatus"]').val();
                let updateTkStatus = tkOldStatus != tkNewStatus;
                
                if (updateTkStatus) {
                    confirmMessage = app.vtranslate('HelpDesk.JS_CONFIRM_SEND_REPLY_AND_UPDATE_STATUS');
                    
                    let oldStatusLabel = form.find('[name="ticketstatus"]').find(`option[value="${tkOldStatus}"]`).text();
                    let newStatusLabel = form.find('[name="ticketstatus"]').find(`option[value="${tkNewStatus}"]`).text();

                    confirmMessage = confirmMessage.replace('%oldstatus%', `<strong>${oldStatusLabel}</strong>`);
                    confirmMessage = confirmMessage.replace('%newstatus%', `<strong>${newStatusLabel}</strong>`);
                }

                app.helper.showConfirmationBox({
                    message: confirmMessage,
                    buttons: {
                        cancel: {
                            label: app.vtranslate('JS_CANCEL'),
                            className : 'btn-default confirm-box-btn-pad pull-right'
                        },
                        confirm: {
                            label: app.vtranslate('JS_CONFIRM'),
                            className : 'confirm-box-ok confirm-box-btn-pad btn-primary'
                        }
                    }
                }).then(function () {
                    let data = new FormData(form[0]);
                    app.helper.showProgress();

                    // Send email
                    $.ajax({
                        url: 'index.php',
                        method: 'POST',
                        cache: false,
                        contentType: false,
                        processData: false,
                        enctype: 'multipart/form-data',
                        data: data,
                        success: function (res) {
                            app.helper.hideProgress();
                            
                            if (res.result.success) {
                                app.helper.showSuccessNotification({
                                    message: app.vtranslate('HelpDesk.JS_SEND_EMAIL_SUCCESS')
                                });
                                self.clearForm();
                                app.event.trigger('post.EmailReplyForm.send');
                            }
                            else {
                                app.helper.showErrorNotification({
                                    message: app.vtranslate('HelpDesk.JS_SEND_EMAIL_FAILED')
                                });
                            }
                        },
                        error: function (err) {
                            app.helper.hideProgress();
                            app.helper.showErrorNotification({message: app.vtranslate('JS_THERE_WAS_SOMETHING_ERROR', 'Vtiger') });
                            console.log(err);
                        },
                        complete: function () {
                            // Handle update status
                            if (updateTkStatus) {
                                let params = {
                                    module: 'HelpDesk',
                                    action: 'HandleAjax',
                                    mode: 'updateTicketStatus',
                                    ticket_id: app.getRecordId(),
                                    ticketstatus: tkNewStatus
                                };
                                
                                app.request.post({data: params}).then(function (err, res) {
                                    app.helper.hideProgress();
                                    
                                    if (res.success == 1) {
                                        app.helper.showSuccessNotification({message: app.vtranslate('HelpDesk.JS_UPDATE_TICKET_STATUS_SUCCESS')});
                                        form.find('[name="ticketstatus"]').val(tkNewStatus).data('oldValue', tkNewStatus).trigger('change');
                                    }
                                    else if (res.success == 2) {
                                        app.helper.showAlertNotification({message: app.vtranslate('HelpDesk.JS_UPDATE_TICKET_STATUS_FAILED_MISSING_DATA')});
                                        HelpDeskModalUtils.openStatusModal(tkNewStatus);
                                        form.find('[name="ticketstatus"]').val(tkOldStatus).trigger('change');
                                    }
                                    else {
                                        app.helper.showErrorNotification({message: app.vtranslate('HelpDesk.JS_UPDATE_TICKET_STATUS_FAILED')});
                                        form.find('[name="ticketstatus"]').val(tkOldStatus).trigger('change');
                                    }
                                });
                            }
                        }
                    });
                });
            }
        });
    }

    initCKEditor() {
        let form = this.$el;
        this.ckInstance = new Vtiger_CkEditor_Js();
        let textArea = form.find('[name="emailContent"]');

        if (textArea.length > 0) {
            this.ckInstance.loadCkEditor(textArea, {
                toolbar: [
                    { name: 'basicstyles', groups: ['basicstyles', 'cleanup', 'align', 'list', 'indent', 'colors', 'links'], items: ['Bold', 'Italic', 'Underline', '-', 'TextColor', 'BGColor', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', '-', 'NumberedList', 'BulletedList', '-', 'Link', 'Unlink', '-', 'RemoveFormat'] },
                    { name: 'styles', items: ['Font', 'FontSize'] },
                    { name: 'actions', items: ['SelectEmailTemplate', '-', 'SelectFaq']}
                ]
            });

            let instance = this.ckInstance.getCkEditorInstanceFromName();

            instance.addCommand('SelectEmailTemplate', { 
                exec: () => this.openEmailTemplateModal()
            });

            instance.ui.addButton('SelectEmailTemplate', {
                label: app.vtranslate('HelpDesk.JS_LBL_BTN_SELECT_EMAIL_TEMPLATE'),
                command: 'SelectEmailTemplate',
                icon: ''
            });

            instance.addCommand('SelectFaq', {
                exec: () => this.openFaqModal()
            });

            instance.ui.addButton('SelectFaq', {
                label: app.vtranslate('HelpDesk.JS_LBL_BTN_SELECT_FAQ'),
                command: 'SelectFaq',
                icon: ''
            });
        }
    }

    registerEmailCCField() {
        let form = this.$el;
        let ccField = form.find('[name="emailCC"]');
        let separator = ccField.data('seperator');

        ccField.select2({
            tags: [],
            separator: separator,
            minimumInputLength: 1,
            formatInputTooShort: () => app.vtranslate('HelpDesk.JS_MSG_ENTER_VALID_EMAIL'),
            placeholder: app.vtranslate ('HelpDesk.JS_MSG_ENTER_VALID_EMAIL')
        });

        ccField.on('change', function () {
            if ($(this).val() == '') return;

            let data = [];
            let emailRegex = /^[_/a-zA-Z0-9*]+([!"#$%&'()*+,./:;<=>?\^_`'{|}~-]?[a-zA-Z0-9/_/-])*@[a-zA-Z0-9]+([\_\.]?[a-zA-Z0-9\-]+)*\.([\-\_]?[a-zA-Z0-9])+(\.?[a-zA-Z0-9]+)?$/;
            let emails = $(this).val().trim().split(separator);
            let invalidEmails = [];

            for (let i in emails) {
                if (emailRegex.test(emails[i])) {
                    data.push({
                        id: emails[i],
                        text: emails[i]
                    })
                }
                else  {
                    invalidEmails.push(emails[i]);
                }
            }

            ccField.select2('data', data);

            if (invalidEmails.length > 0) {
                app.helper.showAlertBox({
                    message: `<span style="font-weight: bold;">${app.vtranslate('HelpDesk.JS_INVAILD_EMAIL')}:</span><br>${invalidEmails.join('<br>')}`
                });
            }
        });
    }

    initAttachmentsField() {
        let field = this.$el.find('[name="emailAttachments[]"]');
        let options = {
            STRING: {
                remove: app.vtranslate('JS_MULTIFILES_ICON_REMOVE'), 
                selected: app.vtranslate('JS_MULTIFILES_SELECTED_LIST'), 
                denied: app.vtranslate('JS_MULTIFILES_INVALID_FORMAT'), 
                duplicate: app.vtranslate('JS_MULTIFILES_DUPLICATED'),
                toobig: app.vtranslate('JS_MULTIFILES_OVER_MAX_FILE_SIZE'),
                toomany: app.vtranslate('JS_MULTIFILES_OVER_MAX_FILE_NUM'),
                toomuch: app.vtranslate('JS_MULTIFILES_OVER_MAX_TOTAL_FILE_SIZE'),
            }
        };

        let validator = field.attr('data-filevalidator');
        validator = validator.isJsonString() ? JSON.parse(validator) : {};

        if (Object.keys(validator).length > 0) {
            if (validator.max_total_files_size != -1) {
                options.maxsize = validator.max_total_files_size;
            }
            if (validator.max_upload_files != -1) {
                options.max = validator.max_upload_files;
            }
            if (validator.allowed_upload_file_exts.length > 0) {
                options.accept = validator.allowed_upload_file_exts.join('|');
            }
        }

        field.MultiFile(options);
    }

    clearForm() {
        if (CKEDITOR.instances['emailContent']) {
            CKEDITOR.instances['emailContent'].setData('');
        }

        this.$el.find('.fileUploadContainer').find('.MultiFile-remove').trigger('click');
    }

    loadTicketData() {
        let self = this;
        
        Vtiger_Edit_Js.getInstance().getRecordDetails({source_module: 'HelpDesk', record: app.getRecordId()}).then(function (res) {
            let ticket = res.data;
            self.$el.find('[name="ticketstatus"]').val(ticket.ticketstatus).data('oldValue', ticket.ticketstatus).trigger('change');    
            let relatedEmails = ticket.helpdesk_related_emails ?? '';
           
            if (relatedEmails != '') {
                relatedEmails = relatedEmails.split(' |##| ');
                let seperator = self.$el.find('[name="emailCC"]').data('seperator');
                
                self.$el.find('[name="emailCC"]').select2({
                    tags: relatedEmails,
                    separator: seperator
                }).val(relatedEmails.join(seperator)).trigger('change')
            }
        });
    }
    
    openEmailTemplateModal() {
        let self = this;

        let params = {
            module: 'EmailTemplates',
            view: 'PopupDatatable'
        };

        app.helper.showProgress();

        app.request.get({ data: params }).then((error, response) => {
            app.helper.hideProgress();

            app.helper.showModal(response, {
                cb: function (container) {
                    let table = container.find('table');
                    table.find('.row_templatename').live('click', function() {
                        let emailTemplateId = $(this).data('templateid');
                        
                        let params = {
                            module: 'HelpDesk',
                            action: 'HandleAjax',
                            mode: 'fetchEmailTemplate',
                            ticket_id: app.getRecordId(),
                            template_id: emailTemplateId
                        };
                        
                        app.request.post({ data: params }).then(function (err, res) {
                            if (!err && res.success == 1) {
                                let body = res.body;
                                self.ckInstance.loadContentsInCkeditor(body);
                            }
                            
                            app.helper.hideModal();
                        });
                    });
                }
            });
        });
    }

    openFaqModal() {
        let self = this;
        let popupInstance = Vtiger_Popup_Js.getInstance();
        
        let params = {
            module: 'Faq',
            view: 'Popup',
            search_params: [[['faq_used_for', 'c', 'Customer,Partner']]]
        };

        app.helper.showProgress();
        
        popupInstance.show(params, function (selectedRow) {
            app.helper.hideProgress();
            selectedRow = JSON.parse(selectedRow);
            let id = Object.keys(selectedRow)[0];

            Vtiger_Edit_Js.getInstance().getRecordDetails({record:id, source_module: 'Faq'}).then(function (res) {
                let answer = res.data.faq_answer;
                answer = answer.replaceAll('\n', '<br>');
                answer = answer.replaceAll('\t', '<br>');
                self.ckInstance.loadContentsInCkeditor($('<textarea/>').html(answer).text());
            });
        });    
    }
}