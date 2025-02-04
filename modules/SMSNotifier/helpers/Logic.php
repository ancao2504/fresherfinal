<?php

/*
	Class SMSNotifier_Logic_Helper
	Author: Hieu Nguyen
	Date: 2020-11-24
	Purpose: provide util functions for SMS Notifier
*/

class SMSNotifier_Logic_Helper {

	static function hasActiveGateway() {
		$gateway = SMSNotifier_Provider_Model::getActiveGateway();
		if (!$gateway) return false;
		return true;
	}

	static function canSendSMSMsg() {
		if (isForbiddenFeature('SendMessageViaSMS')) return false;
		return self::hasActiveGateway();
	}
}