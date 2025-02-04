/**
 * @author Tin Bui
 * @email tin.bui@onlinecrm.vn
 * @create date 2022.03.28
 * @desc Helpdesk Form Parent
 */

class HelpDesk_Form_Js {
    constructor() {
        this.$form = $('#EditView, #QuickCreate');
        if (this.$form.length == 0) return;

        this.initEvent();
    }

    initEvent() {
        let self = this;
        this.initRelatedEmailField();

        this.$form.find('[name="contact_id"]').on(Vtiger_Edit_Js.postReferenceSelectionEvent, function () {
            let contactId = $(this).val();

            Vtiger_Edit_Js.getInstance().getRecordDetails({source_module: 'Contacts', record: contactId}).then(function(res){
				let contact = res.data;
                
                self.$form.find('[name="contact_email"]').val(contact.email);
                self.$form.find('[name="helpdesk_contact_type"]').val(contact.contacts_type).trigger('change');
                self.$form.find('[name="contact_mobile"]').val(contact.mobile);
			});
        });

        this.$form.find('.contact_id .clearReferenceSelection').on('click', function () {
            self.$form.find('[name="contact_email"]').val('');
            self.$form.find('[name="helpdesk_contact_type"]').val().trigger('change');
            self.$form.find('[name="contact_mobile"]').val();
        });

        if (!this.$form.find('[name="record"]').val()) {
            if (this.$form.find('[name="contact_id"]').val()) {
                this.$form.find('[name="contact_id"]').trigger(Vtiger_Edit_Js.postReferenceSelectionEvent);
            }
            
            this.$form.find('[name="ticketstatus"]').val('Open').trigger('change').prop('readonly', true);
        }

    }

    initRelatedEmailField() {
        let field = this.$form.find('[name="helpdesk_related_emails"]');
        let rawValue = field.val() ?? '';
        let tags = rawValue.split(' |##| ');
        
        field.select2({
            tags: tags,
            separator: ' |##| ',
            minimumInputLength: 1,
            formatInputTooShort: () => app.vtranslate('HelpDesk.JS_MSG_ENTER_VALID_EMAIL'),
            placeholder: app.vtranslate ('HelpDesk.JS_MSG_ENTER_VALID_EMAIL')
        });

        field.on('change', function () {
            let data = [];
            let emailRegex = /^[_/a-zA-Z0-9*]+([!"#$%&'()*+,./:;<=>?\^_`'{|}~-]?[a-zA-Z0-9/_/-])*@[a-zA-Z0-9]+([\_\.]?[a-zA-Z0-9\-]+)*\.([\-\_]?[a-zA-Z0-9])+(\.?[a-zA-Z0-9]+)?$/;
            let emails = $(this).val().trim().split(' |##| ');
            
            for (let i in emails) {
                if (emailRegex.test(emails[i])) {
                    data.push({id: emails[i], text: emails[i]});
                }
            }
            
            $(this).select2('data', data);
        });        
    }
}
