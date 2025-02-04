<?php
	/*
	*	SouthTelecomSOAP_Provider
	*	Author: Hieu Nguyen
	*	Date: 2018-06-27
	*	Purpose: to provide helper class for sending SMS with SouthTelecom SOAP APIs
	*/

	require_once('modules/SMSNotifier/BaseProvider.php');

	class SMSNotifier_SouthTelecomSOAP_Provider extends SMSNotifier_Base_Provider {

		function __construct() {
			$this->serviceURI = 'http://wcf.worldsms.vn';
			$this->requiredParams = array(
				array('name' => 'Sender', 'label' => 'Brandname', 'type' => 'text')
			);
		}

		public function getName() {
			return 'SouthTelecomSOAP';
		}

		public function getServiceURL($type = false) {
			switch (strtoupper($type)) {
				case self::SERVICE_SEND: return $this->serviceURI . '/apisms.svc?wsdl';
				default: return false;
			}
		}

		public function send($message, $toNumbers) {
			$serviceURL = $this->getServiceURL(self::SERVICE_SEND);
			$client = new SoapClient($serviceURL, $this->soapOptions);
			$params = $this->prepareParameters();
			$results = array();

			foreach ($toNumbers as $number => $customerId) {
                $number = $this->correctPhoneNumber($number);
                $populatedMsg = populateTemplateWithRecordData($message, $customerId);   // Replace variables
                $populatedMsg = unUnicode($populatedMsg); // SMS message does not support unicode character

				$params['Phone'] = $number;
                $params['Msg'] = $populatedMsg;

				$response = $client->PushMsg2Phone($params);

				$result = array(
					'to' => $number,
					'id' => microtime(true),	// This provider does not return message id. Use microtime as message id
                    'message' => $populatedMsg,  // Return populated message
					'status' => $response->PushMsg2PhoneResult == '1' ? self::MSG_STATUS_DELIVERED : self::MSG_STATUS_FAILED,
                    'statusmessage' => '',  // TODO: check response message later when we have customer using this provider
                    'customer_id' => $customerId,                      
					'error' => $response->PushMsg2PhoneResult == '0'
				);

				$results[] = $result;
			}

			return $results;
		}
	}

?>