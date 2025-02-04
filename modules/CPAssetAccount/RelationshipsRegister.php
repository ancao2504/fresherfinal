<?php

/* System auto-generated on 2019-08-16 02:23:56 pm.  */

$relationships = array(
    array(
        'leftSideModule' => 'CPAssetAccount',
        'rightSideModule' => 'CPReceipt',
        'relationshipType' => '1:N',
        'relationshipName' => 'LBL_CPRECEIPT_LIST',
        'enabledActions' => array(
            'ADD'
        ),
        'listingFunctionName' => 'get_dependents_list',
        'leftSideReferenceFieldName' => null,
        'rightSideReferenceFieldName' => 'asset_account_id'
    ),
    array(
        'leftSideModule' => 'CPAssetAccount',
        'rightSideModule' => 'CPPayment',
        'relationshipType' => '1:N',
        'relationshipName' => 'LBL_CPPAYMENT_LIST',
        'enabledActions' => array(
            'ADD'
        ),
        'listingFunctionName' => 'get_dependents_list',
        'leftSideReferenceFieldName' => null,
        'rightSideReferenceFieldName' => 'asset_account_id'
    ),
    array(
        'leftSideModule' => 'CPAssetAccount',
        'rightSideModule' => 'CPTransferMoney',
        'relationshipType' => '1:N',
        'relationshipName' => 'LBL_CPACCOUNTTRANSFER_TO_LIST',
        'enabledActions' => array(
        ),
        'listingFunctionName' => 'get_dependents_list',
        'leftSideReferenceFieldName' => null,
        'rightSideReferenceFieldName' => 'target_asset_account_id'
    ),
    array(
        'leftSideModule' => 'CPAssetAccount',
        'rightSideModule' => 'CPTransferMoney',
        'relationshipType' => '1:N',
        'relationshipName' => 'LBL_CPACCOUNTTRANSFER_FROM_LIST',
        'enabledActions' => array(
        ),
        'listingFunctionName' => 'get_dependents_from_list',
        'leftSideReferenceFieldName' => null,
        'rightSideReferenceFieldName' => 'source_asset_account_id'
    )
);

