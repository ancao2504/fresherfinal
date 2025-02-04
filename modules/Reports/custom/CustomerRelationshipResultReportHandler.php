<?php

/*
    CustomerRelationshipResultReportHandler.php
    Author: Phuc Lu
    Date: 2020.05.05
*/

require_once('modules/Reports/custom/CustomReportHandler.php');
require_once('include/utils/CustomReportUtils.php');

class CustomerRelationshipResultReportHandler extends CustomReportHandler {

    protected $reportFilterTemplate = 'modules/Reports/tpls/CustomerRelationshipResultReport/CustomerRelationshipResultReportFilter.tpl';

    public function renderReportFilter(array $params) {
        $this->reportFilterMeta = [
            'departments' => Reports_CustomReport_Helper::getAllDepartments(),
            'filter_users' => Reports_CustomReport_Helper::getUsersByDepartment($params['department'], true, false),
            'input_validators' => [
                'from_date' => [
                    'mandatory' => false,
                    'presence' => true,
                    'quickcreate' => false,
                    'masseditable' => false,
                    'defaultvalue' => false,
                    'type' => 'date',
                    'name' => 'from_date',
                    'label' => vtranslate('LBL_REPORT_FROM', 'Reports'),
                ],
                'to_date' => [
                    'mandatory' => false,
                    'presence' => true,
                    'quickcreate' => false,
                    'masseditable' => false,
                    'defaultvalue' => false,
                    'type' => 'date',
                    'name' => 'to_date',
                    'label' => vtranslate('LBL_REPORT_TO', 'Reports'),
                ],
            ],
        ];

        return parent::renderReportFilter($params);
    }

    public function getReportHeaders() {
        $headers = [
            vtranslate('LBL_REPORT_NO', 'Reports') => '',
            vtranslate('LBL_REPORT_EMPLOYEE', 'Reports') => '30%',
            vtranslate('LBL_REPORT_CALL', 'Reports') =>  '5%',
            vtranslate('LBL_REPORT_MEETING', 'Reports') => '5%',
            vtranslate('LBL_REPORT_TASK', 'Reports') =>  '5%',
            vtranslate('LBL_REPORT_NOTE_COMMENT', 'Reports') =>  '5%',
            vtranslate('LBL_REPORT_SENT_EMAIL', 'Reports') =>  '5%',
        ];

        if (!isForbiddenFeature('SMSIntegration')) {
            $headers[vtranslate('LBL_REPORT_SENT_SMS', 'Reports')] = '5%';
        }

        if (!isForbiddenFeature('ZaloIntegration')) {
            $headers[vtranslate('LBL_REPORT_SENT_ZALO_MESSAGE', 'Reports')] = '5%';
        }

        if (!isForbiddenFeature('FacebookIntegration')) {
            $headers[vtranslate('LBL_REPORT_FACEBOOK_MESSAGE', 'Reports')] = '5%';
        }

        if (!isForbiddenFeature('HanaIntegration')) {
            $headers[vtranslate('LBL_REPORT_HANA_MESSAGE', 'Reports')] = '5%';
        }

        $headers = array_merge($headers, [
            vtranslate('LBL_REPORT_LATEST_CALL_TIME', 'Reports') =>  '9%',
            vtranslate('LBL_REPORT_LATEST_ACTIVITY_TIME', 'Reports') =>  '9%',
        ]);

        return $headers;
    }

    protected function getReportData($params, $forExport = false) {
        global $adb;

        if (empty($params['employees'])) {
            return [];
        }

        // Get employees
        $employees = $params['employees'];
        $departments = $params['departments'];

        if (in_array('0', $employees)) {
            $employees = Reports_CustomReport_Helper::getUsersByDepartment($departments, false, false);
            $employees = array_keys($employees);
        }

        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params, true);
        $employeeIds = implode("', '", $employees);
        $fullNameField = getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');

        $sql = "SELECT id, {$fullNameField} AS user_full_name FROM vtiger_users WHERE id IN ('{$employeeIds}')";
        $result = $adb->pquery($sql, []);
        $data = [];
        $dataMaxTime = [];
        $no = 0;

        while ($row = $adb->fetchByAssoc($result)) {
            $data[$row['id']] = [
                'id' => (!$forExport ? $row['id'] : ++$no),
                'user_full_name' => trim($row['user_full_name']),
                'call' => 0,
                'meeting' => 0,
                'task' => 0,
                'emails' => 0,
                'comment' => 0
            ];

            if (!isForbiddenFeature('SMSIntegration')) {
                $data[$row['id']]['sms'] = 0;
            }

            if (!isForbiddenFeature('ZaloIntegration')) {
                $data[$row['id']]['zalo'] = 0;
            }

            if (!isForbiddenFeature('FacebookIntegration')) {
                $data[$row['id']]['facebook'] = 0;
            }

            if (!isForbiddenFeature('HanaIntegration')) {
                $data[$row['id']]['hana'] = 0;
            }

            $data[$row['id']] = array_merge($data[$row['id']], [
                'last_call_time' => '',
                'last_activity_time' => ''
            ]);
        }

        // For all data
        $data['all'] = current($data);
        $data['all']['id'] = (!$forExport ? 'all' : '');
        $data['all']['user_full_name'] = vtranslate('LBL_REPORT_TOTAL', 'Reports');

        // Get call, meeting, task, email
        $sql = "SELECT main_owner_id, activitytype, activityid, MAX(date_start) AS max_time, COUNT(activityid) AS activities_num
            FROM vtiger_activity
            INNER JOIN vtiger_crmentity ON (activityid = crmid AND deleted = 0)
            WHERE (activitytype = 'Emails' OR eventstatus IN ('Held', 'Completed') OR vtiger_activity.status IN ('Completed'))
                AND main_owner_id IN ('{$employeeIds}') AND date_start BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY main_owner_id, activitytype
            ORDER BY main_owner_id";

        $result = $adb->pquery($sql, []);

        while ($row = $adb->fetchByAssoc($result)) {
            $activity = strtolower($row['activitytype']);
            $data[$row['main_owner_id']][$activity] = $row['activities_num'];
            $data['all'][$activity] += $row['activities_num'];

            if ($row['activities_num'] > 0 && $row['activitytype'] == 'Call') {
                $date = new DateTimeField($row['max_time']);
                $data[$row['main_owner_id']]['last_call_time'] = $date->getDisplayDate();
            }

            if ($row['activities_num'] > 0 && (empty($data[$row['main_owner_id']]['last_activity_time']) || strtotime($data[$row['main_owner_id']]['last_activity_time']) < strtotime($row['max_time']))) {
                $data[$row['main_owner_id']]['last_activity_time'] = $row['max_time'];
            }
        }

        // Get note
        $sql = "SELECT userid, COUNT(modcommentsid) AS comment_num, MAX(DATE(createdtime)) AS max_time
        FROM vtiger_modcomments
        INNER JOIN vtiger_crmentity ON (deleted = 0 AND crmid = modcommentsid)
        WHERE userid IN ('{$employeeIds}') AND createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
        GROUP BY userid";

        $result = $adb->pquery($sql, []);

        while ($row = $adb->fetchByAssoc($result)) {
            $data[$row['userid']]['comment'] = $row['comment_num'];
            $data['all']['comment'] += $row['comment_num'];

            if ($row['activities_num'] > 0 && (empty($data[$row['userid']]['last_activity_time']) || strtotime($data[$row['userid']]['last_activity_time']) < strtotime($row['max_time']))) {
                $data[$row['userid']]['last_activity_time'] = $row['max_time'];
            }
        }

        // Get SMS
        if (!isForbiddenFeature('ZaloIntegration')) {
            $sql = "SELECT smcreatorid, COUNT(smsnotifierid) AS sms_num, MAX(DATE(createdtime)) AS max_time
                FROM (
                    SELECT DISTINCT vtiger_smsnotifier_status.smsnotifierid, vtiger_smsnotifier_status.customer_id, vtiger_smsnotifier_status.tonumber, vtiger_crmentity.smcreatorid, vtiger_crmentity.createdtime
                    FROM vtiger_smsnotifier
                    INNER JOIN vtiger_crmentity ON (deleted = 0 AND vtiger_smsnotifier.smsnotifierid = crmid)
                    INNER JOIN vtiger_smsnotifier_status ON (vtiger_smsnotifier.smsnotifierid = vtiger_smsnotifier_status.smsnotifierid)
                    WHERE customer_id IS NOT NULL AND customer_id != '' AND smcreatorid IN ('{$employeeIds}') AND createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
                ) AS temp
                GROUP BY smcreatorid";
            $result = $adb->pquery($sql, []);

            while ($row = $adb->fetchByAssoc($result)) {
                $data[$row['smcreatorid']]['sms'] = $row['sms_num'];
                $data['all']['sms'] += $row['sms_num'];

                if ($row['sms'] > 0 && (empty($data[$row['smcreatorid']]['last_activity_time']) || strtotime($data[$row['smcreatorid']]['last_activity_time']) < strtotime($row['max_time']))) {
                    $data[$row['smcreatorid']]['last_activity_time'] = $row['max_time'];
                }
            }
        }

        // Get Zalo Message
        if (!isForbiddenFeature('ZaloIntegration')) {
            $sql = "SELECT smcreatorid, COUNT(cpsocialmessagelogid) AS zalo_num, MAX(scheduled_send_date) AS max_time
                FROM vtiger_cpsocialmessagelog
                INNER JOIN vtiger_crmentity ON (deleted = 0 AND crmid = cpsocialmessagelogid)
                WHERE cpsocialmessagelog_social_channel = 'Zalo' AND cpsocialmessagelog_status != 'queued' AND smcreatorid IN ('{$employeeIds}') AND scheduled_send_date BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
                GROUP BY smcreatorid";

            $result = $adb->pquery($sql, []);

            while ($row = $adb->fetchByAssoc($result)) {
                $data[$row['smcreatorid']]['zalo'] = $row['zalo_num'];
                $data['all']['zalo'] += $row['zalo_num'];

                if ($row['zalo'] > 0 && (empty($data[$row['smcreatorid']]['last_activity_time']) || strtotime($data[$row['smcreatorid']]['last_activity_time']) < strtotime($row['max_time']))) {
                    $data[$row['smcreatorid']]['last_activity_time'] = $row['max_time'];
                }
            }
        }

        // Get facebook
        if (!isForbiddenFeature('FacebookIntegration')) {
        }

        // Get Hana
        if (!isForbiddenFeature('HanaIntegration')) {
            $sql = "SELECT main_owner_id, COUNT(crmid) AS hana_num, MAX(DATE(createdtime)) AS max_time
                FROM vtiger_cpchatmessagelog
                INNER JOIN vtiger_crmentity ON (deleted = 0 and crmid = cpchatmessagelogid)
                WHERE cpchatmessagelog_channel = 'Hana' AND main_owner_id IN ('{$employeeIds}') AND createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
                GROUP BY main_owner_id";

            $result = $adb->pquery($sql, []);

            while ($row = $adb->fetchByAssoc($result)) {
                $data[$row['main_owner_id']]['hana'] = $row['hana_num'];
                $data['all']['hana'] += $row['hana_num'];

                if ($row['hana'] > 0 && (empty($data[$row['main_owner_id']]['last_activity_time']) || strtotime($data[$row['main_owner_id']]['last_activity_time']) < strtotime($row['max_time']))) {
                    $data[$row['main_owner_id']]['last_activity_time'] = $row['max_time'];
                }
            }
        }

        foreach ($data as $key => $row) {
            if ($key == 'all') break;

            if (!empty($row['last_activity_time'])) {
                $date = new DateTimeField($row['last_activity_time']);
                $data[$key]['last_activity_time'] = $date->getDisplayDate();
            }
        }

        return array_values($data);
    }

    function renderReportResult($filterSql, $showReportName = false, $print = false) {
        $params = $this->getFilterParams();

        $reportFilter = $this->renderReportFilter($params);
        $reportHeaders = $this->getReportHeaders();
        $reportData = $this->getReportData($params);

        $viewer = new Vtiger_Viewer();
        $viewer->assign('REPORT_FILTER', $reportFilter);
        $viewer->assign('REPORT_DATA', $reportData);
        $viewer->assign('REPORT_HEADERS', $reportHeaders);
        $viewer->assign('PARAMS', $params);
        $viewer->assign('REPORT_ID', $this->reportid);

        $viewer->display('modules/Reports/tpls/CustomerRelationshipResultReport/CustomerRelationshipResultReport.tpl');
    }

    function writeReportToExcelFile($tempFileName, $advanceFilterSql) {
        $request = new Vtiger_Request($_REQUEST, $_REQUEST);
        $filters = $request->get('advanced_filter');
        $params = [];

        foreach ($filters as $filter) {
            $params[$filter['name']] = $filter['value'];
        }

        $reportData = $this->getReportData($params, true);
        CustomReportUtils::writeReportToExcelFile($this, $reportData, $tempFileName, $advanceFilterSql);
    }
}