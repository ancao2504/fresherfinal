<?php

/* System auto-generated on 2020-05-25 10:38:44 am.  */

$relationships = array(
    array(
        'leftSideModule' => 'CPEventManagement',
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
        'leftSideModule' => 'CPEventManagement',
        'rightSideModule' => 'CPEventRegistration',
        'relationshipType' => '1:N',
        'relationshipName' => 'LBL_CPEVENTREGISTRATION_LIST',
        'enabledActions' => array(
            'ADD'
        ),
        'listingFunctionName' => 'get_dependents_list',
        'leftSideReferenceFieldName' => null,
        'rightSideReferenceFieldName' => 'related_cpeventmanagement'
    ),
    array(
        'leftSideModule' => 'CPEventManagement',
        'rightSideModule' => 'CPTargetList',
        'relationshipType' => '1:N',
        'relationshipName' => 'LBL_CPTARGETLIST_LIST',
        'enabledActions' => array(
            'ADD'
        ),
        'listingFunctionName' => 'get_dependents_list',
        'leftSideReferenceFieldName' => null,
        'rightSideReferenceFieldName' => 'related_cpeventmanagement'
    ),
    // Added by Phu Vo on 2021.11.20
    array(
        'leftSideModule' => 'CPEventManagement',
        'rightSideModule' => 'CPZaloAdsForm',
        'relationshipType' => '1:N',
        'relationshipName' => 'LBL_CPZALOADSFORM_LIST',
        'enabledActions' => array(
            'ADD'
        ),
        'listingFunctionName' => 'get_dependents_list',
        'leftSideReferenceFieldName' => null,
        'rightSideReferenceFieldName' => 'related_event'
    ),
    // End Phu Vo
);

