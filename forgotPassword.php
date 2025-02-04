<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

/**
 * Refactor and maintained by Phu Vo
 * Date: 2020.06.09
 */

require_once('includes/main/WebUI.php');
require_once('include/utils/utils.php');
require_once('include/utils/VtlibUtils.php');
require_once('modules/Vtiger/helpers/ShortURL.php');
require_once('include/Mailer.php');

global $adb, $log;
$adb = PearDatabase::getInstance();

// Added by Hieu Nguyen on 2020-01-08 to check forgot password captcha
session_start();
checkLoginCaptcha($_REQUEST['g-recaptcha-response'], 'forgot');
// End Hieu Nguyen

if (isset($_REQUEST['username']) && isset($_REQUEST['emailId'])) {
	$username = vtlib_purify($_REQUEST['username']);
	$result = $adb->pquery('SELECT email1 FROM vtiger_users WHERE user_name = ? ', [$username]);

	if ($adb->num_rows($result) > 0) {
		$email = $adb->query_result($result, 0, 'email1');
	}

	if (vtlib_purify($_REQUEST['emailId']) == $email) {
        // Added by Hieu Nguyen on 2020-01-08 to reset captcha check after success check
        updateLoginCaptchaSession(true);
        // End Hieu Nguyen

		$time = time();
		$options = array(
			'handler_path' => 'modules/Users/handlers/ForgotPassword.php',
			'handler_class' => 'Users_ForgotPassword_Handler',
			'handler_function' => 'changePassword',
			'handler_data' => array(
				'username' => $username,
				'email' => $email,
				'time' => $time,
				'hash' => md5($username . $time)
			)
		);
		
		$templateId = getSystemEmailTemplateByName('Reset Password');

		if (empty($templateId)) {
			$log->info("Email template is not set for password reset");
			return;
		}

		$trackURL = Vtiger_ShortURL_Helper::generateURL($options);

		$mainReceivers = [
			['name' => $email, 'email' => $email],
		];
		
		// Replace variables
		$variables = [
			'username' => $username,
			'datetime' => date("Y-m-d H:i:s"),
			'tracker_url' => $trackURL,
		];

		$result = Mailer::send(true, $mainReceivers, $templateId, $variables);

		if ($result['success']) {
			header('Location:  index.php?modules=Users&view=Login&mailStatus=success');
		} else {
			header('Location:  index.php?modules=Users&view=Login&tab=forgot&error=statusError');   // Modified by Hieu Nguyen on 2020-01-08 to render the right view
		}
	} else {
        // Added by Hieu Nguyen on 2020-01-08 to check captcha after 5 times check failures
        updateLoginCaptchaSession(false);
        // End Hieu Nguyen

		header('Location:  index.php?modules=Users&view=Login&tab=forgot&error=fpError');   // Modified by Hieu Nguyen on 2020-01-08 to render the right view
	}
}
