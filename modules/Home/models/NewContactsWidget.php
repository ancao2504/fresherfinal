<?php

/**
 * NewContactsWidget
 * Author: Phu Vo
 * Date: 2020.08.28
 */

class Home_NewContactsWidget_Model extends Home_BaseListCustomDashboard_Model {

    public function getDefaultParams() {
        $defaultParams = [
            'period' => 'month',
        ];

        return $defaultParams;
    }

    public function getWidgetHeaders($params) {
        $widgetHeaders = [
            [
                'name' => 'record_name',
                'label' => vtranslate('LBL_FULL_NAME'),
            ],
            [
                'name' => 'account',
                'label' => vtranslate('Account Name', 'Contacts'),
            ],
            [
                'name' => 'title',
                'label' => vtranslate('Title', 'Contacts'),
            ],
            [
                'name' => 'email',
                'label' => vtranslate('Email', 'Contacts'),
            ],
            [
                'name' => 'phone',
                'label' => vtranslate('Phone', 'Contacts'),
            ],
        ];

        return $widgetHeaders;
    }

    public function getWidgetData($params) {
        global $adb;

        $data = [];
        $total = 0;
        
        $periodInfo = Reports_CustomReport_Helper::getPeriodFromFilter($params);
        $aclQuery = CRMEntity::getListViewSecurityParameter('Contacts');

        $sql = "SELECT
                vtiger_crmentity.crmid AS record_id,
                vtiger_crmentity.label AS record_name,
                vtiger_crmentity.setype AS record_module,
                account_entity.label AS account,
                vtiger_contactdetails.title,
                vtiger_contactdetails.email,
                vtiger_contactdetails.mobile AS phone 
            FROM vtiger_contactdetails
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_contactdetails.contactid AND vtiger_crmentity.setype = 'Contacts' AND vtiger_crmentity.deleted = 0)
            LEFT JOIN vtiger_crmentity AS account_entity ON (account_entity.crmid = vtiger_contactdetails.accountid AND account_entity.setype = 'Accounts' AND vtiger_crmentity.deleted = 0) 
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE('{$periodInfo['from_date']}')
                AND DATE(vtiger_crmentity.createdtime) <= DATE('{$periodInfo['to_date']}')
                AND vtiger_contactdetails.contacts_type = 'Customer'
                {$aclQuery}
            ORDER BY vtiger_crmentity.createdtime DESC";

        $totalSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_contactdetails
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_contactdetails.contactid AND vtiger_crmentity.setype = 'Contacts' AND vtiger_crmentity.deleted = 0)
            LEFT JOIN vtiger_crmentity AS account_entity ON (account_entity.crmid = vtiger_contactdetails.accountid AND account_entity.setype = 'Accounts' AND vtiger_crmentity.deleted = 0) 
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE('{$periodInfo['from_date']}')
                AND DATE(vtiger_crmentity.createdtime) <= DATE('{$periodInfo['to_date']}')
                AND vtiger_contactdetails.contacts_type = 'Customer'
                {$aclQuery}";

        if (!empty($params['length'])) {
            $sql .= " LIMIT {$params['length']}";
            if (!empty($params['start'])) $sql .= " OFFSET {$params['start']}";
        }
        
        $result = $adb->pquery($sql);
        $total = $adb->getOne($totalSql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $data[] = $row;
        }

        $result = [
            'draw' => intval($params['draw']),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => array_values($data),
            'offset' => $params['start'],
            'length' => $params['length'],
        ];

        return $result;
    }
}