<?php

/**
 * CustomerHaveBirthdayThisMonthWidget
 * Author: Phu Vo
 * Date: 2020.08.27
 */

class Home_CustomerHaveBirthdayThisMonthWidget_Model extends Home_BaseListCustomDashboard_Model {

    function getWidgetHeaders($params) {
        $widgetHeaders = [
            [
                'name' => 'record_name',
                'label' => vtranslate('LBL_FULL_NAME'),
            ],
            [
                'name' => 'email',
                'label' => vtranslate('Email'),
            ],
            [
                'name' => 'phone',
                'label' => vtranslate('Phone'),
            ],
            [
                'name' => 'age',
                'label' => vtranslate('LBL_AGE'),
            ],
            [
                'name' => 'birthday',
                'label' => vtranslate('LBL_BIRTHDAY'),
            ],
            [
                'name' => 'action',
                'label' => vtranslate('LBL_ACTION'),
            ],
        ];

        return $widgetHeaders;
    }
    
    function getWidgetData($params) {
        global $adb, $current_user;

        $data = [];
        $aclQuery = CRMEntity::getListViewSecurityParameter('Contacts');

        $sql = "SELECT
                vtiger_crmentity.label AS record_name,
                vtiger_crmentity.crmid AS record_id,
                vtiger_crmentity.setype AS record_module,
                vtiger_contactdetails.email,
                vtiger_contactdetails.mobile AS phone,
                YEAR (CURRENT_TIMESTAMP) - YEAR (vtiger_contactsubdetails.birthday) - (RIGHT (CURRENT_TIMESTAMP, 5) < RIGHT (vtiger_contactsubdetails.birthday, 5)) AS age,
                vtiger_contactsubdetails.birthday AS birthday
            FROM vtiger_contactdetails
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_contactdetails.contactid AND vtiger_crmentity.setype = 'Contacts' AND vtiger_crmentity.deleted = 0)
            INNER JOIN vtiger_contactsubdetails ON (vtiger_contactsubdetails.contactsubscriptionid = vtiger_contactdetails.contactid) 
            WHERE MONTH(vtiger_contactsubdetails.birthday) = MONTH(CURRENT_DATE()) {$aclQuery}
            ORDER BY DAY(vtiger_contactsubdetails.birthday) ASC";

        if (!empty($params['length'])) {
            $sql .= " LIMIT {$params['length']}";
            if (!empty($params['start'])) $sql .= " OFFSET {$params['start']}";
        }
        
        $totalSql = "SELECT
            COUNT(vtiger_crmentity.crmid)
            FROM vtiger_contactdetails
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_contactdetails.contactid AND vtiger_crmentity.setype = 'Contacts' AND vtiger_crmentity.deleted = 0)
            INNER JOIN vtiger_contactsubdetails ON (vtiger_contactsubdetails.contactsubscriptionid = vtiger_contactdetails.contactid) 
            WHERE MONTH(vtiger_contactsubdetails.birthday) = MONTH(CURRENT_DATE()) {$aclQuery}";

        $result = $adb->pquery($sql);
        $total = $adb->getOne($totalSql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $birthdayTimeField = new DateTimeField($row['birthday']);
            $row['birthday'] = $birthdayTimeField->getDisplayDate($current_user);
            $row['action'] = $this->getActionHtml($row);
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

    private function getActionHtml($row) {
        $currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

        $html = '
            <span class="more dropdown action">
                <button class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                    Chọn thao tác
                </button>
                <ul class="dropdown-menu widgetActions" style="position: relative; display: none;" data-row="">
        ';

        // Send SMS button
        $SMSNotifierModuleModel = Vtiger_Module_Model::getInstance('SMSNotifier');
		if(!empty($SMSNotifierModuleModel) && $currentUserModel->hasModulePermission($SMSNotifierModuleModel->getId())) {
            $html .= '<li><a href="javascript:void(0)" class="sendSMS">' . vtranslate('LBL_SEND_SMS', 'SMSNotifier') . '</a></li>';
        }
        
        if(CPSocialIntegration_Config_Helper::isZaloMessageAllowed()) {
            $html .= '<li><a href="javascript:void(0)" class="sendZalo">' . vtranslate('LBL_SOCIAL_INTEGRATION_SEND_ZALO_MESSAGE', 'Vtiger') . '</a></li>';
		}
        
        // $html .= '<li><a href="javascript:void(0)" class="sendMessageFacebook">Gửi tin nhắn Facebook</a></li>';
            
        $html .= '
                </ul>
            </span>
        ';

        return $html;
    }
}