/*
    ListView.js
    Author: Phuc Lu
    Date: 2019.11.26
    Purpose: to handle logic on the UI
*/

jQuery(function($) {

    // Added by Phuc on 2019.11.25 to remove action quick edit when status is Converted in list
    $('.listViewEntryValue').each(function() {
        if ($(this).data('rawvalue') == 'Converted') {
            $(this).html($(this).find('.fieldValue').find('.value').html());
        }
    })
    
    // Ended by Phuc
})