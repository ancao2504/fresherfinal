/*
    FBChatWidgetHelper.js
    Author: Hieu Nguyen
    Date: 2021-08-26
    Purpose: Handle custom behavior of FB Chat Widget
*/

jQuery(function ($) {
    var chatboxState = '';

    window.fbAsyncInit = function() {
        FB.init({
            xfbml: true,
            version: 'v11.0'
        });
    };

    $('#btn-open-fb-chat').on('click', function () {
        // If the widget is not loaded yet, init the FB script
        if ($('#fb-root').find('iframe')[0] == null) {
            app.helper.showProgress();

            (function (d, s, id) {
                var js, fjs = d.getElementsByTagName(s)[0];
                if (d.getElementById(id)) return;
                js = d.createElement(s); js.id = id;
                js.src = 'https://connect.facebook.net/vi_VN/sdk/xfbml.customerchat.js';

                js.addEventListener('load', function (e) {
                    console.log('[FBChatWidget] JS loaded');

                    // Handle event widget render
                    var onRenderComplete = function () {
                        console.log('[FBChatWidget] Chatbox render completed');

                        setTimeout(function () {
                            app.helper.hideProgress();
                            FB.CustomerChat.showDialog();
                        }, 500);
                    };

                    FB.Event.subscribe('xfbml.render', onRenderComplete);

                    // Handle event chatbox show
                    var onDialogShow = function () {
                        chatboxState = 'opened';
                    };

                    FB.Event.subscribe('customerchat.onDialogShow', onDialogShow);

                    // Handle event chatbox hide
                    var onDialogHide = function () {
                        chatboxState = 'closed';
                    };

                    FB.Event.subscribe('customerchat.dialogHide', onDialogHide);
                });

                fjs.parentNode.insertBefore(js, fjs);
            }(document, 'script', 'facebook-jssdk'));
        }
        // Otherwise, show the chatbox
        else {
            console.log('[FBChatWidget] Chatbox state', chatboxState);

            if (chatboxState == 'closed') {
                FB.CustomerChat.showDialog();
            }
        }
    });
});