<?php

/*
	System auto-generated on 2025-01-04 10:09:24 am by admin. 
	THIS FILE IS FOR DEVELOPER TO UPDATE FROM LAYOUT EDITOR. YOU CAN MODIFY THIS FILE FOR CUSTOMIZING BUT REMEMBER THAT ALL COMMENTS WILL BE REMOVED!!!
*/

$editViewBlocks = array(
    'LBL_CUSTOM_INFORMATION' => array(
        'sequence' => '5'
    ),
    'LBL_ADDRESS_INFORMATION' => array(
        'sequence' => '2'
    ),
    'LBL_DESCRIPTION_INFORMATION' => array(
        'sequence' => '3'
    ),
    'LBL_INVOICE_OUTPUT_INFORMATION' => array(
        'sequence' => '4'
    )
);

$detailViewBlocks = array(

);

$fields = array(
    'accounts_business_type' => array(
        'columnname' => 'accounts_business_type',
        'tablename' => 'vtiger_account',
        'generatedtype' => '2',
        'uitype' => '16',
        'fieldname' => 'accounts_business_type',
        'fieldlabel' => 'LBL_ACCOUNTS_BUSINESS_TYPE',
        'readonly' => '1',
        'presence' => '2',
        'defaultvalue' => 'B2C',
        'maximumlength' => '100',
        'sequence' => '31',
        'displaytype' => '1',
        'typeofdata' => 'V~M',
        'quickcreate' => '2',
        'quickcreatesequence' => '9',
        'info_type' => 'BAS',
        'masseditable' => '2',
        'helpinfo' => '',
        'summaryfield' => '1',
        'headerfield' => '0',
        'isunique' => '0',
        'editview_sequence' => '31',
        'editview_presence' => '2',
        'columntype' => 'varchar(255)',
        'editview_block_name' => 'LBL_ACCOUNT_INFORMATION',
        'detailview_block_name' => 'LBL_ACCOUNT_INFORMATION'
    ),
    'employees' => array(
        'editview_block_name' => 'LBL_ACCOUNT_INFORMATION',
        'editview_sequence' => '32',
        'editview_presence' => '2',
        'detailview_block_name' => 'LBL_ACCOUNT_INFORMATION',
        'sequence' => '32',
        'presence' => '2',
        'typeofdata' => 'I~M',
        'quickcreate' => '2'
    ),
    'annual_revenue' => array(
        'editview_block_name' => 'LBL_ACCOUNT_INFORMATION',
        'editview_sequence' => '33',
        'editview_presence' => '2',
        'typeofdata' => 'N~O',
        'quickcreate' => '2'
    ),
    'accounttype' => array(
        'typeofdata' => 'V~O',
        'quickcreate' => '2'
    ),
    'accountname' => array(
        'typeofdata' => 'V~M',
        'quickcreate' => '2'
    ),
    'tax_code' => array(
        'typeofdata' => 'V~M~LE~50',
        'quickcreate' => '2'
    ),
    'account_no' => array(
        'editview_block_name' => 'LBL_ACCOUNT_INFORMATION',
        'editview_sequence' => '34',
        'editview_presence' => '2',
        'detailview_block_name' => 'LBL_ACCOUNT_INFORMATION',
        'sequence' => '33',
        'presence' => '2'
    )
);

