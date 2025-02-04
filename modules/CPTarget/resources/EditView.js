/*
    EditView.js
    Author: Phuc Lu
    Date: 2019.10.31
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
});