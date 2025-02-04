<?php
	/*
	*	Mitek SMS Provider
	*	Author: Tin Bui
	*	Date: 2021-08-19
	*	Purpose: to provide helper class for sending SMS with REST APIs
	*/

    require_once('modules/SMSNotifier/BaseProvider.php');

	class SMSNotifier_Mitek_Provider extends SMSNotifier_Base_Provider {

		function __construct() {
			$this->serviceURI = 'http://api.misms.vn:8668';
			$this->requiredParams = [
                ['name' => 'brandname', 'label' => 'Brandname', 'type' => 'text'],
                ['name' => 'api_key', 'label' => 'API Key', 'type' => 'password']
            ];
		}

		public function getName() {
			return 'Mitek';
		}

		public function getServiceURL($type = false) {
            switch (strtoupper($type)) {
                case self::SERVICE_SEND: return $this->serviceURI . '/v1/sms/send';
                default: return false;
            }
        }

        public function prepareParameters() {
            $params = array(
                'sender' => $this->parameters['brandname'],
                'type_send' => 'CSKH'
            );
            return $params;
        }

        protected function getRestClient($serviceURL, $headers = array(), $noHeaders = false) {
			if ($noHeaders) {
				$headers = array('cache-control: no-cache');
			}

			$curl = curl_init();
			
            curl_setopt_array($curl, array(
                CURLOPT_URL => $serviceURL,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_HTTPHEADER => $headers,
            ));

            return $curl;
        }

		public function send($message, $toNumbers) {
			$serviceURL = $this->getServiceURL(self::SERVICE_SEND);
			$results = array();

			foreach ($toNumbers as $number => $customerId) {
                $number = $this->correctPhoneNumber($number);
                $populatedMsg = populateTemplateWithRecordData($message, $customerId);   // Replace variables
                $populatedMsg = unUnicode($populatedMsg); // SMS message does not support unicode character

                $params = $this->prepareParameters($number, $populatedMsg);
                $params['receiver'] = $number;
                $params['message'] = $populatedMsg;

                // Prepare headers
                $headers = array('Authorization: Basic ' . $this->parameters['api_key']);

                $client = $this->getRestClient($serviceURL, $headers);
				$response = $this->callRestAPI($client, $params, false);

				$result = [
					'to' => $number,
					'id' => $response->SMSID,
                    'message' => $populatedMsg,  // Return populated message
					'status' => $response->CodeResult == '100' ? self::MSG_STATUS_DISPATCHED : self::MSG_STATUS_FAILED,
                    'statusmessage' => $response->ErrorMessage,
                    'customer_id' => $customerId,                       
					'error' => $response == false || $response->CodeResult != '100'
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