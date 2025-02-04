<?php
/**
 * @author Tin.Bui
 * @create date 08-05-2021
 * @desc HelpDesk Ajax Requests Handler
 */

class HelpDesk_HandleAjax_Action extends Vtiger_Action_Controller {

    function checkPermission(Vtiger_Request $request) {
        $moduleName = $request->getModule();
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        $allowAccess = $currentUserPrivilegesModel->hasModulePermission($moduleModel->getId());

        if (!$allowAccess) {
            throw new AppException(vtranslate($moduleName, $moduleName) . ' ' . vtranslate('LBL_NOT_ACCESSIBLE'));
        }
    }

    public function process(Vtiger_Request $request) {
		$mode = $request->get('mode');
		
		if (method_exists($this, $mode)) {
            $this->$mode($request);
        }
        else {
            $response = new Vtiger_Response();
            $response->setResult(['success' => 0]);
            $response->emit();
        }
	}

    function getTicketLogs(Vtiger_Request $request) {
        $response = new Vtiger_Response();
        
        try {
            $ticketId = $request->get('ticket_id');
            $result = ['success' => 0];
            
            if (!empty($ticketId) && isRecordExists($ticketId)) {
                $logs = CPTicketCommunicationLog_Data_Model::getLogsByTicketId($ticketId);
                $result = [
                    'success' => 1,
                    'data' => $logs['data'],
                    'total' => $logs['total']
                ];
            }
            
            $response->setResult($result);
            $response->emit();
        }
        catch (Exception $e) {
            $response->setResult(['success' => 0]);
            $response->emit();
        }
    }

    function updateTicketStatus(Vtiger_Request $request) {
        $response = new Vtiger_Response();
        
        try {
            $ticketId = $request->get('ticket_id');
            $ticketStatus = $request->get('ticketstatus');
            $result = ['success' => 0];

            if (!empty($ticketStatus) && !empty($ticketId) && isRecordExists($ticketId)) {
                $ticketRecord = Vtiger_Record_Model::getInstanceById($ticketId, 'HelpDesk');
                $validateStatus = HelpDesk_GeneralUtils_Helper::validateRecordStatus($ticketStatus, $ticketRecord);
               
                if ($validateStatus['isValid']) {
                    $ticketRecord->set('mode', 'edit');
                    $ticketRecord->set('ticketstatus', $ticketStatus);
                    $ticketRecord->save();
                    $result = ['success' => 1];
                }
                else {
                     $result = ['success' => 2];
                }
                
            }
            
            $response->setResult($result);
            $response->emit();
        }
        catch (Exception $e) {
            $response->setResult(['success' => 0]);
            $response->emit();
        }
    }

    function getEditViewReplyBlockHtml(Vtiger_Request $request) {
        $fileUploadValidatorConfigs = HelpDesk_GeneralUtils_Helper::getFileUploadValidatorConfigs();

        $viewer = new Vtiger_Viewer();
		$viewer->assign('FILE_VALIDATOR_CONFIGS', $fileUploadValidatorConfigs);
        $viewer->display('modules/HelpDesk/tpls/AddReplyBlockEditView.tpl');
    }

    function getTicketStatusHistoryLogs(Vtiger_Request $request) {
        $response = new Vtiger_Response();
        
        try {
            $ticketId = $request->get('ticket_id');
            $result = ['success' => 0];
            
            if (!empty($ticketId) && isRecordExists($ticketId)) {
                $logs = HelpDesk_GeneralUtils_Helper::getTicketStatusHistoryLog($ticketId);
                $result = [
                    'success' => 1,
                    'data' => $logs
                ];
            }
            
            $response->setResult($result);
            $response->emit();
        }
        catch (Exception $e) {
            $response->setResult(['success' => 0]);
            $response->emit();
        }
    }

    function fetchEmailTemplate(Vtiger_Request $request) {
        $response = new Vtiger_Response();
        
        try {
            $ticketId = $request->get('ticket_id');
            $templateId = $request->get('template_id');
            $result = ['success' => 0];

            if (!empty($templateId)) {
                $templateRecord = EmailTemplates_Record_Model::getInstanceById($templateId);
                $body = html_entity_decode($templateRecord->get('body'));
                
                if (!empty($ticketId) && isRecordExists($ticketId)) {
                    $ticketRecord = HelpDesk_Record_Model::getInstanceById($ticketId);
                    $body = getMergedDescription($body, $ticketId, 'HelpDesk');

                    if (!empty($ticketRecord->get('contact_id'))) {
                        $body = getMergedDescription($body, $ticketRecord->get('contact_id'), 'Contacts');
                    }

                    if (!empty($ticketRecord->get('parent_id'))) {
                        $body = getMergedDescription($body, $ticketRecord->get('parent_id'), 'Accounts');
                    }

                    if (!empty($ticketRecord->get('related_cpslacategory'))) {
                        $body = getMergedDescription($body, $ticketRecord->get('related_cpslacategory'), 'CPSLACategory');
                    }
                }

                $result = ['success' => 1, 'body' => $body];
            }

            $response->setResult($result);
            $response->emit();
        }
        catch (Exception $e) {
            $response->setResult(['success' => 0]);
            $response->emit();
        }
    }
}