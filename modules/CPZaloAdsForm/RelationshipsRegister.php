<?php

/*
	System auto-generated on 2021-11-08 03:03:56 am by admin. 
*/

$relationships = array(
    array(
        'leftSideModule' => 'CPZaloAdsForm',
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
        'leftSideModule' => 'CPZaloAdsForm',
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
        'leftSideModule' => 'CPZaloAdsForm',
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
        'leftSideModule' => 'CPZaloAdsForm',
        'rightSideModule' => 'CPEventRegistration',
        'relationshipType' => '1:N',
        'relationshipName' => 'LBL_CPEVENTREGISTRATION_LIST',
        'enabledActions' => array(
            'ADD'
        ),
        'listingFunctionName' => 'get_dependents_list',
        'leftSideReferenceFieldName' => null,
        'rightSideReferenceFieldName' => 'related_cpzaloadsform'
    )
);

