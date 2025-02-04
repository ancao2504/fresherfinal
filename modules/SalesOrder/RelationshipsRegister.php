<?php

/*
	System auto-generated on 2022-08-18 11:34:25 am by admin. 
*/

$relationships = array(
    array(
        'leftSideModule' => 'SalesOrder',
        'rightSideModule' => 'CPReceipt',
        'relationshipType' => '1:N',
        'relationshipName' => 'LBL_CPRECEIPT_LIST',
        'enabledActions' => array(
            'ADD'
        ),
        'listingFunctionName' => 'get_dependents_list',
        'leftSideReferenceFieldName' => null,
        'rightSideReferenceFieldName' => 'related_salesorder'
    ),
    array(
        'leftSideModule' => 'SalesOrder',
        'rightSideModule' => 'CPPayment',
        'relationshipType' => '1:N',
        'relationshipName' => 'LBL_CPPAYMENT_LIST',
        'enabledActions' => array(
            'ADD'
        ),
        'listingFunctionName' => 'get_dependents_list',
        'leftSideReferenceFieldName' => null,
        'rightSideReferenceFieldName' => 'related_salesorder'
    ),
    array(
        'leftSideModule' => 'SalesOrder',
        'rightSideModule' => 'PurchaseOrder',
        'relationshipType' => 'N:N',
        'relationshipName' => 'LBL_PURCHASEORDER_LIST',
        'enabledActions' => array(
            'ADD',
            'SELECT'
        ),
        'listingFunctionName' => 'get_related_list',
        'leftSideReferenceFieldName' => null,
        'rightSideReferenceFieldName' => null
    )
);