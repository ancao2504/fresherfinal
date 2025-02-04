<?php
require_once('include/SMSer.php');
require_once('include/Mailer.php');

class Contacts_Service_Model {

    static function sendHappyBirthdayMsg() {
        global $adb, $happyBirthDayConfig;

        $log = LoggerManager::getLogger('PLATFORM');
        $log->info('[CRON] Started sendHappyBirthdayMsg');

    //     // TODO: add logic 
    //     $sql = "SELECT contactid, firstname, lastname, mobile, email
    //     FROM CRM_contactdetails
    //     INNER JOIN CRM_crmentity ON (crmid = contactid AND deleted = 0)
    //     INNER JOIN CRM_contactsubdetails ON (contactsubscriptionid = contactid)
    //     WHERE DATE_FORMAT(birthday, '%m-%d') = DATE_FORMAT(NOW(), '%m-%d')";
    //     $result = $adb->pquery($sql, []);
    //     while($row = $adb->fetchByAssoc($result)) {
    //         $fullName = getFullNameFromArray('Contacts', $row);
    //         // Send SMS
    //         $smsTemplateId = $happyBirthDayConfig['contacts_sms_template_id'];

    //         $variables = ['customer_name' => $fullName,];
    //         SMSer::send($row['mobile'], $smsTemplateId, $variables, $row['contactid'], 'Contacts');
    //         // Send Email
    //         $mainReceivers = [
    //         ['name' => $fullName, 'email' => $row['email']]
    //         ];
    //         $emailTemlateId = $happyBirthDayConfig['contacts_email_template_id'];
    //         $variables = [
    //         'customer_name' => $fullName,
    //         ];
    //         $result = Mailer::send(true, $mainReceivers, templateId: $emailTemlateId, $variables);
    // }

    $sql = "UPDATE vtiger_contactdetails 
    INNER JOIN vtiger_crmentity ON (crmid = contactid AND deleted = 0)
    INNER JOIN vtiger_contactsubdetails ON (contactsubscriptionid = contactid)
    SET firstname = 'Chúc mừng sinh nhật bạn'
    WHERE DATE_FORMAT(birthday, '%m-%d') = DATE_FORMAT(NOW(), '%m-%d')";

    $result = $adb->pquery($sql, []);

        $log->info('[CRON] Finished sendHappyBirthdayMsg');
    }
}
