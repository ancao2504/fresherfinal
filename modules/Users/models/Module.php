<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Users_Module_Model extends Vtiger_Module_Model {
	/**
	 * Function to get list view query for popup window
	 * @param <String> $sourceModule Parent module
	 * @param <String> $field parent fieldname
	 * @param <Integer> $record parent id
	 * @param <String> $listQuery
	 * @return <String> Listview Query
	 */
	public function getQueryByModuleField($sourceModule, $field, $record, $listQuery) {
		if($sourceModule == 'Users' && $field == 'reports_to_id') {
			$overRideQuery = $listQuery;
			if(!empty($record)){
				$currentUser = Users_Record_Model::getCurrentUserModel();
				$overRideQuery = $overRideQuery. " AND vtiger_users.id != ". $record;
				$allSubordinates = $currentUser->getAllSubordinatesByReportsToField($record);
				if(count($allSubordinates) > 0) {
					$overRideQuery .= " AND vtiger_users.id NOT IN (". implode(',',$allSubordinates) .")"; // do not allow the subordinates
				}
			}
			return $overRideQuery;
		}
	}

	/**
	 * Function searches the records in the module, if parentId & parentModule
	 * is given then searches only those records related to them.
	 * @param <String> $searchValue - Search value
	 * @param <Integer> $parentId - parent recordId
	 * @param <String> $parentModule - parent module name
	 * @return <Array of Users_Record_Model>
	 */
	public function searchRecord($searchValue, $parentId=false, $parentModule=false, $relatedModule=false) {
		if(!empty($searchValue)) {
			$db = PearDatabase::getInstance();

			$query = 'SELECT * FROM vtiger_users WHERE (first_name LIKE ? OR last_name LIKE ?) AND status = ?';
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$allSubordinates = $currentUser->getAllSubordinatesByReportsToField($currentUser->getId());
			$params = array("%$searchValue%", "%$searchValue%", 'Active');

			// do not allow the subordinates
			if(count($allSubordinates) > 0) {
				$query .= " AND vtiger_users.id NOT IN (". implode(',',$allSubordinates) .")";
			}

			$result = $db->pquery($query, $params);
			$noOfRows = $db->num_rows($result);

			$matchingRecords = array();
			for($i=0; $i<$noOfRows; ++$i) {
				$row = $db->query_result_rowdata($result, $i);
				$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Record', 'Users');
				$recordInstance = new $modelClassName();
				$matchingRecords['Users'][$row['id']] = $recordInstance->setData($row)->setModuleFromInstance($this);
			}
			return $matchingRecords;
		}
	}

	/**
	 * Function returns the default column for Alphabetic search
	 * @return <String> columnname
	 */
	public function getAlphabetSearchField(){
		return 'last_name';
	}

	/**
	 * Function to get the url for the Create Record view of the module
	 * @return <String> - url
	 */
	public function getCreateRecordUrl() {
		return 'index.php?module=' . $this->get('name') . '&parent=Settings&view=' . $this->getEditViewName();
	}

    // Added by Hieu Nguyen on 2021-03-18
    public function getImportUrl() {
		return parent::getImportUrl() . '&parent=Settings';
	}

    // Added by Hieu Nguyen on 2021-03-18
    public function getExportUrl() {
		return parent::getExportUrl() . '&parent=Settings';
	}

	public function checkDuplicateUser($userName){
		$status = false;
		// To check username existence in db
		$db = PearDatabase::getInstance();
		$query = 'SELECT user_name FROM vtiger_users WHERE user_name = ? AND deleted < 2';	# Modified by Hieu Nguyen on 2022-09-19 to check duplicate with active and inactive users
		$result = $db->pquery($query, array($userName));
		if ($db->num_rows($result) > 0) {
			$status = true;
		}
		return $status;
	}

	/**
	 * Function to delete a given record model of the current module
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function deleteRecord(Vtiger_Record_Model $recordModel) {
		$db = PearDatabase::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$query = "UPDATE vtiger_users SET deleted = 1, status = ?, date_modified = ?, modified_user_id = ? WHERE id = ?";   // Modified by Hieu Nguyen on 2021-07-13 to mark user as deactivated
		$db->pquery($query, array('Inactive', date('Y-m-d H:i:s'), $currentUser->getId(), $recordModel->getId()), true,"Error marking record deleted: ");
	}

	/**
	 * Function to get the url for list view of the module
	 * @return <string> - url
	 */
	public function getListViewUrl() {
		return 'index.php?module='.$this->get('name').'&parent=Settings&view='.$this->getListViewName();
	}

	/**
	* Function to update Base Currency of Product
	* @param- $_REQUEST array
	*/
	public function updateBaseCurrency($currencyName) {
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT currency_code, currency_symbol FROM vtiger_currencies WHERE currency_name = ?', array($currencyName));
		$num_rows = $db->num_rows($result);
		if ($num_rows > 0) {
			$currency_code = decode_html($db->query_result($result, 0, 'currency_code'));
			$currency_symbol = decode_html($db->query_result($result, 0,'currency_symbol'));
		}
		$this->updateConfigFile($currencyName);
		//Updating Database
		$query = 'UPDATE vtiger_currency_info SET currency_name = ?, currency_code = ?, currency_symbol = ? WHERE id = ?';
		$params = array($currencyName, $currency_code, $currency_symbol, '1');
		$db->pquery($query, $params);


	}

	/**
	* Function to update Config file
	* @param- $_REQUEST array
	*/
	public function updateConfigFile($currencyName) {
		$currencyName = '$currency_name = \''.$currencyName.'\'';

		//Updating in config inc file
		$filename = 'config.inc.php';
		if (file_exists($filename)) {
			$contents = file_get_contents($filename);
			$currentBaseCurrenyName = $this->getBaseCurrencyName();
			$contents = str_replace('$currency_name = \''.$currentBaseCurrenyName.'\'', $currencyName, $contents);
			file_put_contents($filename, $contents);
		}
	}

	public function getBaseCurrencyName() {
		$db = PearDatabase::getInstance();
		$result = $db->pquery("SELECT currency_name FROM vtiger_currency_info WHERE id=1",array());
		return $db->query_result($result,0,'currency_name');
	}

	/**
	 * Function to get user setup status
	 * @return-is First User or not
	 */
	public static function insertEntryIntoCRMSetup($userId) {
		$db = PearDatabase::getInstance();

		//updating user setup status into database
		$insertQuery = 'INSERT INTO vtiger_crmsetup (userid, setup_status) VALUES (?, ?)';
		$db->pquery($insertQuery, array($userId, '1'));
	}

	/**
	 * Function to store the login history
	 * @param type $username
	 */
	public function saveLoginHistory($username){
		$adb = PearDatabase::getInstance();

		$userIPAddress = $_SERVER['REMOTE_ADDR'];
		$loginTime = date("Y-m-d H:i:s");
		$query = "INSERT INTO vtiger_loginhistory (user_name, user_ip, logout_time, login_time, status) VALUES (?,?,?,?,?)";
		$params = array($username, $userIPAddress, $loginTime,  $loginTime, 'Signed in');
		//Mysql 5.7 doesn't support invalid date in Timestamp field
		//$params = array($username, $userIPAddress, '0000-00-00 00:00:00',  $loginTime, 'Signed in');
		$adb->pquery($query, $params);
	}

	/**
	 * Function to store the logout history
	 * @param type $username
	 */
	public function saveLogoutHistory(){
		$adb = PearDatabase::getInstance();

		$userRecordModel = Users_Record_Model::getCurrentUserModel();
		$userIPAddress = $_SERVER['REMOTE_ADDR'];
		$outtime = date("Y-m-d H:i:s");

		$loginIdQuery = "SELECT MAX(login_id) AS login_id FROM vtiger_loginhistory WHERE user_name=? AND user_ip=?";
		$result = $adb->pquery($loginIdQuery, array($userRecordModel->get('user_name'), $userIPAddress));
		$loginid = $adb->query_result($result,0,"login_id");

		if (!empty($loginid)){
			$query = "UPDATE vtiger_loginhistory SET logout_time =?, status=? WHERE login_id = ?";
			$result = $adb->pquery($query, array($outtime, 'Signed off', $loginid));
		}
	}

	/**
	 * Function to save packages info
	 * @param <type> $packagesList
	 */
	public static function savePackagesInfo($packagesList) {
		$adb = PearDatabase::getInstance();
		$packagesListFromDB = Users_CRMSetup::getPackagesList();
		$disabledModulesList = array();

		foreach ($packagesListFromDB as $packageName => $packageInfo) {
			if (!$packagesList[$packageName]) {
				$disabledModulesList = array_merge($disabledModulesList, array_keys($packageInfo['modules']));
			}
		}

		if ($disabledModulesList) {
			$updateQuery = 'UPDATE vtiger_tab SET presence = CASE WHEN name IN (' . generateQuestionMarks($disabledModulesList) . ') THEN 1 ';
			$updateQuery .= 'ELSE 0 END WHERE presence != 2 ';
		} else {
			$updateQuery = 'UPDATE vtiger_tab SET presence = 0 WHERE presence != 2';
		}

		$adb->pquery($updateQuery, $disabledModulesList);
	}

	/**
	 * Function to save a given record model of the current module
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function saveRecord(Vtiger_Record_Model $recordModel) {
		$moduleName = $this->get('name');
		$focus = CRMEntity::getInstance($moduleName);
		$fields = $focus->column_fields;
		foreach ($fields as $fieldName => $fieldValue) {
			$fieldValue = $recordModel->get($fieldName);
			if (is_array($fieldValue)) {
				$focus->column_fields[$fieldName] = $fieldValue;
			} else if ($fieldValue !== null) {
				$focus->column_fields[$fieldName] = decode_html($fieldValue);
			}

			// Added by Hieu Nguyen on 2021-09-08 to set default value for users_type field
			if ($fieldName == 'users_type' && !in_array($fieldValue, ['normal_user', 'mobile_user'])) {
				$focus->column_fields[$fieldName] = 'normal_user';
			}
			// End Hieu Nguyen
		}

		$user_hash = $recordModel->get('user_hash');
		if (!empty($user_hash))
			$focus->column_fields['user_hash'] = $user_hash;

		$focus->mode = $recordModel->get('mode');
		$focus->id = $recordModel->getId();
		$focus->save($moduleName);
		return $recordModel->setId($focus->id);
	}

	/**
	* @return an array with the list of currencies which are available in source
	*/
	public function getCurrenciesList() {
		$adb = PearDatabase::getInstance();

		$currency_query = 'SELECT currency_name, currency_code, currency_symbol FROM vtiger_currencies ORDER BY currency_name';
		$result = $adb->pquery($currency_query, array());
		$num_rows = $adb->num_rows($result);
		for($i = 0; $i<$num_rows; $i++) {
			$currencyname = decode_html($adb->query_result($result, $i, 'currency_name'));
			$currencycode = decode_html($adb->query_result($result, $i, 'currency_code'));
			$currencysymbol = decode_html($adb->query_result($result, $i, 'currency_symbol'));
			$currencies[$currencyname] = array($currencycode,$currencysymbol);
		}
		return $currencies;
	}

    // Added by Hieu Nguyen on 2019-11-08 to get user currencies
    public function getUserCurrencies() {
        global $adb;
        $sql = "SELECT id, currency_name AS name, currency_symbol AS symbol 
            FROM vtiger_currency_info 
            WHERE deleted = 0 AND currency_status = 'Active'";
        $result = $adb->pquery($sql, []);
        $currencies = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $currencies[$row['id']] = $row;
        }

        return $currencies;
    }

	/**
	 * @return an array with the list of time zones which are availables in source
	 */
	public function getTimeZonesList() {
		$adb = PearDatabase::getInstance();

		$timezone_query = 'SELECT time_zone FROM vtiger_time_zone';
		$result = $adb->pquery($timezone_query, array());
		$num_rows = $adb->num_rows($result);
		for($i = 0; $i<$num_rows; $i++) {
			$time_zone = decode_html($adb->query_result($result, $i, 'time_zone'));
			$time_zones_list[$time_zone] = $time_zone;
		}
		return $time_zones_list;
	}

	/**
	 * @return an array with the list of languages which are available in source
	 */
	public function getLanguagesList() {
		$adb = PearDatabase::getInstance();
        /**
         * Modified By Kelvin Thang
         * Date: 2018-08-14
         */
		$language_query = 'SELECT prefix, label FROM vtiger_language WHERE active = ? ';

		$result = $adb->pquery($language_query, array('1'));
		$num_rows = $adb->num_rows($result);
		for($i = 0; $i<$num_rows; $i++) {
			$lang_prefix = decode_html($adb->query_result($result, $i, 'prefix'));
			$label = decode_html($adb->query_result($result, $i, 'label'));
			$languages_list[$lang_prefix] = $label;
		}
		asort($languages_list);
		return $languages_list;
	}

	/*
	 * Function to get change owner url for Users
	 */
	public function getChangeOwnerUrl() {
		return 'javascript:Settings_Users_List_Js.showTransferOwnershipForm()';
	}

	/**
	 * Function to get active block name of module
	 * @return type
	 */
	public function getSettingsActiveBlock($viewName) {
		$blocksList = array('Edit'			=> array('block' => 'LBL_USER_MANAGEMENT', 'menu' => 'LBL_USERS'),
							'Calendar'		=> array('block' => 'LBL_MY_PREFERENCES', 'menu' => 'Calendar Settings'),
							'PreferenceEdit'=> array('block' => 'LBL_MY_PREFERENCES', 'menu' => 'My Preferences'));
		return $blocksList[$viewName];
	}

	/**
	 * Function to get Module Header Links (for Vtiger7)
	 * @return array
	 */
	// Modified by Hieu Nguyen on 2021-09-09 to show more buttons
	public function getModuleBasicLinks() {
		$basicLinks = [];
		$moduleName = $this->getName();
		$currentUser = Users_Record_Model::getCurrentUserModel();

		if ($currentUser->isAdminUser() && Users_Privileges_Model::isPermitted($moduleName, 'CreateView')) {
			$basicLinks[] = [
				'linktype' => 'BASIC',
				'linklabel' => canCreateMobileUsers() ? 'LBL_ADD_NORMAL_USER' : 'LBL_ADD_RECORD',
				'linkurl' => $this->getCreateRecordUrl() .'&users_type=normal_user',
				'linkicon' => 'fa-plus'
			];

			if (canCreateMobileUsers()) {
				$basicLinks[] = [
					'linktype' => 'BASIC',
					'linklabel' => 'LBL_ADD_MOBILE_USER',
					'linkurl' => $this->getCreateRecordUrl() .'&users_type=mobile_user',
					'linkicon' => 'fa-plus'
				];
			}
	
			if (Users_Privileges_Model::isPermitted($moduleName, 'Import')) {
				$basicLinks[] = [
					'linktype' => 'BASIC',
					'linklabel' => 'LBL_IMPORT',
					'linkurl' => $this->getImportUrl(),
					'linkicon' => 'fa-download'
				];
			}
		}

		return $basicLinks;
	}

	/**
	 * Function to get Settings links
	 * @return <Array>
	 */
	public function getSettingLinks() {
		$settingsLinks = array();
		$moduleName = $this->getName();

		$currentUser = Users_Record_Model::getCurrentUserModel();
		if ($currentUser->isAdminUser() && Users_Privileges_Model::isPermitted($moduleName, 'DetailView')) {
			$settingsLinks[] = array(
				'linktype' => 'LISTVIEW',
				'linklabel' => 'LBL_EXPORT',
				'linkurl' => $this->getExportUrl(), // Modified by Hieu Nguyen on 2021-03-18 to get url from internal function
				'linkicon' => ''
			);
		}
		return $settingsLinks;
	}

	public function getImportableFieldModels() {
		$focus = CRMEntity::getInstance($this->getName());
		$importableFields = $focus->getImportableFields();

		$importableFieldModels = array();
		foreach ($importableFields as $fieldName => $fieldInstance) {
			$importableFieldModels[$fieldName] = $this->getField($fieldName);
		}
		return $importableFieldModels;
	}
}
