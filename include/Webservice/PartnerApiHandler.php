<?php

/*
*   Class PartnerApiHandler
*   Author: Hieu Nguyen
*   Date: 2020-10-05
*   Purpose: Handle request from 3rd systems
*/

require_once('include/utils/PartnerApiUtils.php');

class PartnerApiHandler extends PartnerApiUtils {

    static function authenticate($accessKey) {
        authenticateUserByAccessKey($accessKey);

        if (empty($_SESSION['authenticated_user_id'])) {
            self::setResponse(401);
        }
    }

    static function getCategoryList(Vtiger_Request $request) {
        global $adb;

        // Process
        $sql = "SELECT productcategoryid, productcategory FROM vtiger_productcategory";

        $result = $adb->pquery($sql, []);
        $entryList = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $row['display_name'] = vtranslate($row['productcategory'], 'Products');
            $entryList[] = decodeUTF8($row);
        }

        // Respond
        $response = ['success' => 1, 'data' => $entryList];
        self::setResponse(200, $response);
    }

    static function getProductList(Vtiger_Request $request) {
        global $adb;

        // Process
        $sql = "SELECT p.productid, p.product_no, p.productname, p.manufacturer, c.productcategoryid, c.productcategory, p.active, ROUND(p.weight) AS weight, ROUND(p.qtyinstock) AS qtyinstock, 
                p.usageunit, ROUND(p.qty_per_unit) AS qty_per_unit, ROUND(p.purchase_cost) AS purchase_price, ROUND(p.unit_price) AS selling_price, CONCAT(i.path, i.attachmentsid, '_', i.name) AS image, e.description
            FROM vtiger_products AS p
            INNER JOIN vtiger_crmentity AS e ON (e.crmid = p.productid AND e.setype = 'Products' AND e.deleted = 0)
            INNER JOIN vtiger_productcategory AS c ON (c.productcategory = p.productcategory)
            LEFT JOIN vtiger_seattachmentsrel AS pi ON (pi.crmid = p.productid)
            LEFT JOIN vtiger_attachments AS i ON (i.attachmentsid = pi.attachmentsid)";

        $result = $adb->pquery($sql, []);
        $entryList = [];

        while ($row = $adb->fetchByAssoc($result)) {
            if (empty($row['purchase_price'])) {
                $row['purchase_price'] = $row['selling_price'];
            }

            $entryList[] = decodeUTF8($row);
        }

        // Respond
        $response = ['success' => 1, 'data' => $entryList];
        self::setResponse(200, $response);
    }

    static function createOrder(Vtiger_Request $request) {
        global $current_user;
        $data = $request->get('Data');
        $salesOrderData = [];

        if (empty($data) || empty($data['source']) || empty($data['customer']) || empty($data['shipping']) || empty($data['items'])) {
            self::setResponse(400);
        }

        // Handle customer info
        $customerRecordModel = self::_saveCustomerInfo($data);
        
        // Handle relationship with customer
        $salesOrderData['contact_id'] = $customerRecordModel->getId();

        if (!empty($customerRecordModel->get('account_id'))) {
            $salesOrderData['account_id'] = $customerRecordModel->get('account_id');
        }
        else {
            $salesOrderData['account_id'] = Accounts_Data_Helper::getPersonalAccountId();
        }

        // Assign general informations
        $currencySymbolAndRate = getCurrencySymbolandCRate($current_user->currency_id);
        $salesOrderData['subject'] = 'Đơn hàng của KH ' . $customerRecordModel->get('full_name') .' - '. DateTimeField::convertToUserFormat(date('Y-m-d H:i:s'));
        $salesOrderData['sostatus'] = 'Created';
        $salesOrderData['description'] = $data['note'];
        $salesOrderData['currency_id'] = $current_user->currency_id;
        $salesOrderData['conversion_rate'] = $currencySymbolAndRate['rate'];
        $salesOrderData['assigned_user_id'] = $current_user->id;
        $salesOrderData['main_owner_id'] = $current_user->id;

        // Shipping info
        $salesOrderData['ship_street'] = $data['shipping']['ship_street'];
        $salesOrderData['ship_city'] = $data['shipping']['ship_city'];
        $salesOrderData['ship_state'] = $data['shipping']['ship_state'];
        $salesOrderData['ship_country'] = $data['shipping']['ship_country'];
        $salesOrderData['receiver_name'] = $data['shipping']['receiver_name'];
        $salesOrderData['receiver_phone'] = $data['shipping']['receiver_phone'];

        // Billing info
        $salesOrderData['issue_invoice'] = $data['billing']['issue_invoice'];
        $salesOrderData['bill_street'] = $data['billing']['bill_street'];
        $salesOrderData['bill_city'] = $data['billing']['bill_city'];
        $salesOrderData['bill_state'] = $data['billing']['bill_state'];
        $salesOrderData['bill_country'] = $data['billing']['bill_country'];

        // Assign items
        $_REQUEST['hidtax_row_no0'] = '';
        $_REQUEST['productName0'] = '';
        $_REQUEST['hdnProductId0'] = '';
        $_REQUEST['lineItemType0'] = '';
        $_REQUEST['subproduct_ids0'] = '';
        $_REQUEST['comment0'] = '';
        $_REQUEST['qty0'] = '0';
        $_REQUEST['purchaseCost0'] = '0';
        $_REQUEST['margin0'] = '0';
        $_REQUEST['listPrice0'] = '0';
        $_REQUEST['discount_type0'] = 'zero';
        $_REQUEST['discount_percentage0'] = '0';
        $_REQUEST['section_0'] = '0';
        $_REQUEST['section_name0'] = '0';

        foreach ($data['items'] as $i => $item) {
            $index = $i + 1;

            $itemPrice = $item['price'] * $item['quantity'];
            $purchaseCost = $item['purchase_price'] * $item['quantity'];
            $margin = $itemPrice - $purchaseCost;

            $_REQUEST['hidtax_row_no' . $index] = '';
            $_REQUEST['hdnProductId' . $index] = $item['productid'];
            $_REQUEST['productName' . $index] = $item['productname'];
            $_REQUEST['lineItemType' . $index] = 'Products';
            $_REQUEST['subproduct_ids' . $index] = '';
            $_REQUEST['comment' . $index] = '';
            $_REQUEST['qty' . $index] = $item['quantity'];
            $_REQUEST['purchaseCost' . $index] = $purchaseCost;
            $_REQUEST['margin' . $index] = $margin;
            $_REQUEST['listPrice' . $index] = $item['price'];
            $_REQUEST['discount_type' . $index] = 'zero';
            $_REQUEST['discount' . $index] = 'on';
            $_REQUEST['discount_percentage' . $index] = '';
            $_REQUEST['discount_amount' . $index] = '0';
            $_REQUEST['section_' . $index] = '1';
            $_REQUEST['section_name' . $index] = '';
        }

        $_REQUEST['totalProductCount'] = count($data['items']);
        $_REQUEST['region_id'] = '0';

        // Tax info (tax2 = 10%)
        $_REQUEST['taxtype'] = 'group';
        $_REQUEST['tax2_group_percentage'] = $data['tax_percent'];

        // Discount and adjustment
        $_REQUEST['discount_type_final'] = 'amount';
        $_REQUEST['discount_final'] = $data['discount_amount'];
        $_REQUEST['discount_amount_final'] = $data['discount_amount'];
        $_REQUEST['adjustment'] = '0';

        // Total amount
        $_REQUEST['subtotal'] = $data['sub_total'];
        $_REQUEST['pre_tax_total'] = $data['sub_total'] - $data['discount_amount'];
        $_REQUEST['shipping_handling_charge'] = '0';
        $_REQUEST['total'] = $data['grand_total'];

        // Save data
        $salesOrderData['source'] = strtoupper($data['source']);
        $salesOrderRecordModel = Vtiger_Record_Model::getCleanInstance('SalesOrder');
        $salesOrderRecordModel->setData($salesOrderData);
        $salesOrderRecordModel->save();

        // Respond
        $response = ['success' => 1, 'id' => $salesOrderRecordModel->getId()];
        self::setResponse(200, $response);
    }
}