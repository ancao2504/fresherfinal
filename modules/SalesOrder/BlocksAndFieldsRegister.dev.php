<?php

/*
	System auto-generated on 2025-01-05 03:31:31 pm by admin. 
	THIS FILE IS FOR DEVELOPER TO UPDATE FROM LAYOUT EDITOR. YOU CAN MODIFY THIS FILE FOR CUSTOMIZING BUT REMEMBER THAT ALL COMMENTS WILL BE REMOVED!!!
*/

$editViewBlocks = array(

);

$detailViewBlocks = array(

);

$fields = array(
    'order_date' => array(
        'typeofdata' => 'D~M',
        'defaultvalue' => '04-01-2025'
    ),
    'account_id' => array(
        'typeofdata' => 'I~M'
    ),
    'salesorder_no' => array(
        'detailview_block_name' => 'LBL_SO_INFORMATION',
        'sequence' => '27',
        'presence' => '2',
        'editview_block_name' => 'LBL_SO_INFORMATION',
        'editview_sequence' => '27',
        'editview_presence' => '2'
    ),
    'purchase_cost' => array(
        'editview_block_name' => 'LBL_ITEM_DETAILS',
        'editview_sequence' => '20',
        'editview_presence' => '1'
    ),
    'unit_price' => array(
        'columnname' => 'unit_price',
        'tablename' => 'vtiger_salesorder',
        'generatedtype' => '2',
        'uitype' => '1',
        'fieldname' => 'unit_price',
        'fieldlabel' => 'LBL_UNIT_PRICE',
        'readonly' => '1',
        'presence' => '2',
        'defaultvalue' => '',
        'maximumlength' => '100',
        'sequence' => '31',
        'displaytype' => '1',
        'typeofdata' => 'V~O~LE~10',
        'quickcreate' => '3',
        'quickcreatesequence' => '38',
        'info_type' => 'BAS',
        'masseditable' => '1',
        'helpinfo' => '',
        'summaryfield' => '0',
        'headerfield' => '0',
        'isunique' => '0',
        'editview_sequence' => '31',
        'editview_presence' => '2',
        'columntype' => 'varchar(10)',
        'editview_block_name' => 'LBL_SO_INFORMATION',
        'detailview_block_name' => 'LBL_SO_INFORMATION'
    )
);

