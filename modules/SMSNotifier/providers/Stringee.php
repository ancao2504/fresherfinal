<?php
	/*
	*	Stringee_Provider
	*	Author: Hieu Nguyen
	*	Date: 2018-10-16
	*	Purpose: to provide helper class for sending SMS with Stringee REST APIs
	*/

    require_once('modules/SMSNotifier/BaseProvider.php');
    require_once('libraries/UUID/UUID.php');
    require_once('libraries/PHP-JWT/src/JWT.php');
    use \Firebase\JWT\JWT;

	class SMSNotifier_Stringee_Provider extends SMSNotifier_Base_Provider {

		function __construct() {
			$this->serviceURI = 'https://api.stringee.com/v1/sms';
			$this->requiredParams = array(
                array('name' => 'sid', 'label' => 'Account SID', 'type' => 'text'),
                array('name' => 'key', 'label' => 'Account Key', 'type' => 'text'),
                array('name' => 'brandname', 'label' => 'Brandname', 'type' => 'text')
            );
		}

		public function getName() {
			return 'Stringee';
		}

		public function getServiceURL($type = false) {
            switch (strtoupper($type)) {
                case self::SERVICE_SEND: return $this->serviceURI;
                default: return false;
            }
        }

        public function getToken($params) {
            $now = time();
            
            $payload = array(
                'jti' => $params['sid'] . '-' . $now,
                'iss' => $params['sid'],
                'exp' => $now + 3600,
                'rest_api' => true
            );

            $header = array('cty' => 'stringee-api;v=1');
            $token = JWT::encode($payload, $params['key'], 'HS256', null, $header);

		    return $token;
        }

		public function send($message, $toNumbers) {
			$serviceURL = $this->getServiceURL(self::SERVICE_SEND);
            $token = $this->getToken($this->parameters);
            $client = $this->getRestClient($serviceURL, array('X-STRINGEE-AUTH: ' . $token));
			$results = array();

			foreach ($toNumbers as $number => $customerId) {
                $number = $this->correctPhoneNumber($number);
                $populatedMsg = populateTemplateWithRecordData($message, $customerId);   // Replace variables
                $populatedMsg = unUnicode($populatedMsg); // SMS message does not support unicode character

                $restParams = array(
                    'sms' => array(
                        array(
                            'from' => $this->parameters['brandname'],
                            'to' => $number,
                            'text' => $populatedMsg
                        )
                    )
                );

				$response = $this->callRestAPI($client, $restParams, true, true);

				$result = array(
					'to' => $number,
					'id' => UUID::v4(),
                    'message' => $populatedMsg,  // Return populated message
					'status' => ($response && $response->smsSent == '1') ? self::MSG_STATUS_DELIVERED : self::MSG_STATUS_FAILED,
                    'statusmessage' => '',  // TODO: check response message later when we have customer using this provider
                    'customer_id' => $customerId,     
					'error' => $response == false || $response->smsSent != '1'
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