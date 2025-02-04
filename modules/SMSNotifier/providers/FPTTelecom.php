<?php
	/*
	*	FPT SMS Provider
	*	Author: Tin Bui
	*	Date: 2021-08-19
	*	Purpose: to provide helper class for sending SMS with REST APIs
	*/

    require_once('modules/SMSNotifier/BaseProvider.php');

	class SMSNotifier_FPTTelecom_Provider extends SMSNotifier_Base_Provider {

		function __construct() {
			$this->serviceURI = 'https://app.sms.fpt.net';
			$this->requiredParams = [
                ['name' => 'brandname', 'label' => 'Brandname', 'type' => 'text'],
                ['name' => 'client_id', 'label' => 'Client ID', 'type' => 'password'],
                ['name' => 'client_secret', 'label' => 'Secret', 'type' => 'password'],
            ];
		}

		public function getName() {
			return 'FPTTelecom';
		}

		public function getServiceURL($type = false) {
            switch (strtoupper($type)) {
                case self::SERVICE_SEND: return $this->serviceURI . '/api/push-brandname-otp';
                case self::SERVICE_AUTH: return $this->serviceURI . '/oauth2/token';
                default: return false;
            }
        }

        private function generateUniqueKey($length = 32) {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $string = '';

            for ($i = 0; $i < $length; $i++) {
                $string .= $characters[mt_rand(0, strlen($characters) - 1)];
            }

            return $string;
        }

        public function prepareParameters() {
            $params = array(
                'BrandName' => $this->parameters['brandname'],
                'session_id' => $this->generateUniqueKey(),
            );
            return $params;
        }

        public function getAccessToken() {
            $serviceURL = $this->getServiceURL(self::SERVICE_AUTH);
            $client = $this->getRestClient($serviceURL);
            $params = [
                'client_id' => $this->parameters['client_id'],
                'client_secret' => $this->parameters['client_secret'],
                'session_id' => $this->generateUniqueKey(),
                'scope' => 'send_brandname_otp send_brandname',
                'grant_type' => 'client_credentials'
            ];

            $response = $this->callRestAPI($client, $params);

            if (!empty($response->error)) {
                return [
                    'success' => 0,
                    'message' => !empty($response->error_description) ? $response->error_description : 'Error when get access token'
                ];
            }
            return [
                'success' => 1,
                'access_token' => $response->access_token,
                'expires_in' => intval($response->expires_in),
                'token_type' => $response->token_type,
                'scope' => 'send_brandname_otp send_brandname'
            ];
        }

		public function send($message, $toNumbers) {
            // get access token first
            $result = $this->getAccessToken();
            if (!$result['success']) {
                return [
                    'success' => 0,
                    'message' => $result['message']
                ];
            }

            $token = $result['access_token'];
			$serviceURL = $this->getServiceURL(self::SERVICE_SEND);
			$results = array();

			foreach ($toNumbers as $number => $customerId) {
                $number = $this->correctPhoneNumber($number);
                $populatedMsg = populateTemplateWithRecordData($message, $customerId);   // Replace variables
                $populatedMsg = unUnicode($populatedMsg); // SMS message does not support unicode character
                $encodedMsg = base64_encode($populatedMsg);

                $client = $this->getRestClient($serviceURL);
                $params = $this->prepareParameters();
                $params = array_merge($params, [
                    'access_token' => $token,
                    'Phone' => $number,
                    'Message' => $encodedMsg
                ]);
				$response = $this->callRestAPI($client, $params);

				$result = [
					'to' => $number,
					'id' => $response->MessageId, // Provider id
                    'message' => $populatedMsg,
					'status' => !empty($response->Error) ? self::MSG_STATUS_FAILED : self::MSG_STATUS_DISPATCHED,
                    'statusmessage' => !empty($response->Error_description) ? $response->Error_description : '',
                    'customer_id' => $customerId,                       
					'error' => $response == false || !empty($response->Error)
                ];

				$results[] = $result;
			}

			return $results;
        }
                
        public function getProviderEditFieldTemplateName() {
            return 'BaseProviderEditFields.tpl';    // This template will not show username and password field
        }
    }
?>