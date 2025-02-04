<?php

/*
	System auto-generated on 2022-08-18 11:35:03 am by admin. 
*/

$relationships = array(
    array(
        'leftSideModule' => 'PurchaseOrder',
        'rightSideModule' => 'Invoice',
        'relationshipType' => '1:N',
        'relationshipName' => 'LBL_INVOICE_LIST',
        'enabledActions' => array(
            'ADD'
        ),
        'listingFunctionName' => 'get_dependents_list',
        'leftSideReferenceFieldName' => null,
        'rightSideReferenceFieldName' => 'related_purchaseorder'
    ),
    array(
        'leftSideModule' => 'PurchaseOrder',
        'rightSideModule' => 'CPPayment',
        'relationshipType' => '1:N',
        'relationshipName' => 'LBL_CPPAYMENT_LIST',
        'enabledActions' => array(
            'ADD'
        ),
        'listingFunctionName' => 'get_dependents_list',
        'leftSideReferenceFieldName' => null,
        'rightSideReferenceFieldName' => 'related_purchaseorder'
    ),
    array(
        'leftSideModule' => 'PurchaseOrder',
        'rightSideModule' => 'CPReceipt',
        'relationshipType' => '1:N',
        'relationshipName' => 'LBL_CPRECEIPT_LIST',
        'enabledActions' => array(
            'ADD'
        ),
        'listingFunctionName' => 'get_dependents_list',
        'leftSideReferenceFieldName' => null,
        'rightSideReferenceFieldName' => 'related_purchaseorder'
    ),
    array(
        'leftSideModule' => 'PurchaseOrder',
        'rightSideModule' => 'SalesOrder',
        'relationshipType' => 'N:N',
        'relationshipName' => 'LBL_SALESORDER_LIST',
        'enabledActions' => array(
            'ADD',
            'SELECT'
        ),
        'listingFunctionName' => 'get_related_list',
        'leftSideReferenceFieldName' => null,
        'rightSideReferenceFieldName' => null
    )
);

