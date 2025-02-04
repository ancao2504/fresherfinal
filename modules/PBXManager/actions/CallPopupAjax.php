<?php

require_once('modules/PBXManager/BaseConnector.php');

/**
 * PBXManager_CallPopupAjax_Action
 * @package CallPopup
 * @author Phu Vo (2020.01.06)
 */

class PBXManager_CallPopupAjax_Action extends Vtiger_Action_Controller {

    function __construct() {
        $this->exposeMethod('saveCallLog');
        $this->exposeMethod('loadSelect2AjaxList');
        $this->exposeMethod('searchFaqByKeyword');
        $this->exposeMethod('searchCustomer');
        $this->exposeMethod('saveCustomer');
        $this->exposeMethod('sendPopupCustomerInfo');
        $this->exposeMethod('sendFaqEmail');
        $this->exposeMethod('getInitCallPopupData');
        $this->exposeMethod('getCustomerInfo');
        $this->exposeMethod('savePotentialForLeads');
        $this->exposeMethod('writeOutboundCache');  // Added by Hieu Nguyen on 2020-05-04 to help WebPhone outbound call to recognize the right customer
        $this->exposeMethod('transferCall');        // Added by Hieu Nguyen on 2020-05-08 to support transfer a call to another agent
        $this->exposeMethod('getTransferableList');
        $this->exposeMethod('getWebPhoneToken');    // Added by Hieu Nguyen on 2020-07-13 to get new webphone token
    }

    function checkPermission(Vtiger_Request $request) {
        return;
    }

    public function process(Vtiger_Request $request) {
        $mode = $request->getMode();

        if(!empty($mode) && $this->isMethodExposed($mode)) {
            return $this->invokeExposedMethod($mode, $request);
        }
    }

    public function saveCallLog(Vtiger_Request $request) {
        global $current_user;
        $data = $request->getAll();
        
        // Update customer information
        if ($data['events_call_result'] == 'call_result_customer_interested') {
            PBXManager_CallPopup_Model::updateCustomer($data);
        }
        else if ($data['customer_type'] == 'Accounts' && !empty($data['contact_id'])) {
            PBXManager_CallPopup_Model::updateContactAccountRelationship($data['contact_id'], $data['account_id']);
        }

        // Handle Save call log
        $callLog = PBXManager_CallPopup_Model::saveCallLog($data);

        // [START] Handle call result book call back later result
        if ($data['events_call_result'] == 'call_result_call_back_later') {
            PBXManager_CallPopup_Model::saveCallBackRecord($data);
        }
        // [END] Handle call result book call back later result

        // [START] Handle call result customer interested result
        if ($data['events_call_result'] == 'call_result_customer_interested') {
            // Update relationship with product, service
            PBXManager_CallPopup_Model::updateProductServiceCustomerRelation($data);
        }
        // [END] Handle call result customer interested result

		// Added By Vu Mai on 2022-11-08 to update customer info in telesales campaign according to call result
		$currentStatus = '';
		$updatedStatus = '';

		if (!empty($data['campaign_id'])) {
			// Get current status
			$currentStatus = CPTelesales_Telesales_Model::getCustomerStatusInCampaign($data['campaign_id'], $data['customer_id']);

			// Update customer info and get updated status
			$updatedStatus = PBXManager_CallPopup_Model::updateCustomerInfoInTelesalesCampaign($data);
		}
		// End Vu Mai

        // Send customer info to all clients. Modified By Vu Mai on 2022-11-09 to add event, current and updated customer status to msg
        $msg = array(
            'state' => 'COMPLETED',
            'call_id' => $data['pbx_call_id'],
            'receiver_id' => $current_user->id,
            'event' => 'AFTER_SAVE_CALL_LOG',
			'current_status' => $currentStatus,
			'updated_status' => $updatedStatus,
        );
		// End Vu Mai

        PBXManager_Base_Connector::forwardToCallCenterBridge($msg);

        // Result
        $result = $callLog->getData();

        // Respond
        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }

    public function loadSelect2AjaxList(Vtiger_Request $request) {
        $module = $request->get('targetModule');
        $keyword = $request->get('keyword');

        $result = PBXManager_CallPopup_Model::getAjaxSelect2Options($module, $keyword);

        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }

    public function searchFaqByKeyword(Vtiger_Request $request) {
        $data = $request->getAll();
        $result = PBXManager_CallPopup_Model::getFaqs($data);

        $this->_returnResponse($result);
    }

    public function searchCustomer(Vtiger_Request $request) {
        $data = $request->getAll();

        // Trim all field in request
        foreach ($data as $key => $value) {
            $data[$key] = trim($value);
        }

        $result = PBXManager_CallPopup_Model::getCustomers($data);

        $this->_returnResponse($result);
    }

    public function saveCustomer(Vtiger_Request $request) {
        $result = PBXManager_CallPopup_Model::saveCustomer($request->getAll());

        // Respond
        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }

    public function sendPopupCustomerInfo(Vtiger_Request $request) {
        global $current_user;
        $data = $request->getAll();

        // Send customer info to all clients
        $msg = [
            'call_id' => $data['pbx_call_id'],
            'receiver_id' => $current_user->id,
            'customer_number' => $data['customer_number'],
            'customer_id' => $data['customer_id'],
            'customer_name' => $data['customer_name'],
            'customer_type' => $data['customer_type'],
            'customer_avatar' => '', // No data for now
            'assigned_user_id' => $data['assigned_user_id'],
            'assigned_user_name' => $data['assigned_user_name'],
            'assigned_user_ext' => $data['assigned_user_ext'],
        ];

        PBXManager_Base_Connector::forwardToCallCenterBridge($msg);

        $response = new Vtiger_Response();
        $response->setResult(true);
        $response->emit();
    }

    public function sendFaqEmail(Vtiger_Request $request) {
        require_once('include/Mailer.php');
        global $current_user;

        // Retrive, init important datas and validate action
        $response = new Vtiger_Response();
        $emailTemplateId = getSystemEmailTemplateByName('Faq Email');

        if (empty($emailTemplateId)) {
            $message = vtranslate('LBL_CALL_POPUP_FAQ_EMAIL_TEMPLATE_NOT_FOUND', 'PBXManager');
            $response->setError(400, $message);
        }

        $data = $request->getAll();
        $receiveEmails = explode(',', $data['emails']);
        $ccEmails = explode(',', $data['ccs']);

        // preprocess
        foreach ($receiveEmails as $index => $email) {
            $receiveEmails[$index] = [
                'email' => trim($email),
                'name' => '-',
            ];
        }

        foreach ($ccEmails as $index => $email) {
            $ccEmails[$index] = [
                'email' => trim($email),
                'name' => '-',
            ];
        }

        // Fetch data
        $faqRecord = Vtiger_Record_Model::getInstanceById($data['record'], 'Faq');

        // Process email info
        $variables = [
            'user_name' => getUserFullName($current_user->id),
            'question' => $faqRecord->get('question'),
            'answer' => $faqRecord->get('faq_answer'),
        ];

        // Decode UTF8 before perform send email
        $variables = decodeUTF8($variables);

        $result = Mailer::send(true, $receiveEmails, $emailTemplateId, $variables, $ccEmails);

        $response->setResult($result);
        $response->emit();
    }

    public function getCallInfo(Vtiger_Request $request) {
        $data = $request->getAll();

        if (empty($data['call_log_id'])) return;

        try {
            $callLog = Vtiger_Record_Model::getInstanceById($data['call_log_id'], 'Events');
            $callLogData = $callLog->getData();
        }
        catch (Exception $e) {
            $callLogData = [];
        }

        $response = new Vtiger_Response();
        $response->setResult($callLogData);
        $response->emit();
    }

    public function getCustomerInfo(Vtiger_Request $request) {
        $data = $request->getAll();
        $customerId = $data['customer_id'];
        $customerType = $data['customer_type'];
        $response = new Vtiger_Response();

        // Just process with Contacts and Leads
        $customerModules = ['Contacts', 'Leads', 'CPTarget', 'Accounts'];   // Modified by Hieu Nguyen on 2022-05-11 to support identify Target customer

        if (!in_array($customerType, $customerModules)) {
            $response->setError(400, vtranslate('LBL_CALL_POPUP_GET_CUSTOMER_INFO_ERROR_INVALID_CUSTOMER_TYPE'));
            $response->emit();
        }

        // Get customer data
        $customerData = PBXManager_CallPopup_Model::getCustomerInfo($customerId, $customerType);

        $response->setResult($customerData);
        $response->emit();
    }

    public function getInitCallPopupData(Vtiger_Request $request) {
        $data = $request->getAll();
        $customerId = $data['customer_id'];
        $customerType = $data['customer_type'];
        $response = new Vtiger_Response();

        // Just process with Contacts and Leads
        $customerModules = ['Contacts', 'Leads', 'CPTarget', 'Accounts'];   // Modified by Hieu Nguyen on 2022-05-11 to support identify Target customer

        if (!in_array($data['customer_type'], $customerModules)) {
            $response->setError(400, vtranslate('LBL_CALL_POPUP_GET_CUSTOMER_INFO_ERROR_INVALID_CUSTOMER_TYPE'));
            $response->emit();
        }

        // Return array with key is module name / Activity type and value is related record count
        $counts = PBXManager_CallPopup_Model::getRelatedListCounts($customerId, $customerType);

        // Get customer data
        $customerData = PBXManager_CallPopup_Model::getCustomerInfo($data['customer_id'], $data['customer_type']);

        $result = [
            'counts' => $counts,
            'customer' => decodeUTF8($customerData),
        ];

        $response->setResult($result);
        $response->emit();
    }

    /**
     * Method use to save Lead Potential for Call Popup
     * @param Vtiger_Request $request
     * @return void
     */
    function savePotentialForLeads(Vtiger_Request $request) {
        $data = $request->getAll();

        // Initial useful infomations
        $leadId = $data['lead_id'];
        $recordModel = Vtiger_Record_Model::getInstanceById($leadId);
        $response = new Vtiger_Response();

        // Check if lead exist
        if (!$recordModel->getId()) {
            $response->setError(400, 'Lead Not Found');
            $response->emit();
        }

        // Check if that Lead converted but has no contact
        if ($recordModel->get('leadstatus') === 'Converted' && !$recordModel->get('contact_converted_id')) {
            $response->setError(400, 'Contact Not Found');
            $response->emit();
        }

        // Auto convert that lead to contact if not
        if ($recordModel->get('leadstatus') !== 'Converted') {
            $entityIds = $this->convertLead($recordModel);
            list($moduleId, $contactId) = explode('x', $entityIds['Contacts']);
            list($moduleId, $accountId) = explode('x', $entityIds['Accounts']);
            if (!empty($contactId)) {
                $recordModel->set('converted', 1);
                $recordModel->set('contact_converted_id', $contactId);
                $recordModel->set('leadstatus', 'Converted');
            }
            if (!empty($accountId)) {
                $recordModel->set('account_converted_id', $accountId);
            }
        }

        // Check if that Lead has a linked Contact
        if ($recordModel->get('leadstatus') === 'Converted' && $recordModel->get('contact_converted_id')) {
            // Remove some lead info from request
            $request->set('lead_id', '');

            // Init more params to request object
            $request->set('module', 'Potentials');
            $request->set('action', 'SaveAjax');
            $request->set('mode', '');
            $request->set('contact_id', $recordModel->get('contact_converted_id'));
            $request->set('sourceModule', 'Contacts');
            $request->set('sourceRecord', $recordModel->get('contact_converted_id'));
            $request->set('relationOperation', true);
            $request->set('parent_id', $recordModel->get('contact_converted_id'));

            // Link to account if needed
            if ($recordModel->get('account_converted_id') && empty($request->get('related_to'))) {
                $request->set('related_to', $recordModel->get('account_converted_id'));
            }

            // Create Potential and link with contact
            $handler = new Potentials_SaveAjax_Action();

            // Ensure handler validates the request
            $handler->validateRequest($request);
            $handler->checkPermission($request);
            $handler->process($request);
        }
        else {
            $response->setError(400, 'Something error when save Potential');
            $response->emit();
        }
    }

    protected function convertLead($recordModel) {
        require_once('include/Webservices/ConvertLead.php');
        $userModal = Users_Record_Model::getCurrentUserModel();

        // Init data to convert
        $entityValues = [];
        $entityValues['transferRelatedRecordsTo'] = 'Contacts';
        $entityValues['assignedTo'] = $recordModel->get('assigned_user_id');
        $entityValues['leadId'] = vtws_getWebserviceEntityId('Leads', $recordModel->getId());
        $entityValues['imageAttachmentId'] = '';

        $convertLeadFields = $recordModel->getConvertLeadFields();

        $modules = ['Contacts'];

        // In case this lead is organization customer, create Account too
        if (!empty($recordModel->get('company'))) {
            $modules[] = 'Accounts';
        }

        foreach ($modules as $moduleName) {
            if (vtlib_isModuleActive($moduleName)) {

                $entityValues['entities'][$moduleName] = [];

                $entityValues['entities'][$moduleName]['create'] = true;
                $entityValues['entities'][$moduleName]['name'] = $moduleName;
                $entityValues['entities'][$moduleName]['source'] = 'CRM';

                foreach ($convertLeadFields[$moduleName] as $fieldModel) {
                    $fieldName = $fieldModel->getName();
                    $fieldValue = $recordModel->get($fieldName);

                    if ($fieldModel->getFieldDataType() === 'currency') {
                        if ($fieldModel->get('uitype') == 72){
                            // Some of the currency fields like Unit Price, Total , Sub-total - doesn't need currency conversion during save
                            $fieldValue = Vtiger_Currency_UIType::convertToDBFormat($fieldValue, null, true);
                        }
                        else {
                            $fieldValue = Vtiger_Currency_UIType::convertToDBFormat($fieldValue);
                        }
                    }
                    else if ($fieldModel->getFieldDataType() === 'date') {
                        $fieldValue = DateTimeField::convertToDBFormat($fieldValue);
                    }
                    else if ($fieldModel->getFieldDataType() === 'reference' && $fieldValue) {
                        $ids = vtws_getIdComponents($fieldValue);
                        if (count($ids) === 1) {
                            $fieldValue = vtws_getWebserviceEntityId(getSalesEntityType($fieldValue), $fieldValue);
                        }
                    }
                    $entityValues['entities'][$moduleName][$fieldName] = $fieldValue;
                }
            }
        }

        $entityValues = decodeUTF8($entityValues); // Decode when convert to void UTF8 issue

        return vtws_convertlead($entityValues, $userModal);
    }

    protected function _returnResponse($response) {
        $response = json_encode($response);
        echo($response);
    }

    // Implemented by Hieu Nguyen on 2020-05-04
    public function writeOutboundCache(Vtiger_Request $request) {
        $customerId = $request->get('customer_id');
        $customerNumber = $request->get('customer_number');
        $callLogId = $request->get('call_log_id');
        if (empty($customerId) || empty($customerNumber)) return;

        $response = new Vtiger_Response();
        $user = Users_Record_Model::getCurrentUserModel();
	    $userNumber = $user->phone_crm_extension;

        if (empty($userNumber)) {
            $response->setResult(['success' => false, 'message' => 'User number is empty']);
            $response->emit();
            exit;
        }

        PBXManager_Logic_Helper::saveOutboundCache($userNumber, $customerId, $customerNumber, $callLogId);

        $response->setResult(['success' => true]);
        $response->emit();
    }

    // Implemented by Hieu Nguyen on 2020-05-08
    public function transferCall(Vtiger_Request $request) {
        $callId = $request->get('call_id');
        $destAgentExt = $request->get('dest_agent_ext');
        $destAgentName = $request->get('dest_agent_name');
        if (empty($destAgentExt) || empty($destAgentName)) return;

        $serverModel = PBXManager_Server_Model::getInstance();
        $connector = $serverModel->getConnector();
        $response = new Vtiger_Response();

        if (!$connector) {
            $response->setResult(['success' => false, 'message' => 'NO_ACTIVE_PROVIDER']);
            $response->emit();
        }

        // Check for agent status if the call center provide this method
        if (method_exists($connector, 'checkAgentStatus')) {
            $agentStatus = $connector->checkAgentStatus($destAgentExt);
            $readyToTransfer = true;

            if ($agentStatus == false) {
                $readyToTransfer = false;
                $message = 'CANNOT_CHECK_AGENT_STATUS';
            }
            elseif ($agentStatus == 'OFFLINE') {
                $readyToTransfer = false;
                $message = 'AGENT_IS_NOT_ONLINE';
            }
            elseif ($agentStatus == 'BUSY') {
                $readyToTransfer = false;
                $message = 'AGENT_IS_BUSY';
            }
            else if ($agentStatus == 'WRAPUP') {
                $readyToTransfer = false;
                $message = 'AGENT_IN_WRAPUP_TIME';
            }

            if (!$readyToTransfer) {
                $response->setResult(['success' => false, 'message' => $message]);
                $response->emit();
                return;
            }
        }
            
        $success = $connector->transferCall($callId, $destAgentExt, $destAgentName);
        $response->setResult(['success' => $success]);
        $response->emit();
    }

    public function getTransferableList(Vtiger_Request $request) {
        $data = $request->getAll();
        $responseResult = PBXManager_CallPopup_Model::getTransferableList($data);

        $this->_returnResponse($responseResult);
    }

    // Implemented by Hieu Nguyen on 2020-07-13
    public function getWebPhoneToken(Vtiger_Request $request) {
        $token = PBXManager_Logic_Helper::getWebPhoneToken();
        $result = ['success' => true, 'token' => $token];

        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }
}
