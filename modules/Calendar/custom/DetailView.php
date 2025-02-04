<?php
    $displayParams = array(
        'scripts' => '
            <link type="text/css" rel="stylesheet" href="{vresource_url("modules/Calendar/resources/DetailView.css")}">
            <script type="text/javascript" src="{vresource_url("modules/Calendar/resources/DetailView.js")}"></script>
        ',
        'form' => array(
            'hiddenFields' => '

            ',
        ),
        'fields' => array(
            // Added by Hieu Nguyen on 2019-10-29 to show meeting location on Google Maps
            'location' => array(
                'customTemplate' => '{include file="modules/Calendar/tpls/LocationField.tpl"}',
            ),
            // End Hieu Nguyen
            'events_call_direction' => array(
                'customTemplate' => '{include file="modules/Calendar/tpls/EventsCallDirectionField.tpl"}',
            ),
            'pbx_call_id' => array(
                'customTemplate' => '{include file="modules/Calendar/tpls/PBXCallIdField.tpl"}',
            ),
            // Added by Phu Vo on 2019.07.02
            'checkin_salesman_image' => array(
                'customTemplate' => '{include file="modules/Calendar/tpls/CheckinSalesmanImageField.tpl"}',
            ),
            'checkin_customer_image' => array(
                'customTemplate' => '{include file="modules/Calendar/tpls/CheckinCustomerImageField.tpl"}',
            ),
            'activitytype' => array(
                'customTemplate' => '{include file="modules/Calendar/tpls/ActivityTypeField.tpl"}',
            ),
            // End Phu Vo
            // Added by Hieu Nguyen on 2019-11-22
            'contact_invitees' => array(
                'customTemplate' => '{include file="modules/Calendar/tpls/ContactInviteesFieldDetailView.tpl"}'
            ),
            'user_invitees' => array(
                'customTemplate' => '{include file="modules/Calendar/tpls/UserInviteesFieldDetailView.tpl"}'
            ),
            // End Hieu Nguyen
            // Added by Phu Vo on 2020.02.17
            'events_call_purpose' => array(
                'customTemplate' => '{include file="modules/Calendar/tpls/EventsCallPurpose.tpl"}'
            ),
            'events_inbound_call_purpose' => array(
                'customTemplate' => '{include file="modules/Calendar/tpls/EventsInboundCallPurpose.tpl"}'
            ),
            'events_call_purpose_other' => array(
                'customTemplate' => '{include file="modules/Calendar/tpls/EventsCallPurposeOther.tpl"}'
            ),
            'events_inbound_call_purpose_other' => array(
                'customTemplate' => '{include file="modules/Calendar/tpls/EventsInboundCallPurposeOther.tpl"}'
            ),
            // End Phu Vo
        ),
    );