<?php
require_once('vtlib/Vtiger/Mailer.php');

class Mailer {
    
    /*
    *   Function send()
    *   Author: Hieu Nguyen
    *   Date: 2018-08-24
    *   Functions: send email from email template to multiple receivers, supports for multiple cc receivers, bcc receivers and attachments
    *   Params structure:
    *       + $immediately = true / false. Email will be queued if $immediately = false. Queued emails will be sent by the scheduler
    *       + $mainReceivers = array(array('name' => 'Hieu Nguyen', 'email' => 'hieu.nguyen@onlinecrm.vn'), ...)
    *       + $variables = array('name' => 'Hieu Nguyen', 'email' => 'hieu.nguyen@onlinecrm.vn', 'phone' => '0984147940', ...)
    *       + $ccReceivers = array(array('name' => 'Hoc Bui', 'email' => 'hoc.bui@onlinecrm.vn'), ...)
    *       + $bccReceivers = array(array('name' => 'Hai Nguyen', 'email' => 'hai.nguyenduc@onlinecrm.vn'), ...)
    *       + $attachments = array(array('name' => 'Report.xls', 'path' => '/path/to/file/Report.xls'), ...)
    *       + $parentIds = array(1, 2, ...)
    */
    static function send($immediately = true, $mainReceivers = [], $templateId, $variables = [], $ccReceivers = [], $bccReceivers = [], $attachments = [], $parentIds = [], $sender = []) {
        try {
            // Get mail template
            $emailTemplateRecord = EmailTemplates_Record_Model::getInstanceById($templateId);
            if (!$emailTemplateRecord) return false;

            // Send email with subject and body from the given email template
            $subject = decodeUTF8($emailTemplateRecord->get('subject'));
            $body = decodeUTF8($emailTemplateRecord->get('body'));
            $result = self::sendEmail($immediately, $mainReceivers, $subject, $body, $variables, $ccReceivers, $bccReceivers, $attachments, $parentIds, $sender);

            return $result;
        }
        catch (Exception $ex) {
            saveLog('PLATFORM', '[Mailer::send] Sending email error:' . $ex->getMessage(), $ex->getTrace());
            return false;
        }
    }

    /*
    *   Function sendEmail()
    *   Author: Hieu Nguyen
    *   Date: 2020-11-17
    *   Functions: send specific email with subject, body to multiple receivers, supports for multiple cc receivers, bcc receivers and attachments
    *   Params structure:
    *       + $immediately = true / false. Email will be queued if $immediately = false. Queued emails will be sent by the scheduler
    *       + $mainReceivers = array(array('name' => 'Hieu Nguyen', 'email' => 'hieu.nguyen@onlinecrm.vn'), ...)
    *       + $variables = array('name' => 'Hieu Nguyen', 'email' => 'hieu.nguyen@onlinecrm.vn', 'phone' => '0984147940', ...)
    *       + $ccReceivers = array(array('name' => 'Hoc Bui', 'email' => 'hoc.bui@onlinecrm.vn'), ...)
    *       + $bccReceivers = array(array('name' => 'Hai Nguyen', 'email' => 'hai.nguyenduc@onlinecrm.vn'), ...)
    *       + $attachments = array(array('name' => 'Report.xls', 'path' => '/path/to/file/Report.xls'), ...)
    *       + $parentIds = array(1, 2, ...)
    */
    static function sendEmail($immediately = true, $mainReceivers = [], $subject, $body, $variables = [], $ccReceivers = [], $bccReceivers = [], $attachments = [], $parentIds = [], $sender = []) {
        // Prepare the mailer
        $mail = new Vtiger_Mailer();
		$mail->IsHTML(true);

        // Set sender info
        if (empty($sender)) {
		    $sender = getSystemEmailAddress();
        }

        $mail->ConfigSenderInfo($sender['email'], $sender['name']);

        // Set mail header
        //$mailer->AddReplyTo('', '');

        // Set main receivers
        if (!empty($mainReceivers)) {
            foreach ($mainReceivers as $receiver) {
                if (!empty($receiver['name']) && isEmailValid($receiver['email'])) {
                    $mail->AddAddress($receiver['email'], $receiver['name']);
                }
            }
        }

        // Set reference receivers
        if (!empty($ccReceivers)) {
            foreach ($ccReceivers as $receiver) {
                if (!empty($receiver['name']) && isEmailValid($receiver['email'])) {
                    $mail->AddCC($receiver['email'], $receiver['name']);
                }
            }
        }

        // Set blind reference receivers
        if (!empty($bccReceivers)) {
            foreach ($bccReceivers as $receiver) {
                if (!empty($receiver['name']) && isEmailValid($receiver['email'])) {
                    $mail->AddBCC($receiver['email'], $receiver['name']);
                }
            }
        }

        // Replace defined variables in the content
        if (!empty($variables)) {
            foreach ($variables as $key => $value) {
                $subject = str_replace("%{$key}%", trim(strip_tags($value)), $subject);
                $body = str_replace("%{$key}%", $value, $body);
            }
        }

        // Set mail content
        $mail->Subject = decodeUTF8($subject);
        $mail->Body = wordwrap(decodeUTF8($body));

        // Added by Phu Vo on 2020.05.26 to handle email log and tracking
        if (!empty($parentIds)) {
            $emailLog = self::saveEmailLog($subject, $body, $parentIds, $mainReceivers, $ccReceivers, $bccReceivers);
            $emailLogId = $emailLog->getId();
            $mail->Body .= Vtiger_Functions::getTrackImageContent($emailLogId, $parentIds[0]);
        }
        // End Phu Vo

        // Set attachments
        if (!empty($attachments)) {
            foreach ($attachments as $file) {
                $mail->AddAttachment($file['path'], $file['name']);
            }
        }

        // Send the email
        $success = $mail->Send($immediately);
        $result = ['success' => $success, 'email_log_id' => $emailLogId];

        return $result;
    }

    // Implemented by Phu Vo on 2020.05.26
    private static function saveEmailLog($subject, $body, array $parentIds, array $mainReceivers, array $ccReceivers = [], array $bccReceivers = []) {
        global $current_user;
        
        $toMailInfo = [];
        $to = '';
        $cc = '';
        $bcc = '';

        // Set main receivers
        if (!empty($mainReceivers)) {
            foreach ($mainReceivers as $index => $receiver) {
                if ($index > 0) $delimiter = ',';
                $to .= $delimiter . $receiver['email'];
                $toMailInfo[] = [$receiver['email']];
            }
        }

        // Set reference receivers
        if (!empty($ccReceivers)) {
            foreach ($ccReceivers as $index => $receiver) {
                if ($index > 0) $delimiter = ',';
                $cc .= $delimiter . $receiver['email'];
            }
        }

        // Set blind reference receivers
        if (!empty($bccReceivers)) {
            foreach ($bccReceivers as $index => $receiver) {
                if ($index > 0) $delimiter = ',';
                $bcc .= $delimiter . $receiver['email'];
            }
        }

        $parentIdsString = join('@1|', $parentIds) . '@1|';

        $emailRecordModel = Vtiger_Record_Model::getCleanInstance('Emails');
        $emailRecordModel->set('subject', $subject);
        $emailRecordModel->set('description', $body);
        $emailRecordModel->set('assigned_user_id', $current_user->id);
        $emailRecordModel->set('main_owner_id', $current_user->id);
        $emailRecordModel->set('email_flag', 'SENT');
        $emailRecordModel->set('toemailinfo', $toMailInfo);
        $emailRecordModel->set('saved_toid', $to);
        $emailRecordModel->set('ccmail', $cc);
        $emailRecordModel->set('bccmail', $bcc);
        $emailRecordModel->set('parent_id', $parentIdsString);
        $emailRecordModel->save();

        // Set email tracker counter
        $emailRecordModel->setAccessCountValue();

        return $emailRecordModel;
    }
}