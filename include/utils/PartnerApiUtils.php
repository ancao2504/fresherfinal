<?php

/*
*   Class PartnerApiUtils
*   Author: Hieu Nguyen
*   Date: 2020-10-06
*   Purpose: Handle request from 3rd systems
*/

require_once('include/utils/RestfulApiUtils.php');

class PartnerApiUtils extends RestfulApiUtils {

    protected static $chatChannels = ['Hana', 'BotBanHang'];
    protected static $socialChannels = ['Facebook', 'Zalo'];

    protected static function _saveCustomerInfo(array $data) {
        require_once('include/utils/SyncCustomerInfoUtils.php');
        global $current_user, $fullNameConfig;
        $source = $data['source'];
        $info = $data['customer'];

        // Handle full name
        if (!empty($info['full_name'])) {
            $nameParts = explode(' ', $info['full_name']);
            $info['firstname'] = ($fullNameConfig['full_name_order'][0] == 'firstname') ? array_shift($nameParts) : array_pop($nameParts);
            $info['lastname'] = join(' ', $nameParts);
        }

        // Find existing contact if any
        $matchedRecordModel = SyncCustomerInfoUtils::findCustomerByPhoneOrEmail($info['mobile'], $info['email']);
        $customerRecordModel = null;

        if (!empty($matchedRecordModel) && $matchedRecordModel->getModuleName() == 'Contacts') {
            $customerRecordModel = $matchedRecordModel;
            $customerRecordModel->set('mode', 'edit');
        }

        if (!$customerRecordModel) {
            $customerRecordModel = Vtiger_Record_Model::getCleanInstance('Contacts');
            $customerRecordModel->set('assigned_user_id', $current_user->id);
            $customerRecordModel->set('main_owner_id', $current_user->id);
        }

        // Assign new info
        foreach ($info as $fieldName => $value) {
            $field = $customerRecordModel->getField($fieldName);
            if (!$field) continue;

            $customerRecordModel->set($fieldName, htmlentities($value));
        }

        $customerRecordModel->set('source', strtoupper($source));

        // Handle avatar
        if (!empty($info['avatar'])) {
            generateUploadFilesFromUrls([$info['avatar']], 'imagename', 'avatar.png');
        }

        // Save company
        if (!empty($info['company'])) {
            $accountRecordModel = Vtiger_Record_Model::getInstanceByConditions('Accounts', ['accountname' => $info['company']]);

            // Create new account if not exist
            if (empty($accountRecordModel)) {
                $accountRecordModel = Vtiger_Record_Model::getCleanInstance('Accounts');
                $accountRecordModel->set('accountname', $info['company']);
                $accountRecordModel->set('assigned_user_id', $current_user->id);
                $accountRecordModel->set('main_owner_id', $current_user->id);

                if (!empty($source)) {
                    $accountRecordModel->set('source', strtoupper($source));
                }

                $accountRecordModel->save();
            }

            // Link account with contact if account_id is empty
            if (empty($customerRecordModel->get('account_id'))) {
                $customerRecordModel->set('account_id', $accountRecordModel->getId());
            }
        }
        
        $customerRecordModel->save();

        // Save additional info for chatbot
        if (in_array($source, self::$chatChannels) && CPChatBotIntegration_Config_Helper::isChatBotEnabled()) {
            self::_saveCustomerTrackingInfoForChatbot($customerRecordModel, $info, $source);
        }

        // Save additional info for social
        if (in_array($source, self::$socialChannels)) {
            self::_saveCustomerTrackingInfoForSocial($customerRecordModel, $info, $source);
        }
        
        return $customerRecordModel;
    }

    protected static function _saveCustomerTrackingInfoForChatbot($customerRecordModel, $info, $chatChannel) {
        if (empty($customerRecordModel) || empty($info['bot_id']) || empty($info['id'])) return;

        // Save tracking info
        $chatbotHelper = CPChatBotIntegration_ChatbotLogic_Helper::getActiveChatbotLogicHelper();
        
        if ($chatbotHelper) {
            $chatbotHelper::setTrackingInfo($customerRecordModel, $info['bot_id']);
            $customerRecordModel->set('mode', 'edit');  // Customer record is already exist here
            $customerRecordModel->save();
        }

        // Insert chat identifiers if not exists
        $crmCustomerType = $customerRecordModel->getModuleName();
        $crmCustomerId = $customerRecordModel->getId();
        $chatIdentifier = CPChatBotIntegration_Data_Model::getChatIdentifierFromCrmId($chatChannel, $info['bot_id'], $crmCustomerType, $crmCustomerId);
        
        if (empty($chatIdentifier)) {
            if ($chatChannel == 'BotBanHang') {
                require_once('include/utils/BBHUtils.php');
                $conversationUrl = BBHUtils::getConversationUrl($info['bot_id'], $info['id']);
            }

            CPChatBotIntegration_Data_Model::insertChatIdentifierMapping($crmCustomerId, $chatChannel, $info['bot_id'], $info['id'], $crmCustomerType, $conversationUrl);
        }
    }

    protected static function _saveCustomerTrackingInfoForSocial($customerRecordModel, $info, $chatChannel) {
        // TODO
    }
}