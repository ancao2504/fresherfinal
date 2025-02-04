<?php

/* System auto-generated on 2019-08-01 03:19:51 pm.  */

$relationships = array(
    array(
        'leftSideModule' => 'Invoice',
        'rightSideModule' => 'CPReceipt',
        'relationshipType' => 'N:N',
        'relationshipName' => 'LBL_CPRECEIPT_LIST',
        'enabledActions' => array(
            'SELECT'
        ),
        'listingFunctionName' => 'get_related_list',
        'leftSideReferenceFieldName' => null,
        'rightSideReferenceFieldName' => null
    ),    
    array(
        'leftSideModule' => 'Invoice',
        'rightSideModule' => 'CPPayment',
        'relationshipType' => 'N:N',
        'relationshipName' => 'LBL_CPPAYMENT_LIST',
        'enabledActions' => array(
            'SELECT'
        ),
        'listingFunctionName' => 'get_related_list',
        'leftSideReferenceFieldName' => null,
        'rightSideReferenceFieldName' => null
    )
);

