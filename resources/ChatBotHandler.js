/*
    ChatBotHandler.js
    Author: Hieu Nguyen
    Date: 2020-04-07
    Purpose: To handle logic for chat bot integration on the UI
*/

var ChatBotHandler = {

    triggerComposeChatMessage: function (channel) {
        app.helper.showProgress();

        var params = {
            module: 'CPChatBotIntegration',
            view: 'ChatMessageAjax',
            channel: channel,
            target_module: app.getModuleName(),
            target_record: app.getRecordId()
        };

        app.request.post({ data: params }).then(function (error, data) {
            app.helper.hideProgress();

            if (error || !data) {
                var message = app.vtranslate('CPChatBotIntegration.JS_UNKNOWN_ERROR_MSG');
                app.helper.showErrorNotification({ message: message });
                return;
            }

            if (data == 'customer_not_synced_with_chat_app') {
                var message = app.vtranslate('CPChatBotIntegration.JS_CUSTOMER_NOT_SYNCED_WITH_CHAT_APP_ERROR_MSG');
                app.helper.showErrorNotification({ message: message });
                return;
            }

            ChatBotHandler.composeChatMessage(channel, data);
        });
    },

    // Show compose chat message popup
    composeChatMessage: function (channel, popupHtml) {
        var chatMessageModal = $(popupHtml);

        // Display modal title
        var translatedChannel = app.vtranslate('CPChatBotIntegration.JS_CHANNEL_' + channel);
        var title = app.vtranslate('CPChatBotIntegration.JS_CHAT_MESSAGE_MODAL_TITLE', translatedChannel);
        chatMessageModal.find('.modal-header .pull-left').text(title);

        // Setup inital values
        var form = chatMessageModal.find('form');
        form.find('[name="channel"]').val(channel);

        var callBackFunction = function (data) {
            data.find('#chat-message-modal').removeClass('hide');
            var form = data.find('#chat-message-form');
            var chatAppInput = form.find('[name="chat_app"]');
            
            // Init modal form
            var controller = Vtiger_Edit_Js.getInstance();
            controller.registerBasicEvents(form);
            vtUtils.applyFieldElementsView(form);
            chatAppInput.select2({ width: 'resolve' });

            // Form validation
            var params = {
                submitHandler: function (form) {
                    var form = jQuery(form);

                    bootbox.confirm({
                        message: app.vtranslate('CPChatBotIntegration.JS_CHAT_MESSAGE_MODAL_SEND_MESSAGE_CONFIRM_MSG'),
                        callback: function (result) {
                            if (result) {
                                ChatBotHandler.sendChatlMessage(form);
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

        app.helper.showModal(chatMessageModal, modalParams);

        return false;
    },

    // Send chat message
    sendChatlMessage: function (form) {
        app.helper.showProgress();
        var currentView = app.getViewName();
        var channelName = form.find('[name="channel"]').val();

        let params = {
            module: 'CPChatBotIntegration',
            action: 'ChatMessageAjax',
            mode: 'send',
            target_module: app.getModuleName(),
            target_record: app.getRecordId(),
            channel: channelName,
            app_id: form.find('[name="chat_app"]').val(),
            message: form.find('[name="message"]').val()
        };

        app.request.post({ data: params }).then(function (error, res) {
            app.helper.hideProgress();
            var message = '';

            if (error || res.status == 'ERROR') {
                message = app.vtranslate('CPChatBotIntegration.JS_CHAT_MESSAGE_MODAL_SEND_MESSAGE_UNKNOWN_ERROR_MSG', channelName);
                app.helper.showErrorNotification({ message: message }, { delay: 5000 });
                return false;
            }

            if (res.status == 'SENT') {
                message = app.vtranslate('CPChatBotIntegration.JS_CHAT_MESSAGE_MODAL_SEND_MESSAGE_SUCCESS_MSG', channelName);
            }

            app.helper.showSuccessNotification({ message: message }, { delay: 5000 });
            form.find('.cancelLink').trigger('click');  // Dismiss modal
        });
    },
}