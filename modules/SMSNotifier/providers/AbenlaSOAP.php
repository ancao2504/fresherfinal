<?php
	/*
	*	Abenla_Provider
	*	Author: Phu Vo
	*	Date: 2019-08-05
	*	Purpose: to provide helper class for sending SMS with Abenla SOAP
	*/

    require_once('modules/SMSNotifier/BaseProvider.php');
    require_once('libraries/Bcrypt/Bcrypt.php');
    require_once('libraries/UUID/UUID.php');

	class SMSNotifier_AbenlaSOAP_Provider extends SMSNotifier_Base_Provider {

		function __construct() {
			$this->serviceURI = 'http://api.abenla.com/Service.asmx?wsdl';
			$this->requiredParams = array(
                array('name' => 'brandname', 'label' => 'Brandname', 'type' => 'text')
            );
		}

		public function getName() {
			return 'AbenlaSOAP';
		}

		public function getServiceURL($type = false) {
            switch (strtoupper($type)) {
                case self::SERVICE_SEND: return $this->serviceURI;
                default: return false;
            }
        }

        public function prepareParameters() {
            $params = array(
                'loginName' => $this->username,
                'brandName' => $this->parameters['brandname'],
                'serviceTypeId' => 1,
            );

            return $params;
        }

		public function send($message, $toNumbers) {
			$serviceURL = $this->getServiceURL(self::SERVICE_SEND);
            $client = new SoapClient($serviceURL, $this->soapOptions);
            $params = $this->prepareParameters();

            // Generate sign param (diference method use diference sign, so we have to do it here)
            $md5Password = md5($this->password);
            $strSign = "{$params['loginName']}-{$md5Password}-{$params['brandName']}-{$params['serviceTypeId']}";
            $params['sign'] = md5($strSign);

            $results = [];
            
			foreach ($toNumbers as $number => $customerId) {
                $number = $this->correctPhoneNumber($number);
                $msgId = UUID::v4();    // This provider required an UUID
                $populatedMsg = populateTemplateWithRecordData($message, $customerId);   // Replace variables
                $populatedMsg = unUnicode($populatedMsg); // SMS message does not support unicode character
                
                $content = [
                    'PhoneNumber' => $number,
                    'Message' => $populatedMsg,
                    'SmsGuid' => $msgId,
                    'ContentType' => '1'
                ];

                $params['content'] = json_encode($content);

                $response = $client->SendSms2($params); // SendSms2 provide the ability to send message in json format
                
                $response = $response->SendSms2Result ?? false;

                // Code 106: Success
				$result = array(
					'to' => $number,
					'id' => $msgId,
                    'message' => $populatedMsg,  // Return populated message
					'status' => ($response && $response->Code == 106) ? self::MSG_STATUS_DELIVERED : self::MSG_STATUS_FAILED,
                    'statusmessage' => '',  // TODO: check response message later when we have customer using this provider
                    'customer_id' => $customerId,
					'error' => $response == false || $response->Code != 106
				);

				$results[] = $result;
			}

			return $results;
        }
    }
?>