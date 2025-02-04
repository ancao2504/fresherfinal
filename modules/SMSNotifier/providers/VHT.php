<?php
	/*
	*	VHT_Provider
	*	Author: Hieu Nguyen
	*	Date: 2018-06-29
	*	Purpose: to provide helper class for sending SMS with VHT REST APIs
	*/

    require_once('modules/SMSNotifier/BaseProvider.php');
    require_once('libraries/UUID/UUID.php');

	class SMSNotifier_VHT_Provider extends SMSNotifier_Base_Provider {

		function __construct() {
			$this->serviceURI = 'http://sms3.vht.com.vn/ccsms/Sms/SMSService.svc';
			$this->requiredParams = array(
                array('name' => 'brandname', 'label' => 'Brandname', 'type' => 'text'),
                array('name' => 'api_key', 'label' => 'API Key', 'type' => 'text'),
                array('name' => 'api_secret', 'label' => 'API Secret', 'type' => 'text'),
            );
		}

		public function getName() {
			return 'VHT';
		}

		public function getServiceURL($type = false) {
			switch (strtoupper($type)) {
				case self::SERVICE_SEND: return $this->serviceURI . '/ccsms/json';
				default: return false;
			}
        }

        public function prepareParameters() {
            $params = array(
                'submission' => array(
                    'api_key' => $this->parameters['api_key'], 
                    'api_secret' => $this->parameters['api_secret'],
                ),
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

				$params['submission']['sms'] = array(
                    array(
                        'id' => $msgId,
                        'brandname' => $this->parameters['brandname'],
                        'to' => $number,
                        'text' => $populatedMsg
                    )
                );

				$response = $this->callRestAPI($client, $params);

				$result = array(
					'to' => $number,
					'id' => $msgId,
                    'message' => $populatedMsg,  // Return populated message
					'status' => $response->submission->sms[0]->status == 0 ? self::MSG_STATUS_DELIVERED : self::MSG_STATUS_FAILED,
                    'statusmessage' => '',  // TODO: check response message later when we have customer using this provider
                    'customer_id' => $customerId,     
					'error' => $response == false || $response->submission->sms[0]->status != 0
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