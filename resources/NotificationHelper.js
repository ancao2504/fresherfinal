/*
*   NotificationHelper.js
*   Author: Hieu Nguyen
*   Date: 2018-10-05
*   Purpose: To provide APIs for showing notifications
*/

var NotificationHelper = {

    init: function(params) {
        // Request for Desktop Notification permission.
        if('Notification' in window) {
            Notification.requestPermission(function(permission) {
                if (Notification.permission == 'granted') {
                    console.log('Desktop Notification permission granted.');
                }
            });
        }
    },

    // Util function to show notification.
    show: function(message, link) {
        // Show in-app notification
        alertify.success(message);

        // Set default link if empty
        if(!link) {
            link = this.getSiteURL();
        }

        // Show desktop notification
        var notification = new Notification('vTiger', {
            body: $(message).text(),
            icon: this.getSiteURL() + 'test/logo/vtiger-crm-logo.png',
            tag: link
        });

        // Redirect to CRM when the notification is clicked 
        notification.onclick = function(e) {
            notification.close();
            window.focus();
            location.href = e.target.tag;
        }
    },

    // Util function to get the site url.
    getSiteURL: function() {
        var siteURL = window.location.origin + window.location.pathname.replace('index.php', '');
        return siteURL;
    },

    // Util function to get current user info
    getUserInfo: function() {
        return _CURRENT_USER_META;
    },

    // Util function to check if user is at login page
    isAtLoginPage: function() {
        return app.getModuleName() == 'Users' && app.getViewName() == 'Login';
    }
}

jQuery(function($) {
    NotificationHelper.init();
});