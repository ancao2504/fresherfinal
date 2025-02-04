<?php

/*
	OTPUtils
	Author: Hieu Nguyen
	Date: 2022-02-15
	Purpose: to provide util functions to handle logic with OTP
*/

class OTPUtils {

	static $STATUS_NEW = 'new';
	static $STATUS_ACTIVATED = 'activated';

	// This function is to generate a new OTP code, you can specify your own OTP code (EX: Ab05eF) or let system generate a 36 character code itself
	static function newOTP($crmId, $crmModule, $code = '', array $data = [], $expiredTime = '') {
		global $adb;
		
		if (empty($code)) {
			$code = md5(rand());
		}

		$sql = "INSERT INTO vtiger_otp(code, data, crm_id, crm_module, expired_time, status) VALUES (?, ?, ?, ?, ?, ?)";
		$params = [$code, json_encode($data), $crmId, $crmModule, $expiredTime, self::$STATUS_NEW];

		try {
			$adb->pquery($sql, $params);
			return $code;
		}
		catch (Exception $e) {
			return null;
		}
	}

	// This function is to check if a generated OTP code is expired when its expired_time is specified
	static function isExpired($otpCode) {
		if (empty($otpCode)) return -2;	// Empty code
		$otpInfo = self::retrieve($otpCode);

		if (empty($otpInfo)) {
			return -1;	// Not exist
		}
		else {
			if (empty($otpInfo['expired_time'])) {
				return 0;	// No expired time specified
			}
			else if (date('Y-m-d H:i:s') > $otpInfo['expired_time']) {
				return true;
			}
		}

		return false;
	}

	// This function is to retrieve a generated OTP code
	static function retrieve($otpCode) {
		global $adb;
		if (empty($otpCode)) return;

		$sql = "SELECT * FROM vtiger_otp WHERE code = ?";
		$result = $adb->pquery($sql, [$otpCode]);
		$info = $adb->fetchByAssoc($result);

		if (is_array($info)) {
			$info['data'] = json_decode(decodeUTF8($info['data']), true);
			return $info;
		}
		else {
			return [];
		}
	}

	// This function is to update the OTP code status, you can specify your own status if you want, or just leave it blank to make OTP code as activated
	static function updateStatus($otpCode, $status = '', $isActivated = false) {
		global $adb, $current_user;
		if (empty($otpCode)) return;
		$sql = "UPDATE vtiger_otp SET status = ?";
		$params = [$status];

		if ($status == self::$STATUS_ACTIVATED || $isActivated === true) {
			$sql .= ", activated_time = NOW(), activated_by = ?";
			$params[] = $current_user->id;
		}

		$sql .= " WHERE code = ?";
		$params[] = $otpCode;

		try {
			$adb->pquery($sql, $params);
			return true;
		}
		catch (Exception $e) {
			return false;
		}
	}
}