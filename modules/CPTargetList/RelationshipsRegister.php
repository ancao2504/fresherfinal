<?php

/* System auto-generated on 2020-11-09 05:05:22 pm.  */

$relationships = array(
    array(
        'leftSideModule' => 'CPTargetList',
        'rightSideModule' => 'Leads',
        'relationshipType' => 'N:N',
        'relationshipName' => 'LBL_LEAD_LIST',
        'enabledActions' => array(
            'ADD',
            'SELECT'
        ),
        'listingFunctionName' => 'get_related_list',
        'leftSideReferenceFieldName' => null,
        'rightSideReferenceFieldName' => null
    ),
    array(
        'leftSideModule' => 'CPTargetList',
        'rightSideModule' => 'Contacts',
        'relationshipType' => 'N:N',
        'relationshipName' => 'LBL_CONTACT_LIST',
        'enabledActions' => array(
            'ADD',
            'SELECT'
        ),
        'listingFunctionName' => 'get_related_list',
        'leftSideReferenceFieldName' => null,
        'rightSideReferenceFieldName' => null
    ),
    array(
        'leftSideModule' => 'CPTargetList',
        'rightSideModule' => 'CPTarget',
        'relationshipType' => 'N:N',
        'relationshipName' => 'LBL_CPTARGET_LIST',
        'enabledActions' => array(
            'ADD',
            'SELECT'
        ),
        'listingFunctionName' => 'get_related_list',
        'leftSideReferenceFieldName' => null,
        'rightSideReferenceFieldName' => null
    ),
    array(
        'leftSideModule' => 'CPTargetList',
        'rightSideModule' => 'CPSocialArticleLog',
        'relationshipType' => '1:N',
        'relationshipName' => 'LBL_CPSOCIALARTICLELOG_LIST',
        'enabledActions' => array(
            'ADD'
        ),
        'listingFunctionName' => 'get_dependents_list',
        'leftSideReferenceFieldName' => null,
        'rightSideReferenceFieldName' => 'related_target_list'
    ),
    array(
        'leftSideModule' => 'CPTargetList',
        'rightSideModule' => 'SMSNotifier',
        'relationshipType' => 'N:N',
        'relationshipName' => 'LBL_SMS_OTT_MESSAGE_NOTIFIER_LIST',
        'enabledActions' => array(
            'ADD',
            'SELECT'
        ),
        'listingFunctionName' => 'get_related_list',
        'leftSideReferenceFieldName' => null,
        'rightSideReferenceFieldName' => null
    )
);

