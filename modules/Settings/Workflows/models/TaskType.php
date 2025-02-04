<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/*
 * Workflow Task Type Model Class
 */
require_once 'modules/com_vtiger_workflow/VTTaskManager.inc';

class Settings_Workflows_TaskType_Model extends Vtiger_Base_Model {

	public function getId() {
		return $this->get('id');
	}

	public function getName() {
		return $this->get('tasktypename');
	}
	
	public function getLabel() {
		return $this->get('label');
	}

	public function getTemplatePath() {
		return $this->get('templatepath');
	}

	public function getEditViewUrl() {
		return '?module=Workflows&parent=Settings&view=EditTask&type='.$this->getName();
	}
    
    public function getV7EditViewUrl() {
		return '?module=Workflows&parent=Settings&view=EditV7Task&type='.$this->getName();
	}

	public static function getInstanceFromClassName($taskClass) {
		$db = PearDatabase::getInstance();
		$result = $db->pquery("SELECT * FROM com_vtiger_workflow_tasktypes where classname=?",array($taskClass));
		$row = $db->query_result_rowdata($result, 0);
		$taskTypeObject = VTTaskType::getInstance($row);
		return self::getInstanceFromTaskTypeObject($taskTypeObject);
	}

	public static function getAllForModule($moduleModel) {
		$taskTypes = VTTaskType::getAll($moduleModel->getName());

		// Added by Hieu Nguyen on 2022-02-28 to prevent adding Create Event, Create Task and Create Record Workflow if this feature is not available in current CRM package
        if (isForbiddenFeature('BasicWorkflows')) {
            unset($taskTypes['VTCreateEventTask']);
            unset($taskTypes['VTCreateTodoTask']);
            unset($taskTypes['VTCreateEntityTask']);
        }
        // End Hieu Nguyen

		// Added by Hieu Nguyen on 2022-02-28 to prevent adding Email Workflow if this feature is not available in current CRM package
        if (isForbiddenFeature('EmailWorkflows')) {
            unset($taskTypes['VTEmailTask']);
        }
        // End Hieu Nguyen

        // Added by Hieu Nguyen on 2019-12-17 to prevent adding SMS Workflow if this feature is not available in current CRM package
        if (isForbiddenFeature('SMSWorkflows') || !SMSNotifier_Logic_Helper::hasActiveGateway()) {
            unset($taskTypes['VTSMSTask']);
        }
        // End Hieu Nguyen

		// Added by Hieu Nguyen on 2022-02-28 to prevent adding Update Fields Workflow if this feature is not available in current CRM package
        if (isForbiddenFeature('UpdateFieldWorkflows')) {
            unset($taskTypes['VTUpdateFieldsTask']);
        }
        // End Hieu Nguyen

        // Added by Hieu Nguyen on 2019-12-17 to prevent adding Auto Call Workflow if this feature is not available in current CRM package
        if (isForbiddenFeature('AutoCallWorkflows') || !PBXManager_Logic_Helper::canMakeAutoCall()) {
            unset($taskTypes['VTAutoCallTask']);
        }
        // End Hieu Nguyen

        // Added by Hieu Nguyen on 2020-11-24 to prevent adding Zalo OTT Message Workflow if this feature is not available in current CRM package
        if (isForbiddenFeature('ZaloZNSMessageWorkflows') || !CPOTTIntegration_Logic_Helper::canSendZaloZNSMsg()) {
            unset($taskTypes['VTZaloOTTMessageTask']);
        }
        // End Hieu Nguyen

        // Added by Hieu Nguyen on 2019-12-17 to prevent adding Zalo Message Workflow if this feature is not available in current CRM package
        if (isForbiddenFeature('ZaloOAMessageWorkflows') || !Settings_Workflows_Util_Helper::isZaloOAMessageWorkflowSupported($moduleModel->getName())) {
            unset($taskTypes['VTZaloOAMessageTask']);
        }
        // End Hieu Nguyen

        // Added by Hieu Nguyen on 2019-12-17 to prevent adding FB Message Workflow if this feature is not available in current CRM package
        if (isForbiddenFeature('FBMessageWorkflows')) {
            unset($taskTypes['VTFBMessageTask']);
        }
        // End Hieu Nguyen

		// Added by Hieu Nguyen on 2021-11-24 to prevent adding Add To Marketing List Workflow if the selected module is not supported
        if (isForbiddenFeature('AddToMarketingListWorkflows') || !Settings_Workflows_Util_Helper::isAddToMarketingListWorkflowSupported($moduleModel->getName())) {
            unset($taskTypes['VTAddToMarketingListTask']);
        }
        // End Hieu Nguyen

		// Added by Hieu Nguyen on 2021-11-24 to prevent adding Assign Customer Tags Workflow if the selected module is not supported
        if (isForbiddenFeature('AssignAndUnlinkTagWorkflows') || !Settings_Workflows_Util_Helper::isAssignCustomerTagsWorkflowSupported($moduleModel->getName())) {
            unset($taskTypes['VTAssignCustomerTagsTask']);
        }
        // End Hieu Nguyen

		// Added by Hieu Nguyen on 2021-12-07 to prevent adding Unlink Customer Tags Workflow if the selected module is not supported
        if (isForbiddenFeature('AssignAndUnlinkTagWorkflows') || !Settings_Workflows_Util_Helper::isUnlinkCustomerTagsWorkflowSupported($moduleModel->getName())) {
            unset($taskTypes['VTUnlinkCustomerTagsTask']);
        }
        // End Hieu Nguyen

		// Added by Hieu Nguyen on 2021-11-24 to prevent adding Update Mautic Stage Workflow if the selected module is not supported
        if (!CPMauticIntegration_Config_Helper::isMauticEnabled()) {
            unset($taskTypes['VTUpdateMauticStageTask']);
        }
		else {
			if (!Settings_Workflows_Util_Helper::isUpdateMauticStageWorkflowSupported($moduleModel->getName())) {
				unset($taskTypes['VTUpdateMauticStageTask']);
			}
			else if (in_array($moduleModel->getName(), ['CPTarget', 'Leads', 'Contacts']) && !CPMauticIntegration_Config_Helper::isActiveModule($moduleModel->getName())) {
				unset($taskTypes['VTUpdateMauticStageTask']);
			}
		}
        // End Hieu Nguyen

		$taskTypeModels = array();
		foreach($taskTypes as $taskTypeObject) {
			$taskTypeModels[] = self::getInstanceFromTaskTypeObject($taskTypeObject);
		}
		return $taskTypeModels;
	}

	public static function getInstance($taskType) {
		$taskTypeObject = VTTaskType::getInstanceFromTaskType($taskType);
		return self::getInstanceFromTaskTypeObject($taskTypeObject);
	}

	public static function getInstanceFromTaskTypeObject($taskTypeObject) {
		return new self($taskTypeObject->data);
	}

	public function getTaskBaseModule() {
		$taskTypeName = $this->get('tasktypename');
		switch($taskTypeName) {
			case 'VTCreateTodoTask' : return Vtiger_Module_Model::getInstance('Calendar');
			case 'VTCreateEventTask' : return Vtiger_Module_Model::getInstance('Events');
		}
	}

}
