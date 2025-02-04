<?php
/*
*	ContactsHandler.php
*	Author: Phuc Lu
*	Date: 2019.06.27
*   Purpose: provide handler events for contact
*/

require_once('include/utils/HanaUtils.php');    // [ChatBotIntegration] Added by Hieu Nguyen on 2020-04-06
require_once('modules/PBXManager/BaseConnector.php');	// [TelesalesCampain] Added by Vu Mai 2022-10-18

class ContactHandler extends VTEventHandler {

	function handleEvent($eventName, $entityData) {
		global $current_user, $callCenterConfig;	// [TelesalesCampain] Added by Vu Mai 2022-10-18
		if($entityData->getModuleName() != 'Contacts') return;
		
		if($eventName === 'vtiger.entity.beforesave') {
			$this->calculateAge($entityData);
		}

		if($eventName === 'vtiger.entity.aftersave') {
			$this->calculateAge($entityData);
            self::syncToHana($entityData); // [ChatBotIntegration] Added by Hieu Nguyen on 2020-04-06
			$this->syncToMauticContact($entityData);

			// Added by Phu Vo on 2022.03.14
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
			$data['account_name'] = decodeUTF8(getAccountName($data['account_id']));
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
			$this->calculateAge($entityData);
			// $entityData->set('age', 0);
		}

		if($eventName === 'vtiger.entity.afterdelete') {			
			$this->resetTargetAndLeadConvertedStatus($entityData);	// Added by Hieu Nguyen on 2022-08-11 to reset Target and Lead converted status when the converted Contact is deleted
			$this->deleteFromMauticContact($entityData);
			
			// Added by Phu Vo on 2021.08.17 to notify deleted record
			if (CPSocialIntegration_Chatbox_Helper::isChatboxSupported()) {
				CPSocialIntegration_Chatbox_Helper::deleteAllCustomerReadStatus($entityData->getId(), $entityData->getModuleName());
				CPSocialIntegration_Chatbox_Helper::notifyRecordDeleted($entityData, $entityData->getId());
			}
			// End Phu Vo
			
			CPChatBotIntegration_Data_Model::deleteChatIdentifierMapping($entityData->getModuleName(), $entityData->getId()); // [ChatBotIntegration] Added by Phu Vo on 2020.04.24
			CPEventRegistration_Data_Helper::deleteRelatedEventRegistration($entityData->getId()); // [EventManagement] Added by Phu Vo on 2022.03.14

			// Added by Phu Vo on 2021.11.09 to update zalo ads form counter
			CPZaloAdsForm_Logic_Helper::updateZaloFormsCustomerCount($entityData->getId());
			// End Phu Vo
		}

		// Added by Phu Vo on 2020.11.11 to handle event after transfer relation
		if ($eventName === 'vtiger.entity.beforemerge') {
			CPChatBotIntegration_ChatbotLogic_Helper::remapChatIdentifers($entityData); // [ChatBotIntegration] Added by Phu Vo on 2020.11.11
		}
		// End Phu Vo
	}

	private function calculateAge($entityData) {
		require_once('include/fields/DateTimeField.php');
		// Kiểm tra nếu ngày sinh rỗng hoặc null
		$birthday = $entityData->get('birthday');
		if (!$birthday) {
			$entityData->set('age', 0); // Đặt tuổi bằng 0
			return; // Dừng hàm tại đây
		}
	
		// Tính tuổi khi có ngày sinh
		$birthdayObj = new DateTimeField($birthday);
		$birthdayDb = $birthdayObj->getDBInsertDateValue();
		$age = date('Y') - date('Y', strtotime($birthdayDb));
		
		// Đặt tuổi vào entityData
		$entityData->set('age', $age);
	}
	
	// Added by Hieu Nguyen on 2021-11-29 to sync changed tags to Mautic
	public static function tagsChanged($parentId, $action, $eventInfo) {
		// For Mautic Integration
		if (CPMauticIntegration_Config_Helper::isActiveModule('Contacts')) {
			// Sync linked tags to Mautic
			if (!$GLOBALS['syncing_tag_from_mautic']) {	// To prevent infinity loop
				if (in_array($action, ['Update', 'Link']) && !empty($eventInfo['new_tags'])) {
					CPMauticIntegration_Data_Helper::addTags($parentId, 'Contacts', $eventInfo['new_tags']);
				}
			}

			// Sync unlinked tags to Mautic
			if (in_array($action, ['Update', 'Unlink']) && !empty($eventInfo['unlinked_tags'])) {
				CPMauticIntegration_Data_Helper::removeTags($parentId, 'Contacts', $eventInfo['unlinked_tags']);
			}
		}
		
		// Added by Vu Mai on 2022-10-18 to notify state data changed with type is linked tag to client
		Vtiger_TagHandler_Helper::handleCustomerTagsChanged($parentId);
		// End Vu Mai
	}

    // [ChatBotIntegration] Implemented by Hieu Nguyen on 2020-04-06
    public static function syncToHana($entityData) {
        if (!CPChatBotIntegration_Config_Helper::isHanaEnabled()) return;
        if ($entityData->isNew()) return;   // Sync updated customers only

        // Get chat identifiers link to this customer
        $chatIdentifiers = CPChatBotIntegration_Data_Model::getChatIdentifiersFromCrmId('Hana', 'Contacts', $entityData->getId());
        if (empty($chatIdentifiers)) return;
        
        foreach ($chatIdentifiers as $identifier) {
            $appInfo = HanaUtils::getHanaBotInfo($identifier['chat_app_id']);

            if (!empty($appInfo)) {
                $chatCustomerId = $identifier['chat_customer_id'];
                $dataForHana = CPChatBotIntegration_HanaLogic_Helper::getCustomerDataForHana($entityData, 'Contacts', $chatCustomerId);

                // User doing mass update, put sync request to queue
                if ($_REQUEST['action'] == 'MassSave') {
                    CPChatBotIntegration_Data_Model::addNewSyncCustomerToHanaQueue($appInfo['bot_id'], 'Contacts', $entityData->getId(), $chatCustomerId);
                }
                // User edit 1 record, sync directly to Hana
                else {
                    CPChatBotIntegration_HanaLogic_Helper::syncCustomerToHana($appInfo, 'Contacts', $dataForHana, $chatCustomerId);
                }
            }
        }
    }

	// [MauticIntegration] Modified by Hieu Nguyen on 2021-11-25
	private function syncToMauticContact($entityData) {
		if (empty($entityData->get('email'))) return;
		if ($GLOBALS['syncing_customer_from_mautic']) return;
		if ($GLOBALS['mautic_queue_sync_customer_skip_' . $entityData->getId()]) return;

		if (CPMauticIntegration_Config_Helper::isActiveModule('Contacts')) {
			require_once('include/utils/MauticUtils.php');
			
			if (!empty($entityData->get('mautic_id'))) {
				MauticUtils::addToQueue($entityData->getId(), 'Contacts', 'Update', []);
			}
			else {
				MauticUtils::addToQueue($entityData->getId(), 'Contacts', 'Create', []);
			}
		}
	}

	// Implemented by Hieu Nguyen on 2022-08-11 to reset Target and Lead converted status when the converted Contact is deleted
	private function resetTargetAndLeadConvertedStatus($entityData) {
		global $adb;
		$contactId = $entityData->getId();

		// For Target
		$sql = "UPDATE vtiger_cptarget SET cptarget_status = '', contact_converted_id = '' WHERE contact_converted_id = ?";
		$adb->pquery($sql, [$contactId]);

		// For Lead: update leadstatus to the last value based on tracker history (set to null when leadstatus has no change log before converted)
		$sql = "UPDATE vtiger_leaddetails SET converted = 0, contact_converted_id = '', 
			leadstatus = (
				SELECT td.postvalue FROM vtiger_modtracker_basic AS tb
				INNER JOIN vtiger_modtracker_detail AS td ON (td.id = tb.id)
				WHERE tb.crmid = vtiger_leaddetails.leadid AND td.fieldname = 'leadstatus' AND td.postvalue != 'Converted'
				ORDER BY tb.changedon DESC
				LIMIT 1
			)
		WHERE contact_converted_id = ?";
		$adb->pquery($sql, [$contactId]);
	}

	// [MauticIntegration] Modified by Hieu Nguyen on 2021-11-25
	private function deleteFromMauticContact($entityData) {
		if (CPMauticIntegration_Config_Helper::isActiveModule('Contacts') && CPMauticIntegration_Config_Helper::shouldDeleteContactInMautic()) {
			require_once('include/utils/MauticUtils.php');
			
			if (!empty($entityData->get('mautic_id'))) {
				MauticUtils::addToQueue($entityData->getId(), 'Contacts', 'Delete', []);
			}
		}
	}
}