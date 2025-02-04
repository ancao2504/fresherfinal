<?php
	/*
	*	VMG_Provider
	*	Author: Hieu Nguyen
	*	Date: 2018-06-29
	*	Purpose: to provide helper class for sending SMS with VietGuys REST APIs
	*/

    require_once('modules/SMSNotifier/BaseProvider.php');

	class SMSNotifier_VMG_Provider extends SMSNotifier_Base_Provider {

		function __construct() {
			$this->serviceURI = 'http://brandsms.vn:8018/vmgApi';
			$this->requiredParams = array(
                array('name' => 'brandname', 'label' => 'Brandname', 'type' => 'text')
            );
		}

		public function getName() {
			return 'VMG';
		}

		public function getServiceURL($type = false) {
            switch (strtoupper($type)) {
                case self::SERVICE_SEND: return $this->serviceURI;
                default: return false;
            }
        }

        public function prepareParameters() {
            $params = array(
                'authenticateUser' => $this->username, 
                'authenticatePass' => $this->password,
                'alias' => $this->parameters['brandname'],
            );

            return $params;
        }

		public function send($message, $toNumbers) {
			$serviceURL = $this->getServiceURL(self::SERVICE_SEND);
            $client = $this->getRestClient($serviceURL);
            $params = $this->prepareParameters();
			$results = array();

			foreach ($toNumbers as $number => $customerId) {
                $number = $this->correctPhoneNumber($number);
                $populatedMsg = populateTemplateWithRecordData($message, $customerId);   // Replace variables
                $populatedMsg = unUnicode($populatedMsg); // SMS message does not support unicode character
                
				$params['cmdCode'] = 'BulkSendSms';
				$params['msisdn'] = $number;
				$params['message'] = $populatedMsg;
				$params['sendTime'] = '';

				$response = $this->callRestAPI($client, $params, true, true);

				$result = array(
					'to' => $number,
					'id' => $response->messageId,
                    'message' => $populatedMsg,  // Return populated message
					'status' => $response->error_code == '0' ? self::MSG_STATUS_DELIVERED : self::MSG_STATUS_FAILED,
                    'statusmessage' => '',  // TODO: check response message later when we have customer using this provider
                    'customer_id' => $customerId,   
					'error' => $response == false || $response->error_code != '0'
				);

				$results[] = $result;
			}

			return $results;
        }
    }
?>