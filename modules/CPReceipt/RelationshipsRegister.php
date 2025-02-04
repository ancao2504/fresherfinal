<?php

/* System auto-generated on 2019-08-16 02:37:54 pm.  */

$relationships = array(
    array(
        'leftSideModule' => 'CPReceipt',
        'rightSideModule' => 'Invoice',
        'relationshipType' => 'N:N',
        'relationshipName' => 'LBL_INVOICE_LIST',
        'enabledActions' => array(
            'ADD',
            'SELECT'
        ),
        'listingFunctionName' => 'get_related_list',
        'leftSideReferenceFieldName' => null,
        'rightSideReferenceFieldName' => null
    ),
    array(
        'leftSideModule' => 'CPReceipt',
        'rightSideModule' => 'CPTransferMoney',
        'relationshipType' => '1:N',
        'relationshipName' => 'LBL_CPACCOUNTTRANSFER_LIST',
        'enabledActions' => array(
            'ADD'
        ),
        'listingFunctionName' => 'get_dependents_list',
        'leftSideReferenceFieldName' => null,
        'rightSideReferenceFieldName' => 'receipt_id'
    )
);

