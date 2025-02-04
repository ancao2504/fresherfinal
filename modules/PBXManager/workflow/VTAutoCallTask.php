<?php

/*
    Workflow Task Auto Call
    Author: Hieu Nguyen
    Date: 2020-07-23
    Purpose: handle workflow action to make auto call and handle customer response key
*/

require_once('modules/com_vtiger_workflow/VTTaskManager.inc');
require_once('modules/com_vtiger_workflow/VTEntityCache.inc');
require_once('modules/com_vtiger_workflow/VTWorkflowUtils.php');
require_once('modules/com_vtiger_workflow/VTSimpleTemplate.inc');

class VTAutoCallTask extends VTTask {

	public $executeImmediately = true; 
	
	public function getFieldNames() {
		$formFields = [
            'phone_field', 
            'text_to_call',
            'handle_response',
            'confirm_key',
            'cancel_key',
            'target_field',
            'confirmed_value',
            'cancelled_value',
        ];

        return $formFields;
	}

    public function customRenderTaskEditForm(&$viewer, $workflowModel) {
        $targetModuleModel = $workflowModel->getModule();
        $targetModuleName = $targetModuleModel->getName();
        $viewer->assign('PICKLIST_FIELDS', getPicklistsByModule($targetModuleName));
    }
	
	public function doTask($entity) {
        VTTask::saveLog('[VTAutoCallTask::doTask] Begin', $entity);
        if (isForbiddenFeature(('AutoCallWorkflows'))) return;
        $serverModel = PBXManager_Server_Model::getInstance();
        $connector = $serverModel->getConnector();

        if (!$connector) {
            VTTask::saveLog('[VTAutoCallTask::doTask] No active connector!');
            return;
        }

        if (!method_exists($connector, 'makeAutoCall')) {
            VTTask::saveLog('[VTAutoCallTask::doTask] Method makeAutoCall not found!');
            return;
        }
        
        // Process task
        $util = new VTWorkflowUtils();
        $adminUser = $util->adminUser();
        $entityCache = new VTEntityCache($adminUser);
        $entityId = $entity->getId();
        
        // Parse phone number
        $phoneFieldParser = new VTSimpleTemplate($this->phone_field);
        $phoneNumber = $phoneFieldParser->render($entityCache, $entityId);

        // Parse content
        $contentParser = new VTSimpleTemplate($this->text_to_call);
        $content = $contentParser->render($entityCache, $entityId);
        $content = strip_tags(br2nl(decodeUTF8($content)));

        // Get record id
        $recordId = end(explode('x', $entityId));
        $recordModule = $entity->getModuleName();

        $result = $connector->makeAutoCall($phoneNumber, $content, $recordId);

        if ($result['success'] == true) {
            $this->callId = $result['call_id'];
            self::saveAutoCallLog($this, $this->callId, $recordModule, $recordId);
            PBXManager_Data_Model::saveAutoCallLog($this->callId, $phoneNumber, $content, $recordModule, $recordId);
        }

        $util->revertUser();
        VTTask::saveLog('[VTAutoCallTask::doTask] Finished', $result);
	}

    static function saveAutoCallLog($task, $callId, $targetRecordModule, $targetRecordId) {
        global $adb;
        $sql = "INSERT INTO vtiger_auto_call_mapping(
                call_id, 
                handle_response, 
                confirm_key, 
                cancel_key, 
                target_module, 
                target_record, 
                target_field, 
                confirmed_value, 
                cancelled_value
            ) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $params = [
            $callId, 
            ($task->handle_response == 'on') ? 1 : 0,
            $task->confirm_key,
            $task->cancel_key,
            $targetRecordModule, 
            $targetRecordId, 
            $task->target_field, 
            $task->confirmed_value, 
            $task->cancelled_value
        ];

        $adb->pquery($sql, $params);
    }

    static function handleResponse($callId, $responseKey) {
        global $adb;
        VTTask::saveLog('[VTAutoCallTask::handleResponse] Begin', ['call_id' => $callId, 'response_key' => $responseKey]);
        $sql = "SELECT * FROM vtiger_auto_call_mapping WHERE call_id = ? AND handle_response = 1";
        $result = $adb->pquery($sql, [$callId]);
        if (empty($result)) return;

        $row = $adb->fetchByAssoc($result);
        VTTask::saveLog('[VTAutoCallTask::handleResponse] Log data', $row);
        $updateValue = null;

        if ($responseKey == $row['confirm_key']) {
            $updateValue = $row['confirmed_value'];
        }
        else if ($responseKey == $row['cancel_key']) {
            $updateValue = $row['cancelled_value'];
        }

        if ($updateValue == null) return;
        $targetRecordModel = Vtiger_Record_Model::getInstanceById($row['target_record'], $row['target_module']);

        if (!empty($targetRecordModel->getId())) {
            $targetRecordModel->set($row['target_field'], $updateValue);
            $targetRecordModel->set('mode', 'edit');
            $targetRecordModel->save();
        }

        VTTask::saveLog('[VTAutoCallTask::handleResponse] Update value: ' . $updateValue);
    }
}