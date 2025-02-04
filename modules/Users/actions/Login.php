<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Users_Login_Action extends Vtiger_Action_Controller {

	function loginRequired() {
		return false;
	}

	function checkPermission(Vtiger_Request $request) {
		return true;
	} 

	function process(Vtiger_Request $request) {
        // Added by Hieu Nguyen on 2020-01-08 to check login captcha
        checkLoginCaptcha($request->get('g-recaptcha-response'), 'login');
        // End Hieu Nguyen

		$username = $request->get('username');
		$password = $request->getRaw('password');

		$user = CRMEntity::getInstance('Users');
		$user->column_fields['user_name'] = $username;

		if ($user->doLogin($password)) {
            // Added by Hieu Nguyen on 2020-01-08 to reset captcha check after success login
            updateLoginCaptchaSession(true);
            // End Hieu Nguyen

			session_regenerate_id(true); // to overcome session id reuse.

			$userid = $user->retrieve_user_id($username);

            // Added by Hieu Nguyen on 2021-07-06 to logout user when user is not exist or has been deleted
            if (empty($userid)) {
                session_regenerate_id(true);
                Vtiger_Session::destroy();
                header('Location: index.php?module=Users&parent=Settings&view=Login&error=login');
                exit();
            }
            // End Hieu Nguyen

            // Added by Hieu Nguyen on 2020-02-26 to prevent duplicate login session
            updateLoginSession($userid, $_SESSION['session_id']);
            // End Hieu Nguyen

			Vtiger_Session::set('AUTHUSERID', $userid);

			// For Backward compatability
			// TODO Remove when switch-to-old look is not needed
			$_SESSION['authenticated_user_id'] = $userid;
			$_SESSION['app_unique_key'] = vglobal('application_unique_key');
			$_SESSION['authenticated_user_language'] = vglobal('default_language');

			//Enabled session variable for KCFINDER 
			$_SESSION['KCFINDER'] = array(); 
			$_SESSION['KCFINDER']['disabled'] = false; 
			$_SESSION['KCFINDER']['uploadURL'] = "test/upload"; 
			$_SESSION['KCFINDER']['uploadDir'] = "../test/upload";
			$deniedExts = implode(" ", vglobal('upload_badext'));
			$_SESSION['KCFINDER']['deniedExts'] = $deniedExts;
			// End

			//Track the login History
			$moduleModel = Users_Module_Model::getInstance('Users');
			$moduleModel->saveLoginHistory($user->column_fields['user_name']);
			//End
			
			// Added by Phu Vo on 2021.03.24 to update user online status
			Users_Data_Model::updateUserOnlineStatus($userid, true);
			// End Phu Vo
			
			// Added by Phu Vo on 2021.03.21  to change language base on login language
			if (!empty($_SESSION['login_language'])) {
				Users_Data_Model::updateUserLanguage($userid, $_SESSION['login_language']);
			}
			// End Phu Vo
			
						
			if(isset($_SESSION['return_params'])){
				$return_params = $_SESSION['return_params'];
			}

			header ('Location: index.php?module=Users&parent=Settings&view=SystemSetup');
			exit();
		} else {
            // Added by Hieu Nguyen on 2020-01-08 to check captcha after 5 times login failures
            updateLoginCaptchaSession(false);
            // End Hieu Nguyen

			header ('Location: index.php?module=Users&parent=Settings&view=Login&error=login');
			exit;
		}
	}

}
