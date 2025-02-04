/**
 * ChatbotHelper
 * Author: Phu Vo
 * Date: 2020.09.11
 * Description: Common helper to using when process chatbot logic
 */

const ChatbotHelper = new class {
    popupCenter(url, title, width, height, callback) {
        let screenWidth = screen.width;
        let screenHeight = screen.height;
        let left = (screenWidth - width) / 2;
        let top = (screenHeight - height) / 2;

        var newWindow = window.open(url, title, 'width=' + width + ', height=' + height + ', top=' + top + ', left=' + left + ',location=no,toolbar=no,menubar=no,scrollbars=yes,resizable=no');
        if (typeof callback === 'function') newWindow.iframeSubmitCallback = callback;

        // Puts focus on the newWindow
        if (window.focus) newWindow.focus();

        // Add some event register
        $(window).unload(function() {
            newWindow.close();
        });

        return newWindow;
    }

    getFieldLabel (moduleName, fieldName) {
        const metaData = window._IFRAME_DATA.meta_data;

        if (metaData[moduleName] && metaData[moduleName].all_fields && metaData[moduleName].all_fields[fieldName]) {
            return metaData[moduleName].all_fields[fieldName].label;
        }

        return '';
    }
};