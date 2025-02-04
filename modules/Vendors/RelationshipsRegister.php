<?php

/* System auto-generated on 2019-08-15 09:02:58 am.  */

$relationships = array(
    array(
        'leftSideModule' => 'Vendors',
        'rightSideModule' => 'Invoice',
        'relationshipType' => '1:N',
        'relationshipName' => 'LBL_INVOICE_LIST',
        'enabledActions' => array(
            'ADD'
        ),
        'listingFunctionName' => 'get_dependents_list',
        'leftSideReferenceFieldName' => null,
        'rightSideReferenceFieldName' => 'related_vendor'
    ),
    array(
        'leftSideModule' => 'Vendors',
        'rightSideModule' => 'CPPayment',
        'relationshipType' => '1:N',
        'relationshipName' => 'LBL_CPPAYMENT_LIST',
        'enabledActions' => array(
            'ADD'
        ),
        'listingFunctionName' => 'get_dependents_list',
        'leftSideReferenceFieldName' => null,
        'rightSideReferenceFieldName' => 'vendor_id'
    )
);

