/*
    EditView.js
    Author: Hieu Nguyen
    Date: 2018-11-29
    Purpose: to handle logic on the UI
*/

jQuery(function($) {
    // Init auto complete address
    GoogleMaps.initAutocomplete($(':input[name="mailingstreet"]'), {
        city: $(':input[name="mailingcity"]'), 
        state: $(':input[name="mailingstate"]'), 
        zip: $(':input[name="mailingzip"]'), 
        country: $(':input[name="mailingcountry"]')
    });

    GoogleMaps.initAutocomplete($(':input[name="otherstreet"]'), {
        city: $(':input[name="othercity"]'), 
        state: $(':input[name="otherstate"]'), 
        zip: $(':input[name="otherzip"]'), 
        country: $(':input[name="othercountry"]')
    });
});