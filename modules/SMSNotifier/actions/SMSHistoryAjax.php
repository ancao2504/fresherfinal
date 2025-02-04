<?php
 /*
    Author: Phuc Lu
    Date: 2020.02.18
    Purpose: get sms notifier list
*/

class SMSNotifier_SMSHistoryAjax_Action extends Vtiger_Action_Controller {

    function checkPermission(Vtiger_Request $request) {
        return true;
    }

    function process(Vtiger_Request $request) {
        $db = PearDatabase::getInstance();

        $draw = $request->get('draw');
        $start = $request->get('start');
        $length = $request->get('length');
        $search = $request->get('search');
        $recordId = $request->get('recordId');
        $order = $request->get('order');
        $messages = [];

        // Generate sql sections
        $select = "SELECT vtiger_smsnotifier.message, vtiger_smsnotifier_status.tonumber, vtiger_crmentity.createdtime, vtiger_smsnotifier_status.status, vtiger_smsnotifier_status.statusmessage";
        $selectTotal = "SELECT COUNT(vtiger_smsnotifier.smsnotifierid) AS total";
        $from = "FROM vtiger_smsnotifier";
        $join = "INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_smsnotifier.smsnotifierid AND deleted = 0 AND setype = 'SMSNotifier')
                INNER JOIN vtiger_crmentityrel ON (vtiger_crmentityrel.crmid = vtiger_smsnotifier.smsnotifierid AND vtiger_crmentityrel.relcrmid = {$recordId})
                INNER JOIN vtiger_smsnotifier_status ON (vtiger_smsnotifier_status.smsnotifierid = vtiger_smsnotifier.smsnotifierid)";
        $paging = "LIMIT {$start}, {$length}";
        $where = "WHERE vtiger_smsnotifier_status.customer_id = '{$recordId}'";

        // If having search
        if (!empty($search['value'])) {
            $where .= " AND vtiger_smsnotifier.message LIKE '%{$search['value']}%'";
        }

        // If having sorting
        $orderableFields = ['vtiger_smsnotifier.message', 'vtiger_smsnotifier_status.tonumber', 'vtiger_crmentity.createdtime', 'vtiger_smsnotifier_status.status', 'vtiger_smsnotifier_status.statusmessage']; 
        $order = $order[0]; // Mapping with input
        $orderBy = $orderableFields[$order['column']];
        $orderDirection = strtoupper($order['dir']);
        $order = "ORDER BY {$orderBy} {$orderDirection}";


        // Generate completed sql
        $totalSql = "{$selectTotal} {$from} {$join}";
        $totalFilterSql = "{$selectTotal} {$from} {$join} {$where}";
        $smsDataSql = "{$select} {$from} {$join} {$where} {$order} {$paging}";

        // Get result
        $totalMessages = $db->getOne($totalSql, []);
        $totalFilerMessages = $db->getOne($totalFilterSql, []);
        $result = $db->pquery($smsDataSql, []);
        
        while ($row = $db->fetchByAssoc($result)) {
            $createTime = new DateTimeField($row['createdtime']);
            $messages[] = [
                $row['message'],
                $row['tonumber'],
                $createTime->getDisplayDateTimeValue(),
                vtranslate($row['status']),
                $row['statusmessage'],
            ];
        }

        $return = [
            'draw' => intval($draw),
            'recordsTotal' => intval($totalMessages),
            'recordsFiltered' => $search['value'] == '' ? intval($totalMessages) : intval($totalFilerMessages),
            'data' => $messages
        ];
        
        echo json_encode($return);
    }
}