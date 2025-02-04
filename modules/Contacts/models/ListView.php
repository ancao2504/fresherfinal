<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Contacts_ListView_Model extends Vtiger_ListView_Model {

	/**
	 * Function to get the list of Mass actions for the module
	 * @param <Array> $linkParams
	 * @return <Array> - Associative array of Link type to List of  Vtiger_Link_Model instances for Mass Actions
	 */
    // Modified by Hieu Nguyen on 2020-11-11 to make standard buttons for Leads and Targets to inherit
	public function getListViewMassActions($linkParams) {
		$massActionLinks = parent::getListViewMassActions($linkParams);
		$currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        $moduleModel = $this->getModule();
        $moduleName = $moduleModel->getName();

        // Email
		$emailModuleModel = Vtiger_Module_Model::getInstance('Emails');

		if ($currentUserModel->hasModulePermission($emailModuleModel->getId())) {
			$massActionLink = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_SEND_EMAIL',
				'linkurl' => 'javascript:Vtiger_List_Js.triggerSendEmail("'. getMassActionUrl('send_email', $moduleName) .'", "Emails");',
				'linkicon' => ''
            ];

			$massActionLinks['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
		}

        // SMS
		if (SMSNotifier_Logic_Helper::canSendSMSMsg()) {
			$sendSMSLink = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_SEND_SMS',
				'linkurl' => 'javascript:Vtiger_List_Js.triggerSendSMSOTT("'. getMassActionUrl('send_sms_ott', $moduleName) .'", "SMS", this);',
				'linkicon' => ''
			];

			$massActionLinks['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($sendSMSLink);
		}

		// Zalo ZNS
		if (CPOTTIntegration_Logic_Helper::canSendZaloZNSMsg()) {
			$sendZaloZNSMessageLink = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_SEND_ZALO_OTT_MESSAGE',
				'linkurl' => 'javascript:Vtiger_List_Js.triggerSendSMSOTT("'. getMassActionUrl('send_sms_ott', $moduleName) .'", "Zalo", this);',
				'linkicon' => ''
			];

			$massActionLinks['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($sendZaloZNSMessageLink);
		}

        // Social
        if (!isForbiddenFeature('SocialIntegration')) {
            // Zalo OA
            if (CPSocialIntegration_Config_Helper::isZaloEnabled()) {
                if (CPSocialIntegration_Config_Helper::isZaloMessageAllowed()) {
					if (!isForbiddenFeature('SendMessageViaZaloOA')) {
						$sendZaloOAMessageLink = array(
							'linktype' => 'LISTVIEWMASSACTION',
							'linklabel' => 'LBL_SOCIAL_INTEGRATION_SEND_ZALO_MESSAGE',
							'linkurl' => 'javascript:SocialHandler.composeSocialMessage("Zalo");',
							'linkicon' => ''
						);
						
						$massActionLinks['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($sendZaloOAMessageLink);
					}

                    $shareZaloContactInfoRequestLink = array(
                        'linktype' => 'LISTVIEWMASSACTION',
                        'linklabel' => 'LBL_SOCIAL_INTEGRATION_REQUEST_SHARE_ZALO_CONTACT_INFO',
                        'linkurl' => 'javascript:SocialHandler.triggerRequestShareZaloContactInfo();',
                        'linkicon' => ''
                    );
                    
                    $massActionLinks['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($shareZaloContactInfoRequestLink);
                }
            }
        }

		// Mautic. Added by Phuc on 2020.02.24
		if (!isForbiddenFeature('MauticIntegration') && CPMauticIntegration_Config_Helper::isActiveModule($moduleName)) {	// Modified condition by Hieu Nguyen on 2021-08-27
			$mauticAddToSegmentLink = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_MAUTIC_ADD_TO_SEGMENT',
                'linkurl' => 'javascript:MauticHelper.addToMauticSegment();',
				'linkicon' => ''
			];
            
			$massActionLinks['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($mauticAddToSegmentLink);

			$mauticUpdateStageLink = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_MAUTIC_UPDATE_MAUTIC_STAGE',
                'linkurl' => 'javascript:MauticHelper.updateMauticStage();',
				'linkicon' => ''
            ];
            
			$massActionLinks['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($mauticUpdateStageLink);
		}
		
        // Transfer
		if ($currentUserModel->hasModuleActionPermission($moduleModel->getId(), 'EditView')) {
			$massActionLink = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_TRANSFER_OWNERSHIP',
				'linkurl' => 'javascript:Vtiger_List_Js.triggerTransferOwnership("'. getMassActionUrl('transfer_ownership', $moduleName) .'")',
				'linkicon' => ''
            ];

			$massActionLinks['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);		
		}

		return $massActionLinks;
	}

	/**
	 * Function to get the list of listview links for the module
	 * @param <Array> $linkParams
	 * @return <Array> - Associate array of Link Type to List of Vtiger_Link_Model instances
	 */
	function getListViewLinks($linkParams) {
		$links = parent::getListViewLinks($linkParams);

		$index=0;
		foreach($links['LISTVIEWBASIC'] as $link) {
			if($link->linklabel == 'Send SMS') {
				unset($links['LISTVIEWBASIC'][$index]);
			}
			$index++;
		}
		return $links;
	}

    // Override this function by Hieu Nguyen on 2020-05-21
    public function getListViewHeaders() {
        $headers = parent::getListViewHeaders();

        // Change this field type into text field so that it can be search as a text field
        if ($headers['chat_app']) $headers['chat_app']->fieldDataType = 'text';

        return $headers;
    }
}