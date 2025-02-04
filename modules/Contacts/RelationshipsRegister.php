<?php

/* System auto-generated on 2020-04-07 02:54:55 pm.  */

$relationships = array(
    array(
        'leftSideModule' => 'Contacts',
        'rightSideModule' => 'CPSocialMessageLog',
        'relationshipType' => '1:N',
        'relationshipName' => 'LBL_CPSOCIALMESSAGELOG_LIST',
        'enabledActions' => array(
            'ADD'
        ),
        'listingFunctionName' => 'get_dependents_list',
        'leftSideReferenceFieldName' => null,
        'rightSideReferenceFieldName' => 'related_customer'
    ),
    array(
        'leftSideModule' => 'Contacts',
        'rightSideModule' => 'CPSocialArticleLog',
        'relationshipType' => '1:N',
        'relationshipName' => 'LBL_CPSOCIALARTICLELOG_LIST',
        'enabledActions' => array(
            'ADD'
        ),
        'listingFunctionName' => 'get_dependents_list',
        'leftSideReferenceFieldName' => null,
        'rightSideReferenceFieldName' => 'related_customer'
    ),
    array(
        'leftSideModule' => 'Contacts',
        'rightSideModule' => 'CPSocialFeedback',
        'relationshipType' => '1:N',
        'relationshipName' => 'LBL_CPSOCIALFEEDBACK_LIST',
        'enabledActions' => array(
            'ADD'
        ),
        'listingFunctionName' => 'get_dependents_list',
        'leftSideReferenceFieldName' => null,
        'rightSideReferenceFieldName' => 'related_customer'
    ),
    array(
        'leftSideModule' => 'Contacts',
        'rightSideModule' => 'CPReceipt',
        'relationshipType' => '1:N',
        'relationshipName' => 'LBL_CPRECEIPT_LIST',
        'enabledActions' => array(
            'ADD'
        ),
        'listingFunctionName' => 'get_dependents_list',
        'leftSideReferenceFieldName' => null,
        'rightSideReferenceFieldName' => 'contact_id'
    ),
    array(
        'leftSideModule' => 'Contacts',
        'rightSideModule' => 'CPPayment',
        'relationshipType' => '1:N',
        'relationshipName' => 'LBL_CPPAYMENT_LIST',
        'enabledActions' => array(
            'ADD'
        ),
        'listingFunctionName' => 'get_dependents_list',
        'leftSideReferenceFieldName' => null,
        'rightSideReferenceFieldName' => 'contact_id'
    ),
    array(
        'leftSideModule' => 'Contacts',
        'rightSideModule' => 'CPMauticContactHistory',
        'relationshipType' => '1:N',
        'relationshipName' => 'LBL_CPMAUTICCONTACTHISTORY_LIST',
        'enabledActions' => array(
        ),
        'listingFunctionName' => 'get_dependents_list',
        'leftSideReferenceFieldName' => null,
        'rightSideReferenceFieldName' => 'related_id'
    ),
    array(
        'leftSideModule' => 'Contacts',
        'rightSideModule' => 'CPChatMessageLog',
        'relationshipType' => '1:N',
        'relationshipName' => 'LBL_CHAT_MESSAGE_LOG_LIST',
        'enabledActions' => array(
        ),
        'listingFunctionName' => 'get_dependents_list',
        'leftSideReferenceFieldName' => null,
        'rightSideReferenceFieldName' => 'related_customer'
    ),
    array(
        'leftSideModule' => 'Contacts',
        'rightSideModule' => 'CPSMSOTTMessageLog',
        'relationshipType' => '1:N',
        'relationshipName' => 'LBL_SMS_OTT_MESSAGE_LOG_LIST',
        'enabledActions' => array(),
        'listingFunctionName' => 'get_dependents_list',
        'leftSideReferenceFieldName' => null,
        'rightSideReferenceFieldName' => 'related_customer'
    ),
    array(
        'leftSideModule' => 'Contacts',
        'rightSideModule' => 'CPEventRegistration',
        'relationshipType' => '1:N',
        'relationshipName' => 'LBL_CPEVENTREGISTRATION_LIST',
        'enabledActions' => array(
            'ADD'
        ),
        'listingFunctionName' => 'get_dependents_list',
        'leftSideReferenceFieldName' => null,
        'rightSideReferenceFieldName' => 'related_customer'
    ),
     array(
        'leftSideModule' => 'Contacts',
        'rightSideModule' => 'CPTicketCommunicationLog',
        'relationshipType' => '1:N',
        'relationshipName' => 'LBL_CPTICKETCOMMUNICATIONLOG_LIST',
        'enabledActions' => array(

        ),
        'listingFunctionName' => 'get_dependents_list',
        'leftSideReferenceFieldName' => null,
        'rightSideReferenceFieldName' => 'customer_id'
    )
);

