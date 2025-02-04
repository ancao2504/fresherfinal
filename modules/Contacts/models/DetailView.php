<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

//Same as Accounts Detail View
class Contacts_DetailView_Model extends Accounts_DetailView_Model {

    // Added by Hieu Nguyen on 2019-07-16
    public function getDetailViewLinks($linkParams) {
        require_once('libraries/ArrayUtils/ArrayUtils.php');
        $detailViewLinks = parent::getDetailViewLinks($linkParams);
        $currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        $moduleModel = $this->getModule();
        $moduleName = $moduleModel->getName();
        $recordModel = $this->getRecord();
        $recordId = $recordModel->getId();

        // Open Chatbox. TODO: check for other channels that the Chatbox supported
        if (CPSocialIntegration_Chatbox_Helper::isChatboxSupported() && CPSocialIntegration_Chatbox_Helper::canUseChatbox()) {
            $socialIdentifiers = CPSocialIntegration_Data_Model::getSocialIdentifiersFromCrmId('Zalo', $moduleName, $recordId);
            
            if (!empty($socialIdentifiers)) {
                $socialChatLink = [
                    'linktype' => 'DETAILVIEWBASIC',
                    'linklabel' => 'LBL_SOCIAL_CHATBOX_OPEN_CHAT',
                    'linkurl' => 'javascript:SocialChatboxPopup.open("Zalo", "'. $recordId .'");',
                    'linkicon' => ''
                ];

                $detailViewLinks['DETAILVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($socialChatLink);
            }
        }

        // Email
        $emailModuleModel = Vtiger_Module_Model::getInstance('Emails');

		if ($currentUserModel->hasModulePermission($emailModuleModel->getId())) {
			$sendEmailLink = [
				'linktype' => 'DETAILVIEWBASIC',
				'linklabel' => 'LBL_SEND_EMAIL',
				'linkurl' => 'javascript:Vtiger_Detail_Js.triggerSendEmail("'. getMassActionUrl('send_email', $moduleName) .'", "Emails");',
				'linkicon' => ''
			];

			$detailViewLinks['DETAILVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($sendEmailLink);
		}

        // SMS
        if (SMSNotifier_Logic_Helper::canSendSMSMsg()) {
            $sendSMSLink = [
                'linktype' => 'DETAILVIEW',
                'linklabel' => 'LBL_SEND_SMS',
                'linkurl' => 'javascript:Vtiger_Detail_Js.triggerSendSMSOTT("'. getMassActionUrl('send_sms_ott', $moduleName) .'", "SMS", this);',
                'linkicon' => ''
            ];

            $detailViewLinks['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($sendSMSLink);
        }

        // Zalo ZNS
        if (CPOTTIntegration_Logic_Helper::canSendZaloZNSMsg()) {
            $sendZaloZNSMessageLink = [
                'linktype' => 'DETAILVIEW',
                'linklabel' => 'LBL_SEND_ZALO_OTT_MESSAGE',
                'linkurl' => 'javascript:Vtiger_Detail_Js.triggerSendSMSOTT("'. getMassActionUrl('send_sms_ott', $moduleName) .'", "Zalo", this);',
                'linkicon' => ''
            ];

            $detailViewLinks['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($sendZaloZNSMessageLink);
        }

        // Social
        if (!isForbiddenFeature('SocialIntegration')) {
            // Zalo OA
            if (CPSocialIntegration_Config_Helper::isZaloEnabled()) {
                $socialIdentifiers = CPSocialIntegration_Data_Model::getSocialIdentifiersFromCrmId('Zalo', $moduleName, $recordId);

                if (!empty($socialIdentifiers)) {
                    // Send message
                    if (CPSocialIntegration_Config_Helper::isZaloMessageAllowed()) {
                        if (!isForbiddenFeature('SendMessageViaZaloOA')) {
                            $sendZaloOAMessageLink = [
                                'linktype' => 'DETAILVIEW',
                                'linklabel' => 'LBL_SOCIAL_INTEGRATION_SEND_ZALO_MESSAGE',
                                'linkurl' => 'javascript:SocialHandler.composeSocialMessage("Zalo");',
                                'linkicon' => ''
                            ];

                            $detailViewLinks['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($sendZaloOAMessageLink);
                        }

                        $shareZaloContactInfoRequestLink = [
                            'linktype' => 'DETAILVIEW',
                            'linklabel' => 'LBL_SOCIAL_INTEGRATION_REQUEST_SHARE_ZALO_CONTACT_INFO',
                            'linkurl' => 'javascript:SocialHandler.triggerRequestShareZaloContactInfo();',
                            'linkicon' => ''
                        ];

                        $detailViewLinks['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($shareZaloContactInfoRequestLink);
                    }
                }
            }
        }

        // Chatbot
        if (!isForbiddenFeature('ChatBotIntegration')) {
            // Hana
            if (CPChatBotIntegration_Config_Helper::isHanaEnabled()) {
                $chatIdentifiers = CPChatBotIntegration_Data_Model::getChatIdentifiersFromCrmId('Hana', $moduleName, $recordId);

                if (!empty($chatIdentifiers)) {
                    // Button send Hana message
                    if (!isForbiddenFeature('SendMessageViaChatbot')) {
                        $hanaMessageLink = [
                            'linktype' => 'DETAILVIEW',
                            'linklabel' => 'LBL_CHAT_BOT_INTEGRATION_SEND_HANA_MESSAGE',
                            'linkurl' => 'javascript:ChatBotHandler.triggerComposeChatMessage("Hana");',
                            'linkicon' => ''
                        ];

                        $detailViewLinks['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($hanaMessageLink);
                    }

                    // Button show Hana chat history
                    // $hanaChatHistoryLink = [
                    //     'linktype' => 'DETAILVIEW',
                    //     'linklabel' => 'LBL_CHAT_BOT_INTEGRATION_SHOW_HANA_CHAT_HISTORY',
                    //     'linkurl' => 'javascript:window.open("'. $chatIdentifiers[0]['chat_history_url'] .'");',
                    //     'linkicon' => ''
                    // ];

                    global $chatBotConfig;
                    $hanaBotId = $chatIdentifiers[0]['chat_app_id'];
                    $hanaCustomerId = $chatIdentifiers[0]['chat_customer_id'];
                    $pageScopedId = end(explode('_', $hanaCustomerId));
                    $hanaChatDetailUrl = $chatBotConfig['hana']['chat_detail_iframe_url'] . $hanaBotId . '/chats?fromPsids=' . $pageScopedId;
                    $iframeHeaderTitle = vtranslate('LBL_CHAT_DETAIL_IFRAME_HEADER_TITLE', 'CPChatBotIntegration', [
                        '%customer_type' => vtranslate('SINGLE_' . $chatIdentifiers[0]['mapping_module']),
                        '%customer_name' => $this->record->get('full_name'),
                    ]);

                    $chatDetailUrl = 'index.php?module=CPChatBotIntegration&view=Iframe&iframe_url='. urlencode(base64_encode($hanaChatDetailUrl)) .'&custom_title='. urlencode(base64_encode($iframeHeaderTitle));

                    $hanaChatHistoryLink = [
                        'linktype' => 'DETAILVIEW',
                        'linklabel' => 'LBL_CHAT_BOT_INTEGRATION_SHOW_HANA_CHAT_HISTORY',
                        'linkurl' => 'javascript:window.open("'. $chatDetailUrl .'");',
                        'linkicon' => ''
                    ];

                    $detailViewLinks['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($hanaChatHistoryLink);
                }
            }

            // BBH
            if (CPChatBotIntegration_Config_Helper::isBBHEnabled()) {
                $chatIdentifiers = CPChatBotIntegration_Data_Model::getChatIdentifiersFromCrmId('BotBanHang', $moduleName, $recordId);

                if (!empty($chatIdentifiers)) {
                    // Button send BBH message
                    if (!isForbiddenFeature('SendMessageViaChatbot')) {
                        $bbhMessageLink = [
                            'linktype' => 'DETAILVIEW',
                            'linklabel' => 'LBL_CHAT_BOT_INTEGRATION_SEND_BBH_MESSAGE',
                            'linkurl' => 'javascript:ChatBotHandler.triggerComposeChatMessage("BotBanHang");',
                            'linkicon' => ''
                        ];

                        $detailViewLinks['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($bbhMessageLink);
                    }

                    // Button show BBH chat history
                    $bbhChatHistoryLink = [
                        'linktype' => 'DETAILVIEW',
                        'linklabel' => 'LBL_CHAT_BOT_INTEGRATION_SHOW_BBH_CHAT_HISTORY',
                        'linkurl' => 'javascript:window.open("'. $chatIdentifiers[0]['chat_history_url'] .'");',
                        'linkicon' => ''
                    ];
                    
                    $detailViewLinks['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($bbhChatHistoryLink);
                }
            }
        }

        return $detailViewLinks;
    }
	
	// Added by Phu Vo on 2019.08.20 to remove sub panel Social Article Log
	public function getDetailViewRelatedLinks() {
        $relatedLinks = parent::getDetailViewRelatedLinks();
        $moduleModel = $this->getModule();
        $recordModel = $this->getRecord();
        $moduleName = $moduleModel->getName();

        // Modified by Tin Bui on 2022.03.17 - Hide module subpanels
        $hideSubpanelModules = [
            'CPSocialArticleLog',
            'Campaigns',
            'CPTicketCommunicationLog'
        ];

        // Added by Phuc on 2020.03.24 for Mautic subpanel
        // Modified by Hieu Nguyen on 2021-12-02 to hide Mautic History subpanel when Mautic Config is not enabled yet or when linked mautic_id is empty
        if (
            !CPMauticIntegration_Config_Helper::isMauticEnabled() || !CPMauticIntegration_Config_Helper::hasConfig($moduleName)
            || !CPMauticIntegration_Data_Helper::getMauticId($recordModel->getId(), $moduleModel->basetable, $moduleModel->basetableid)
        ) {
            $hideSubpanelModules[] = 'CPMauticContactHistory';
        }
        // Ended by Phuc

        foreach ($relatedLinks as $key => $relatedLink) {
            if (isset($relatedLink['relatedModuleName']) && in_array($relatedLink['relatedModuleName'], $hideSubpanelModules)) {
                unset($relatedLinks[$key]);
            }
        }
        // Ended by Tin Bui

		return $relatedLinks;
	}
	// End Phu Vo
}
