<?php
/**
 * @author Tin Bui
 * @email tin.bui@onlinecrm.vn
 * @create date 2022.03.16
 * @desc Email utils for ticket
 */

require_once 'include/Mailer.php';

class HelpDesk_EmailUtils_Helper {

    static public function isSentFirstEmail($ticketId) {
        global $adb;
        
        $sqlCount = "SELECT COUNT(cpticketcommunicationlogid) 
                FROM vtiger_cpticketcommunicationlog 
                WHERE ticket_id = ? AND direction = 'outbound'";
        $isSentEmail = intval($adb->getOne($sqlCount, [$ticketId])) > 0 ? true : false;

        return $isSentEmail;
    }
    
    static public function getReplyTicketEmailSubject($ticketId, $ticketRecord = null) {
        if (empty($ticketId) || !isRecordExists($ticketId)) return false;
        
        $ticketRecord = HelpDesk_Record_Model::getInstanceById($ticketId);
        $subject = "[Ticket ID: {$ticketRecord->get('ticket_no')}] {$ticketRecord->get('ticket_title')}";
        
        if (self::isSentFirstEmail($ticketId)) {
            $subject = 'Re: ' . $subject;
        }

        return $subject;
    }
    
    static function sendReplyEmail($ticketId, $emailContent, $ccEmails = [], $immediately = true) {
        if (empty($ticketId) || !isRecordExists($ticketId)) return false;

        global $adb;
        $ticketRecord = HelpDesk_Record_Model::getInstanceById($ticketId);

        // Prepare emails
        $email = $ticketRecord->get('contact_email');
        $receiverId = $ticketRecord->get('contact_id');
        if (empty($receiverId) || empty($email)) return false;

        $receiverName = html_entity_decode(Vtiger_Functions::getCRMRecordLabel($receiverId));
        $receivers = [
            ['name' => $receiverName, 'email' => $email]
        ];

        $currentCCEmails = array_filter(explode(' |##| ', strval($ticketRecord->get('helpdesk_related_emails'))));
        $diffEmails = array_diff($ccEmails, $currentCCEmails); // Find emails in new that not in current
        
        if (is_array($diffEmails) && count($diffEmails) > 0) {
            $ccEmails = array_merge($currentCCEmails, $diffEmails); // Merge diff emails to current cc emails
            $sql = "UPDATE vtiger_troubletickets SET helpdesk_related_emails = ? WHERE ticketid = ?";
            $adb->pquery($sql, [implode(' |##| ', $ccEmails), $ticketId]);
        }

        $ccEmails = array_map(function ($email) {
            return ['name' => '_', 'email' => $email];
        }, $ccEmails);
        
        // Prepare email title and body
        $subject = self::getReplyTicketEmailSubject($ticketId, $ticketRecord);

        // If user select send default template / ticket created manual by mail scanner
        if (empty($emailContent)) {
            $templateId = getSystemEmailTemplateByName('[Ticket] Default received ticket email');
            
            // If email template not exist, cancel send email process
            if (empty($templateId)) return false;

            $templateRecord = EmailTemplates_Record_Model::getInstanceById($templateId);
            $emailContent = html_entity_decode($templateRecord->get('body'));

            global $HELPDESK_SUPPORT_EMAIL_ID, $site_URL;
            $companyDetails = Vtiger_CompanyDetails_Model::getInstanceById();
            $companyLogo = $companyDetails->getLogo();
            $companyLogoPath = $site_URL . '/' . $companyLogo->get('imagepath');
            $contactRecord = Contacts_Record_Model::getInstanceById($receiverId);

            $variables = [
                'customer_salutation' => vtranslate($contactRecord->get('salutationtype'), 'Vtiger'),
                'customer_name' => $receiverName,
                'ticket_no' => $ticketRecord->get('ticket_no'),
                'company_name' => $companyDetails->get('organizationname'),
                'company_logo' => $companyLogoPath,
                'company_address' => $companyDetails->get('address'),
                'support_email' => $HELPDESK_SUPPORT_EMAIL_ID,
                'hotline' => $companyDetails->get('phone')
            ];

            // Replace first to save log
            foreach ($variables as $key => $value) {
                $subject = str_replace("%{$key}%", trim(strip_tags($value)), $subject);
                $emailContent = str_replace("%{$key}%", $value, $emailContent);
            }
        }

        // Handle save reply log and link attachments
        $result = Vtiger_Util_Helper::transformUploadedFiles($_FILES, true);
		$_FILES = $result['emailAttachments'];
        $logRecord = self::createReplyLog($ticketId, $receiverId, '', $subject, $emailContent, 'outbound', 'email', 'CRM');
        $_FILES = []; // Empty files before save email record
        
        if (!empty($logRecord)) {
            $attachments = $logRecord->getAttachmentDetails();
            $attachments = array_filter(array_map(function($attachment) {
                $attachment = decodeUTF8($attachment); // Tin Bui on 2022.06.20 - Decode name file
                
                return [
                    'name' => $attachment['attachment'],
                    'path' => $attachment['path'] . $attachment['fileid'] . '_' . $attachment['attachment'],
                ];
            }, $attachments));
            $mailRes = Mailer::sendEmail($immediately, $receivers, $subject, $emailContent, [], $ccEmails, [], $attachments, [$ticketId], []);
        }

        return !empty($mailRes['success']);
    }

    static function sendAssignmentEmail($ticketId) {
        if (empty($ticketId) || !isRecordExists($ticketId)) return;

        $templateId = getSystemEmailTemplateByName('[Ticket] Assignment email');
        $ticketRecord = HelpDesk_Record_Model::getInstanceById($ticketId);
        $receiverId = $ticketRecord->get('main_owner_id');
        $receiverEmail = getUserEmail($receiverId);
        $receiverName = decodeUTF8(getUserFullName($receiverId));

        // If email template or contactid not exist, cancel send email process
        if (empty($templateId) || empty($receiverId) || empty($receiverEmail)) return false;
        
        $receivers = [
            ['name' => $receiverName, 'email' => $receiverEmail]
        ];

        global $HELPDESK_SUPPORT_EMAIL_ID, $site_URL;
        $companyDetails = Vtiger_CompanyDetails_Model::getInstanceById();
        $companyLogo = $companyDetails->getLogo();
        $companyLogoPath = $site_URL . '/' . $companyLogo->get('imagepath');

        $variables = [
            'assignee_name' => $receiverName,
            'ticket_no' => $ticketRecord->get('ticket_no'),
            'ticket_title' => $ticketRecord->get('ticket_title'),
            'ticket_status' => vtranslate($ticketRecord->get('ticketstatus'), 'HelpDesk'),
            'ticket_prority' => vtranslate($ticketRecord->get('ticketpriorities'), 'HelpDesk'),
            'ticket_description' => nl2br($ticketRecord->get('description')),
            'contact_name' => html_entity_decode(Vtiger_Functions::getCRMRecordLabel($ticketRecord->get('contact_id'))),
            'account_name' => html_entity_decode(Vtiger_Functions::getCRMRecordLabel($ticketRecord->get('parent_id'))),
            'ticket_detail_url' => $site_URL . '/' . $ticketRecord->getDetailViewUrl(),
            'company_name' => $companyDetails->get('organizationname'),
            'company_logo' => $companyLogoPath,
            'company_address' => $companyDetails->get('address'),
            'support_email' => $HELPDESK_SUPPORT_EMAIL_ID,
            'hotline' => $companyDetails->get('phone')
        ];

        $mailRes = Mailer::send(false, $receivers, $templateId, $variables, [], [], [], [$ticketId], []);

        return !empty($mailRes);
    }

    static function sendProcessedTicketEmail($ticketId) {
        $ticketRecord = HelpDesk_Record_Model::getInstanceById($ticketId);
        $templateId = getSystemEmailTemplateByName('[Ticket] Ticket processed email');
        $receiverId = $ticketRecord->get('contact_id');

        // If email template or contactid not exist, cancel send email process
        if (empty($templateId) || empty($receiverId)) return false;

        $templateRecord = EmailTemplates_Record_Model::getInstanceById($templateId);
        $body = html_entity_decode($templateRecord->get('body'));
        $receiverName = html_entity_decode(Vtiger_Functions::getCRMRecordLabel($receiverId));

        global $HELPDESK_SUPPORT_EMAIL_ID, $site_URL, $ticketConfigs;
        $companyDetails = Vtiger_CompanyDetails_Model::getInstanceById();
        $companyLogo = $companyDetails->getLogo();
        $companyLogoPath = $site_URL . '/' . $companyLogo->get('imagepath');
        $contactRecord = Contacts_Record_Model::getInstanceById($receiverId);

        $variables = [
            'customer_salutation' => vtranslate($contactRecord->get('salutationtype'), 'Vtiger'),
            'customer_name' => $receiverName,
            'ticket_no' => $ticketRecord->get('ticket_no'),
            'company_name' => $companyDetails->get('organizationname'),
            'company_logo' => $companyLogoPath,
            'company_address' => $companyDetails->get('address'),
            'support_email' => $HELPDESK_SUPPORT_EMAIL_ID,
            'hotline' => $companyDetails->get('phone'),
            'auto_close_time' => $ticketConfigs['auto_close_hours'] . ' ' . vtranslate('LBL_HOUR'),
        ];

        foreach ($variables as $key => $value) {
            $body = str_replace("%{$key}%", $value, $body);
        }

        $mailRes = HelpDesk_EmailUtils_Helper::sendReplyEmail($ticketRecord->getId(), $body, [], false);

        return !empty($mailRes);
    }

    static function createReplyLog($ticketId, $customerId, $customerEmail, $title, $body, $direction, $type, $ownerType) {
        $record = Vtiger_Record_Model::getCleanInstance('CPTicketCommunicationLog');
        $record->set('name', $title);
        $record->set('customer_id', $customerId);
        $record->set('owner_type', $ownerType);
        $record->set('ticket_id', $ticketId);
        $record->set('description', self::cleanHtml($body));
        $record->set('source', $type);
        $record->set('direction', $direction);
        $record->set('customer_email', $customerEmail);
        $record->save();

        return $record;
    }

    // START - Email body clean utils
    public static function convertToSafeHtml($text) {
        $text = decodeUTF8($text);
        $permantlyDeleteTags = ['script', 'style', 'img', 'link', 'head'];
        $convertToDivTags = ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
        $removeAttrs  = ['class', 'style', 'href'];

        foreach ($permantlyDeleteTags  as $tag) {
            if ($tag == 'img') {
                $regEx = '#<' . $tag . '.*?>(.*?)#is';
            }
            else {
                $regEx = '#<' . $tag . '.*?>(.*?)</' . $tag . '>#is';
            }
            $text = preg_replace($regEx, '', $text);
        }

        foreach ($removeAttrs as $attr) {
            $text = preg_replace('/(<[^>]+) ' . $attr . '=".*?"/i', '$1', $text);
            $text = preg_replace("/(<[^>]+) " . $attr . "='.*?'/i", '$1', $text);
            $text = preg_replace('/(<[^>]+) ' . $attr . '=.*?/i', '$1', $text);
        }

        // Perform remove tags
		foreach ($convertToDivTags as $tag) {
            $regEx = '#<' . $tag . '.*?>(.*?)</' . $tag . '>#is';
            $text = preg_replace($regEx, '<div>$1</div>', $text);
		}

        return $text;
    }

    static function replaceTags($htmlStr, $tags) {
        $htmlStr = mb_convert_encoding($htmlStr, 'HTML-ENTITIES', 'UTF-8');
		$htmlStr = '<div>' . $htmlStr . '</div>'; // Workaround to get the HTML back from DOMDocument without the <html><head> and <body> tags
		$dom = new DOMDocument('1.0', 'utf-8');
		$dom->loadHTML($htmlStr);
		$htmlStr = substr($dom->saveHTML($dom->getElementsByTagName('div')->item(0)), 5, -6);
		
        // Use simple string replace to replace tags
		foreach ($tags as $search => $replace) {
			$htmlStr = str_replace('<' . $search . '>', '<' . $replace . '>', $htmlStr);
			$htmlStr = str_replace('<' . $search . ' ', '<' . $replace . ' ', $htmlStr);
			$htmlStr = str_replace('</' . $search . '>', '</' . $replace . '>', $htmlStr);
		}
		
        return $htmlStr;
	}

	static function stripTags($htmlStr, $ignoreTags) {
		$htmlStr = mb_convert_encoding($htmlStr, 'HTML-ENTITIES', 'UTF-8');
		$htmlStr = '<div>' . $htmlStr . '</div>'; // Workaround to get the HTML back from DOMDocument without the <html><head> and <body> tags
		$dom = new DOMDocument('1.0', 'utf-8');
		$dom->loadHTML($htmlStr);

        $nodes = $dom->getElementsByTagName("*");
        $tagsInDom = [];
        
        foreach ($nodes as $node) {
            $tagsInDom[$node->tagName] = $node->tagName;
            $success = true;

            while ($node->attributes->length && $success) {
                try {
                    $success = $node->removeAttribute($node->attributes->item(0)->name);
                }
                catch (Exception $e) {
                    $success = false;
                }
            }
        }

		$htmlStr = substr($dom->saveHTML($dom->getElementsByTagName('div')->item(0)), 5, -6);

		foreach ($tagsInDom as $tag) {
            if (in_array($tag, $ignoreTags)) continue;

			$htmlStr = str_replace('<' . $tag . '>', '', $htmlStr);
			$htmlStr = str_replace('</' . $tag . '>', '', $htmlStr);
		}

		return $htmlStr;
	}

    static function deleteTags($htmlStr, $permantlyDeleteTags) {
        $htmlStr = mb_convert_encoding($htmlStr, 'HTML-ENTITIES', 'UTF-8');
		$htmlStr = '<div>' . $htmlStr . '</div>'; // Workaround to get the HTML back from DOMDocument without the <html><head> and <body> tags
		$dom = new DOMDocument('1.0', 'utf-8');
		$dom->loadHTML($htmlStr);

        foreach ($permantlyDeleteTags as $delete) {
			$nodes = $dom->getElementsByTagName($delete);
			
            foreach ($nodes as $node) {
				$node->parentNode->removeChild($node);
			}
		}

        $htmlStr = substr($dom->saveHTML($dom->getElementsByTagName('div')->item(0)), 5, -6);

        return $htmlStr;
    }

    static function cleanHtml($htmlStr) {
        $htmlStr = decodeUTF8($htmlStr);

        // 1. Replace tags
        // Convert to p block tag to avoid missing close tag
        $htmlStr = self::replaceTags($htmlStr, [
			'i' => 'em',
			'b' => 'strong',
            'div' => 'p', 
            'h1' => 'h6', 
            'h2' => 'h6', 
            'h3' => 'h6', 
            'h4' => 'h6', 
            'h5' => 'h6',
            'blockquote' => 'p',
            'pre' => 'p'
		]);
        
        // 2. Delete risk tags
        $htmlStr = self::deleteTags($htmlStr, ['script', 'style', 'img', 'link', 'head']);
    
        // 3. Strip tags
        $htmlStr = self::stripTags($htmlStr, ['br', 'ul', 'li', 'p', 'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td']);

        // skip repeat br tags
        $htmlStr = preg_replace("/(<br\s*\/>\s*)+/", '<br>', $htmlStr); // case <br />, <br/>
        $htmlStr = preg_replace("/(<br\s*\>\s*)+/", '<br>', $htmlStr); // case <br >, <br>

        // remove empty tags
        while (preg_match("/<[^\/>]*>([\s]?)*<\/[^>]*>/", $htmlStr)) {
            $htmlStr = preg_replace("/<[^\/>]*>([\s]?)*<\/[^>]*>/", '', $htmlStr);
        }

        return $htmlStr;
    }
}