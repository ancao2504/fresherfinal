<?php

$relationships = array(
    array(
        'leftSideModule' => 'SMSNotifier',
        'rightSideModule' => 'CPSMSOTTMessageLog',
        'relationshipType' => '1:N',
        'relationshipName' => 'LBL_SMS_OTT_MESSAGE_LOG_LIST',
        'enabledActions' => array(),
        'listingFunctionName' => 'get_dependents_list',
        'leftSideReferenceFieldName' => null,
        'rightSideReferenceFieldName' => 'related_sms_ott_notifier'
    ),
);

