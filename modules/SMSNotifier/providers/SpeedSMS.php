<?php
	/*
	*	SpeedSMS Provider
	*	Author: Tung Bui
	*	Date: 2018-06-29
	*	Purpose: to provide helper class for sending SMS with REST APIs
	*/

    require_once('modules/SMSNotifier/BaseProvider.php');

	class SMSNotifier_SpeedSMS_Provider extends SMSNotifier_Base_Provider {

		function __construct() {
			$this->serviceURI = "https://api.speedsms.vn/index.php";
			$this->requiredParams = [
                ['name' => 'brandname', 'label' => 'Brandname', 'type' => 'text'],
                ['name' => 'token', 'label' => 'Token', 'type' => 'password'],
            ];
		}

		public function getName() {
			return 'SpeedSMS';
		}

		public function getServiceURL($type = false) {
            switch (strtoupper($type)) {
                case self::SERVICE_SEND: return $this->serviceURI;
                default: return false;
            }
        }

        public function prepareParameters() {
            $params = array(
                'token' => $this->parameters['token'],
                'alias' => $this->parameters['brandname'],
                'sms_type' => '3', 
                'sender' => $this->parameters['brandname']
            );

            return $params;
        }

		public function send($message, $toNumbers) {
            global $smsConfig;
            $params = $this->prepareParameters();
			$results = array();

			foreach ($toNumbers as $number => $customerId) {
                $number = $this->correctPhoneNumber($number);
                $populatedMsg = populateTemplateWithRecordData($message, $customerId);   // Replace variables
                $populatedMsg = unUnicode($populatedMsg); // SMS message does not support unicode character
                
                // Prepare api params
                $params = array_merge($params, [
                    'to' => [$number], 
                    'content' => $populatedMsg, 
                ]);

                $params = json_encode($params);
				$headers = array('Content-type: application/json');
				$url = $this->serviceURI . '/sms/send';
				$http = curl_init($url);

				curl_setopt($http, CURLOPT_HEADER, false);
				curl_setopt($http, CURLOPT_CUSTOMREQUEST, "POST");
				curl_setopt($http, CURLOPT_POSTFIELDS, $params);
				curl_setopt($http, CURLOPT_SSL_VERIFYHOST, 2);
				curl_setopt($http, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt($http, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($http, CURLOPT_VERBOSE, 0);
				curl_setopt($http, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
				curl_setopt($http, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
				curl_setopt($http, CURLOPT_HTTPHEADER, $headers);
				curl_setopt($http, CURLOPT_USERPWD, $params['token'] . ':x');

				$result = curl_exec($http);
                $err = curl_error($http);
				
                curl_close($http);

				$response = json_decode($result, true);
				$responseData = $response["data"];

                // Save debug log
                if ($smsConfig['debug'] == true) {
                    $requestParams = [
                        'params' => $params, 
                        'response' => $result, 
                        'error' => $err
                    ];
				    saveLog('SMS', '[SMSNotifier_Base_Provider::send] Request param', $requestParams);
                }

				//Result
				$result = array(
					'to' => $number,
					'id' => $responseData['tranId'],
                    'message' => $populatedMsg,  // Return populated message
					'status' => $response["code"] == '00' ? self::MSG_STATUS_DELIVERED : self::MSG_STATUS_FAILED,
                    'statusmessage' => $response["code"] == '00' ? "" : $response["message"], 
                    'customer_id' => $customerId,   
					'error' => $response["status"] != "success" || $response["code"] != '00'
				);

				$results[] = $result;
			}

			return $results;
        }

        public function getProviderEditFieldTemplateName() {
            return 'BaseProviderEditFields.tpl';    // This template will not show username and password field
        }
    }
?>