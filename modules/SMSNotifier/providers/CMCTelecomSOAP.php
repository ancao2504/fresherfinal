<?php
	/*
	*	CMCTelecomSOAP_Provider
	*	Author: Hieu Nguyen
	*	Date: 2018-09-21
	*	Purpose: to provide helper class for sending SMS with CMC Telecom SOAP APIs
	*/

    require_once('modules/SMSNotifier/BaseProvider.php');

	class SMSNotifier_CMCTelecomSOAP_Provider extends SMSNotifier_Base_Provider {

		function __construct() {
			$this->serviceURI = 'http://124.158.14.49/CMC_BRAND/Service.asmx?wsdl';
			$this->requiredParams = array(
                array('name' => 'brandname', 'label' => 'Brandname', 'type' => 'text')
            );
		}

		public function getName() {
			return 'CMCTelecomSOAP';
		}

		public function getServiceURL($type = false) {
			switch (strtoupper($type)) {
				case self::SERVICE_SEND: return $this->serviceURI;
				default: return false;
			}
		}

        public function prepareParameters() {
            $params = array(
                'username' => $this->username, 
                'password' => $this->password,
                'sender' => $this->parameters['brandname'],
            );

            return $params;
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

				$params['phone'] = $number;
                $params['sms'] = $populatedMsg;

				$response = $client->SendSMSBrandName($params);

				$result = array(
					'to' => $number,
					'id' => microtime(true),	// This provider does not return message id. Use microtime as message id
                    'message' => $populatedMsg,  // Return populated message
					'status' => $response->SendSMSBrandNameResult == '1' ? self::MSG_STATUS_DELIVERED : self::MSG_STATUS_FAILED,
                    'statusmessage' => '',  // TODO: check response message later when we have customer using this provider
                    'customer_id' => $customerId,                      
					'error' => $response->SendSMSBrandNameResult != '1'
				);

				$results[] = $result;
			}

			return $results;
		}
    }
?>