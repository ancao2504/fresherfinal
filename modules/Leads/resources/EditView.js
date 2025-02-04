/*
    EditView.js
    Author: Hieu Nguyen
    Date: 2018-11-29
    Purpose: to handle logic on the UI
*/

jQuery(function($) {
    // Init auto complete address
    GoogleMaps.initAutocomplete($(':input[name="lane"]'), {
        city: $(':input[name="city"]'), 
        state: $(':input[name="state"]'), 
        zip: $(':input[name="code"]'), 
        country: $(':input[name="country"]')
    });

    // Added by Phuc on 2019.10.25 to remove converted status in leads when editing
    if ($('.fieldValue.leadstatus .inputElement:visible').length) {
        var statusElement = $('[name="leadstatus"]');
        statusElement.find('option[value="Converted"]').remove();
        statusElement.select2("destroy");
        statusElement.select2();
    }
    // Ended by Phuc
});