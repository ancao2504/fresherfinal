<?php

/* System auto-generated on 2019-08-08 06:05:04 pm.  */

$relationships = array(
    array(
        'leftSideModule' => 'Leads',
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
        'leftSideModule' => 'Leads',
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
        'leftSideModule' => 'Leads',
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
        'leftSideModule' => 'Leads',
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
        'leftSideModule' => 'Leads',
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
        'leftSideModule' => 'Leads',
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
        'leftSideModule' => 'Leads',
        'rightSideModule' => 'CPSMSOTTMessageLog',
        'relationshipType' => '1:N',
        'relationshipName' => 'LBL_SMS_OTT_MESSAGE_LOG_LIST',
        'enabledActions' => array(),
        'listingFunctionName' => 'get_dependents_list',
        'leftSideReferenceFieldName' => null,
        'rightSideReferenceFieldName' => 'related_customer'
    ),
    array(
        'leftSideModule' => 'Leads',
        'rightSideModule' => 'HelpDesk',
        'relationshipType' => '1:N',
        'relationshipName' => 'LBL_HELPDESK_LIST',
        'enabledActions' => array(
            'ADD'
        ),
        'listingFunctionName' => 'get_dependents_list',
        'leftSideReferenceFieldName' => null,
        'rightSideReferenceFieldName' => 'related_lead'
    )
);

