/**
 * Name: SocialIntegrationConfigFBFanpageSelector.js
 * Author: Phu Vo
 */

$('input[type="checkbox"]').on('change', (event) => {
    if ($(event.target).is(':checked')) {
        $(event.target).closest('.row').addClass('highlight');
    }
    else {
        $(event.target).closest('.row').removeClass('highlight');
    }
}).trigger('change');
