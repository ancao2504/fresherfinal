<?php
/**
 * @author Tin Bui
 * @email tin.bui@onlinecrm.vn
 * @create date 2022.03.16
 * @desc Ticket survey utils
 */

require_once 'include/Mailer.php';

class HelpDesk_SurveyUtils_Helper {

    const INVALID_INPUT = 1;
    const FORM_EXPIRED = 2;
    const FORM_ACTIVE = 3;
    const SURVEY_DONE = 4;

    public static function getSurveyFormUrl($ticketId, $surveyCreatedtime) {
        $options = [
            'onetime' => 0,
            'handler_path' => 'modules/HelpDesk/handlers/SurveyForm.php',
            'handler_class' => 'HelpDesk_SurveyForm_Handler',
            'handler_function' => 'process',
            'handler_data' => [
                'ticket_id' => $ticketId,
                'survey_createdtime' => $surveyCreatedtime
            ],
        ];

        return Vtiger_ShortURL_Helper::generateURL($options);
    }

    public static function getFormData($ticketId, $surveyCreatedtime) {
        global $ticketConfigs;

        if (empty($ticketId) || empty($surveyCreatedtime) || !isRecordExists($ticketId)) {
            return ['state' => self::INVALID_INPUT];
        }

        $ticketRecord = HelpDesk_Record_Model::getInstanceById($ticketId);
        $formData = [
            'rawData' => $ticketRecord->getData(),
            'summaryData' => self::getSurveySummaryTicketData($ticketRecord),
        ];

        if ((strtotime(date('Y-m-d H:i')) - $surveyCreatedtime) / 86400 > $ticketConfigs['survey_form_lifetime']) {
            $formData['state'] = self::FORM_EXPIRED;
            return $formData;
        }
        
        $surveyStatus = $ticketRecord->get('helpdesk_survey_status');

        // If customer haven't submited survey yet
        if ($surveyStatus == 'sent_mail') {
            $formData['state'] = self::FORM_ACTIVE;
        }
        else if ($surveyStatus == 'customer_did_survey') {
            $formData['state'] = self::SURVEY_DONE;
        }
        else {
            $formData['state'] = self::INVALID_INPUT;
        }

        return $formData;
    }

    public static function getSurveySummaryTicketData($ticketRecord, $lang = 'vn_vn') {
        $customLabel = [
            'ticket_title' => 'LBL_SURVEY_FORM_TICKET_TITLE',
            'createdtime' => 'LBL_SURVEY_FORM_TICKET_CREATEDTIME',
            'main_owner_id' => 'LBL_SURVEY_FORM_TICKET_ASIGNEE',
            'total_process_time' => 'LBL_SURVEY_FORM_TICKET_TOTAL_PROCESS_TIME',
            'helpdesk_rating' => 'LBL_SURVEY_FORM_TICKET_SATISFACTION_SCORE',
            'rating_description' => 'LBL_SURVEY_FORM_TICKET_RATING_DESCRIPTION',
        ];

        $fields = [
            'ticket_title',
            'createdtime',
            'main_owner_id',
            'total_process_time'
        ];

        $surveyStatus = $ticketRecord->get('helpdesk_survey_status');
        if ($surveyStatus == 'customer_did_survey') {
            $fields = array_merge($fields, [
                'helpdesk_rating',
                'rating_description'
            ]);
        }

        $data = [];

        foreach ($fields as $field) {
            $fieldModel = HelpDesk_GeneralUtils_Helper::getFieldModelFromName($field, $ticketRecord);
            $fieldLabel = vtranslate($fieldModel->get('fieldlabel'), 'HelpDesk');
            
            if (array_key_exists($field, $customLabel)) {
                $fieldLabel = Vtiger_Language_Handler::getTranslatedString($customLabel[$field], 'HelpDesk', $lang);
            }
            
            $fieldValue = $fieldModel->getDisplayValue($ticketRecord->get($field));

            $data[$field] = [
                'label' => $fieldLabel,
                'value' => $fieldValue
            ];
        }

        return $data;
    }

    public static function handleSurveyFormSubmition($ticketId) {
        global $site_URL;

        $request = new Vtiger_Request($_REQUEST, $_REQUEST);
        $shortUrlId = $request->get('id');
        $score = "rating_{$request->get('helpdesk_rating')}";
        $ratingDescription = $request->get('rating_description');

        try {
            if (!empty($ticketId) && isRecordExists($ticketId)) {
                $ticketRecord = HelpDesk_Record_Model::getInstanceById($ticketId);
                $ticketRecord->set('mode', 'edit');
                $ticketRecord->set('helpdesk_rating', $score);
                $ticketRecord->set('rating_description', $ratingDescription);
                $ticketRecord->set('helpdesk_survey_status', 'customer_did_survey');
                $ticketRecord->save();
            }   
        }
        catch (Exception $e) {
            
        }

        $returnLocation = $site_URL . "/shorturl.php?id={$shortUrlId}";
        header("Location: {$returnLocation}");
    }
    
    public static function sendSurveyEmail($ticketRecord, $immediately = false) {
        $templateId = getSystemEmailTemplateByName('[Ticket] Survey email');
        $receiverId = $ticketRecord->get('contact_id');

        // If email template or contactid not exist, cancel send email process
        if (empty($templateId) || empty($receiverId)) return false;

        $templateRecord = EmailTemplates_Record_Model::getInstanceById($templateId);
        $body = html_entity_decode($templateRecord->get('body'));
        $receiverName = html_entity_decode(Vtiger_Functions::getCRMRecordLabel($receiverId));

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
            'hotline' => $companyDetails->get('phone'),
            'tracker_url' => self::getSurveyFormUrl($ticketRecord->getId(), strtotime(date('Y-m-d H:i')))
        ];

        foreach ($variables as $key => $value) {
            $body = str_replace("%{$key}%", $value, $body);
        }

        $mailRes = HelpDesk_EmailUtils_Helper::sendReplyEmail($ticketRecord->getId(), $body, [], $immediately);

        return !empty($mailRes);
    }
}