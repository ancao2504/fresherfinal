<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

vimport('~~/vtlib/Vtiger/Net/Client.php');
class Users_Login_View extends Vtiger_View_Controller {

	function loginRequired() {
		return false;
	}
	
	function checkPermission(Vtiger_Request $request) {
		return true;
	}
	
	function preProcess(Vtiger_Request $request, $display = true) {
        // Added by Hieu Nguyen on 2020-02-26 to prevent user back to login page after success login
        if (!empty($_SESSION['authenticated_user_id'])) {
            header('Location: index.php');
        }
        // End Hieu Nguyen

		$viewer = $this->getViewer($request);
		$viewer->assign('PAGETITLE', $this->getPageTitle($request));
		$viewer->assign('SCRIPTS', $this->getHeaderScripts($request));
		$viewer->assign('STYLES', $this->getHeaderCss($request));
		$viewer->assign('MODULE', $request->getModule());
		$viewer->assign('VIEW', $request->get('view'));
		if ($display) {
			$this->preProcessDisplay($request);
		}
	}

	function process (Vtiger_Request $request) {
		global $loginPageConfig, $default_language, $current_language; // Added by Phu Vo on 2021.03.20
		$finalJsonData = array();

        // Modified by Hieu Nguyen on 2020-01-08 to boost the login page speed
		/*$modelInstance = Settings_ExtensionStore_Extension_Model::getInstance();
		$news = $modelInstance->getNews();

		if ($news && $news['result']) {
			$jsonData = $news['result'];
			$oldTextLength = vglobal('listview_max_textlength');
			foreach ($jsonData as $blockData) {
				if ($blockData['type'] === 'feature') {
					$blockData['heading'] = "What's new in Vtiger Cloud";
				} else if ($blockData['type'] === 'news') {
					$blockData['heading'] = "Latest News";
					$blockData['image'] = '';
				}

				vglobal('listview_max_textlength', 80);
				$blockData['displayTitle'] = textlength_check($blockData['title']);

				vglobal('listview_max_textlength', 200);
				$blockData['displaySummary'] = textlength_check($blockData['summary']);
				$finalJsonData[$blockData['type']][] = $blockData;
			}
			vglobal('listview_max_textlength', $oldTextLength);
		}*/

		$viewer = $this->getViewer($request);
		/*$viewer->assign('DATA_COUNT', count($jsonData));
		$viewer->assign('JSON_DATA', $finalJsonData);*/
        // End Hieu Nguyen

		// Added by Phu Vo on 2021.03.20 to load login config base on language
		if (!empty($request->get('language'))) $_SESSION['login_language'] = $request->get('language');
		if (empty($_SESSION['login_language'])) $_SESSION['login_language'] = $_COOKIE['login_language'] ?? $default_language;
		$loginConfig = $loginPageConfig[$_SESSION['login_language']];
		$default_language = $_SESSION['login_language'];
		setcookie('login_language', $_SESSION['login_language']);
		if (!empty($_SESSION['login_language'])) $current_language = $_SESSION['login_language'];
		// End Phu Vo

		$mailStatus = $request->get('mailStatus');
		$error = $request->get('error');
		$message = '';

		// Modified by Phu Vo on 2021.03.20 to translate error label
		if ($error) {
			switch ($error) {
				case 'captchaError'	:	$message = vtranslate('LBL_LOGIN_CAPTCHA_ERROR');						    break;  // Added by Hieu Nguyen on 2020-01-08 to show the captcha error
				case 'login'		:	$message = vtranslate('LBL_LOGIN_CREDENTIALS_ERROR');						break;
				case 'fpError'		:	$message = vtranslate('LBL_LOGIN_FP_ERROR');			break;
				case 'statusError'	:	$message = vtranslate('LBL_LOGIN_STATUS_ERROR');	break;
				case 'sessionOverridden': $message = vtranslate('LBL_LOGIN_SESSION_OVERRIDEN_ERROR') . $request->get('login_time'); break;  // Added by Hieu Nguyen on 2020-02-26 to display duplicate session error
				case 'sessionTimeoutOrLoggedOut' : $message = vtranslate('LBL_LGOIN_SESSSION_TIMEOUT_ERROR'); break;    // Added by Hieu Nguyen on 2021-03-22 to display session timeout error
				case 'apiOnly' : $message = vtranslate('LBL_LOGIN_ERROR_API_USER_ACCESS_CRM_UI'); break;    	// Added by Hieu Nguyen on 2021-09-08 to display error for invalid access from api user
				case 'mobileOnly' : $message = vtranslate('LBL_LOGIN_ERROR_MOBILE_USER_ACCESS_CRM_UI'); break;	// Added by Hieu Nguyen on 2021-09-08 to display error for invalid access from mobile user
				case 'invalidUserType' : $message = vtranslate('LBL_LOGIN_ERROR_INVALID_USER_TYPE'); break;		// Added by Hieu Nguyen on 2021-09-08 to display error for invalid user type
			}
		} else if ($mailStatus) {
			$message = vtranslate('LBL_LOGIN_EMAIL_SENT_MESSAGE');
		}
		// End Phu Vo
		
		// Added by Phu Vo on 2021.03.20 to assign config to tpl
		$viewer->assign('LOGIN_LANGUAGE', $_SESSION['login_language']);
		$viewer->assign('LOGIN_CONFIG', $loginConfig);
		// End Phu Vo

		$viewer->assign('LANGUAGE_STRINGS', $this->getJSLanguageStrings($request)); // Added by Phu Vo on 2021.03.24
		$viewer->assign('ERROR', $error);
		$viewer->assign('MESSAGE', $message);
		$viewer->assign('MAIL_STATUS', $mailStatus);
		$viewer->assign('CHECK_ACTION', 'Login');
		$viewer->view('Login.tpl', 'Users');
	}

	function postProcess(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$viewer->view('Footer.tpl', $moduleName);
	}

	function getPageTitle(Vtiger_Request $request) {
		$companyDetails = Vtiger_CompanyDetails_Model::getInstanceById();
		return $companyDetails->get('organizationname');
	}

	function getHeaderScripts(Vtiger_Request $request){
		$headerScriptInstances = parent::getHeaderScripts($request);

		$jsFileNames = array(
							'~libraries/jquery/boxslider/jquery.bxslider.min.js',
							'modules.Vtiger.resources.List',
							'modules.Vtiger.resources.Popup',
							);
		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($jsScriptInstances,$headerScriptInstances);
		return $headerScriptInstances;
	}
}