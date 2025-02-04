/**
 * Name: ConnectHanet.js
 * Author: Phu Vo
 * Date: 2021.04.24
 */

jQuery(function ($) {
    $('.place').on('click', function () {
        window.location = $(this).data('href');
    });

    $('.completeBtn').on('click', function () {
        if (window.callBack) window.callBack();
        window.close();
    });
});