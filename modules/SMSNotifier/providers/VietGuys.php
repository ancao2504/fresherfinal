<?php
	/*
	*	VietGuys_Provider
	*	Author: Hieu Nguyen
	*	Date: 2018-06-29
	*	Purpose: to provide helper class for sending SMS with VietGuys REST APIs
	*/

    require_once('modules/SMSNotifier/BaseProvider.php');
    require_once('libraries/UUID/UUID.php');

	class SMSNotifier_VietGuys_Provider extends SMSNotifier_Base_Provider {

		function __construct() {
			$this->serviceURI = 'https://cloudsms.vietguys.biz:4438/api/index.php';
			$this->requiredParams = [
                array('name' => 'brandname', 'label' => 'Brandname', 'type' => 'text'),
                array('name' => 'prepaid', 'label' => 'Prepaid', 'type' => 'radio')
            ];
		}

		public function getName() {
			return 'VietGuys';
		}

		public function getServiceURL($type = false) {
            if ($this->parameters['prepaid'] == '1') {
                $this->serviceURI = 'https://cloudsms4.vietguys.biz:4438/api/index.php';
            }

            switch (strtoupper($type)) {
                case self::SERVICE_SEND: return $this->serviceURI;
                default: return false;
            }
        }

        public function prepareParameters() {
            $params = array(
                'u' => $this->username, 
                'pwd' => $this->password,
                'from' => $this->parameters['brandname'],
            );

            return $params;
        }

		public function send($message, $toNumbers) {
			$serviceURL = $this->getServiceURL(self::SERVICE_SEND);
            $client = $this->getRestClient($serviceURL, null, true);
            $params = $this->prepareParameters();
			$results = array();

			foreach ($toNumbers as $number => $customerId) {
                $number = $this->correctPhoneNumber($number);
                $msgId = UUID::v4();    // This provider required an UUID
                $populatedMsg = populateTemplateWithRecordData($message, $customerId);   // Replace variables
                $populatedMsg = unUnicode($populatedMsg); // SMS message does not support unicode character

				$params['bid'] = $msgId;
				$params['phone'] = $number;
				$params['sms'] = $populatedMsg;

				$response = $this->callRestAPI($client, $params, false, false);

				$result = array(
					'to' => $number,
					'id' => $msgId,
                    'message' => $populatedMsg,  // Return populated message
					'status' => $response != '' ? self::MSG_STATUS_DELIVERED : self::MSG_STATUS_FAILED,
                    'statusmessage' => '',  // TODO: check response message later when we have customer using this provider
                    'customer_id' => $customerId,                     
					'error' => $response == false || $response == ''
				);

				$results[] = $result;
			}

			return $results;
        }
    }
?>