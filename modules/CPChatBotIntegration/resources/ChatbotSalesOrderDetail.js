/**
 * ChatbotIframeSalesOrderDetailPopup
 * Author: Phu Vo
 * Date: 20202.09.11
 * Description: Using this file to show sales order detail popup
 */

(() => {
    const initData = {
        meta_data: {},
        overlay: false,
        salesorder: {
            items: [],
        },
    };

    window.App = new Vue({
        el: '#app',

        data: $.extend({}, initData, window._IFRAME_DATA),

        mounted() {
            $(this.$el).show();
        }
    });
})();
