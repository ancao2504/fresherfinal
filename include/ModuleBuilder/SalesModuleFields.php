<?php

$salesModuleFields = array(
    //array('column_name' => 'accountid', 'field_name' => 'account_id',  'ui_type' => 73, 'data_type' => 'I~M', 'label' => 'Account Name', 'details_block' => true), //-- Remove By Kelvin Thang 2020-04-18 -- Field accountid is relationship for account. Works independently for custom modules
    array('column_name' => 'currency_id', 'field_name' => 'currency_id',  'ui_type' => 117, 'data_type' => 'I~O', 'label' => 'Currency', 'details_block' => true),
    array('column_name' => 'conversion_rate', 'field_name' => 'conversion_rate',  'ui_type' => 16, 'data_type' => 'N~O', 'label' => 'Tax Region', 'details_block' => true),
    array('column_name' => 'subtotal', 'field_name' => 'hdnSubTotal',  'ui_type' => 72, 'data_type' => 'N~O', 'label' => 'Sub Total', 'details_block' => true),
    array('column_name' => 'taxtype', 'field_name' => 'hdnTaxType',  'ui_type' => 16, 'data_type' => 'V~O', 'label' => 'Tax Type', 'details_block' => true),
    array('column_name' => 'pre_tax_total', 'field_name' => 'pre_tax_total',  'ui_type' => 71, 'data_type' => 'N~O', 'label' => 'Pre Tax Total', 'details_block' => true),
    array('column_name' => 'discount_percent', 'field_name' => 'hdnDiscountPercent',  'ui_type' => 1, 'data_type' => 'N~O', 'label' => 'Discount Percent', 'details_block' => true),
    array('column_name' => 'discount_amount', 'field_name' => 'hdnDiscountAmount',  'ui_type' => 72, 'data_type' => 'N~O', 'label' => 'Discount Amount', 'details_block' => true),
    array('column_name' => 's_h_percent', 'field_name' => 'hdnS_H_Percent',  'ui_type' => 1, 'data_type' => 'N~O', 'label' => 'S&H Percent', 'details_block' => true),
    array('column_name' => 's_h_amount', 'field_name' => 'hdnS_H_Amount',  'ui_type' => 72, 'data_type' => 'N~O', 'label' => 'S&H Amount', 'details_block' => true),
    array('column_name' => 'total', 'field_name' => 'hdnGrandTotal',  'ui_type' => 72, 'data_type' => 'N~O', 'label' => 'Total', 'details_block' => true),
    array('column_name' => 'adjustment', 'field_name' => 'txtAdjustment',  'ui_type' => 72, 'data_type' => 'NN~O', 'label' => 'Adjustment', 'details_block' => true),
    array('column_name' => 'compound_taxes_info', 'field_name' => 'compound_taxes_info',  'ui_type' => 10, 'data_type' => 'V~O', 'label' => 'Compound Taxes Info', 'details_block' => true),
);

$inventoryFields = array(
    array('table_name' => 'vtiger_inventoryproductrel', 'column_name' => 'productid', 'field_name' => 'productid',  'ui_type' => 10, 'data_type' => 'V~M', 'label' => 'Item Name'),
    array('table_name' => 'vtiger_inventoryproductrel', 'column_name' => 'quantity', 'field_name' => 'quantity',  'ui_type' => 7, 'data_type' => 'N~O', 'label' => 'Quantity'),
    array('table_name' => 'vtiger_inventoryproductrel', 'column_name' => 'listprice', 'field_name' => 'listprice',  'ui_type' => 71, 'data_type' => 'N~O', 'label' => 'List Price'),
    array('table_name' => 'vtiger_inventoryproductrel', 'column_name' => 'comment', 'field_name' => 'comment',  'ui_type' => 10, 'data_type' => 'V~O', 'label' => 'Item Comment'),
    array('table_name' => 'vtiger_inventoryproductrel', 'column_name' => 'discount_amount', 'field_name' => 'discount_amount',  'ui_type' => 71, 'data_type' => 'N~O', 'label' => 'Item Discount Amount'),
    array('table_name' => 'vtiger_inventoryproductrel', 'column_name' => 'discount_percent', 'field_name' => 'discount_percent',  'ui_type' => 7, 'data_type' => 'V~O', 'label' => 'Item Discount Percent'),
    array('table_name' => 'vtiger_inventoryproductrel', 'column_name' => 'tax1', 'field_name' => 'tax1',  'ui_type' => 83, 'data_type' => 'V~O', 'label' => 'VAT'),
    array('table_name' => 'vtiger_inventoryproductrel', 'column_name' => 'tax2', 'field_name' => 'tax2',  'ui_type' => 83, 'data_type' => 'V~O', 'label' => 'Sales'),
    array('table_name' => 'vtiger_inventoryproductrel', 'column_name' => 'tax3', 'field_name' => 'tax3',  'ui_type' => 83, 'data_type' => 'V~O', 'label' => 'Service'),
    array('table_name' => 'vtiger_inventoryproductrel', 'column_name' => 'image', 'field_name' => 'image',  'ui_type' => 56, 'data_type' => 'V~O', 'label' => 'Image', 'display' => false),
    array('table_name' => 'vtiger_inventoryproductrel', 'column_name' => 'purchase_cost', 'field_name' => 'purchase_cost',  'ui_type' => 71, 'data_type' => 'N~O', 'label' => 'purchase_cost', 'display' => false),
    array('table_name' => 'vtiger_inventoryproductrel', 'column_name' => 'margin', 'field_name' => 'margin',  'ui_type' => 71, 'data_type' => 'N~O', 'label' => 'Margin', 'display' => false),
);