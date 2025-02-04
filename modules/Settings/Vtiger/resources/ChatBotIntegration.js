/*
    File: ChatBotIntegration.js
    Author: Phu Vo
    Date: 2019.03.22
    Purpose: System notification ui handler
*/

CustomView_BaseController_Js('Settings_Vtiger_ChatBotIntegration_Js', {}, {
    registerEvents: function () {
        this._super();
        this.showHideChatChanel();
        this.handleRemoveChatbot();
        this.registerEventFormSubmit();
        this.initSelectUsers($('.inputElement.select-users'));
    },

    showHideChatChanel: function () {
        const form = $('form[name="configs"]');

        form.find('[name="current_channel"]').on('change', function() {
            let value = $(this).val();

            $('.bot-channel').hide();

            if (!value) {
                $('#bot-default').show();
                $('.saveButton').prop('disabled', true);
            }
            else if ($(`#${value}`)[0] != null) {
                $(`#${value}`).show();
                $('.saveButton').prop('disabled', false);
            }
        }).trigger('change');
    },

    showHideBotInput: function (modal, botName) {
        let showClass = `.${botName}-input`;
        modal.find('.bot-input').hide();
        modal.find(showClass).show();
    },

    getChatbots: function (botName) {
        let chatbots = {};

        $(`#${botName} ul.form-list li`).each(function (index, target) {
            let botData = $(target).data();
            chatbots[botData['id']] = botData;
        });

        return chatbots;
    },

    validateBotId: function (botName, id) {
        let chatbots = this.getChatbots(botName);
        if (typeof chatbots[botName] != 'undefined') return false;

        return true;
    },

    showHanaBotModal: function (mode, target) {
        const modal = $('.editChatbotModal').clone().attr('id', 'editChatbot');
        const self = this;
        let botName = $('[name="current_channel"]').val();
        let li = null;
        
        if (mode == 'add') {
            li = $($(`#${botName}`).find('.list-template').html());
        }
        else if (mode == 'edit') {
            li = $(target).closest('li');
            modal.find('.modal-header h4.pull-left').html(app.vtranslate('JS_CHAT_BOT_EDIT_CHATBOT'));
        }

        app.helper.showModal(modal, {
            preShowCb: function (modal) {
                const form = modal.find('form[name="edit_chatbot"]');

                self.showHideBotInput(modal, botName);

                if (mode == 'edit') {
                    form.find('[name="id"]').val(li.data('id'));
                    form.find('[name="name"]').val(li.data('name'));
                    form.find('[name="auth_token"]').val(li.data('auth_token'));
                }

                form.vtValidate({
                    submitHandler: function() {
                        li.data('id', form.find('[name="id"]').val());
                        li.data('name', form.find('[name="name"]').val());
                        li.data('auth_token', form.find('[name="auth_token"]').val());
    
                        if (mode == 'add') {
                            let oldLength = $(`#${botName}`).find('.form-list li').length;
    
                            li.find('.label').text(oldLength + 1 + '. ' + form.find('[name="name"]').val());
                            $(`#${botName}`).find('.form-list').append(li);
    
                        }
                        else if (mode == 'edit') {
                            li.find('.label').text(li.index() + 1 + '. ' + form.find('[name="name"]').val());
                        }
    
                        modal.find('button.close').trigger('click');
    
                        return false;
                    }
                });
            }
        });
    },

    showBBHBotModal: function (mode, target) {
        const modal = $('.editChatbotModal').clone().attr('id', 'editChatbot');
        const self = this;
        let botName = $('[name="current_channel"]').val();
        let li = null;

        if (mode == 'add') {
            li = $($(`#${botName}`).find('.list-template').html());
        }
        else if (mode == 'edit') {
            li = $(target).closest('li');
            modal.find('.modal-header h4.pull-left').html(app.vtranslate('JS_CHAT_BOT_EDIT_CHATBOT'));
        }

        app.helper.showModal(modal, {
            preShowCb: function (modal) {
                const form = modal.find('form[name="edit_chatbot"]');

                self.showHideBotInput(modal, botName);

                if (mode == 'edit') {
                    form.find('[name="id"]').val(li.data('id'));
                    form.find('[name="name"]').val(li.data('name'));
                    form.find('[name="access_token"]').val(li.data('access_token'));
                }

                form.vtValidate({
                    submitHandler: function() {    
                        li.data('id', form.find('[name="id"]').val());
                        li.data('name', form.find('[name="name"]').val());
                        li.data('access_token', form.find('[name="access_token"]').val());
    
                        if (mode == 'add') {
                            let oldLength = $(`#${botName}`).find('.form-list li').length;
    
                            li.find('.label').text(oldLength + 1 + '. ' + form.find('[name="name"]').val());
                            $(`#${botName}`).find('.form-list').append(li);
    
                        }
                        else if (mode == 'edit') {
                            li.find('.label').text(li.index() + 1 + '. ' + form.find('[name="name"]').val());
    
                        }
    
                        modal.find('button.close').trigger('click');
    
                        return false;
                    }
                });
            }
        });
    },

    handleRemoveChatbot: function () {
        $('[name="configs"]').on('click', '.removeChatbot', function() {
            const li = $(this).closest('li');
            let label = li.data('name');

            app.helper.showConfirmationBox({
                message: app.vtranslate('JS_CHAT_BOT_REMOVE_CHATBOT_CONFIRM', { chatbot_name: `<strong>${label}</strong>` }),
            }).then(function() {
                li.remove();
            });
        });
    },

    /**
     * Method to process User select with ajax (Options may include User and Group)
     * @author Phu Vo (2019.08.01)
     * @param {jQuery} dom
     * Example:
     *  <input
            type="text" autocomplete="off" class="inputElement select-users" style="width: 100%"
            placeholder="Chọn một người dùng"
            name="missed_call_alert_no_main_owner_specific_user"
            data-user-only="true"
            data-selected-tags='{ZEND_JSON::encode(Vtiger_Owner_UIType::getCurrentOwners($CONFIG->missed_call_alert_no_main_owner_specific_user))}'
        />
     */
    initSelectUsers(dom) {
        // Make sure input dom is a jQuery object
        if (! (dom instanceof jQuery)) dom = $(dom);

        // It may pass in a jquery list of dom
        dom.each((index, target) => {
            target = $(target); // Alternative for $(this);

            // Init some options
            let multiple = target.data('multiple') ? target.data('multiple') : target.prop('multiple'); // Use this param to control multiple select logic
            let userOnly = target.data('user-only') ? true : false; // Set to false to include group in option list
            let params = {}; // Params to work with jquery select2
            let useType = target.data('use-type') ? true : false; // Set to True to include owner type (Users|Groups) in select value
            let selectedTags = target.data('selectedTags');
            let placeholder = target.data('placeholder') || target.attr('placeholder') || '';

            // Ajax data process method, use specific method to apply with recursive solution (Don't bother it now)
            let resultProcessor = (results) => {
                results = results.map((result, index) => {// May use to peform other logic process, we will refactor it later
                    // It may contain sub level
                    if (result.children) resultProcessor(result.children);

                    // Prety sure that ajax handler will alway return data with owner type at id, so we can process it here to remove with condition
                    if (!useType && result.id) result.id = result.id.replace(/Users\:|Groups\:/g, '').trim();

                    return result;
                });
            }

            // Init default params
            params = {
                minimumInputLength: _VALIDATION_CONFIG.autocomplete_min_length,    // Refactored by Hieu Nguyen on 2021-01-15
                ajax: {
                    type: 'POST',
                    dataType: 'json',
                    cache: true,
                    data: (term, page) => {
                        let data = {
                            module: 'Vtiger',
                            action: 'HandleOwnerFieldAjax',
                            mode: 'loadOwnerList',
                            assignable_users_only: false, // It get all user|group without privilege
                            keyword: term, // String to search
                        };

                        if (userOnly) data['user_only'] = true; // Receive only user list or include group list

                        return data;
                    },
                    results: (data) => {
                        let results = data.result || []; // Make sure it will have something to return

                        // Process logic hook start from here to modify result output data
                        resultProcessor(results);

                        return { results };
                    },
                    transport: (params) => {
                        return jQuery.ajax(params);
                    },
                },
            };

            // Extra params for multiple select
            if (multiple) {
                params.closeOnSelect = false;
                params.tags = [];
                params.tokenSeparators = [','];

                // Manual format item
                params.formatSelection = (object, container) => {
                    if (object.id) {
                        let params = object.id.split(':');
                        let template =  `<div>${object.text}</div>`;

                        // Process item type
                        if (useType) container
                            .closest('.select2-search-choice')
                            .attr('data-type', params[0])

                        return template;
                    }

                    return object.text;
                }
            }

            // Process selected tag before apply
            if (!useType && selectedTags) resultProcessor(selectedTags);
            if (!multiple && selectedTags) selectedTags = selectedTags[0];

            // Apply select2 with ajax
            target.select2(params);

            // Apply and trigger data
            if (selectedTags) target.select2('data', selectedTags).trigger('change');

            // Process Single select clear
            // [Todo] Refactor to peform this action after select2 was fully applied to void async problem
            if (!multiple) {
                target.select2('container').closest('.fieldValue').addClass('select-users-wraper');

                // Create clear button next to select2 container
                let clearusers = target
                    .select2('container')
                    .after('<span class="clearUsers far fa-times"></span>')
                    .next('.clearUsers');

                // And then bind it with click event
                clearusers.on('click', e => {
                    // Replace display value with placeholder text
                    target.select2('data', {id: '', text: placeholder}).trigger('change');
                });
            }
        });
    },
    registerEventFormSubmit: function () {
        const form = $('form[name="configs"]');
        const self = this;

        form.on('submit', e => {
            e.preventDefault();

            let target = $(e.target);
            let formData = target.serializeFormData();
            let botName = $('[name="current_channel"]').val();
            let chatbots = self.getChatbots(botName);

            // Prevent save config without any bot
            if (Object.keys(chatbots).length == 0) {
                app.helper.showErrorNotification({message: app.vtranslate('JS_CHATBOT_INTEGRATION_SAVE_CONFIG_BOT_EMPTY_ERROR_MSG')});
                return;
            }

            target.find('input[type="checkbox"]').each( function (e) {
                let fieldName = $(this).attr('name');
                formData[fieldName] = $(this).is(':checked') ? 1 : 0;
            });

            // Assign chatbot to formData object
            if (typeof formData.chatbot_config == 'undefined') formData.chatbot_config = {};
            formData.chatbot_config.chatbots = chatbots;

            let params = {
                module: 'Vtiger',
                parent: 'Settings',
                action: 'SaveChatBotIntegrationConfig',
                configs: formData,
            }

            app.helper.showProgress();

            app.request.post({data: params}).then((err, res) => {
                app.helper.hideProgress();

                if(err) {
                    app.helper.showErrorNotification({message: app.vtranslate('JS_CHATBOT_INTEGRATION_SAVE_CONFIG_ERROR_MSG')});
                    return;
                }

                app.helper.showSuccessNotification({message: app.vtranslate('JS_CHATBOT_INTEGRATION_SAVE_CONFIG_SUCCESS_MSG')});
                return;
            });
        });
    }
});