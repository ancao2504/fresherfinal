<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

require_once('modules/com_vtiger_workflow/VTEntityCache.inc');
require_once('modules/com_vtiger_workflow/VTWorkflowUtils.php');
require_once('modules/com_vtiger_workflow/VTSimpleTemplate.inc');

require_once('modules/SMSNotifier/SMSNotifier.php');

class VTSMSTask extends VTTask {
	public $executeImmediately = true; 
	
	public function getFieldNames(){
		return array('content', 'sms_recepient');
	}
	
	public function doTask($entity){
		// Added by Hieu Nguyen on 2021-08-30 to check if this feature can be run
		if (isForbiddenFeature('SMSWorkflows') || !SMSNotifier_Logic_Helper::hasActiveGateway()) return;
		// End Hieu Nguyen
		
		if(SMSNotifier::checkServer()) {
			
			global $adb, $current_user,$log;
			
			$util = new VTWorkflowUtils();
			$admin = $util->adminUser();
			$ws_id = $entity->getId();
			$entityCache = new VTEntityCache($admin);
			
			$et = new VTSimpleTemplate($this->sms_recepient);
			$recepient = $et->render($entityCache, $ws_id);
			$recepients = explode(',',$recepient);
			$relatedIds = $this->getRelatedIdsFromTemplate($this->sms_recepient, $entityCache, $ws_id);
			$relatedIds = explode(',', $relatedIds);
			$relatedIdsArray = array();

			// Modified by Phuc on 2020.08.04 to update related record ids
			foreach ($relatedIds as $entityId) {
				if (!empty($entityId)) {
					list($moduleId, $recordId) = vtws_getIdComponents($entityId);

					if (!empty($recordId)) {
						$relatedIdsArray[] = $recordId;
					}
					else {
						$relatedIdsArray[] = '';
					}
				}
				else {
					$relatedIdsArray[] = '';
				}
			}
			// Ended by Phuc

			$ct = new VTSimpleTemplate($this->content);
			$content = decodeUTF8($ct->render($entityCache, $ws_id)); // Modified by Hoang Duc 2020-06-15 to decode UTF content of message
			$relatedCRMid = substr($ws_id, stripos($ws_id, 'x')+1);
			$relatedIdsArray[] = $relatedCRMid;
			
			$relatedModule = $entity->getModuleName();
			
			/** Pickup only non-empty numbers */
			// Modified by Phuc on 2020.08.04 to update related record ids
			$tonumbers = array();

			foreach($recepients as $key => $tonumber) {
				if(!empty($tonumber)) $tonumbers[$tonumber] = $relatedIdsArray[$key]; // Modified by Hoang Duc 2020-05-21 to fix workflow cannot send SMS
			}

			$relatedIdsArray = array_unique($relatedIdsArray);
			// Ended by Phuc

			//As content could be sent with HTML tags.
			$content = strip_tags(br2nl($content));

            $GLOBALS['sms_ott_channel'] = 'SMS';    // Added by Hieu Nguyen on 2020-12-03 to specify sending channel
			$this->smsNotifierId = SMSNotifier::sendsms($content, $tonumbers, $current_user->id, $relatedIdsArray);
			$util->revertUser();
		}
		
	}

	public function getRelatedIdsFromTemplate($template, $entityCache, $entityId) {
		$this->template = $template;
		$this->cache = $entityCache;
		$this->parent = $this->cache->forId($entityId);
		return preg_replace_callback('/\\$(\w+|\((\w+) : \(([_\w]+)\) (\w+)\))/', array($this,"matchHandler"), $this->template);
	}

	public function matchHandler($match) {
		preg_match('/\((\w+) : \(([_\w]+)\) (\w+)\)/', $match[1], $matches);
		// If parent is empty then we can't do any thing here
		if(!empty($this->parent)){
			if(count($matches) != 0){
				list($full, $referenceField, $referenceModule, $fieldname) = $matches;
				$referenceId = $this->parent->get($referenceField);
				if ($referenceId == null) { // Updated by Phuc on 2020.08.04 to remove condition referenceModule = user
					$result ="";
				} else {
					$result = $referenceId;
				}
			}
			// Added by Phuc on 2020.08.04
			// If not matches found and $match[1] is not null, so that mean the matching field will be get from record
			else { 
				$result = getCustomerIdFromRecord($this->parent, $this->parent->moduleName);

				if (!empty($result) && strpos($result, 'x') === false) {
					$result = 'x'. $result;
				}
			}
			// Ended by Phuc
		}
		return $result;
	}
}
?>
