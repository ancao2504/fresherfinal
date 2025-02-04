<?php
	/*
	*	CMCTelecom_Provider
	*	Author: Hieu Nguyen
	*	Date: 2018-09-21
	*	Purpose: to provide helper class for sending SMS with CMC Telecom REST APIs
	*/

    require_once('modules/SMSNotifier/BaseProvider.php');
    require_once('libraries/Bcrypt/Bcrypt.php');
    require_once('libraries/UUID/UUID.php');

	class SMSNotifier_CMCTelecom_Provider extends SMSNotifier_Base_Provider {

		function __construct() {
			$this->serviceURI = 'http://124.158.14.49/CMCTelecom/api/sms';
			$this->requiredParams = array(
                array('name' => 'brandname', 'label' => 'Brandname', 'type' => 'text')
            );
		}

		public function getName() {
			return 'CMCTelecom';
		}

		public function getServiceURL($type = false) {
            switch (strtoupper($type)) {
                case self::SERVICE_SEND: return $this->serviceURI . '/send';
                default: return false;
            }
        }

        public function prepareParameters() {
            $params = array(
                'user' => $this->username, 
                'pass' => Bcrypt::hash($this->password),
                'brandName' => $this->parameters['brandname'],
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
                $msgId = UUID::v4();    // This provider required an UUID
                $populatedMsg = populateTemplateWithRecordData($message, $customerId);   // Replace variables
                $populatedMsg = unUnicode($populatedMsg); // SMS message does not support unicode character

				$params['messageId'] = $msgId;
				$params['phoneNumber'] = $number;
				$params['message'] = $populatedMsg;

				$response = $this->callRestAPI($client, $params, true, true);

				$result = array(
					'to' => $number,
					'id' => $msgId,
                    'message' => $populatedMsg,  // Return populated message
					'status' => ($response && $response->data->status == '1') ? self::MSG_STATUS_DELIVERED : self::MSG_STATUS_FAILED,
                    'statusmessage' => '',  // TODO: check response message later when we have customer using this provider
                    'customer_id' => $customerId,                    
					'error' => $response == false || empty($response->data) || $response->data->status != '1'
				);

				$results[] = $result;
			}

			return $results;
        }
    }
?>