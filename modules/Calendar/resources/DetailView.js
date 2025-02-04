/*
    DetailView.js
    Author: Hieu Nguyen
    Date: 2018-12-10
    Purpose: to handle logic on the UI
*/

jQuery(function ($) {
    // Hide Event's specific blocks on Task's form
    if ($('.block_LBL_TASK_INFORMATION')[0] != null) {
        $('.block_LBL_EVENT_INFORMATION').remove();
        $('.block_LBL_CHECKIN').remove();
        $('.block_LBL_INVITEES').remove();
    }
});