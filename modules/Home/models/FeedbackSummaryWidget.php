<?php

/**
 * Name: FeedbackSummaryWidget.php
 * Author: Phu Vo
 * Date: 2020.08.26
 */

class Home_FeedbackSummaryWidget_Model extends Home_BaseSummaryCustomDashboard_Model {

    public $lastPeriod = true;

    function getDefaultParams() {
        $defaultParams = [
            'period' => 'month',
        ];

        return $defaultParams;
    }

    public function getWidgetHeaders($params) {
        $widgetHeaders = [
            [
                'name' => 'call',
                'label' => vtranslate('LBL_DASHBOARD_CALL', 'Home'),
            ],
            [
                'name' => 'meeting',
                'label' => vtranslate('LBL_DASHBOARD_MEETING', 'Home'),
            ],
            [
                'name' => 'task',
                'label' => vtranslate('LBL_DASHBOARD_TASK', 'Home'),
            ],
            [
                'name' => 'sms',
                'label' => vtranslate('LBL_DASHBOARD_SMS', 'Home'),
            ],
            [
                'name' => 'email',
                'label' => vtranslate('LBL_DASHBOARD_EMAIL', 'Home'),
            ],
            [
                'name' => 'comment',
                'label' => vtranslate('LBL_DASHBOARD_COMMENT', 'Home'),
            ],
            [
                'name' => 'zalo',
                'label' => vtranslate('LBL_DASHBOARD_FREE_ZALO', 'Home'),
            ],
            [
                'name' => 'zalo_ott',
                'label' => vtranslate('LBL_DASHBOARD_ZALO_OTT', 'Home'),
            ],
        ];

        return $widgetHeaders;
    }

    public function getWidgetData($params) {
        global $adb;

        $data = [];
        $periodFilterInfo = Reports_CustomReport_Helper::getPeriodFromFilter($params);
        $subDay = $this->periodToAddUnitMapping($params['period']);
        $data['sms'] = [];
        $data['email'] = [];
        $data['zalo_messenger'] = [];
        $data['comment'] = [];

        // Call
        $thisPeriodSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_activity
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_activity.activityid AND vtiger_crmentity.setype = 'Calendar' AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE('{$periodFilterInfo['from_date']}')
                AND DATE(vtiger_crmentity.createdtime) <=DATE('{$periodFilterInfo['to_date']}')
                AND vtiger_activity.activitytype = 'Call'";

        $lastPeriodSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_activity
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_activity.activityid AND vtiger_crmentity.setype = 'Calendar' AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE_SUB(DATE('{$periodFilterInfo['from_date']}'), INTERVAL 1 {$subDay})
                AND DATE(vtiger_crmentity.createdtime) <= DATE_SUB(DATE('{$periodFilterInfo['to_date']}'), INTERVAL 1 {$subDay})
                AND vtiger_activity.activitytype = 'Call'";

        // Calculate data
        $data['call']['value'] = $adb->getOne($thisPeriodSql);
        $data['call']['last_period'] = $adb->getOne($lastPeriodSql);
        $data['call']['change'] = $this->getPeriodChange($data['call']['value'], $data['call']['last_period']);
        $data['call']['direction'] = $this->resolveDirection($data['call']['value'], $data['call']['last_period']);

        // Meeting
        $thisPeriodSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_activity
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_activity.activityid AND vtiger_crmentity.setype = 'Calendar' AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE('{$periodFilterInfo['from_date']}')
                AND DATE(vtiger_crmentity.createdtime) <=DATE('{$periodFilterInfo['to_date']}')
                AND vtiger_activity.activitytype = 'Meeting'";

        $lastPeriodSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_activity
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_activity.activityid AND vtiger_crmentity.setype = 'Calendar' AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE_SUB(DATE('{$periodFilterInfo['from_date']}'), INTERVAL 1 {$subDay})
                AND DATE(vtiger_crmentity.createdtime) <= DATE_SUB(DATE('{$periodFilterInfo['to_date']}'), INTERVAL 1 {$subDay})
                AND vtiger_activity.activitytype = 'Meeting'";

        // Calculate data
        $data['meeting']['value'] = $adb->getOne($thisPeriodSql);
        $data['meeting']['last_period'] = $adb->getOne($lastPeriodSql);
        $data['meeting']['change'] = $this->getPeriodChange($data['meeting']['value'], $data['meeting']['last_period']);
        $data['meeting']['direction'] = $this->resolveDirection($data['meeting']['value'], $data['meeting']['last_period']);

        // Task
        $thisPeriodSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_activity
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_activity.activityid AND vtiger_crmentity.setype = 'Calendar' AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE('{$periodFilterInfo['from_date']}')
                AND DATE(vtiger_crmentity.createdtime) <=DATE('{$periodFilterInfo['to_date']}')
                AND vtiger_activity.activitytype = 'Task'";

        $lastPeriodSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_activity
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid ON vtiger_activity.activityid AND vtiger_crmentity.setype = 'Calendar' AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE_SUB(DATE('{$periodFilterInfo['from_date']}'), INTERVAL 1 {$subDay})
                AND DATE(vtiger_crmentity.createdtime) <= DATE_SUB(DATE('{$periodFilterInfo['to_date']}'), INTERVAL 1 {$subDay})
                AND vtiger_activity.activitytype = 'Task'";

        // Calculate data
        $data['task']['value'] = $adb->getOne($thisPeriodSql);
        $data['task']['last_period'] = $adb->getOne($lastPeriodSql);
        $data['task']['change'] = $this->getPeriodChange($data['task']['value'], $data['task']['last_period']);
        $data['task']['direction'] = $this->resolveDirection($data['task']['value'], $data['task']['last_period']);

        // SMS
        $thisPeriodSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_cpsmsottmessagelog
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_cpsmsottmessagelog.cpsmsottmessagelogid AND vtiger_crmentity.setype = 'CPSMSOTTMessageLog' AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE('{$periodFilterInfo['from_date']}')
                AND DATE(vtiger_crmentity.createdtime) <=DATE('{$periodFilterInfo['to_date']}')
                AND vtiger_cpsmsottmessagelog.sms_ott_message_type = 'SMS'";

        $lastPeriodSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_cpsmsottmessagelog
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_cpsmsottmessagelog.cpsmsottmessagelogid AND vtiger_crmentity.setype = 'CPSMSOTTMessageLog' AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE_SUB(DATE('{$periodFilterInfo['from_date']}'), INTERVAL 1 {$subDay})
                AND DATE(vtiger_crmentity.createdtime) <= DATE_SUB(DATE('{$periodFilterInfo['to_date']}'), INTERVAL 1 {$subDay})
                AND vtiger_cpsmsottmessagelog.sms_ott_message_type = 'SMS'";

        // Calculate data
        $data['sms']['value'] = $adb->getOne($thisPeriodSql);
        $data['sms']['last_period'] = $adb->getOne($lastPeriodSql);
        $data['sms']['change'] = $this->getPeriodChange($data['sms']['value'], $data['sms']['last_period']);
        $data['sms']['direction'] = $this->resolveDirection($data['sms']['value'], $data['sms']['last_period']);

        // Email
        $thisPeriodSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_activity
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_activity.activityid AND vtiger_crmentity.setype = 'Emails' AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE('{$periodFilterInfo['from_date']}')
                AND DATE(vtiger_crmentity.createdtime) <=DATE('{$periodFilterInfo['to_date']}')";

        $lastPeriodSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_activity
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_activity.activityid AND vtiger_crmentity.setype = 'Emails' AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE_SUB(DATE('{$periodFilterInfo['from_date']}'), INTERVAL 1 {$subDay})
                AND DATE(vtiger_crmentity.createdtime) <= DATE_SUB(DATE('{$periodFilterInfo['to_date']}'), INTERVAL 1 {$subDay})";

        // Calculate data
        $data['email']['value'] = $adb->getOne($thisPeriodSql);
        $data['email']['last_period'] = $adb->getOne($lastPeriodSql);
        $data['email']['change'] = $this->getPeriodChange($data['email']['value'], $data['email']['last_period']);
        $data['email']['direction'] = $this->resolveDirection($data['email']['value'], $data['email']['last_period']);
        
        // Comment
        $thisPeriodSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_modcomments
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_modcomments.modcommentsid AND vtiger_crmentity.setype = 'ModComments' AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE('{$periodFilterInfo['from_date']}')
                AND DATE(vtiger_crmentity.createdtime) <=DATE('{$periodFilterInfo['to_date']}')";

        $lastPeriodSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_modcomments
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_modcomments.modcommentsid AND vtiger_crmentity.setype = 'ModComments' AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE_SUB(DATE('{$periodFilterInfo['from_date']}'), INTERVAL 1 {$subDay})
                AND DATE(vtiger_crmentity.createdtime) <= DATE_SUB(DATE('{$periodFilterInfo['to_date']}'), INTERVAL 1 {$subDay})";

        // Calculate data
        $data['comment']['value'] = $adb->getOne($thisPeriodSql);
        $data['comment']['last_period'] = $adb->getOne($lastPeriodSql);
        $data['comment']['change'] = $this->getPeriodChange($data['comment']['value'], $data['comment']['last_period']);
        $data['comment']['direction'] = $this->resolveDirection($data['comment']['value'], $data['comment']['last_period']);

        // Zalo
        $thisPeriodSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_cpsocialmessagelog
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_cpsocialmessagelog.cpsocialmessagelogid AND vtiger_crmentity.setype = 'CPSocialMessageLog' AND vtiger_crmentity.deleted = 0)
            WHERE 
                DATE(vtiger_crmentity.createdtime) >= DATE('{$periodFilterInfo['from_date']}')
                AND DATE(vtiger_crmentity.createdtime) <=DATE('{$periodFilterInfo['to_date']}')
                AND vtiger_cpsocialmessagelog.cpsocialmessagelog_social_channel = 'Zalo'";

        $lastPeriodSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_cpsocialmessagelog
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_cpsocialmessagelog.cpsocialmessagelogid AND vtiger_crmentity.setype = 'CPSocialMessageLog' AND vtiger_crmentity.deleted = 0)
            WHERE 
                DATE(vtiger_crmentity.createdtime) >= DATE_SUB(DATE('{$periodFilterInfo['from_date']}'), INTERVAL 1 {$subDay})
                AND DATE(vtiger_crmentity.createdtime) <= DATE_SUB(DATE('{$periodFilterInfo['to_date']}'), INTERVAL 1 {$subDay})
                AND vtiger_cpsocialmessagelog.cpsocialmessagelog_social_channel = 'Zalo'";

        // Calculate data
        $data['zalo']['value'] = $adb->getOne($thisPeriodSql);
        $data['zalo']['last_period'] = $adb->getOne($lastPeriodSql);
        $data['zalo']['change'] = $this->getPeriodChange($data['zalo']['value'], $data['zalo']['last_period']);
        $data['zalo']['direction'] = $this->resolveDirection($data['zalo']['value'], $data['zalo']['last_period']);

        // Zalo OTT
        $thisPeriodSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_cpsmsottmessagelog
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_cpsmsottmessagelog.cpsmsottmessagelogid AND vtiger_crmentity.setype = 'CPSMSOTTMessageLog' AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE('{$periodFilterInfo['from_date']}')
                AND DATE(vtiger_crmentity.createdtime) <=DATE('{$periodFilterInfo['to_date']}')
                AND vtiger_cpsmsottmessagelog.sms_ott_message_type = 'Zalo'";

        $lastPeriodSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_cpsmsottmessagelog
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_cpsmsottmessagelog.cpsmsottmessagelogid AND vtiger_crmentity.setype = 'CPSMSOTTMessageLog' AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE_SUB(DATE('{$periodFilterInfo['from_date']}'), INTERVAL 1 {$subDay})
                AND DATE(vtiger_crmentity.createdtime) <= DATE_SUB(DATE('{$periodFilterInfo['to_date']}'), INTERVAL 1 {$subDay})
                AND vtiger_cpsmsottmessagelog.sms_ott_message_type = 'Zalo'";

        // Calculate data
        $data['zalo_ott']['value'] = $adb->getOne($thisPeriodSql);
        $data['zalo_ott']['last_period'] = $adb->getOne($lastPeriodSql);
        $data['zalo_ott']['change'] = $this->getPeriodChange($data['zalo_ott']['value'], $data['zalo_ott']['last_period']);
        $data['zalo_ott']['direction'] = $this->resolveDirection($data['zalo_ott']['value'], $data['zalo_ott']['last_period']);

        return $data;
    }
}