<?php

/*
	Action CampaignAjax
	Author: Hieu Nguyen
	Date: 2019-07-17
	Purpose: handle sending social message via ajax
*/

class Campaigns_CampaignAjax_Action  extends Vtiger_Action_Controller {

	function __construct() {
		$this->exposeMethod('loadMetadata');
		$this->exposeMethod('loadSMSAndOTTTemplates');
		$this->exposeMethod('sendSMSAndOTTMessage');
	}

	function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->get('module');

		if ($request->get('channel') == 'Zalo' && isForbiddenFeature('ZaloIntegration')) {
			throw new AppException(vtranslate($moduleName, $moduleName) .' '. vtranslate('LBL_NOT_ACCESSIBLE'));
		}
	}

	function process(Vtiger_Request $request) {
		$mode = $request->getMode();

		if (!empty($mode) && $this->isMethodExposed($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	function loadMetadata(Vtiger_Request $request) {
		$campaignId = $request->get('campaign_id');
		$channel = $request->get('channel');

		$metadata = Campaigns_Logic_Helper::getMetadataForSMSAndOTTMessage($campaignId, $channel);

		// Respond
		$result = ['metadata' => $metadata];
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	function sendSMSAndOTTMessage(Vtiger_Request $request) {
		$channel = $request->get('channel');
		$campaignId = $request->get('campaign_id');
		$phoneFields = $request->get('phone_fields');
		$message = $request->get('message');
		$targetLists = $request->get('target_lists');
		$data = $request->getAllPurified();
		$result = [];

		// Check request
		if (empty($channel) || empty($campaignId) || empty($phoneFields) || empty($message) || empty($targetLists)) {
			return;
		}

		// Check active gateway
		if ($channel == 'SMS') {
			$activeGateway = SMSNotifier_Provider_Model::getActiveGateway();
		}
		else {
			$activeGateway = CPOTTIntegration_Gateway_Model::getActiveGateway($channel);
		}

		if (!$activeGateway) return;

		// Save queue
		try {
			$result['success'] = true;

			if (!empty($data['email_to'])) {
				$emailResult = Campaigns_Logic_Helper::sendSMSPromotionRequestToTelco($campaignId, $targetLists, $phoneFields, $data);
				
				if (!$emailResult) {
					$result['success'] = false;
				}
			}
			else {
				$notiferId = Campaigns_Data_Model::addNewSMSOTTMessageNotifier(['campaign_id' => $campaignId, 'channel' => $channel, 'message' => $message]);
				Campaigns_Logic_Helper::putToSMSOTTMessageQueue($data, $notiferId, $targetLists);
			}
		}
		catch (Exception $ex) {
			$result['success'] = false;
		}

		// Respond
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}