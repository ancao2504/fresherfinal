<?php

/* System auto-generated on 2019-07-26 02:24:47 pm.  */

$relationships = array(
    array(
        'leftSideModule' => 'CPTarget',
        'rightSideModule' => 'CPTargetList',
        'relationshipType' => 'N:N',
        'relationshipName' => 'LBL_CPTARGETLIST_LIST',
        'enabledActions' => array(
            'ADD',
            'SELECT'
        ),
        'listingFunctionName' => 'get_related_list',
        'leftSideReferenceFieldName' => null,
        'rightSideReferenceFieldName' => null
    ),
    array(
        'leftSideModule' => 'CPTarget',
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
        'leftSideModule' => 'CPTarget',
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
        'leftSideModule' => 'CPTarget',
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
        'leftSideModule' => 'CPTarget',
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
        'leftSideModule' => 'CPTarget',
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
        'leftSideModule' => 'CPTarget',
        'rightSideModule' => 'CPSMSOTTMessageLog',
        'relationshipType' => '1:N',
        'relationshipName' => 'LBL_SMS_OTT_MESSAGE_LOG_LIST',
        'enabledActions' => array(),
        'listingFunctionName' => 'get_dependents_list',
        'leftSideReferenceFieldName' => null,
        'rightSideReferenceFieldName' => 'related_customer'
    )
);

