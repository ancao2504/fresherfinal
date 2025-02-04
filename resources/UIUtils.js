/*
	UIUtils
	Author: Hieu Nguyen
	Date: 2020-07-22
	Purpose: to provide util functions for the UI
*/

var UIUtils = {
    // Insert value at selected cursor of an input
    insertAtCursor: function (value, input) {
        var cursorPos = input.prop('selectionStart');
        var text = input.val();
        var head = text.substring(0, cursorPos);
        var tail = text.substring(cursorPos, text.length);

        input.val(head + value + tail);
    },

    // Copied from https://stackoverflow.com/questions/4068373/center-a-popup-window-on-screen and modified by Hieu Nguyen on 2019-07-10
    popupCenter: function (url, title, width, height) {
        // Fixes dual-screen position                         Most browsers      Firefox
        var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : window.screenX;
        var dualScreenTop = window.screenTop != undefined ? window.screenTop : window.screenY;

        var screenWidth = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
        var screenHeight = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

        var systemZoom = screenWidth / window.screen.availWidth;
        var left = (screenWidth - width) / 2 / systemZoom + dualScreenLeft;
        var top = (screenHeight - height) / 2 / systemZoom + dualScreenTop;
        var newWindow = window.open(url, title, 'scrollbars=yes, width=' + width / systemZoom + ', height=' + height / systemZoom + ', top=' + top + ', left=' + left);

        // Puts focus on the newWindow
        if (window.focus) newWindow.focus();

        return newWindow;
    }
}