<?php

/*
	Class ZaloUtils
	Author: Hieu Nguyen
	Date: 2019-07-03
	Purpose: To provide util functions for handling integration with Zalo
*/

require_once('include/utils/WebhookUtils.php');
require_once('vendor/autoload.php');

use Zalo\Zalo;
use Zalo\FileUpload\ZaloFile;

class ZaloUtils extends WebhookUtils {

	static $logger = 'SOCIAL_INTEGRATION';

	static function getZaloClient() {
		$config = array(
			'app_id' => '1',
			'app_secret' => '1',
			'callback_url' => '1'
		);
		
		return new Zalo($config);
	}

	static function getZaloFile(string $filePath) {
		return new ZaloFile($filePath);
	}

	static function callZaloApi(Zalo $zaloClient, string $method, string $path, string $accessToken, array $params = []) {
		global $socialConfig;
		$serviceUrl = $socialConfig['zalo']['service_url'];
		$endpointUrl = $serviceUrl . $path;
		$result = null;

		try {
			if ($method == 'GET') {
				$response = $zaloClient->get($endpointUrl, $accessToken, $params);
			}
			else {
				$response = $zaloClient->post($endpointUrl, $accessToken, $params);
			}

			$result = $response->getDecodedBody();
		}
		catch (Exception $ex) {
			if (get_class($ex) == 'Zalo\Exceptions\ZaloResponseException') {
				$result = $ex->getResponseData();
			}
			else {
				$result = ['error' => true, 'message' => $ex->getMessage()];
			}
		}

		self::saveDebugLog("[Zalo] Call Zalo API: {$method} {$endpointUrl}", [], $params, $result);

		// Update following status to false when the Zalo return this error
		if ($result['error'] !== 0 && $result['message'] == 'User has not followed OA' && !empty($zaloClient->oaId)) {
			$customerSocialId = '';

			// Param from API send message
			if (!empty($params['recipient']['user_id'])) {
				$customerSocialId = $params['recipient']['user_id'];
			}
			// Param from API get profile
			else if (!empty($params['data']) && is_string($params['data'])) {
				$data = json_decode($params['data'], true);

				if (!empty($data['user_id'])) {
					$customerSocialId = $data['user_id'];
				}
			}

			if (!empty($customerSocialId)) {
				CPSocialIntegration_Data_Model::updateSocialIdentifierMappingFollowingStatus($customerSocialId, 'Zalo', $zaloClient->oaId, false);
			}
		}

		return $result;
	}

	// Return format: {"error": int, "message": String, "data": {"attachment_id": String}}
	static function uploadImage($oaId, $filePath, bool $isGIF = false) {
		$zalo = self::getZaloClient();
		$accessToken = self::retrieveAccessToken($oaId);
		$params = ['file' => new ZaloFile($filePath)];
		$apiPath = 'oa/upload/image';
		
		if ($isGIF) {
			$apiPath = 'oa/upload/gif';
		}

		return self::callZaloApi($zalo, 'POST', $apiPath, $accessToken, $params);
	}

	// Return format: {"error": int, "message": String, "data": {"token": String}}
	static function uploadFile($oaId, $filePath) {
		$zalo = self::getZaloClient();
		$accessToken = self::retrieveAccessToken($oaId);
		$params = ['file' => new ZaloFile($filePath)];

		return self::callZaloApi($zalo, 'POST', 'oa/upload/file', $accessToken, $params);
	}

	static function storeAccessToken($oaId, array $tokenInfo) {
		$tokens = Settings_Vtiger_Config_Model::loadConfig('zalo_oa_tokens', true) ?? [];
		$tokenInfo['oa_id'] = (string) $oaId;	// Convert to string to prevent bug PHP return 404732131684315247 as 404732131684315260 :(
		$tokenInfo['issue_time'] = date('Y-m-d H:i:s');
		$tokenInfo['enabled'] = true;	// Enabled by default for new OA
		$tokens[$oaId] = $tokenInfo;
		Settings_Vtiger_Config_Model::saveConfig('zalo_oa_tokens', $tokens);
		return true;
	}

	static function retrieveOAInfo($oaId) {
		static $cache = [];
		if (!empty($cache[$oaId])) return $cache[$oaId];

		$tokens = Settings_Vtiger_Config_Model::loadConfig('zalo_oa_tokens', true) ?? [];

		if (!empty($tokens[$oaId])) {
			$oaInfo = array_merge($tokens[$oaId], ['id' => $oaId]);
			$cache[$oaId] = $oaInfo;
			return $oaInfo;
		}

		return null;
	}

	// Return full access token info, including access_token, refresh_token and expires_in attributes
	static function retrieveAccessTokenInfo($oaId) {
		static $cache = [];
		if (!empty($cache[$oaId])) return $cache[$oaId];
		$tokens = Settings_Vtiger_Config_Model::loadConfig('zalo_oa_tokens', true) ?? [];

		if (!empty($tokens) && !empty($tokens[$oaId])) {
			$cache[$oaId] = $tokens[$oaId];
			return $tokens[$oaId];
		}

		return null;
	}

	// Return only the access_token attribute to call APIs
	static function retrieveAccessToken($oaId) {
		$accessTokenInfo = self::retrieveAccessTokenInfo($oaId);
		if (!empty($accessTokenInfo)) return $accessTokenInfo['access_token'];
		return null;
	}

	static function getZaloOAList(bool $shopOnly = false, bool $countFollowers = false, bool $storeOAInfo = false, bool $enabledOnly = true) {
		global $socialConfig;
		$tokens = Settings_Vtiger_Config_Model::loadConfig('zalo_oa_tokens') ?? [];
		$zalo = self::getZaloClient();
		$zaloOAList = [];

		foreach ($tokens as $oaId => $token) {
			// Skip this non-shop OA if shop is required
			if ($shopOnly && !$token->is_shop) {
				continue;
			}

			// Skip this disabled OA if enabled is required
			if ($enabledOnly && !$token->enabled) {
				continue;
			}

			// Get OA info
			$result = self::callZaloApi($zalo, 'GET', 'oa/getoa', $token->access_token ?? '', []);

			if ($result && $result['error'] === 0) {
				// Get followers count
				$followersCount = 0;

				if ($countFollowers == true) {
					$followersParams = ['data' => json_encode(['offset' => 0, 'count' => 1])];
					$followersResult = self::callZaloApi($zalo, 'GET', 'oa/getfollowers', $token->access_token, $followersParams);

					if ($followersResult && $followersResult['error'] === 0) {
						$followersCount = $followersResult['data']['total'];
					}
				}

				$zaloOAList[] = [
					'id' => $oaId,
					'name' => $result['data']['name'],
					'avatar' => $result['data']['avatar'],
					'token_issue_time' => $token->issue_time, 
					'enabled' => $token->enabled,
					'followers_count' => $followersCount,
					'is_shop' => $token->is_shop, // Added by Phu Vo on 2019.07.24
					'token_status' => 'active',
				];

				// Store OA info back to the config
				if ($storeOAInfo) {
					$tokens->$oaId->name = $result['data']['name'];
					$tokens->$oaId->avatar = $result['data']['avatar'];
					Settings_Vtiger_Config_Model::saveConfig('zalo_oa_tokens', $tokens);
				}
			}
			else {
				$zaloOAList[] = [
					'id' => $oaId,
					'name' => $oaId,
					'avatar' => 'resources/images/zalo.png',
					'token_issue_time' => $token->issue_time,
					'enabled' => $token->enabled,
					'followers_count' => 0,
					'is_shop' => $token->is_shop, // Added by Phu Vo on 2019.07.24
					'error_msg' => $result['message'],
					'token_status' => 'expired',
				];
			}
		}

		return $zaloOAList;
	}

	static function getZaloOAMessage(array $data) {
		global $socialConfig;

		// Request share info
		if ($data['message_type'] == 'request_info') {
			$senderId = $data['sender_id'];
			$requestConfig = $socialConfig['zalo']['request_share_contact_info'][$senderId];

			$message = [
				'text' => 'Request contact info',
				'attachment' => [
					'type' => 'template',
					'payload' => [
						'template_type' => 'request_user_info',
						'elements' => [
							0 => [
								'title' => $requestConfig['title'],
								'subtitle' => $requestConfig['message'],
								'image_url' => $requestConfig['image_url'],
							]
						]
					]
				]
			];
		}
		// Broadcast article message
		else if ($data['message_type'] == 'broadcast_article') {
			$socialArticleId = $GLOBALS['social_article_data']['social_id'];

			$message = [
				'attachment' => [
					'type' => 'template',
					'payload' => [
						'template_type' => 'media',
						'elements' => [
							0 => [
								'media_type' => 'article',
								'attachment_id' => $socialArticleId,
							]
						]
					]
				]
			];
		}
		// Message with template
		else if (!empty($data['template_id'])) { 
			$message = self::getZaloOAMessageFromTemplate($data['template_id'], $data['message']);
		}
		// Instant message
		else {
			$message = ['text' => $data['message']];
		}
		
		return $message;
	}

	static function getZaloOAMessageFromTemplate($templateId, string $message = '') {
		$templateModal = Vtiger_Record_Model::getInstanceById($templateId, 'CPSocialMessageTemplate');
		$messageTemplate = json_decode(base64_decode($templateModal->get('message_content')));
		$coverData = $messageTemplate->cover;
		$messageType = $templateModal->get('cpsocialmessagetemplate_content_type');

		// Message type = list
		if ($messageType == 'list') {
			$itemsData = $messageTemplate->items;

			// First item in the list is the main cover
			$items = [
				0 => [
					'title' => $coverData->title,
					'subtitle' => $coverData->description,
					'image_url' => $coverData->image_url,
					'default_action' => [
						'type' => 'oa.open.url',
						'url' => $coverData->click_url
					],
				]
			];

			foreach ($itemsData as $item) {
				$items[] = [
					'title' => $item->title,
					'image_url' => $item->icon_url,
					'default_action' => [
						'type' => 'oa.open.url',
						'url' => $item->data
					],
				];
			}

			$message = [
				'attachment' => [
					'type' => 'template',
					'payload' => [
						'template_type' => 'list',
						'elements' => $items,
					]
				]
			];
		}
		// Message type = buttons
		else if ($messageType == 'buttons') {
			$buttonsData = $messageTemplate->buttons;
			$buttons = [];

			foreach ($buttonsData as $button) {
				$buttons[] = [
					'title' => $button->title,
					'type' => 'oa.open.url',
					'payload' => ['url' => $button->data]
				];
			}

			$message = [
				'attachment' => [
					'type' => 'template',
					'payload' => [
						'template_type' => 'list',
						'elements' => [
							0 => [
								'title' => $coverData->title,
								'subtitle' => $coverData->description,
								'image_url' => $coverData->image_url,
								'default_action' => [
									'type' => 'oa.open.url',
									'url' => $coverData->click_url
								],
							]
						],
						'buttons' => $buttons,
					]
				]
			];
		}
		// Message type = text
		else {
			$message = [
				'text' => $message
			];
		}

		return $message;
	}

	static function getZaloFormFields($oaId, $formId) {
		$zaloResult = self::getZaloFormData($oaId, $formId, 0, 1);
		
		if ($zaloResult && $zaloResult['error'] === 0) {
			return $zaloResult['data']['questions'];
		}

		return [];
	}

	static function getZaloFormData($oaId, $formId, $offset, $limit) {
		$zalo = self::getZaloClient();
		$accessToken = self::retrieveAccessToken($oaId);
		
		$params = [
			'form_id' => $formId,
			'from_time' => (new DateTime())->sub(new DateInterval('P30D'))->getTimestamp(),
			'to_time' => (new DateTime())->getTimestamp(),
			'offset' => $offset,
			'limit' => $limit
		];

		return self::callZaloApi($zalo, 'GET', 'oa/form/get', $accessToken, $params);
	}

	static function getZaloErrorForLogging(array $zaloResult) {
		if (!empty($zaloResult)) {
			return "Error Code: {$zaloResult['error']}, Error Message: {$zaloResult['message']}";
		}

		return 'Unknown error';
	}

	static function handleWebhookEvents(array $data) {
		if (!CPSocialIntegration_Config_Helper::isZaloEnabled()) return;

		// We have to ignore this event as Zalo send duplicate event at the same time when customer accepted sharing contact info that cause CRM created double record
		if ($data['event_name'] == 'user_send_text' && strpos($data['message']['text'], 'Bạn đã gởi thông tin cho OA') !== false) {
			saveLog('SOCIAL_INTEGRATION', '[ZaloUtils::handleWebhookEvents] Ignore this event to prevent error when customer accepted sharing contact info!');
			exit;
		}

		// Follow
		if ($data['event_name'] === 'follow') {
			saveLog('SOCIAL_INTEGRATION', '[ZaloUtils::handleWebhookEvents] Customer followed OA', $data);
			CPSocialIntegration_ZaloEvent_Helper::handleEventCustomerFollowOA($data);
		}

		// Unfollow
		if ($data['event_name'] === 'unfollow') {
			saveLog('SOCIAL_INTEGRATION', '[ZaloUtils::handleWebhookEvents] Customer unfollowed OA', $data);
			CPSocialIntegration_ZaloEvent_Helper::handleEventCustomerUnfollowOA($data);
		}

		// Handle event customer asking for product
		if ($data['event_name'] === 'user_asking_product') {
			saveLog('SOCIAL_INTEGRATION', '[ZaloUtils::handleWebhookEvents] Customer asked for product info', $data);
			CPSocialIntegration_ZaloEvent_Helper::handleEventCustomerAskingProduct($data);
		}

		// Handle event customer send message to OA
		if (CPSocialIntegration_ZaloEvent_Helper::isCustomerSendMessageEvent($data)) {
			saveLog('SOCIAL_INTEGRATION', '[ZaloUtils::handleWebhookEvents] Customer sent a message', $data);
			CPSocialIntegration_ZaloEvent_Helper::handleEventCustomerSendMessage($data);
		}

		// Handle event AO send message to customer. [Chatbox] Added by Hieu Nguyen on 2020-01-06
		if (CPSocialIntegration_ZaloEvent_Helper::isOASendMessageEvent($data)) {
			saveLog('SOCIAL_INTEGRATION', '[ZaloUtils::handleWebhookEvents] OA sent a message to customer', $data);
			CPSocialIntegration_ZaloEvent_Helper::handleEventOASendMessage($data);
		}

		// Handle event customer read OA message
		if ($data['event_name'] === 'user_seen_message') {
			saveLog('SOCIAL_INTEGRATION', '[ZaloUtils::handleWebhookEvents] Customer seen message sent by OA', $data);
			CPSocialIntegration_ZaloEvent_Helper::handleEventCustomerSeenMessage($data);
		}

		// Handle event customer access wifi authenticated by Zalo
		if ($data['event_name'] === 'user_authentication') {
			saveLog('SOCIAL_INTEGRATION', '[ZaloUtils::handleWebhookEvents] Customer accessed Zalo Wifi', $data);
			CPSocialIntegration_ZaloEvent_Helper::handleEventCustomerAccessWifiAuthenticatedByZalo($data);
		}

		// Handle event customer accept sharing contact info
		if ($data['event_name'] === 'user_submit_info') {
			saveLog('SOCIAL_INTEGRATION', '[ZaloUtils::handleWebhookEvents] Customer accepted request share contact info', $data);
			CPSocialIntegration_ZaloEvent_Helper::handleEventCustomerSharingContactInfo($data);
		}
	}

	static function saveDebugLog(string $description, array $headers = null, array $input = null, array $response = null) {
		global $socialConfig;

		if ($socialConfig['zalo']['debug'] == true) {
			parent::saveLog($description, $headers, $input, $response);
		}
	}
}

class ZaloOauthUtils {

	private static function callOauthApi($secretKey, array $params) {
		$serviceUrl = 'https://oauth.zaloapp.com/v4/oa/access_token';
		$headers = [
    		'Content-Type: application/x-www-form-urlencoded',
			'secret_key: ' . $secretKey,
		];

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $serviceUrl);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_TIMEOUT, 10);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
		$contents = curl_exec($curl);
		curl_close($curl);

		if (!empty($contents)) {
			return json_decode($contents, true);
		}

		return [];
	}

	static function getNewAccessToken($appId, $secretKey, $authCode, $verifyCode) {
		$params = [
			'app_id' => $appId,
			'code' => $authCode,
			'grant_type' => 'authorization_code',
			'code_verifier' => $verifyCode,
		];
		
		$result = self::callOauthApi($secretKey, $params);
		return $result;
	}

	static function renewAccessToken($appId, $secretKey, $refreshToken) {
		$params = [
			'app_id' => $appId,
			'refresh_token' => $refreshToken,
			'grant_type' => 'refresh_token',
		];
		
		$result = self::callOauthApi($secretKey, $params);
		return $result;
	}
}