/*
    EditView.js
    Author: Hieu Nguyen
    Date: 2018-11-29
    Purpose: to handle logic on the UI
*/

jQuery(function($) {
    // Init auto complete address
    GoogleMaps.initAutocomplete($(':input[name="location"]'));

    // Display form
    showHideCallDirectionField();
    
    $('select[name="activitytype"]').on('change', function () {
        showHideCallDirectionField();
    });
});

function showHideCallDirectionField() {
    var activityTpe = $('select[name="activitytype"]').val();

    if (activityTpe == 'Call' || activityTpe == 'Mobile Call') {
        $('.events_call_direction').show();
    } 
    else {
        $('.events_call_direction').hide();
        $('select[name="events_call_direction"]').val('').trigger('change');
    }
}