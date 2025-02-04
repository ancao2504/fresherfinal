<?php

/* System auto-generated on 2019-10-31 05:29:23 pm.  */

$relationships = array(
    array(
        'leftSideModule' => 'ServiceContracts',
        'rightSideModule' => 'CPPayment',
        'relationshipType' => '1:N',
        'relationshipName' => 'LBL_CPPAYMENT_LIST',
        'enabledActions' => array(
            'ADD'
        ),
        'listingFunctionName' => 'get_dependents_list',
        'leftSideReferenceFieldName' => null,
        'rightSideReferenceFieldName' => 'related_servicecontract'
    ),
    array(
        'leftSideModule' => 'ServiceContracts',
        'rightSideModule' => 'CPReceipt',
        'relationshipType' => '1:N',
        'relationshipName' => 'LBL_CPRECEIPT_LIST',
        'enabledActions' => array(
            'ADD'
        ),
        'listingFunctionName' => 'get_dependents_list',
        'leftSideReferenceFieldName' => null,
        'rightSideReferenceFieldName' => 'related_servicecontract'
    )
);

