/*
    QuickCreate.js
    Author: Hieu Nguyen
    Date: 2019-11-21
    Purpose: to handle logic on the UI
*/

jQuery(function ($) {
    var moduleName = 'Events';
    var form = $('form#QuickCreate');

    // Init auto complete address
    setTimeout(() => {
        // [Core] Bug #455: Added by Phu Vo on 2020.03.19 to fix js console issue when location field did't added on form
        if (form.find('input[name="location"]')[0] == undefined) return;
        // End Bug #455

        GoogleMaps.initAutocomplete(form.find('input[name="location"]'));
    }, 1000);

    // Added by Phu Vo on 2019.12.20 Display form
    const callFields = [
        'missed_call',
        'events_call_direction',
        'pbx_call_id',
        'events_call_purpose',
        'events_call_result',
        'events_call_purpose_other',
        'events_inbound_call_purpose',
        'events_inbound_call_purpose_other',
    ];

    const toggleFields = (fields, show = true) => {
        fields.forEach((field) => {
            fieldElements = form.find(`.${field}`);

            fieldElements.toggle(show);
            form.find(`[name="${field}"]:input`).attr('disabled', !show);
        });
    }

    const toggleVisibilityFields = (fields, show = true) => {
        fields.forEach((field) => {
            fieldElements = form.find(`.${field}`);

            fieldElements.css('visibility', show ? 'visible' : 'hidden');
            form.find(`[name="${field}"]:input`).attr('disabled', true);
        });
    }

    const registerShowHideEventCallFields = () => {
        const activityTypeField = form.find('[name="activitytype"]');

        activityTypeField.on('change', (event) => {
            const activityType = $(event.target).val();
            const isCallType = activityType === 'Call' || activityType === 'Mobile Call';
            toggleFields(callFields, isCallType);
        }).trigger('change');
    }

    // Added by Phu Vo on 2020.01.20
    const registerShowHideCallPurposeOther = () => {
        const callPurposeField = form.find('[name="events_call_purpose"]');

        callPurposeField.on('change', (event) => {
            const purpose = $(event.target).val();
            const isOtherPurpose = purpose === 'call_purpose_other';

            toggleFields(['events_call_purpose_other'], isOtherPurpose);
        }).trigger('change');
    }

    // Added by Phu Vo on 2020.01.20
    const registerShowHideCallInboundPurposeOther = () => {
        const callInboundPurposeField = form.find('[name="events_inbound_call_purpose"]');

        callInboundPurposeField.on('change', (event) => {
            const purpose = $(event.target).val();
            const isOtherPurpose = purpose === 'inbound_call_purpose_other';

            toggleFields(['events_inbound_call_purpose_other'], isOtherPurpose);
        }).trigger('change');
    }

    registerShowHideEventCallFields();
    registerShowHideCallPurposeOther();
    registerShowHideCallInboundPurposeOther();
    // End Phu Vo

    // [Calendar] Request #251: Added by Phu Vo on 2020.03.11 to handle reminder time default value
    function setDefaultReminderTime() {
        form.find('input[name="set_reminder"]').attr('checked', true).trigger('change');
        form.find('#js-reminder-selections').css('visibility', 'visible');
        var activityType = form.find('[name="activitytype"]').val();
        var defaultTime = null;

        if (activityType == 'Call') {
            defaultTime = _CALENDAR_USER_SETTINGS.default_call_reminder_time;
        }
        else if (activityType == 'Meeting') {
            defaultTime = _CALENDAR_USER_SETTINGS.default_meeting_reminder_time;
        }

        if (defaultTime) {
            form.find('[name="remdays"]').val(defaultTime.days).trigger('change');
            form.find('[name="remhrs"]').val(defaultTime.hours).trigger('change');
            form.find('[name="remmin"]').val(defaultTime.mins).trigger('change');
        }
    }

    function registerReminderTimeValidation() {
        form.find('[type="submit"]').on('click', function() {
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

    // Set default event reminder time for new Event and Task
    if (!$('[name="record"]').val()) {
        setDefaultReminderTime();

        $('[name="activitytype"]').on('change', function () {
            setDefaultReminderTime();
        });
    }

    // Validate reminder time
    registerReminderTimeValidation();

    CustomOwnerField.initCustomOwnerFields($('[name="user_invitees"]'));
    // End Request #251

    // Trigger parent field change event to force event handlers run at begining
    form.find('.fieldValue.parent_id').find('[name="parent_id"]').trigger('Vtiger.PostReference.Selection', form);
    form.find('.fieldValue.related_account').find('[name="related_account"]').trigger('Vtiger.PostReference.Selection', form);
    form.find('.fieldValue.contact_id').find('[name="contact_id"]').trigger('Vtiger.PostReference.Selection', form);
    form.find('.fieldValue.related_lead').find('[name="related_lead"]').trigger('Vtiger.PostReference.Selection', form);
});
