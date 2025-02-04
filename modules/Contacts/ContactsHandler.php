<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

function Contacts_sendCustomerPortalLoginDetails($entityData){
	$adb = PearDatabase::getInstance();
	$moduleName = $entityData->getModuleName();
	$wsId = $entityData->getId();
	$parts = explode('x', $wsId);
	$entityId = $parts[1];
	$entityDelta = new VTEntityDelta();
	$email = $entityData->get('email');

	$isEmailChanged = $entityDelta->hasChanged($moduleName, $entityId, 'email') && $email;//changed and not empty
	$isPortalEnabled = $entityData->get('portal') == 'on' || $entityData->get('portal') == '1';

	if ($isPortalEnabled) {
		//If portal enabled / disabled, then trigger following actions
		$sql = "SELECT id, user_name, user_password, isactive FROM vtiger_portalinfo WHERE id=?";
		$result = $adb->pquery($sql, array($entityId));

		$insert = true;
		if ($adb->num_rows($result)) {
			$insert = false;
			$dbusername = $adb->query_result($result,0,'user_name');
			$isactive = $adb->query_result($result,0,'isactive');
			if($email == $dbusername && $isactive == 1 && !$entityData->isNew()){
				$update = false;
			} else if($isPortalEnabled) {
				$sql = "UPDATE vtiger_portalinfo SET user_name=?, isactive=? WHERE id=?";
				$adb->pquery($sql, array($email, 1, $entityId));
				$update = true;
			} else {
				$sql = "UPDATE vtiger_portalinfo SET user_name=?, isactive=? WHERE id=?";
				$adb->pquery($sql, array($email, 0, $entityId));
				$update = false;
			}
		}

		//generate new password
		$password = makeRandomPassword();
		$enc_password = Vtiger_Functions::generateEncryptedPassword($password);

		//create new portal user
		$sendEmail = false;
		if ($insert) {
			$sql = "INSERT INTO vtiger_portalinfo(id,user_name,user_password,cryptmode,type,isactive) VALUES(?,?,?,?,?,?)";
			$params = array($entityId, $email, $enc_password, 'CRYPT', 'C', 1);
			$adb->pquery($sql, $params);
			$sendEmail = true;
		}

		//update existing portal user password
		if ($update && $isEmailChanged) {
			$sql = "UPDATE vtiger_portalinfo SET user_password=?, cryptmode=? WHERE id=?";
			$params = array($enc_password, 'CRYPT', $entityId);
			$adb->pquery($sql, $params);
			$sendEmail = true;
		}

		//trigger send email
		if ($sendEmail && $entityData->get('emailoptout') == 0) {
            // Modified by Hieu Nguyen on 2020-06-22 to send customer portal account with new email template
            require_once('include/Mailer.php');
            global $PORTAL_URL;
            $customerName = getFullNameFromArray('Contacts', ['firstname' => $entityData->get('firstname'), 'lastname' => $entityData->get('lastname')]);

            $mainReceivers = [
                ['name' => $customerName, 'email' => $email]
            ];

            $templateId = getSystemEmailTemplateByName('[Portal] Customer Account');

            $variables = [
                'full_name' => $customerName,
                'username' => $email,
                'email' => $email,
                'password' => $password,
                'portal_url' => $PORTAL_URL
            ];

            $result = Mailer::send(true, $mainReceivers, $templateId, $variables);
            // End Hieu Nguyen
		}
	} else {
		$sql = "UPDATE vtiger_portalinfo SET user_name=?,isactive=0 WHERE id=?";
		$adb->pquery($sql, array($email, $entityId));
	}
}

?>
