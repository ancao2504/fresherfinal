<?php

/*
*	LeadHandler.php
*	Author: Phuc Lu
*	Date: 2019.06.27
*   Purpose: provide handler events for leads
*/

require_once('include/utils/HanaUtils.php');    // [ChatBotIntegration] Added by Hieu Nguyen on 2020-04-06
require_once('modules/PBXManager/BaseConnector.php');	// [TelesalesCampain] Added by Vu Mai 2022-10-18

class LeadHandler extends VTEventHandler {

	function handleEvent($eventName, $entityData) {
		global $current_user, $callCenterConfig;	// [TelesalesCampain] Added by Vu Mai 2022-10-18
		if($entityData->getModuleName() != 'Leads') return;

		if($eventName === 'vtiger.entity.beforesave') {
			// Add handler functions here
			$this->handleInCampaignRelationFromMauticEvent($entityData);
		}

		if($eventName === 'vtiger.entity.aftersave') {
            $this->syncToHana($entityData); // [ChatBotIntegration] Added by Hieu Nguyen on 2020-04-06
			$this->syncToMauticContact($entityData);
			$this->updateMauticStage($entityData);

			// Added by Phu Vo on 2020.05.23
			if (!$entityData->isNew()) {
				CPEventRegistration_Data_Helper::resyncCustomerInfoForAllRegistrations($entityData);
			}
			// End Phu Vo

			// Added by Vu Mai on 2022-10-18 to notify state data changed with type is customer info to client
			if ($entityData->focus->isBulkSaveMode()) return;
			if ($callCenterConfig['enable'] == false) return;

			$recordModel = Vtiger_Record_Model::getInstanceById($entityData->getId(), $entityData->getModuleName());
        	$data = $recordModel->getData();
			
			// Format data to matching with orther customer module
			$data['info_name'] = vtranslate($data['salutationtype']) . ' ' . $data['full_name'];
			$data['customer_number'] = $data['mobile'];
			$data['main_owner_id'] = $data['main_owner_id'];
			$data['assigned_user_name'] = decodeUTF8(getUserName($data['main_owner_id']));
			$data['assigned_user_ext'] = getUserData('phone_crm_extension', $data['main_owner_id']);
			$data['assigned_user_type'] = vtws_getOwnerType($data['main_owner_id']);

			// Notify msg to client 
			$msg = [
				'state' => 'DATA_CHANGED',
				'receiver_id' => $current_user->id,
				'data_type' => 'CUSTOMER_INFO',
				'customer_id' => $entityData->get('id'),
				'extra_data' => $data,
			];
	
			PBXManager_Base_Connector::forwardToCallCenterBridge($msg);
			// End Vu Mai
		}

		if($eventName === 'vtiger.entity.beforedelete') {
			// Add handler functions here
		}

		if($eventName === 'vtiger.entity.afterdelete') {
			$this->resetTargetConvertedStatus($entityData);	// Added by Hieu Nguyen on 2022-08-11 to reset Target converted status when the converted Lead is deleted
			$this->deleteFromMauticContact($entityData);
			CPChatBotIntegration_Data_Model::deleteChatIdentifierMapping($entityData->getModuleName(), $entityData->getId()); // [ChatBotIntegration] Added by Phu Vo on 2020.04.24
			
			// Added by Phu Vo on 2021.08.17 to notify deleted record
			if (CPSocialIntegration_Chatbox_Helper::isChatboxSupported()) {
				CPSocialIntegration_Chatbox_Helper::deleteAllCustomerReadStatus($entityData->getId(), $entityData->getModuleName());
				CPSocialIntegration_Chatbox_Helper::notifyRecordDeleted($entityData, $entityData->getId());
			}
			// End Phu Vo
			
			CPSocialIntegration_Data_Model::deleteSocialIdentifierMapping($entityData->getModuleName(), $entityData->getId()); // Added by Phu Vo on 2020.02.19
			CPEventRegistration_Data_Helper::deleteRelatedEventRegistration($entityData->getId()); // [EventManagement] Added by Phu Vo on 2020.05.28

			// Added by Phu Vo on 2021.11.09 to update zalo ads form counter
			CPZaloAdsForm_Logic_Helper::updateZaloFormsCustomerCount($entityData->getId());
			// End Phu Vo
		}

		// Added by Phu Vo on 2020.02.19 to handle lead converted logic
		if ($eventName === 'vtiger.lead.convertlead') {
            $this->removeRedundantConvertedLeadFromRelatedMKTLists($entityData);	// Added by Hieu Nguyen on 2022-12-12
            $this->updateChatMapping($entityData);  // [ChatBotIntegration] Added by Hieu Nguyen on 2020-04-09
			$this->updateSocialMapping($entityData); // Added by Phu Vo on 2020.02.19
			$this->syncLeadConvertedRelationField($entityData);
			$this->syncMauticDataToConvertedContact($entityData); // Added by Phuc on 2020.03.04
			$this->updateMauticStage($entityData); // Added by Phu Vo on 2020.05.23
			$this->updateCustomerTypeAndIdInCampaign($entityData);	// [TelesalesCampign] Added by Vu Mai on 2022-11-23
		}
		// End Phu Vo

		// Added by Phu Vo on 2020.11.11 to handle event after transfer relation
		if ($eventName === 'vtiger.entity.beforemerge') {
			CPChatBotIntegration_ChatbotLogic_Helper::remapChatIdentifers($entityData); // [ChatBotIntegration] Added by Phu Vo on 2020.11.11
		}
		// End Phu Vo
	}

	// Added by Hieu Nguyen on 2021-11-29 to sync changed tags to Mautic
	public static function tagsChanged($parentId, $action, $eventInfo) {
		// For Mautic Integration
		if (CPMauticIntegration_Config_Helper::isActiveModule('Leads')) {
			$recordModel = Vtiger_Record_Model::getInstanceById($parentId, 'Leads');

			// Do not sync tags of converted Lead to Mautic
			if (empty($recordModel) || $recordModel->get('leadstatus') == 'Converted') {
				return;
			}

			// Sync linked tags to Mautic
			if (!$GLOBALS['syncing_tag_from_mautic']) {	// To prevent infinity loop
				if (in_array($action, ['Update', 'Link']) && !empty($eventInfo['new_tags'])) {
					CPMauticIntegration_Data_Helper::addTags($parentId, 'Leads', $eventInfo['new_tags']);
				}
			}

			// Sync unlinked tags to Mautic
			if (in_array($action, ['Update', 'Unlink']) && !empty($eventInfo['unlinked_tags'])) {
				CPMauticIntegration_Data_Helper::removeTags($parentId, 'Leads', $eventInfo['unlinked_tags']);
			}
		}

		// Added by Vu Mai on 2022-10-18 to notify state data changed with type is linked tag to client
		Vtiger_TagHandler_Helper::handleCustomerTagsChanged($parentId);
		// End Vu Mai
	}

    // [ChatBotIntegration] Implemented by Hieu Nguyen on 2020-04-06
    private function syncToHana($entityData) {
        if (!CPChatBotIntegration_Config_Helper::isHanaEnabled()) return;
        if ($entityData->isNew()) return;   // Sync updated customers only

        // Get chat identifiers link to this customer
        $chatIdentifiers = CPChatBotIntegration_Data_Model::getChatIdentifiersFromCrmId('Hana', 'Leads', $entityData->getId());
        if (empty($chatIdentifiers)) return;

        foreach ($chatIdentifiers as $identifier) {
            $appInfo = HanaUtils::getHanaBotInfo($identifier['chat_app_id']);

            if (!empty($appInfo)) {
                $chatCustomerId = $identifier['chat_customer_id'];
                $dataForHana = CPChatBotIntegration_HanaLogic_Helper::getCustomerDataForHana($entityData, 'Leads', $chatCustomerId);

                // User doing mass update, put sync request to queue
                if ($_REQUEST['action'] == 'MassSave') {
                    CPChatBotIntegration_Data_Model::addNewSyncCustomerToHanaQueue($appInfo['bot_id'], 'Leads', $entityData->getId(), $chatCustomerId);
                }
                // User edit 1 record, sync directly to Hana
                else {
                    CPChatBotIntegration_HanaLogic_Helper::syncCustomerToHana($appInfo, 'Leads', $dataForHana, $chatCustomerId);
                }
            }
        }
    }

	// Implemented by Hieu Nguyen on 2022-12-12 to remove converted Lead from related MKT Lists as it is already replaced by the new Contact
	private function removeRedundantConvertedLeadFromRelatedMKTLists($entityData) {
		global $adb;
		$leadId = $entityData->getId();

		try {
			// Get related MKT List ids before unlink with current Lead record
			$sqlGetRelatedMKTListIds = "SELECT GROUP_CONCAT(mkt_list_id) AS mkt_list_ids
				FROM (
					SELECT relcrmid AS mkt_list_id FROM vtiger_crmentityrel WHERE crmid = ? AND relmodule = 'CPTargetList'
					UNION
					SELECT crmid FROM vtiger_crmentityrel WHERE relcrmid = ? AND module = 'CPTargetList'
				) AS temp";
			$mktListIdsStr = $adb->getOne($sqlGetRelatedMKTListIds, [$leadId, $leadId]);

			// Unlink current Lead record with all related MKT Lists
			$sqlUnlink = "DELETE FROM vtiger_crmentityrel WHERE (crmid = ? AND relmodule = 'CPTargetList') OR (relcrmid = ? AND module = 'CPTargetList')";
			$adb->pquery($sqlUnlink, [$leadId, $leadId]);

			// Re-calculate customers count in all related MKT Lists
			$mktListIds = explode(',', $mktListIdsStr);

			foreach ($mktListIds as $mktListId) {
				CPTargetList_Data_Model::reCalcCustomersCount($mktListId);
			}
		}
		catch (Exception $e) {
			saveLog('CHATBOT_INTEGRATION', '[LeadHandler::removeRedundantConvertedLeadFromRelatedMKTLists] Error: ' . $e->getMessage(), $e->getTrace());
		}
	}

    // [ChatBotIntegration] Implemented by Hieu Nguyen on 2020-04-09
    private function updateChatMapping($entityData) {
        if (!CPChatBotIntegration_Config_Helper::isChatBotEnabled()) return;
        if ($entityData->isNew()) return;   // Handle updated customers only

		try {
			$convertedContactId = $entityData->entityIds['Contacts'];

			if (!empty($convertedContactId)) {
				list($moduleId, $contactId) = explode('x', $convertedContactId);

				// Move mapping from Lead to converted Contact
				CPChatBotIntegration_Data_Model::updateChatIdentifierMapping('Leads', $entityData->getId(), 'Contacts', $contactId);
			}
		}
		catch (Exception $e) {
			saveLog('CHATBOT_INTEGRATION', '[LeadHandler::updateChatMapping] Error: ' . $e->getMessage(), $e->getTrace());
		}
	}

	/** Implemented by Phu Vo on 2020.06.26 */
	private function updateSocialMapping($entityData) {
        if ($entityData->isNew()) return;   // Handle updated customers only

		try {
			$convertedContactId = $entityData->entityIds['Contacts'];

			if (!empty($convertedContactId)) {
				list($moduleId, $contactId) = explode('x', $convertedContactId);

				// Move mapping from Lead to converted Contact
				CPSocialIntegration_Data_Model::updateSocialIdentifierMapping('Leads', $entityData->getId(), 'Contacts', $contactId);
				
				if ($entityData->get('zalo_id_synced') == 1) {
					$entityData->set('zalo_id_synced', 0);
					$entityData->set('mode', 'edit');
					$entityData->focus->save($entityData->getModuleName(), $entityData->getId());
				
					$destRecord = Vtiger_Record_Model::getInstanceById($contactId, 'Contacts');
					$destRecord->set('zalo_id_synced', 1);
					$destRecord->set('mode', 'edit');
					$destRecord->save();
				}

				// Notify changes to chat users
				if (CPSocialIntegration_Chatbox_Helper::isChatboxSupported()) {
					CPSocialIntegration_Chatbox_Helper::notifySocialMappingUpdate($entityData, 'Leads', $entityData->getId(), 'Contacts', $contactId);
				}
			}
		}
		catch (Exception $e) {
			saveLog('SOCIAL_INTEGRATION', '[LeadHandler::updateSocialMapping] Error: ' . $e->getMessage(), $e->getTrace());
		}
	}

	// [MauticIntegration] Modified by Hieu Nguyen on 2021-11-26
	private function syncToMauticContact($entityData) {
		if (empty($entityData->get('email'))) return;
		if ($entityData->get('leadstatus') == 'Converted') return;
		if ($GLOBALS['syncing_customer_from_mautic']) return;
		if ($GLOBALS['mautic_queue_sync_customer_skip_' . $entityData->getId()]) return;

		if (CPMauticIntegration_Config_Helper::isActiveModule('Leads')) {
			require_once('include/utils/MauticUtils.php');

			if (!empty($entityData->get('mautic_id'))) {
				MauticUtils::addToQueue($entityData->getId(), 'Leads', 'Update', []);
			}
			else {
				MauticUtils::addToQueue($entityData->getId(), 'Leads', 'Create', []);
			}
		}
	}

	// Implemented by Hieu Nguyen on 2022-08-11 to reset Target converted status when the converted Lead is deleted
	private function resetTargetConvertedStatus($entityData) {
		global $adb;
		$sql = "UPDATE vtiger_cptarget SET cptarget_status = '', lead_converted_id = '' WHERE lead_converted_id = ?";
		$adb->pquery($sql, [$entityData->getId()]);
	}

	// [MauticIntegration] Modified by Hieu Nguyen on 2021-11-26
	private function deleteFromMauticContact($entityData) {
		if ($entityData->get('leadstatus') == 'Converted') return;

		if (CPMauticIntegration_Config_Helper::isActiveModule('Leads') && CPMauticIntegration_Config_Helper::shouldDeleteContactInMautic()) {
			require_once('include/utils/MauticUtils.php');

			if (!empty($entityData->get('mautic_id'))) {
				MauticUtils::addToQueue($entityData->getId(), 'Leads', 'Delete', []);
			}
		}
	}

	// [MauticIntegration] Added by Phuc on 2020.03.04 to sync mautic history
	// Modified by Hieu Nguyen on 2021-11-26
	private function syncMauticDataToConvertedContact($entityData) {
		try {
			if (CPMauticIntegration_Config_Helper::isActiveAllModules(['Leads', 'Contacts'])) {
				$convertedEntityIds = $entityData->entityIds;

				// Check to validate input data
				if (!is_array($convertedEntityIds)) return;
				if (count($convertedEntityIds) === 0) return;
				if (!isset($convertedEntityIds['Contacts'])) return;

				list($wsModuleId, $entityId) = explode('x', $convertedEntityIds['Contacts']);

				// Sync mautic_id and last_synced_mautic_history_time to converted lead
				$mauticId = $entityData->get('mautic_id');
				$lastSyncedToMauticTime = $entityData->get('last_synced_to_mautic_time');
				$lastHistoryTime = $entityData->get('last_synced_mautic_history_time');

				CPMauticIntegration_Data_Helper::updateCustomerFields($entityId, 'Contacts',
					['mautic_id' => $mauticId, 'last_synced_to_mautic_time' => $lastSyncedToMauticTime, 'last_synced_mautic_history_time' => $lastHistoryTime]
				);
			}
		}
		catch (Exception $e) {
			saveLog('MAUTIC_INTEGRATION', '[LeadHandler::syncMauticDataToConvertedContact] Error: ' . $e->getMessage(), $e->getTrace());
		}
	}
	// Ended by Phuc

	// [MauticIntegration] Added by Phuc on 2020.03.04 to sync mautic stage
	private function updateMauticStage($entityData) {
		// Commented out to disable unused logic by Hieu Nguyen on 2021-11-02
		// if (CPMauticIntegration_Config_Helper::isActiveModule('Leads')) {
		// 	CPMauticIntegration_Data_Helper::updateContactStageSegmentByStatus('Leads', $entityData);
		// }
	}
	// Ended by Phuc

	/**
	 * Added by Phu Vo on 2020.02.19
	 * EntityData passing to this function will not going to trigger method save
	 * So we will have to do it ourself or using sql to handle
	 */
	private function syncLeadConvertedRelationField($entityData) {
		global $adb;

		try {
			$convertedEntityIds = $entityData->entityIds;

			// Check to validate input data
			if (!is_array($convertedEntityIds)) return;
			if (count($convertedEntityIds) === 0) return;

			$moduleFieldMapping = [
				'Accounts' => 'account_converted_id',
				'Contacts' => 'contact_converted_id',
				'Potentials' => 'potential_converted_id',
			];
			$params = [];

			$sql = "UPDATE vtiger_leaddetails SET ";

			$index = 0;
			foreach ($convertedEntityIds as $module => $wsEntityId) {
				list($wsModuleId, $entityId) = explode('x', $wsEntityId);

				if ($index !== 0) $sql .= ', ';
				$sql .= "{$moduleFieldMapping[$module]} = ? ";
				$params[] = $entityId;
				$index++;
			}

			// Concat with where condition
			$sql .= "WHERE leadid = ?";
			$params[] = $entityData->getId();

			$adb->pquery($sql, $params);
		}
		catch (Exception $e) {
			saveLog('PLATFORM', '[LeadHandler::syncLeadConvertedRelationField] Error: ' . $e->getMessage(), $e->getTrace());
		}
	}

	// Implemented by Phu Vo on 2020.05.27
	private function handleInCampaignRelationFromMauticEvent($entityData) {
		CPEventRegistration_Logic_Helper::saveDebugLog('BEGIN: handleInCampaignRelationFromMauticEvent');

		try {
			// Get old data
			$vtEntityDelta = new VTEntityDelta();
			$delta = $vtEntityDelta->getEntityDelta('Leads', $entityData->getId());
	
			// Modified by Phu Vo on 2021.11.14 to prevent logic trigger on empty string value
			if (
				($entityData->isNew() && !empty($entityData->get('mautic_campaign_id')))
				|| (isset($delta['mautic_campaign_id']) && !empty($delta['mautic_campaign_id']['currentValue']))
			) {
				$entityData->set('related_campaign', $entityData->get('mautic_campaign_id'));
			}
			// End Phu Vo

			CPEventRegistration_Logic_Helper::saveDebugLog('END: handleInCampaignRelationFromMauticEvent');
		}
		catch (Exception $e) {
			CPEventRegistration_Logic_Helper::saveDebugLog('ERROR: handleInCampaignRelationFromMauticEvent ' . $e->getMessage(), $e->getTrace());
		}
	}

	//	Added by Vu Mai on 2022-11-23 to update customer type and id in campaign
	private function updateCustomerTypeAndIdInCampaign($entityData) {
		global $adb;

		list($moduleId, $contactId) = explode('x', $entityData->entityIds['Contacts']);
		$sql = "UPDATE vtiger_telesales_campaign_distribution SET customer_type = 'Contacts', customer_id = ? WHERE customer_id = ?";
		$adb->pquery($sql, [$contactId, $entityData->getId()]);

		return true;
	}
}
