/*
    EditView.js
    Author: Hieu Nguyen
    Date: 2018-11-29
    Purpose: to handle logic on the UI
*/

jQuery(function ($) {
    // Init auto complete address
    GoogleMaps.initAutocomplete($(':input[name="location"]'));

    // Added by Phu Vo on 2020.01.17
    const editForm = $('form#EditView');
    // End Phu Vo

    // Modified by Phu Vo on 2020.01.17 to change
    // how dependence activitytype fields visibility
    editForm.find('select[name="activitytype"]').on('change', function(event) {
        showHideCallInformationBlock(event, editForm);
    }).trigger('change');
    // End Phu Vo

    // Added by Phu Vo on 2020.01.17 to handle visibility behavior
    // event call purpose other visibility behavior
    editForm.find('[name="events_call_purpose"]').on('change', function(event) {
        showHideCallPurposeOtherField(event, editForm);
    }).trigger('change');

    // event inbound call purpose other visibility behavior
    editForm.find('[name="events_inbound_call_purpose"]').on('change', function(event) {
        showHideInboundCallPurposeOtherField(event, editForm);
    }).trigger('change');
    // End Phu

    editForm.find('[name="events_call_direction"]').on('change', function(event) {
        showHideCallPurposeFieldsBaseOnDirection($(event.target).val(), editForm);
    }).trigger('change');

    // Hide Event's specific blocks on Task's form
    if ($('[name="module"]').val() == 'Calendar') {
        $('[data-block="LBL_EVENT_INFORMATION"]').remove();
        $('[data-block="LBL_CHECKIN"]').remove();
        $('[data-block="LBL_INVITEES"]').remove();
    }

    // Init user invitees field
    CustomOwnerField.initCustomOwnerFields($('[name="user_invitees"]'));

    // Set default event reminder time for new Event and Task
    if (!$('[name="record"]').val()) {
        var urlParams = app.convertUrlToDataParams(location.href);
        
        if (urlParams.set_reminder == null) {
            setDefaultReminderTime();
        }
        
        $('[name="activitytype"]').on('change', function() {
            setDefaultReminderTime();
        });
    }

    // Validate reminder time
    registerReminderTimeValidation();
});

// Modified by Phu Vo on 2020.01.17
function showHideCallInformationBlock(event, container) {
    const activityType = $(event.target).val();
    const callBlock = container.find('[data-block="LBL_CALL_INFORMATION"]');

    if (activityType == 'Call' || activityType == 'Mobile Call') {
        callBlock.show();
        callBlock.find(':input').attr('disabled', false);
    } 
    else {
        callBlock.hide();
        callBlock.find(':input').attr('disabled', true);
    }
}

// Added by Phu Vo on 2020.01.17
function showHideCallPurposeOtherField(event, container) {
    const eventPurpose = $(event.target).val();

    if (eventPurpose === 'call_purpose_other') {
        container.find('.events_call_purpose_other').css('visibility', 'initial');
    }
    else {
        container.find('.events_call_purpose_other').css('visibility', 'hidden');
        container.find(':input[name="events_call_purpose_other"]').val('').trigger('change');
    }
}

function showHideInboundCallPurposeOtherField(event, container) {
    const eventPurpose = $(event.target).val();

    if (eventPurpose === 'inbound_call_purpose_other') {
        container.find('.events_inbound_call_purpose_other').css('visibility', 'initial');
    }
    else {
        container.find('.events_inbound_call_purpose_other').css('visibility', 'hidden');
        container.find(':input[name="events_inbound_call_purpose_other"]').val('').trigger('change');
    }
}

function showHideCallPurposeFieldsBaseOnDirection(direction, container) {
    if (direction === 'Inbound') {
        container.find('.events_call_purpose').hide();
        container.find('.events_call_purpose_other').hide();
        container.find('.events_inbound_call_purpose').show();
        container.find('.events_inbound_call_purpose_other').show();
        container.find('[name="events_call_purpose"]:input').val('').trigger('change');
    }
    else if (direction === 'Outbound') {
        container.find('.events_call_purpose').show();
        container.find('.events_call_purpose_other').show();
        container.find('.events_inbound_call_purpose').hide();
        container.find('.events_inbound_call_purpose_other').hide();
        container.find('[name="events_inbound_call_purpose"]:input').val('').trigger('change');
    }
    else {
        container.find('.events_call_purpose').hide();
        container.find('.events_call_purpose_other').hide();
        container.find('.events_inbound_call_purpose').hide();
        container.find('.events_inbound_call_purpose_other').hide();
        container.find('[name="events_call_purpose"]:input').val('').trigger('change');
        container.find('[name="events_inbound_call_purpose"]:input').val('').trigger('change');
    }
}

function setDefaultReminderTime() {
    $('input[name="set_reminder"]').attr('checked', true).trigger('change');
    $('#js-reminder-selections').css('visibility', 'visible');
    var activityType = $('[name="activitytype"]').val();

    if (activityType == 'Call') {
        defaultTime = _CALENDAR_USER_SETTINGS.default_call_reminder_time;
    }
    else if (activityType == 'Meeting') {
        defaultTime = _CALENDAR_USER_SETTINGS.default_meeting_reminder_time;
    }
    else {
        defaultTime = _CALENDAR_USER_SETTINGS.default_task_reminder_time;
    }

    $('[name="remdays"]').val(defaultTime.days).trigger('change');
    $('[name="remhrs"]').val(defaultTime.hours).trigger('change');
    $('[name="remmin"]').val(defaultTime.mins).trigger('change');
}

function registerReminderTimeValidation() {
    $('.saveButton').on('click', function() {
        var form = $(this).closest('form');

        if (form.find('[name="set_reminder"]:checkbox').is(':checked')) {
            if (form.find('[name="remdays"]').val() == 0 && form.find('[name="remhrs"]').val() == 0 && form.find('[name="remmin"]').val() == 0) {
                var message = app.vtranslate('Calendar.JS_REMINDER_TIME_TIME_MUST_GREATER_THAN_0_MINUTES_VALIDATE_MSG');
                app.helper.showErrorNotification({ message: message });
                form.find('[name="remmin"]').select2('open');
                return false;
            }
        }
    });
}

// End Phu Vo