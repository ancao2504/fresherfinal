<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2
 * ("License"); You may not use this file except in compliance with the
 * License. You may obtain a copy of the License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an  "AS IS"  basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * The Original Code is:  SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): ______________________________________.
 ********************************************************************************/
/*********************************************************************************
 * $Header$
 * Description:  Includes generic helper functions used throughout the application.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 * ****************************************************************************** */

require_once('include/utils/utils.php'); //new
require_once('include/utils/RecurringType.php');
require_once('include/utils/EmailTemplate.php');
require_once 'include/QueryGenerator/QueryGenerator.php';
require_once 'include/QueryGenerator/EnhancedQueryGenerator.php';
require_once 'include/ListView/ListViewController.php';
require_once 'includes/runtime/Cache.php';

function is_admin($user) {
	return Vtiger_Functions::userIsAdministrator($user);
}

function parse_calendardate($local_format) {
	return Vtiger_Functions::currentUserJSDateFormat($local_format);
}

function from_html($string, $encode = true) {
	return Vtiger_Functions::fromHTML($string, $encode);
}

function fck_from_html($string) {
	return Vtiger_Functions::fromHTML_FCK($string);
}

function popup_from_html($string, $encode = true) {
	return Vtiger_Functions::fromHTML_Popup($string, $encode);
}

function fetchCurrency($id) {
	return Vtiger_Functions::userCurrencyId($id);
}

function getCurrencyName($currencyid, $show_symbol = true) {
	return Vtiger_Functions::getCurrencyName($currencyid, $show_symbol);
}

function getTabid($module) {
	return Vtiger_Functions::getModuleId($module);
	}

function getFieldid($tabid, $fieldname, $onlyactive = true) {
	return Vtiger_Functions::getModuleFieldId($tabid, $fieldname, $onlyactive);
}

function getTabOwnedBy($module) {
	return Vtiger_Functions::getModuleOwner($module);
}

function getSalesEntityType($crmid) {
	return Vtiger_Functions::getCRMRecordType($crmid);
}

function getAccountName($account_id) {
	return Vtiger_Functions::getCRMRecordLabel($account_id);
}

function getProductName($product_id) {
	return Vtiger_Functions::getCRMRecordLabel($product_id);
}

function getPotentialName($potential_id) {
	return Vtiger_Functions::getCRMRecordLabel($potential_id);
}

function getContactName($contact_id) {
	return Vtiger_Functions::getCRMRecordLabel($contact_id);
}

function getFullNameFromQResult($result, $row_count, $module) {
	return Vtiger_Deprecated::getFullNameFromQResult($result, $row_count, $module);
}

function getFullNameFromArray($module, $fieldValues) {
	return Vtiger_Deprecated::getFullNameFromArray($module, $fieldValues);
}

function getCampaignName($campaign_id) {
	return Vtiger_Functions::getCRMRecordLabel($campaign_id);
}

function getVendorName($vendor_id) {
	return Vtiger_Functions::getCRMRecordLabel($vendor_id);
}

function getQuoteName($quote_id) {
	return Vtiger_Functions::getCRMRecordLabel($quote_id);
}

function getPriceBookName($pricebookid) {
	return Vtiger_Functions::getCRMRecordLabel($pricebookid);
}

function getSoName($so_id) {
	return Vtiger_Functions::getCRMRecordLabel($so_id);
}

function getGroupName($groupid) {
	return Vtiger_Functions::getGroupName($groupid);
}

function getUserName($userid) {
	return Vtiger_Functions::getUserName($userid);
}

function getUserFullName($userid) {
	return Vtiger_Functions::getUserRecordLabel($userid);
}

function getParentName($parent_id) {
	return Vtiger_Functions::getCRMRecordLabel($parent_id);
	}

function getValidDisplayDate($cur_date_val) {
	return Vtiger_Functions::currentUserDisplayDate($cur_date_val);
}

function getNewDisplayDate() {
	return Vtiger_Functions::currentUserDisplayDateNew();
}

/** This function returns the conversion rate and vtiger_currency symbol
 * in array format for a given id.
 * param $id - vtiger_currency id.
 */
function getCurrencySymbolandCRate($id) {
	return Vtiger_Functions::getCurrencySymbolandRate($id);
}

/** This function returns the terms and condition from the database.
 * Takes no param and the return type is text.
 */
function getTermsAndConditions($moduleName) {
	return Vtiger_Functions::getInventoryTermsAndCondition($moduleName);
}

/** This function returns a string with removed new line character, single quote, and back slash double quoute.
 * param $str - string to be converted.
 */
function br2nl($str) {
	return Vtiger_Functions::br2nl($str);
}

/**
 * This function is used to get the blockid of the customblock for a given module.
 * Takes the input parameter as $tabid - module tabid and $label - custom label
 * This returns string type value
 */
function getBlockId($tabid, $label) {
	return Vtiger_Deprecated::getBlockId($tabid, $label);
}

/**
 * This function is used to get the Parent Tab name for a given module.
 * Takes the input parameter as $module - module name
 * This returns value string type
 */
function getParentTabFromModule($module) {
	return Vtiger_Deprecated::getParentTabFromModule($module);
}

/**
 * This function is used to get the Parent Tab name for a given module.
 * Takes no parameter but gets the vtiger_parenttab value from form request
 * This returns value string type
 */
function getParentTab() {
	return Vtiger_Deprecated::getParentTab();
}

/**
 * This function is used to set the Object values from the REQUEST values.
 * @param  object reference $focus - reference of the object
 */
function setObjectValuesFromRequest($focus) {
	return Vtiger_Deprecated::copyValuesFromRequest($focus);
}

function create_tab_data_file() {
	return Vtiger_Deprecated::createModuleMetaFile();
}

/**
 * Function to write the vtiger_parenttabid and name to a flat file parent_tabdata.txt so that the data
 * is obtained from the file instead of repeated queries
 * returns null
 */
function create_parenttab_data_file() {
	return Vtiger_Deprecated::createModuleGroupMetaFile();
}

function getEntityName($module, $ids_list, $compute=true) {
	if ($compute) {
		return Vtiger_Functions::computeCRMRecordLabels($module, $ids_list);
	} else {
		return Vtiger_Functions::getCRMRecordLabels($module, $ids_list);
	}
}

/**
 * 	This function is used to decide the File Storage Path in where we will upload the file in the server.
 * 	return string $filepath  - filepath inwhere the file should be stored in the server will be return
 */
function decideFilePath() {
	return Vtiger_Functions::initStorageFileDirectory();
}

/**
 * 	This function is used to check whether the attached file is a image file or not
 * 	@param string $file_details  - vtiger_files array which contains all the uploaded file details
 * 	return string $save_image - true or false. if the image can be uploaded then true will return otherwise false.
 */
function validateImageFile($file_details) {
	return Vtiger_Functions::validateImage($file_details);
}

/**
 * 	This function is used to get the Email Template Details like subject and content for particular template.
 * 	@param integer $templateid  - Template Id for an Email Template
 * 	return array $returndata - Returns Subject, Body of Template of the the particular email template.
 */
function getTemplateDetails($templateid) {
	return Vtiger_Deprecated::getTemplateDetails($templateid);
}

/**
 * 	This function is used to merge the Template Details with the email description
 *  @param string $description  -body of the mail(ie template)
 * 	@param integer $tid  - Id of the entity
 *  @param string $parent_type - module of the entity
 * 	return string $description - Returns description, merged with the input template.
 */
function getMergedDescription($description, $id, $parent_type, $removeTags = false) {
	return Vtiger_Functions::getMergedDescription($description, $id, $parent_type, $removeTags);
}

/** 	Function used to retrieve a single field value from database
 * 	@param string $tablename - tablename from which we will retrieve the field value
 * 	@param string $fieldname - fieldname to which we want to get the value from database
 * 	@param string $idname	 - idname which is the name of the entity id in the table like, inoviceid, quoteid, etc.,
 * 	@param int    $id	 - entity id
 * 	return string $fieldval  - field value of the needed fieldname from database will be returned
 */
function getSingleFieldValue($tablename, $fieldname, $idname, $id) {
	return Vtiger_Functions::getSingleFieldValue($tablename, $fieldname, $idname, $id);
}

/** 	Function used to retrieve the announcements from database
 * 	The function accepts no argument and returns the announcements
 * 	return string $announcement  - List of announments for the CRM users
 */
function get_announcements() {
	return Vtiger_Deprecated::getAnnouncements();
}

/**
 *  Function to get recurring info depending on the recurring type
 *  return  $recurObj       - Object of class RecurringType
 */
function getrecurringObjValue() {
	return Vtiger_Functions::getRecurringObjValue();
}

function getTranslatedString($str, $module = 'Vtiger', $language = '') {
	return Vtiger_Functions::getTranslatedString($str, $module, $language);
}

/**
 * Get translated currency name string.
 * @param String $str - input currency name
 * @return String $str - translated currency name
 */
function getTranslatedCurrencyString($str) {
	return Vtiger_Deprecated::getTranslatedCurrencyString($str);
	}

function getTicketComments($ticketid) {
	return Vtiger_Functions::getTicketComments($ticketid);
}

function makeRandomPassword() {
	return Vtiger_Functions::generateRandomPassword();
}

/**
 * This function is used to get cvid of default "all" view for any module.
 * @return a cvid of a module
 */
function getCvIdOfAll($module) {
	return Vtiger_Deprecated::getIdOfCustomViewByNameAll($module);
}

/** gives the option  to display  the tagclouds or not for the current user
 * * @param $id -- user id:: Type integer
 * * @returns true or false in $tag_cloud_view
 * * Added to provide User based Tagcloud
 * */
function getTagCloudView($id = "") {
	return Vtiger_Functions::getTagCloudView($id);
}

/** Stores the option in database to display  the tagclouds or not for the current user
 * * @param $id -- user id:: Type integer
 * * Added to provide User based Tagcloud
 * */
function SaveTagCloudView($id = "") {
	return Vtiger_Deprecated::SaveTagCloudView($id);
}

/**     function used to change the Type of Data for advanced filters in custom view and Reports
 * *     @param string $table_name - tablename value from field table
 * *     @param string $column_nametable_name - columnname value from field table
 * *     @param string $type_of_data - current type of data of the field. It is to return the same TypeofData
 * *            if the  field is not matched with the $new_field_details array.
 * *     return string $type_of_data - If the string matched with the $new_field_details array then the Changed
 * *	       typeofdata will return, else the same typeofdata will return.
 * *
 * *     EXAMPLE: If you have a field entry like this:
 * *
 * * 		fieldlabel         | typeofdata | tablename            | columnname       |
 * *	        -------------------+------------+----------------------+------------------+
 * *		Potential Name     | I~O        | vtiger_quotes        | potentialid      |
 * *
 * *     Then put an entry in $new_field_details  like this:
 * *
 * *				"vtiger_quotes:potentialid"=>"V",
 * *
 * *	Now in customview and report's advance filter this field's criteria will be show like string.
 * *
 * */
function ChangeTypeOfData_Filter($table_name, $column_name, $type_of_data) {
	return Vtiger_Functions::transformFieldTypeOfData($table_name, $column_name, $type_of_data);
}

/** Clear the Smarty cache files(in Smarty/smarty_c)
 * * This function will called after migration.
 * */
function clear_smarty_cache($path = null) {
	Vtiger_Deprecated::clearSmartyCompiledFiles($path);
}

/** Get Smarty compiled file for the specified template filename.
 * * @param $template_file Template filename for which the compiled file has to be returned.
 * * @return Compiled file for the specified template file.
 * */
function get_smarty_compiled_file($template_file, $path = null) {
	return Vtiger_Deprecated::getSmartyCompiledTemplateFile($template_file, $path);
}

/** Function to carry out all the necessary actions after migration */
function perform_post_migration_activities() {
	Vtiger_Deprecated::postApplicationMigrationTasks();
}

/** Function to get picklist values for the given field that are accessible for the given role.
 *  @ param $tablename picklist fieldname.
 *  It gets the picklist values for the given fieldname
 *  	$fldVal = Array(0=>value,1=>value1,-------------,n=>valuen)
 *  @return Array of picklist values accessible by the user.
 */
function getPickListValues($tablename, $roleid) {
	return Vtiger_Functions::getPickListValuesFromTableForRole($tablename, $roleid);
}

/** Function to check the file access is made within web root directory and whether it is not from unsafe directories */
function checkFileAccessForInclusion($filepath) {
	Vtiger_Deprecated::checkFileAccessForInclusion($filepath);
}

/** Function to check the file deletion within the deletable (safe) directories*/
function checkFileAccessForDeletion($filepath) {
	Vtiger_Deprecated::checkFileAccessForDeletion($filepath);
}

/** Function to check the file access is made within web root directory. */
function checkFileAccess($filepath) {
	Vtiger_Deprecated::checkFileAccess($filepath);
}

/**
 * function to return whether the file access is made within vtiger root directory
 * and it exists.
 * @global String $root_directory vtiger root directory as given in config.inc.php file.
 * @param String $filepath relative path to the file which need to be verified
 * @return Boolean true if file is a valid file within vtiger root directory, false otherwise.
 */
function isFileAccessible($filepath) {
	return Vtiger_Deprecated::isFileAccessible($filepath);
}

/** Function to get the ActivityType for the given entity id
 *  @param entityid : Type Integer
 *  return the activity type for the given id
 */
function getActivityType($id) {
	return Vtiger_Functions::getActivityType($id);
}

/** Function to get owner name either user or group */
function getOwnerName($id) {
	return Vtiger_Functions::getOwnerRecordLabel($id);
}

/** Function to get owner name either user or group */
function getOwnerNameList($idList) {
	return Vtiger_Functions::getOwnerRecordLabels($idList);
}

/**
 * This function is used to get the blockid of the settings block for a given label.
 * @param $label - settings label
 * @return string type value
 */
function getSettingsBlockId($label) {
	return Vtiger_Deprecated::getSettingsBlockId($label);
}

/**
 * this function returns the entity field name for a given module; for e.g. for Contacts module it return concat(lastname, ' ', firstname)
 * @param string $module - the module name
 * @return string $fieldsname - the entity field name for the module
 */
function getEntityField($module) {
	return Vtiger_Functions::getEntityModuleSQLColumnString($module);
}

/**
 * this function returns the entity information for a given module; for e.g. for Contacts module
 * it returns the information of tablename, modulename, fieldsname and id gets from vtiger_entityname
 * @param string $module - the module name
 * @return array $data - the entity information for the module
 */
function getEntityFieldNames($module) {
	return Vtiger_Functions::getEntityModuleInfoFieldsFormatted($module);
}

/**
 * this function returns the entity field name for a given module; for e.g. for Contacts module it return concat(lastname, ' ', firstname)
 * @param1 $module - name of the module
 * @param2 $fieldsName - fieldname with respect to module (ex : 'Accounts' - 'accountname', 'Contacts' - 'lastname','firstname')
 * @param3 $fieldValues - array of fieldname and its value
 * @return string $fieldConcatName - the entity field name for the module
 */
function getEntityFieldNameDisplay($module, $fieldsName, $fieldValues) {
	return Vtiger_Deprecated::getCurrentUserEntityFieldNameDisplay($module, $fieldsName, $fieldValues);
}

// vtiger cache utility
require_once('include/utils/VTCacheUtils.php');

// vtlib customization: Extended vtiger CRM utlitiy functions
require_once('include/utils/VtlibUtils.php');

// END

function vt_suppressHTMLTags($string) {
	return Vtiger_Functions::suppressHTMLTags($string);
}

function getSqlForNameInDisplayFormat($input, $module, $glue = ' ') {
	return Vtiger_Deprecated::getSqlForNameInDisplayFormat($input, $module, $glue);
}

function getModuleSequenceNumber($module, $recordId) {
	return Vtiger_Deprecated::getModuleSequenceNumber($module, $recordId);
	}

function getInvoiceStatus($invoiceId) {
	return Vtiger_Functions::getInvoiceStatus($invoiceId);
}

function decimalFormat($value){
	return Vtiger_Functions::formatDecimal($value);
}

function updateRecordLabel($module,$recordId){
	return Vtiger_Functions::updateCRMRecordLabel($module, $recordId);
}

function get_group_options() {
    return Vtiger_Functions::get_group_options();
}

// Implemented by Hieu Nguyen on 2018-10-30 to get all request headers if the built-in function is not exists
if (!function_exists('getallheaders')) { 
    function getallheaders() { 
		$headers = array(); 
		
       	foreach ($_SERVER as $name => $value) { 
           	if (substr($name, 0, 5) == 'HTTP_') { 
            	$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value; 
           	} 
		}
		
       	return $headers; 
    } 
} 

// Implemented by Hieu Nguyen on 2018-10-20 to get url scheme
function getUrlScheme() {
	if(isset($_SERVER['HTTPS']) || ($visitor = json_decode($_SERVER['HTTP_CF_VISITOR'])) && $visitor->scheme == 'https') {
		return 'https';
	}

	return 'http';
}

// Implemented by Hieu Nguyen on 2018-10-03 to get global variable anywhere, including in smarty template
function getGlobalVariable($name) {
	return $GLOBALS[$name];
}

// Implemented by Hieu Nguyen on 2018-10-19 to decode values from database. Modified by Phu Vo on 2020.08.20 to support object
function decodeUTF8($data) {
    if (is_string($data)) {
        return html_entity_decode($data, ENT_QUOTES);
    }
    else if (is_object($data)) {
        foreach ($data as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $data->$key = decodeUTF8($value);
            }
            else {
                $data->$key = html_entity_decode($value, ENT_QUOTES);
            }
        }
    }
	else if (is_array($data)) {
		foreach ($data as $key => $value) {
			if (is_array($value) || is_object($value)) {
				$data[$key] = decodeUTF8($value);
            }
			else { 
				$data[$key] = html_entity_decode($value, ENT_QUOTES);
			}
		}
	
		return $data;
	}

    return $data;
}

// Implemented by Hieu Nguyen on 2018-11-13 to validate email address
function isEmailValid($email) {
	$email = trim($email);

	if(empty($email)) {
		return false;
	}

	$pattern = '/[A-Z0-9\._%-]+@[A-Z0-9\.-]+\.[A-Za-z]{2,}$/i';
	$matches = preg_match($pattern, $email);

	if($matches === false || $matches == 0) {
		return false;
	}

	return true;
}

// Implemented by Hieu Nguyen on 2018-12-03 to replace unicode characters from the string
function unUnicode($str){
	$mapping = [
		'a' => 'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
		'd' => 'đ',
		'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
		'i' => 'í|ì|ỉ|ĩ|ị',
		'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
		'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
		'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
		'A' => 'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
		'D' => 'Đ',
		'E' => 'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
		'I' => 'Í|Ì|Ỉ|Ĩ|Ị',
		'O' => 'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
		'U' => 'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
		'Y' => 'Ý|Ỳ|Ỷ|Ỹ|Ỵ',
	];
	 
	foreach($mapping as $nonUnicode => $unicode){
		$str = preg_replace("/($unicode)/i", $nonUnicode, $str);
	}

	return $str;
}

// Implemented by Hieu Nguyen on 2019-01-28 to get date range. Modified by Vu Mai on 2022-11-10 to support more option
function getDateRange($option) {
    $range = [];

    if ($option == 'this_week') {
        $monday = date('w') == 1 ? strtotime(date('Y-m-d')) : strtotime('last monday');
        $sunday = strtotime(date('Y-m-d', $monday) . ' +6 days');
    
        $range['from'] = date('Y-m-d', $monday);
        $range['to'] = date('Y-m-d', $sunday);
    }
    else if ($option == 'last_week') {
        $thisWeekRange = getDateRange('this_week');
        $monday = strtotime($thisWeekRange['from'] . ' -7 days');
        $sunday = strtotime(date('Y-m-d', $monday) . ' +6 days');
    
        $range['from'] = date('Y-m-d', $monday);
        $range['to'] = date('Y-m-d', $sunday);
    }
    else if ($option == 'this_month') {
        $range['from'] = date('Y-m-d', strtotime('first day of this month'));
        $range['to'] = date('Y-m-d', strtotime('last day of this month'));
    }
    else if ($option == 'last_month') {
        $range['from'] = date('Y-m-d', strtotime('first day of last month'));
        $range['to'] = date('Y-m-d', strtotime('last day of last month'));
    }
    else if ($option == 'this_quarter') {
        $startMonth = floor((date('n') - 1) / 3) * 3 + 1;
        $endMonth = floor((date('n') + 2) / 3) * 3;
        
        $range['from'] = date(sprintf('Y-%s-01', $startMonth < 10 ? '0' . $startMonth : $startMonth));
        $range['to'] = date(sprintf('Y-%s-t', $endMonth < 10 ? '0' . $endMonth : $endMonth));
        $range['to'] = date('Y-m-t', strtotime($range['to']));
    }
    else if ($option == 'last_quarter') {
        $thisQuarterRange = getDateRange('this_quarter');
        $startMonth = date("m", strtotime($thisQuarterRange['from'])) - 3;
        $endMonth = date("m", strtotime($thisQuarterRange['to'])) - 3;

        $range['from'] = date(sprintf('Y-%s-01', $startMonth < 10 ? '0' . $startMonth : $startMonth));
        $range['to'] = date(sprintf('Y-%s-t', $endMonth < 10 ? '0' . $endMonth : $endMonth));
        $range['to'] = date('Y-m-t', strtotime($range['to']));
    }
    else if ($option == 'this_year') {
        $range['from'] = date('Y-01-01');
        $range['to'] = date('Y-12-31');
    }
    else if ($option == 'last_year') {
        $lastYear = date('Y') - 1;
        
        $range['from'] = date("{$lastYear}-01-01");
        $range['to'] = date("{$lastYear}-12-31");
    }
    else if ($option == 'today') {
        $range['from'] = date("Y-m-d");
        $range['to'] = date("Y-m-d");
    }
    else if ($option == 'yesterday') {
        $range['from'] = date('Y-m-d', strtotime('-1 days'));
        $range['to'] = date('Y-m-d', strtotime('-1 days'));
    }
    else if ($option == '3_day') {
        $range['from'] = date('Y-m-d', strtotime('-2 days'));
        $range['to'] = date("Y-m-d");
    }
    else if ($option == '7_day') {
        $range['from'] = date('Y-m-d', strtotime('-6 days'));
        $range['to'] = date("Y-m-d");
    }
    else if ($option == '14_day') {
        $range['from'] = date('Y-m-d', strtotime('-13 days'));
        $range['to'] = date("Y-m-d");
    }

    return $range;
}

// Implemented by Hieu Nguyen on 2019-06-21 to fix query that has WHERE clause in subquery
function fixSplittedQueryPartsByWhere($splittedParts) {
    if(count($splittedParts) > 2) {
        $beforeWhere = $splittedParts[0]; // Store the first part
        unset($splittedParts[0]); // Remove the unused parts

        $afterWhere = join('WHERE', $splittedParts);  // Joint the second part
        $fixedParts = [$beforeWhere, $afterWhere]; // Build new result array

        return $fixedParts;
    }

    return $splittedParts;
}

// Implemented by Hieu Nguyen on 2019-03-11
function getField($fieldName, $module) {
    global $adb;

    // $module contains module id
    if(is_numeric($module)) {
        $moduleId = $module;
    }

    // $module contains module name
    if(is_string($module)) {
        $moduleModel = Vtiger_Module_Model::getInstance($module);
        $moduleId = $moduleModel->id;
    }

    // $module contains module model
    if(is_object($module)) {
        $moduleId = $module->id;
    }

    $sql = "SELECT * FROM vtiger_field WHERE fieldname = ? AND tabid = ?";
    $result = $adb->pquery($sql, [$fieldName, $moduleId]);
    $field = $adb->fetchByAssoc($result);

    return $field;
}

// Implemented by Hieu Nguyen on 2019-08-13
function getMultiPicklistValues($dbValues) {
    $values = explode(' |##| ', $dbValues);
    $values = array_map('trim', $values);
    
    return $values;
}

// Implemented by Phu Vo on 2019.02.12
function getFieldsInfo($moduleName) {
	$fieldsInfo = [];
	$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
	$moduleFields = $moduleModel->getFields();

	foreach($moduleFields as $fieldName => $fieldModel) {
		$fieldsInfo[$fieldName] = $fieldModel->getFieldInfo();
	}

	return $fieldsInfo;
}

// Implemented by Phu Vo on 2019.03.29
function translateActivityTypeForNotification($row, $language = '') {
    $extraData = $row['extra_data'];

    if($extraData['activity_type']) {
        $type = $extraData['activity_type'];
    }
    else {
        $type = 'SINGLE_' . $row['related_module_name']; //Modified by Phu Vo on 2019.07.09 translate single module name
    }

    if(!empty($language)) {
        // Modified by Phu Vo on 2019.07.09 translate single module name
        return getTranslatedString($type, $row['related_module_name'], $language);
        // End translate single module name
    }

    return vtranslate($type, $row['related_module_name']);
}

// Implemented by Phu Vo on 2019.03.29
function translateNotificationMessage($row, $language = '', $timezone = '') {
    global $current_user, $default_timezone, $default_language;

    $sourceModule = "CPNotifications";
    $string = '';
    $mapping = [];
    $extraData = $row['extra_data'];
    $extra = [];
    $formatedString = "";
    $dbTimezone = new DateTimeZone(DateTimeField::getDBTimeZone());
    
    // Assign default variable to use later
    if(empty($language)) $language = $current_user->language ?? $default_language;
    if(empty($timezone)) $timezone = $current_user->time_zone ?? $default_timezone;
    $dateFormat = DateTimeField::getPHPDateFormat($current_user) ?? 'd-m-Y';
    $timeFormat = $current_user->hour_format == '12' ? 'h:i A' : 'H:i';
    $dateTimeFormat = $dateFormat . ' ' . $timeFormat;

    // Process for tab Notification
    if ($row['type'] == 'notification') {
        if ($extraData['action'] == 'invite') {
            $string = 'MSG_NOTIFICATIONS_MESSAGE_NOTIFICATION_INVITE';

            $mapping = [
                '%inviter' => getUserFullName($extraData['inviter']),
                '%activity_type' => translateActivityTypeForNotification($row, $language),
                '%activity_name' => $row['related_record_name'],
            ];
        }
        else if ($extraData['action'] == 'comment') {
            $string = 'MSG_NOTIFICATIONS_MESSAGE_NOTIFICATION_COMMENT';

            $mapping = [
                '%commenter' => getUserFullName($extraData['commenter']),
                '%activity_type' => translateActivityTypeForNotification($row, $language),
                '%record_name' => $row['related_record_name'],
            ];
        }
        else if ($extraData['action'] == 'close_deal') {
            $string = 'MSG_NOTIFICATIONS_MESSAGE_NOTIFICATION_CLOSE_DEAL';

            $mapping = [
                '%updater' => getUserFullName($extraData['updater']),
                '%deal_result' => getTranslatedString($extraData['deal_result'], $row['related_module_name'], $language),
                '%activity_type' => translateActivityTypeForNotification($row, $language),
                '%record_name' => $row['related_record_name'],
            ];
        }
        else if ($extraData['action'] == 'assign') {
            if (!$extraData['group_owner']) { // Process message base on group owner
                $string = 'MSG_NOTIFICATIONS_MESSAGE_NOTIFICATION_ASSIGN';
            }
            else {
                $string = 'MSG_NOTIFICATIONS_MESSAGE_NOTIFICATION_ASSIGN_GROUP';
            }

            $mapping = [
                '%assigner' => getUserFullName($extraData['assigner']),
                '%activity_type' => translateActivityTypeForNotification($row, $language),
                '%record_name' => $row['related_record_name'],
            ];

            // Process owner name
            if ($extraData['group_owner']) $mapping['%group_name'] = getOwnerName($extraData['group_owner']);
        }
        else if ($extraData['action'] == 'update') {
            if (!$extraData['following'] && !$extraData['group_owner']) {
                $string = 'MSG_NOTIFICATIONS_MESSAGE_NOTIFICATION_UPDATE';
            }
            else if ($extraData['group_owner']) {
                $string = 'MSG_NOTIFICATIONS_MESSAGE_NOTIFICATION_UPDATE_GROUP';
            }
            else if ($extraData['following']) {
                $string = 'MSG_NOTIFICATIONS_MESSAGE_NOTIFICATION_UPDATE_STARRED';
            }

            $mapping = [
                '%updater' => getUserFullName($extraData['updater']),
                '%activity_type' => translateActivityTypeForNotification($row, $language),
                '%record_name' => $row['related_record_name'],
            ];

            if ($extraData['group_owner']) $mapping['%group_name'] = getOwnerName($extraData['group_owner']);
            
            if (!empty($extraData['updated_field']) && !empty($extraData['updated_value'])) {
                $moduleModel = Vtiger_Module_Model::getInstance($row['related_module_name']);
                $fieldModel = $moduleModel->getField($extraData['updated_field']);
                $fieldInfo = $fieldModel->getFieldInfo();

                $translatedUpdateFieldName = getTranslatedString($fieldModel->get('label'), $row['related_module_name'], $language);
                $translatedUpdateFieldValue = strip_tags($fieldModel->getDisplayValue($extraData['updated_value'])); // Modified by Phu Vo on 2019.09.20 to get field display value by default

                if ($extraData['updated_label']) {
                    $translatedUpdateFieldValue = $extraData['updated_label'];
                }
                else if ($fieldInfo['type'] == 'picklist') {
                    $translatedUpdateFieldValue = getTranslatedString($extraData['updated_value'], $row['related_module_name'], $language);
                }
                else if ($fieldInfo['type'] == 'owner') {
                    $translatedUpdateFieldValue = trim(getOwnerName($extraData['updated_value']));
                }
                else if ($fieldInfo['name'] == 'user_invitees') {
                    $translatedUpdateFieldValue = Vtiger_Owner_UIType::getCurrentOwnersForDisplay($extraData['updated_value'], false);
                }

                $extra[] = ": <i>{$translatedUpdateFieldName} => {$translatedUpdateFieldValue}</i>";
            }
        }
        else if ($extraData['action'] == 'missed_call') {
            $string = 'MSG_NOTIFICATIONS_MESSAGE_ALERT_MISSED_CALL';

            $mapping = [
                '%customer_type' => $extraData['new_customer'] ? getTranslatedString('LBL_PHONE_NUMBER', 'PBXManager', $language) : translateActivityTypeForNotification($row, $language),
                '%customer_name' => $extraData['new_customer'] ? $extraData['number'] : $row['related_record_name'],
            ];
        }
        else if ($extraData['action'] == 'transfer_main_owner') {
            $string = 'MSG_NOTIFICATIONS_MESSAGE_NOTIFICATION_UPDATE_MAIN_OWNER';

            if ($extraData['owner_type'] === 'Groups') $string = 'MSG_NOTIFICATIONS_MESSAGE_NOTIFICATION_UPDATE_MAIN_OWNER_GROUP';

            $mapping = [
                '%updater' => getUserFullName($extraData['updater']),
                '%activity_type' => translateActivityTypeForNotification($row, $language),
                '%record_name' => $row['related_record_name'],
                '%new_main_owner' => trim(getOwnerName($extraData['new_main_owner'])),
            ];
        }
        else if ($extraData['action'] == 'inbound_msg') {
            $string = 'MSG_NOTIFICATIONS_MESSAGE_INBOUND_MSG';

            $mapping = [
                '%channel' => $extraData['channel'],
                '%sender_name' => $row['related_record_name'],
                '%message' => $extraData['msg_text'],
            ];
        }
        else if ($extraData['action'] == 'transfer_chat') {
            $string = 'MSG_NOTIFICATIONS_TRANSFER_CHAT_MSG';

            $mapping = [
                '%channel' => $extraData['channel'],
                '%customer_name' => $row['related_record_name'],
                '%activity_type' => translateActivityTypeForNotification($row, $language),
                '%assigner' => getUserFullName($extraData['assigner']),
            ];
        }
        else if ($extraData['action'] == 'reply_comment') {
            $string = 'MSG_NOTIFICATIONS_REPLY_COMMENT';

            $mapping = [
                '%commenter' => getUserFullName($extraData['commenter']),
                '%activity_type' => translateActivityTypeForNotification($row, $language),
                '%record_name' => $row['related_record_name'],
            ];
        }
        else if ($extraData['action'] == 'mention_comment') {
            $string = 'MSG_NOTIFICATIONS_MENTION_COMMENT';

            $mapping = [
                '%commenter' => getUserFullName($extraData['commenter']),
                '%activity_type' => translateActivityTypeForNotification($row, $language),
                '%record_name' => $row['related_record_name'],
            ];
        }
        else if ($extraData['action'] == 'mass_assign') {
            $string = 'MSG_NOTIFICATIONS_MESSAGE_NOTIFICATION_MASS_ASSIGN';
            
            $mapping = [
                '%activity_count' => $extraData['activity_count'],
                '%assigner' => getUserFullName($extraData['assigner']),
                '%activity_type' => translateActivityTypeForNotification($row, $language),
            ];
        }
        // Added by Hieu Nguyen on 2021-04-05
        else if ($extraData['action'] == 'employee_checkin') {
            $string = 'MSG_NOTIFICATIONS_EMPLOYEE_CHECKIN';
            $checkinDateTime = new DateTime($extraData['checkin_time'], $dbTimezone);

            $mapping = [
                '%employee_name' => $row['related_record_name'],
                '%checkin_time' => $checkinDateTime->format($dateTimeFormat)
            ];
        }
        // End Hieu Nguyen

        // Added by Tin Bui on 2022-02-18
        else if ($extraData['action'] == 'customer_reply_ticket_email') {
            $string = 'MSG_NOTIFICATIONS_MESSAGE_NOTIFY_CUSTOMER_WAS_REPLY';

            $mapping = [
                '%ticket_number' => $extraData['ticket_number']
            ];
        } 
        // End by Tin Bui
    }
    // Process for tab Activity Reminder
    elseif ($row['type'] == 'activity') {
        if ($row['subtype'] == 'coming') {
            $string = 'MSG_NOTIFICATIONS_MESSAGE_ACTIVITY_COMING';

            $dueDateObject = new DateTime($extraData['due_time'], $dbTimezone);
            $dueDateObject->setTimezone($timezone);
            $todayObject = new DateTime();
            $todayObject->setTime(0, 0, 0);
            $dueDateObjectStartOfDay = new DateTime($extraData['due_time'], $dbTimezone);
            $dueDateObjectStartOfDay->setTime(0, 0, 0); // set to start of date
            $comingDay = $dueDateObjectStartOfDay->diff($todayObject)->days;

            $mapping = [
                '%activity_type' => translateActivityTypeForNotification($row, $language),
                '%record_name' => $row['related_record_name'],
                '%coming_days' => $comingDay,
                '%due_time' => $dueDateObject->format($dateTimeFormat),
            ];

            // process due day in today or another time
            if($comingDay === 0) $string = 'MSG_NOTIFICATIONS_MESSAGE_ACTIVITY_TODAY';
        }
        elseif ($row['subtype'] == 'overdue') {
            if ($row['related_module_name'] == 'Calendar') {
                $string = 'MSG_NOTIFICATIONS_MESSAGE_ACTIVITY_OVERDUE';

                $dueDateObject = new DateTime($extraData['due_time'], $dbTimezone);
                $dueDateObject->setTimezone($timezone);
                $todayObject = new DateTime();
                $todayObject->setTime(0, 0, 0);
                $dueDateObjectStartOfDay = new DateTime($extraData['due_time'], $dbTimezone);
                $dueDateObjectStartOfDay->setTime(0, 0, 0); // set to start of date
                $overdueDay = $dueDateObjectStartOfDay->diff($todayObject)->days;

                $mapping = [
                    '%activity_type' => translateActivityTypeForNotification($row, $language),
                    '%record_name' => $row['related_record_name'],
                    '%overdue_days' => $overdueDay,
                    '%due_time' => $dueDateObject->format($dateTimeFormat),
                ];
            }
            elseif ($row['related_module_name'] == 'ServiceContracts') {
                $string = 'MSG_NOTIFICATIONS_MESSAGE_CONTRACT_OVERDUE';

                $dueDateObject = new DateTime($extraData['due_time'], $dbTimezone);
                $dueDateObject->setTimezone($timezone);
                $todayObject = new DateTime();
                $todayObject->setTime(0, 0, 0);
                $dueDateObjectStartOfDay = new DateTime($extraData['due_time'], $dbTimezone);
                $dueDateObjectStartOfDay->setTime(0, 0, 0); // set to start of date
                $overdueDay = $dueDateObjectStartOfDay->diff($todayObject)->days;

                $mapping = [
                    '%record_name' => $row['related_record_name'],
                    '%overdue_days' => $overdueDay,
                    '%due_time' => $dueDateObject->format($dateTimeFormat),
                ];
            }
        }
    }
    // Process for tab Customer Birthday reminder
    elseif ($row['type'] == 'birthday') {
        if ($row['subtype'] == 'today') {
            $string = 'MSG_NOTIFICATIONS_MESSAGE_BIRTHDAY_TODAY';

            $birthDayObject = new DateTime($extraData['birthday'], $dbTimezone);
            $birthdayCount = $birthDayObject->diff(new DateTime('', $dbTimezone))->y;

            $countingFixMapping = [
                '1'=> 'st',
                '2'=> 'nd',
                '3'=> 'rd',
                'other'=> 'th',
            ];

            $endNumber = substr($birthdayCount, -1);
            $countingFix = $countingFixMapping[$endNumber] ?? $countingFixMapping['other'];

            if($language != 'vn_vn') $birthdayCount .= $countingFix;

            $mapping = [
                '%customer_name' => $row['related_record_name'],
                '%birthday_count' => $birthdayCount,
            ];
        }
        elseif ($row['subtype'] == 'coming') {
            $string = 'MSG_NOTIFICATIONS_MESSAGE_BIRTHDAY_COMING';

            $birthDay = date($dateFormat, strtotime($extraData['birthday']));
            $thisBirthDay = date('Y') . '-' . date('m-d', strtotime($extraData['birthday']));
            $thisBirthDayObject = new DateTime($thisBirthDay);
            $todayObject = new DateTime();
            $todayObject = $todayObject->setTime(0, 0, 0);

            // Modified by Phu Vo on 2019.08.26 to fix diff with next year date cause diff days invert in time issue
            if ($thisBirthDayObject < $todayObject) $thisBirthDayObject->add(new DateInterval('P1Y'));
            $comingDays = $thisBirthDayObject->diff($todayObject)->days;
            // End fix diff with next year date cause diff days invert in time issue

            $mapping = [
                '%customer_name' => $row['related_record_name'],
                '%coming_days' => $comingDays,
                '%birthday' => $birthDay,
            ];
        }
    }

    // trim mapping
    foreach ($mapping as $key => $value) {
        $mapping[$key] = trim($value);
    }

    // Process with html tag
    $formatedString = html_entity_decode(replaceKeys(getTranslatedString($string, $sourceModule, $language), $mapping));

    if (sizeof($extra) > 0) {
        foreach ($extra as $extraString) {
            $formatedString = trim($formatedString . $extraString);
        }
    }

    return html_entity_decode($formatedString); // Updated by Phuc on 2020.02.04 to fix error on display name with html entity
}

// Implemented by Phu Vo on 2019.03.29
function getUserData($dataName, $userId) {
    global $current_user, $adb;

    if (empty($dataName)) return false;
    if (empty($userId)) $userId = $current_user->id;
    if (empty($adb)) $adb = PearDatabase::getInstance();

    $path = "user_privileges/user_privileges_{$userId}.php";

    if (file_exists($path)) {
        require($path);
        $result = $user_info[$dataName];
    }
    
    if (empty($result)) {
        $sql = "SELECT ? FROM vtiger_users WHERE id = ?";
        $result = $adb->getOne($sql, [$dataName, $userId]);
    }

    return $result;
}

// Implemented by Hieu Nguyen on 2019-07-18 to support process_records event handler
function handleProcessRecords($moduleName, &$recordModel) {
    global $batchHandlerName;

    if($GLOBALS['process_records_handler_register_exists'] || file_exists("modules/{$moduleName}/HandlersRegister.php")) {
        require_once("modules/{$moduleName}/HandlersRegister.php");
        $GLOBALS['process_records_handler_register_exists'] = true;
        
        if($GLOBALS['process_records_batch_handler_exists'] || file_exists("modules/{$moduleName}/handlers/{$batchHandlerName}.php")) {
            require_once("modules/{$moduleName}/handlers/{$batchHandlerName}.php");
            $GLOBALS['process_records_batch_handler_exists'] = true;
            
            if(method_exists($batchHandlerName, 'processRecords')) {
                $batchHandlerName::processRecords($recordModel);
            }
        }
    }
}

// Implemented by Phu Vo on 2019.03.29
function getModulesTranslatedSingleLabel($language = '') {
    global $current_user;

    if(empty($language)) $language = $current_user->language;

    $entityModule = Vtiger_Module_Model::getEntityModules();
    $modules = [];

    foreach($entityModule as $index => $moduleModel) {
        $modules[$moduleModel->name] = getTranslatedString("SINGLE_{$moduleModel->name}", $moduleModel->name, $language);
    }

    return $modules;
}

// Implemented by Phu Vo on 2019.04.17
/**
 * @deprecated Can use Vtiger build-in CRMEntity::isBulkSaveMode() method instead
 */
function isRecordImporting() {
    if (
        // Normal Import case
        strtoupper($_REQUEST['mode']) === 'IMPORT' || 
        strtoupper($_REQUEST['module']) === 'IMPORT'
    ) return true;

    if (!empty($GLOBALS['cronTask'])) {
        if($GLOBALS['cronTask']->getName() === 'ScheduleImport') return true;
    }

    return false;
}

/**
 * Checks if the current action is set to a mass action. Includes the following actions:
 * - Import 
 * - Mass Save
 * 
 * @return bool true if the current action is a mass action and false otherwise
 * @Author Phu Vo
 * @Date 2023.02.23
 */
function isMassAction() {
    if (CRMEntity::isBulkSaveMode()) return true;
    if (strtoupper($_REQUEST['action']) === 'MASSSAVE') return true;

    return false;
}

// Added by Phu Vo on 2019.05.02
function formatPrice($value, $decimal=2) {
    $currencyField = new CurrencyField($value);
    return $currencyField->getDisplayValue(null, true);
}

/**
 * Render Current Owners For List View
 * @param array $owners Owner list info
 * @return string HTML string
 * @author Phu Vo (Date: 2019.05.27)
 */
function renderCurrentOwnersForListView($owners = []) {
    // Declare
    $firstOwner = null;

    // Get number of owner
    $ownerCount = sizeof($owners);

    // Process $owners array for smarty
    $processedOwners = [];

    foreach ($owners as $owner) {
        $idParams = explode(':', $owner['id']);
        $type = $idParams[0];

        // Declare type if not exists
        if (!in_array($type, array_keys($processedOwners))) $processedOwners[$type] = [];

        $processedOwner = [
            'id' => $idParams[1],
            'type' => $type,
            'text' => $owner['text'],
            'email' => in_array('email', array_keys($owner)) ? $owner['email'] : '',
        ];

        $processedOwners[$type][] = $processedOwner;

        // Process first owner (to display)
        if (empty($firstOwner)) $firstOwner = $processedOwner;
    }

    // Process viewer
    $viewer = new Vtiger_Viewer();
    $viewer->assign('FIRST_OWNER', $firstOwner);
    $viewer->assign('OWNER_COUNT', $ownerCount);
    $viewer->assign('OWNERS', $processedOwners);

    // Return HTML string result
    return $viewer->fetch('modules/Vtiger/tpls/CustomOwnerFieldListView.tpl');
}

/**
 * Return all group's member user id
 * @param Number $ownerId
 * @return Array ids
 * @author Phu Vo (2019.06.10)
 */
function getGroupMemberIds($ownerId) {
    require_once('include/utils/GetGroupUsers.php');

    $ownerType = vtws_getOwnerType($ownerId);
    if ($ownerType === 'Users') return [$ownerId];

    $groupUsers = new GetGroupUsers();
    $groupUsers->getAllUsersInGroup($ownerId);
    $userIds = $groupUsers->group_users;

    return $userIds;
}

// Implemented by Hieu Nguyen on 2019-11-15 to get short string from a long string
function getShortString($originStr, int $newLength = 0) {
    if ($originStr == null) return '';
    if (!is_string($originStr)) return $originStr;

    $originStr = decodeUTF8($originStr);
    if (strlen($originStr) <= $newLength) return $originStr;
    
    return mb_substr($originStr, 0, $newLength) . '...';
}

// Implemented by Hieu Nguyen on 2019-11-27 to get user avatar from id
function getUserAvatar($userId) {
    global $site_URL;
    $userModel = Vtiger_Record_Model::getCleanInstance('Users');
    $userModel->setData(['id' => $userId]);
    $avatar = $userModel->getImageDetails();

    if (!empty($avatar[0]['id'])) {
        return "{$site_URL}/{$avatar[0]['path']}_{$avatar[0]['name']}";
    }

    return "{$site_URL}/resources/images/default-user-avatar.png";
}

/**
 * Return system email address
 * @return Array info
 * @author Phuc Lu (2019.11.15)
 */
function getSystemEmailAddress() {
    global $adb;

    $fromName =  $adb->getOne("SELECT organizationname FROM vtiger_organizationdetails WHERE organization_id = '1'", []);
    $fromEmail = Emails_Record_Model::getFromEmailAddress();

    return ['name' => $fromName, 'email' => $fromEmail];
}

// Implemented by Hieu Nguyen on 2019-12-24 to generate the record detail url
function getRecordDetailUrl($id, $type, array $params = []) {
    $url = "index.php?module={$type}&view=Detail&record={$id}";
    if (!empty($params)) $url .= '&' . http_build_query($params);

    return $url;
}

// Implemented by Hieu Nguyen on 2019-12-24 to generate the record edit url
function getRecordEditUrl($id, $type, array $params = []) {
    $url = "index.php?module={$type}&view=Edit&record={$id}";
    if (!empty($params)) $url .= '&' . http_build_query($params);

    return $url;
}

// Implemented by Hieu Nguyen on 2019-12-20 to get all phone numbers of a customer
function getCustomerPhoneNumbers($customerId) {
    global $adb;
    if (empty($customerId)) return [];

    $sql = "SELECT p.setype AS module_name, p.fieldname AS field_name, f.fieldlabel AS field_label, p.fnumber AS number 
        FROM vtiger_pbxmanager_phonelookup AS p
        INNER JOIN vtiger_tab AS t ON (t.name = p.setype)
        INNER JOIN vtiger_field AS f ON (f.fieldname = p.fieldname AND f.tabid = t.tabid)
        WHERE p.crmid = ?";
    $result = $adb->pquery($sql, [$customerId]);
    $phoneNumbers = [];

    while ($row = $adb->fetchByAssoc($result)) {
        $row['field_label'] = vtranslate($row['field_label'], $row['module_name']);
        unset($row['module_name']);

        $phoneNumbers[] = $row;
    }

    return $phoneNumbers;
}

// Added by Hieu Nguyen on 2020-01-16 to remove specific buttons from module links
function removeButtons(array $currentButtons = [], array $removeButtonNames = []) {
    $remainingButtons = [];

    foreach ($currentButtons as $button) {
        $buttonLabel = is_object($button) ? $button->linklabel : $button['linklabel'];

        if (!in_array($buttonLabel, $removeButtonNames)) {
            $remainingButtons[] = $button;
        }
    }

    return $remainingButtons;
}

// Added by Phuc on 2020.02.25 to get all fields for mapping
function getFieldsForSyncMapping($moduleName) {
    global $adb;
    $fields = [];

    $sql = "SELECT vtiger_field.fieldname, vtiger_field.fieldlabel
        FROM vtiger_field
        INNER JOIN vtiger_tab ON (vtiger_tab.tabid = vtiger_field.tabid AND vtiger_tab.isentitytype = 1 AND vtiger_tab.name = ?)
        WHERE vtiger_field.fieldname NOT IN ('starred', 'tags', 'campaignrelstatus') AND vtiger_field.presence IN (0, 2)";
    $result = $adb->pquery($sql, [$moduleName]);

    while ($row = $adb->fetchByAssoc($result)) {
        $fields[$row['fieldname']] =  vtranslate($row['fieldlabel'], $moduleName);
    }

    // Sort by label
    asort($fields);

    return $fields;
}
// Ended by Phuc

//-- Added by Kelvin Thang on 2020.02.03 to format Number To User
function formatNumberToUser($value, $type = 'Integer', $user = null) {
	global $current_user;

	if ((empty($value) || $value == '') && $value != 0) return ''; // Updated by Phuc to report 0 value when input is 0

	if (empty($user)) {
		$user = $current_user;
	}

	$value = CurrencyField::convertToUserFormat(decimalFormat($value), '', true);

	if ($type == 'Integer') {
		$valueDecimalSeparator = explode($user->currency_decimal_separator, $value);

		if ($valueDecimalSeparator[1]) {
			$value = $valueDecimalSeparator[0];
		}
	}

	return $value;
}

// Added by Hieu Nguyen on 2020-03-30 to get active dashboard tabs
function getActiveDashboardTabs() {
    $dashBoardModel = new Vtiger_DashBoard_Model();
    $activeTabs = $dashBoardModel->getActiveTabs();

    return $activeTabs;
}

// Added by Hieu Nguyen on 2020-08-26 to get remote file
function getRemoteFile($fileUrl) {
    if (strpos($fileUrl, 'http') === false) return;

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $fileUrl);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
    $contents = curl_exec($curl);
    curl_close($curl);

    return $contents;
}

// Added by Hieu Nguyen on 2020-04-09 to generate upload files from paths
function generateUploadFilesFromPaths(array $filePaths, string $fieldName, string $fileName = '') {
    $_FILES[$fieldName] = [
        'name' => [],
        'type' => [],
        'tmp_name' => [],
        'error' => [],
        'size' => []
    ];

    foreach ($filePaths as $index => $filePath) {
        if (file_exists($filePath)) {
            $displayName = basename($filePath);

            if (!empty($fileName)) {
                $displayName = str_replace('.', "_{$index}.", $fileName);
                if (count($filePaths) == 1) $displayName = $fileName;
            }

            $_FILES[$fieldName]['name'][] = $displayName;
            $_FILES[$fieldName]['type'][] = mime_content_type($filePath);
            $_FILES[$fieldName]['tmp_name'][] = $filePath;
            $_FILES[$fieldName]['error'][] = 0;
            $_FILES[$fieldName]['size'][] = filesize($filePath);
        }
    }
}

// Added by Hieu Nguyen on 2020-04-09 to generate upload files from urls
function generateUploadFilesFromUrls(array $fileUrls, string $fieldName, string $fileName = '') {
    $filePaths = [];
    $streamOptions = stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);

    foreach ($fileUrls as $fileUrl) {
        if (strpos($fileUrl, 'http') === false) continue;

        $tempFile = tempnam(sys_get_temp_dir(), 'upload');
        $fileContents = file_get_contents($fileUrl, false, $streamOptions);
        file_put_contents($tempFile, $fileContents);
        $filePaths[] = $tempFile;
    }

    generateUploadFilesFromPaths($filePaths, $fieldName, $fileName);
}

// Added by Phuc on 2020.04.13 to generate listview link with search params
function getListViewLinkWithSearchParams($module, $params) {
    $searchParams = urlencode(json_encode($params));
    return "index.php?module={$module}&view=List&search_params={$searchParams}";
}

// Added by Phu Vo on 2020.05.07 to check if a field is mandatory
function isMandatory($fieldName, $moduleName) {
    $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
    $fieldModel = Vtiger_Field_Model::getInstance($fieldName, $moduleModel);

    return $fieldModel->isMandatory();
}

/**
 * Implemented by Phu Vo on 2020.05.25
 */
function saveDateTimeFields(Vtiger_Record_Model $recordModel) {
    global $adb;

    if (empty($recordModel->getId())) return;

    $fields = $recordModel->getModule()->getFields();
    $dateTimeFields = [];
    $ignoreFields = ['modifiedtime', 'createdtime'];

    foreach ($fields as $fieldModel) {
        if (in_array($fieldModel->get('name'), $ignoreFields)) continue;

        if ($fieldModel->getFieldDataType() == 'datetime') {
            $columnName = $fieldModel->get('column');
            $fieldValue = $recordModel->get($fieldModel->get('name'));
            if ($fieldValue) $dateTimeFields[$columnName] = $fieldValue;
        }
    }

    if (empty($dateTimeFields)) return;

    $tableName = $recordModel->getModule()->basetable;
    $primaryKey = $recordModel->getModule()->basetableid;
    $colums = array_keys($dateTimeFields);

    $query = "UPDATE {$tableName} SET " . join(' = ?, ', $colums) . " = ? " . "WHERE {$primaryKey} = ?";

    $params = array_values($dateTimeFields);
    $params[] = $recordModel->getId();

    $adb->pquery($query, $params);
}

/**
 * Util function return system email template id by its name (use in system email logic)
 * @param String $templateName
 * @author Phu Vo
 * @return mix Template Id or null
 */
function getSystemEmailTemplateByName($templateName) {
    global $adb;

    $sql = "SELECT templateid FROM vtiger_emailtemplates WHERE deleted = 0 AND systemtemplate = 1 AND templatename = ?";
    return $adb->getOne($sql, [$templateName]);
}

/**
 * Util function remove deleted record ids from record id array, use for mass action that you want to ignore deleted record
 * @param Array $ids
 * @author Phu Vo
 * @return Array $ids
 */
function removeDeletedFromRecordIds(array $ids) {
    global $adb;

    if (empty($ids)) return $ids;

    $idsString = "('" . join("', '", $ids) . "')";

    $query = "SELECT crmid FROM vtiger_crmentity WHERE deleted = 1 AND crmid IN {$idsString}";
    $result = $adb->pquery($query);

    while ($row = $adb->fetchByAssoc($result)) {
        unset($ids[array_search($row['crmid'], $ids)]);
    }

    return array_filter($ids);
}

/**
 * Implemented by Phu Vo on 2020.07.11
 */
function formatDuration($seconds) {
    $days = floor($seconds / 86400);
    $hours = floor(($seconds % 86400) / 3600);
    $mins = floor(($seconds % 3600) / 60);
    $secs = floor($seconds % 60);

    $formatedString = $secs . ' ' . vtranslate('LBL_DURATION_SECS', 'Events');
    if ($mins > 0 || $hours > 0) $formatedString = $mins . ' ' . vtranslate('LBL_DURATION_MINS', 'Events') . ', ' . $formatedString;
    if ($hours > 0 || $days > 0) $formatedString = $hours . ' ' . vtranslate('LBL_DURATION_HOURS', 'Events') . ', ' . $formatedString;
    if ($days > 0) $formatedString = $days . ' ' . vtranslate('LBL_DURATION_DAYS', 'Events') . ', ' . $formatedString;

    return $formatedString;
}

// Implemented by Hieu Nguyen on 2020-07-27 to get all picklist field and all picklist value belong to that fields in the specified module
function getPicklistsByModule($moduleName) {
    global $adb;
    static $cache = [];
    if (!empty($cache[$moduleName])) return $cache[$moduleName];

    $sql = "SELECT f.fieldname AS name, f.fieldlabel AS label
        FROM vtiger_field AS f 
        INNER JOIN vtiger_tab AS t ON (t.tabid = f.tabid AND t.isentitytype = 1)
        WHERE f.uitype IN (15, 16) AND t.name = ? AND f.presence IN (0, 2) AND f.block IS NOT NULL";
    $result = $adb->pquery($sql, [$moduleName]);
    $picklists = [];

    while ($row = $adb->fetchByAssoc($result)) {
        $picklistName = $row['name'];
        $picklists[$picklistName] = [
            'name' => $picklistName,
            'label' => vtranslate($row['label'], $moduleName),
            'options' => [],
        ];

        $sqlGetValues = "SELECT *
            FROM vtiger_{$picklistName} ORDER BY sortorderid";
        $valuesResult = $adb->pquery($sqlGetValues, []);

        while ($row = $adb->fetchByAssoc($valuesResult)) {
            $values = array_values($row);
            $key = $values[1];

            $picklists[$picklistName]['options'][$key] = [
                'key' => $key,
                'label' => vtranslate($key, $moduleName),
                'color' => $row['color'],
            ];
        }
    }

    $cache[$moduleName] = $picklists;
    return $picklists;
}

/** Implemented by Phu Vo on 2020.07.28*/
// Modified by Hieu Nguyen on 2021-05-31 to apply caching
function isReadonlyModule($moduleName) {
    static $cache = [];
    if (isset($cache[$moduleName])) return $cache[$moduleName];

    $fileName = "modules/{$moduleName}/{$moduleName}.php";
    $result = false;

    if (file_exists($fileName)) {
        $focus = CRMEntity::getInstance($moduleName);
        $result = (!empty($focus) && $focus->isReadonly) ? true : false;
    }

    $cache[$moduleName] = $result;
    return $result;
}

/** Implemented by Phu Vo on 2020.07.28*/
function isPersonModule($moduleName) {
    $fileName = "modules/{$moduleName}/{$moduleName}.php";

    if (file_exists($fileName)) {
        $focus = CRMEntity::getInstance($moduleName);
        return !empty($focus) && $focus->isPerson ? true : false;
    }

    return false;
}

// Added by Phuc on 2020.08.94 to get customer id from record module
function getCustomerIdFromRecord($recordModel, $module = '') {
    global $customerModules, $adb;

    if (empty($module)) {
        $module = $recordModel->getModuleName();
    }

    if (in_array($module, $customerModules)) {
       return $recordModel->getId();
    }

    $moduleModel = Vtiger_Module_Model::getInstance($module);
    $referenceFields = $moduleModel->getFieldsByType('reference');
    $referenceIds = [];

    foreach ($referenceFields as $field) {
        $fieldName = $field->getName();
        $fieldValue = $recordModel->get($fieldName);

        if (!empty($fieldValue)) {
            $referenceIds[] = $fieldValue;
        }
    }
    
    if (empty($referenceIds)) 
        return '';

    $referenceIdsString = join("', '", $referenceIds);
    $customerModulesString = join("', '", $customerModules);

    $sql = "SELECT crmid FROM vtiger_crmentity
        WHERE crmid IN ('{$referenceIdsString}') AND crmid > 0 AND setype IN ('{$customerModulesString}')
        ORDER BY FIELD(setype, '{$customerModulesString}')";
    $customerId = $adb->getOne($sql);

    if (!empty($customerId)) 
        return $customerId;
    
    return '';
}
// Ended by Phuc

/** Implemented by Phu Vo on 2020.08.12 */
function getSalutationModel(Vtiger_Record_Model $recordModel, $currentValue) {
    $salutationFieldModel = null;

    if ($recordModel->getEntity()->isPerson == true) {
        $salutationFieldModel = Vtiger_Field_Model::getInstance('salutationtype', $recordModel->getModule());
    
        if (!empty($currentValue)) {
            $salutationFieldModel->set('fieldvalue', $currentValue);
        } 
        else {
            $salutationFieldModel->set('fieldvalue', $recordModel->get('salutationtype')); 
        }
    }

    return $salutationFieldModel;
}

// Implemented by Hieu Nguyen on 2020-08-31 to get full record data
function getFullRecordData($recordId, $recordModule) {
    static $cache = [];
    if (!empty($cache[$recordId])) return $cache[$recordId];

    try {
        $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $recordModule);
        $data = $recordModel->getData();
        $cache[$recordId] = $data;
        return $data;
    }
    catch(Exception $ex) {
        return [];
    }
}

// Implemented by Hieu Nguyen on 2020-10-15 to get list of user ids from specific role
function getUserIdsFromRoles(array $roleIds) {
    global $adb;
    static $cache = [];
    $cachekey = join('-', $roleIds);
    if (!empty($cache[$cachekey])) return $cache[$cachekey];

    $sql = "SELECT u.id AS user_id, ur.roleid AS role_id 
        FROM vtiger_users AS u
        INNER JOIN vtiger_user2role AS ur ON (ur.userid = u.id AND roleid IN ('". join($roleIds, "', '") ."'))
        WHERE deleted = 0";
    $result = $adb->pquery($sql, []);
    $userIds = [];

    while ($row = $adb->fetchByAssoc($result)) {
        if (!$userIds[$row['role_id']]) $userIds[$row['role_id']] = [];
        $userIds[$row['role_id']][] = $row['user_id'];
    }

    $cache[$cachekey] = $userIds;
    return $userIds;
}

// Implemented by Hieu Nguyen on 2020-10-16 to get picklist option id from option value
function getPicklistOptionId($picklistName, $optionValue) {
    global $adb;
    static $cache = [];
    $cacheKey = $picklistName .'-'. $optionValue;
    if (!empty($cache[$cacheKey])) return $cache[$cacheKey];

    $idField = Vtiger_Util_Helper::getPickListId($picklistName);
    $sql = "SELECT {$idField} FROM vtiger_{$picklistName} WHERE {$picklistName} = ?";
    $optionId = $adb->getOne($sql, [$optionValue]);

    $cache[$cacheKey] = $optionId;
    return $optionId;
}

// Implemented by Hieu Nguyen on 2021-01-08 to get record type from record id
function getRecordType($recordId) {
    global $adb;
    static $cache = [];
    if (!empty($cache[$recordId])) return $cache[$recordId];

    $sql = "SELECT setype FROM vtiger_crmentity WHERE crmid = ?";
    $moduleName = $adb->getOne($sql, [$recordId]);
    if (empty($moduleName)) return '';

    $cache[$recordId] = $moduleName;
    return $moduleName;
}

// Implemented by Hieu Nguyen on 2020-10-19 to get vtws id from crmid
function getVtWsIdFromRecordId($recordId) {
    global $adb;
    static $cache = [];
    if (!empty($cache[$recordId])) return $cache[$recordId];

    $moduleName = getRecordType($recordId);
    if (empty($moduleName)) return '';

    $wsId = vtws_getWebserviceEntityId($moduleName, $recordId);
    $cache[$recordId] = $wsId;
    return $wsId;
}

// Implemented by Hieu Nguyen on 2020-10-19 to generate variable options for workflow templates
function generateTemplateVariableOptions($moduleName) {
    static $cache;
    if (!empty($cache[$moduleName])) return $cache[$moduleName];

    $workflowModel = Settings_Workflows_Record_Model::getCleanInstance($moduleName);
    $taskModel = Settings_Workflows_TaskRecord_Model::getCleanInstance($workflowModel, 'VTEmailTask');
    $recordStructureInstance = Settings_Workflows_RecordStructure_Model::getInstanceForWorkFlowModule($workflowModel, 'EditTask');
    $recordStructureInstance->setTaskRecordModel($taskModel);
    $structure = $recordStructureInstance->getStructure();
    $variableOptions = '';
    
    foreach ($structure as $fields) {
        foreach ($fields as $field) {
            if ($field->getName() == 'assinged_user_id') continue;
            $variableSymbol = ($field->get('workflow_pt_lineitem_field')) ? '' : '$';
            $variableName = $field->get('workflow_columnname');
            $displayName = $field->get('workflow_columnlabel');
            
            $variableOptions .= '<option value="'. $variableSymbol . $variableName . $variableSymbol .'">'. $displayName .'</option>';
        }
    }

    $cache[$moduleName] = $variableOptions;
    return $variableOptions;
}

// Implemented by Hieu Nguyen on 2020-10-19 to populate template with record data
function populateTemplateWithRecordData($content, $recordId) {
    require_once('include/Webservices/Retrieve.php');
    require_once('modules/com_vtiger_workflow/VTEntityCache.inc');
    require_once('modules/com_vtiger_workflow/VTSimpleTemplate.inc');
    $vtWsId = getVtWsIdFromRecordId($recordId);
    $user = Users::getRootAdminUser();

    // Get entity cache
    $entityCache = new VTEntityCache($user);
    $entityCache->forId($vtWsId);

    // Populate content
    $contentTemplate = new VTSimpleTemplate($content, true);
    $content = $contentTemplate->render($entityCache, $vtWsId);

    return decodeUTF8($content);
}

// Implemented by Nguyen on 2020-11-10
function getMassActionUrl($actionType, $targetModule) {
    switch ($actionType) {
        case 'send_sms_ott':
            return "index.php?module={$targetModule}&view=MassActionAjax&mode=showSendSMSOTTModal";
            break;
        case 'send_email':
            return "index.php?module={$targetModule}&view=MassActionAjax&mode=showComposeEmailForm&step=step1";
            break;
        case 'transfer_ownership':
            return "index.php?module={$targetModule}&view=MassActionAjax&mode=transferOwnership";
            break;
    }
    
    return '';
}

// Implemented by Hieu Nguyen on 2021-03-16
function renderCommentWithMentions($commentContent) {
    require_once('include/utils/MentionUtils.php');
    return MentionUtils::toDisplay($commentContent);
}

// Implemented by Hieu Nguyen on 2021-08-09 to determine which developer team is accessing Admin page
function checkDeveloperTeam() {
    // Modified the logic to support Dev team to specify Dev access by setting $developerTeam = 'DEV' instead of using domain name
    global $developerTeam;
    if (!empty($developerTeam)) return $developerTeam;

    return 'CUSTOMER';
}

// Implemented by Phu Vo on 2021.08.27 to add format overflow number util function
function formatOverflowNumber($number, $formatUnderMillion = true) {
    global $current_user;
    
    $number = strval($number);
    $currencySeparator = $current_user->currency_grouping_separator ?? ' ';
    $decimalSeparator = $current_user->currency_decimal_separator ?? ' ';

    $number = str_replace($currencySeparator, '', $number);
    $number = str_replace($decimalSeparator, '.', $number);
    $number = floatval($number);

    if ($number < 1000) {
        return CurrencyField::convertToUserFormat($number);
    }

    if ($number < 1000000) {
        if ($formatUnderMillion) {
            return CurrencyField::convertToUserFormat($number / 1000) . ' K';
        }

        return CurrencyField::convertToUserFormat($number);
    }

    if ($number < 1000000000) {
        return CurrencyField::convertToUserFormat($number / 1000000) . ' M';
    }

    return CurrencyField::convertToUserFormat($number / 1000000000) . ' B';
}

// Implemented by Phu Vo on 2021-07-05 to provide util function for duplicate checking customer info
function findCustomersByPhone($phoneNumber, array $customerTypes = [], $returnFirstResult = false) {
    global $adb;

    if (empty($phoneNumber)) {
        if ($returnFirstResult) return null;
        return [];
    }

    // Init default value
    if (empty($customerTypes)) $customerTypes = ['Contacts', 'Leads', 'CPTarget'];

    $matchedCustomers = [];
    $condition = "";
    $queryParams = [];
    $customerTypesString = "('" . join("', '", $customerTypes) . "')";
    $customerTypesOrder = "FIELD(en.setype, '" . join("', '", $customerTypes) . "')";

    $phoneNumber = PBXManager_Logic_Helper::addLeadingZeroToPhoneNumber($phoneNumber); // Add leading zero number in case the call center provider does not have it
    PBXManager_Data_Model::prepareParamsToFindCustomerByPhoneNumber($phoneNumber, $condition, $queryParams);

    $query = "SELECT pl.crmid AS id, pl.setype AS module
        FROM vtiger_pbxmanager_phonelookup AS pl
        INNER JOIN vtiger_crmentity AS en ON (en.crmid = pl.crmid AND en.setype = pl.setype AND en.deleted = 0)
        WHERE {$condition} AND pl.setype IN {$customerTypesString}
        ORDER BY {$customerTypesOrder}";

    if ($returnFirstResult) {
        $query .= " LIMIT 1";
    }

    $result = $adb->pquery($query, $queryParams);

    while ($row = $adb->fetchByAssoc($result)) {
        $row = decodeUTF8($row);
        $matchedCustomers[] = $row;
    }

    if ($returnFirstResult) return $matchedCustomers[0] ?? null;

    return $matchedCustomers;
}

// Implemented by Phu Vo on 2021-07-05 to provide util function for duplicate checking customer info
function findCustomersByEmail($email, array $customerTypes = [], $returnFirstResult = false) {
    global $adb;

    if (empty($email))  {
        if ($returnFirstResult) return null;
        return [];
    }

    // Init default value
    if (empty($customerTypes)) $customerTypes = ['Contacts', 'Leads', 'CPTarget'];

    $matchedCustomers = [];
    $customerTypesString = "('" . join("', '", $customerTypes) . "')";
    $customerTypesOrder = "FIELD(em.setype, '" . join("', '", $customerTypes) . "')";

    $query = "SELECT em.crmid AS id, em.setype AS module
        FROM vtiger_emailslookup AS em
        INNER JOIN vtiger_crmentity AS en ON (en.crmid = em.crmid AND en.setype = em.setype AND en.deleted = 0)
        WHERE em.value = ? AND em.setype IN {$customerTypesString}
        ORDER BY {$customerTypesOrder}";

    if ($returnFirstResult) {
        $query .= " LIMIT 1";
    }

    $result = $adb->pquery($query, [$email]);

    while ($row = $adb->fetchByAssoc($result)) {
        $row = decodeUTF8($row);
        $matchedCustomers[] = $row;
    }

    if ($returnFirstResult) return $matchedCustomers[0] ?? null;

    return $matchedCustomers;
}

// Implemented by Phu Vo on 2021-07-05 to provide util function for duplicate checking customer info
function findCustomersByPhoneOrEmail($phone, $email, array $customerTypes, $returnFirstResult = false) {
    global $adb;

    if (empty($phone) && empty($email))  {
        if ($returnFirstResult) return null;
        return [];
    }

    $matchedCustomers = [];
    $condition = "";
    $queryParams = [];
    $customerTypesString = "('" . join("', '", $customerTypes) . "')";
    $customerTypesOrder = "FIELD(customer.module, '" . join("', '", $customerTypes) . "')";
    
    $phoneNumber = PBXManager_Logic_Helper::addLeadingZeroToPhoneNumber($phone);
    PBXManager_Data_Model::prepareParamsToFindCustomerByPhoneNumber($phoneNumber, $condition, $queryParams);
        
    $sql = "SELECT DISTINCT *
        FROM (
            SELECT pl.crmid AS id, pl.setype AS module
                FROM vtiger_pbxmanager_phonelookup AS pl
                INNER JOIN vtiger_crmentity AS en ON (en.crmid = pl.crmid AND en.setype = pl.setype AND en.deleted = 0)
                INNER JOIN vtiger_tab AS tb ON (tb.name = pl.setype)
                INNER JOIN vtiger_field AS fl ON (fl.fieldname = pl.fieldname AND fl.tabid = tb.tabid)
                WHERE {$condition} AND pl.setype IN {$customerTypesString}
            UNION ALL
            SELECT em.crmid AS id, em.setype AS module
                FROM vtiger_emailslookup AS em
                INNER JOIN vtiger_crmentity AS en ON (en.crmid = em.crmid AND en.setype = em.setype AND en.deleted = 0)
                INNER JOIN vtiger_field AS fl ON (em.fieldid = fl.fieldid)
                WHERE em.value = ? AND en.setype IN {$customerTypesString}
        ) AS customer
        ORDER BY {$customerTypesOrder}";

    $queryParams[] = $email;

    if ($returnFirstResult) {
        $sql .= " LIMIT 1";
    }

    $result = $adb->pquery($sql, [$email]);

    while ($row = $adb->fetchByAssoc($result)) {
        $row = decodeUTF8($row);
        $matchedCustomers[] = $row;
    }

    if ($returnFirstResult) return $matchedCustomers[0] ?? null;

    return $matchedCustomers;
}

function findCustomersByPhoneAndEmail($phone, $email, $customerTypes = [], $returnFirstResult = false) {
    global $adb;

    if (empty($phone) && empty($email))  {
        return [];
    }

    // Init default value
    if (empty($customerTypes)) $customerTypes = ['Contacts', 'Leads', 'CPTarget'];

    $matchedCustomers = [];
    $condition = "";
    $queryParams = [];
    $customerTypesString = "('" . join("', '", $customerTypes) . "')";
    $customerTypesOrder = "FIELD(customer.module, '" . join("', '", $customerTypes) . "')";

    $phoneNumber = PBXManager_Logic_Helper::addLeadingZeroToPhoneNumber($phone);
    PBXManager_Data_Model::prepareParamsToFindCustomerByPhoneNumber($phoneNumber, $condition, $queryParams);
    
    $query = "SELECT en.crmid AS id, en.setype AS module
        FROM vtiger_pbxmanager_phonelookup AS pl
        INNER JOIN vtiger_crmentity AS en ON (en.crmid = pl.crmid AND en.setype = pl.setype)
        INNER JOIN vtiger_emailslookup AS em ON (en.crmid = em.crmid AND en.setype = em.setype)
        WHERE
            en.deleted = 0
            AND {$condition}
            AND em.value = ?
            AND en.setype IN {$customerTypesString}
        ORDER BY {$customerTypesOrder}";
    $queryParams[] = $email;
        
    $result = $adb->pquery($query, $queryParams);

    while ($row = $adb->fetchByAssoc($result)) {
        $row = decodeUTF8($row);
        $matchedCustomers[] = $row;
    }

    if ($returnFirstResult) return $matchedCustomers[0] ?? null;

    return $matchedCustomers;
}

// Added by Hieu Nguyen on 2021-11-23 to get event handler class of the given module
function getEventHandlerClass($moduleName) {
    static $handlerName = null;
    if ($handlerName !== null) return $handlerName;
    $handlerRegisterPath = "modules/{$moduleName}/HandlersRegister.php";

    if (file_exists($handlerRegisterPath)) {
        require_once($handlerRegisterPath);
        $handlerPath = "modules/{$moduleName}/handlers/{$handlerName}.php";

        if (file_exists($handlerPath)) {
            require_once($handlerPath);
        }
    }

    return $handlerName;
}

// Added by Hieu Nguyen on 2022-12-14 to get global event handler class
function getGlobalEventHandlerClass() {
    $handlerName = 'GlobalEventHandler';
    $handlerPath = "modules/Vtiger/handlers/{$handlerName}.php";
    require_once($handlerPath);

    return $handlerName;
}

// Added by Hieu Nguyen on 2021-11-23 to check if the Lead record is converted
function isConvertedLead($leadId) {
    global $adb;
    $sql = "SELECT converted FROM vtiger_leaddetails WHERE leadid = ?";
    $result = $adb->getOne($sql, [$leadId]);
    return $result === '1';
}

// Added by Hieu Nguyen on 2022-08-01 to check if current user role matched the condition roles or a subordinate of the condition roles
function checkRolesAndSubordinates($userEntity, array $conditionRoleIds) {
    global $adb;
    $matchingRoleSqls = [];

    foreach ($conditionRoleIds as $rolId) {
        $roleId = $adb->sql_escape_string($rolId);
        $matchingRoleSqls[] = "FIND_IN_SET('{$roleId}', REPLACE(parentrole, '::', ',')) > 0";
    }

    $sql = "SELECT 1 FROM vtiger_role WHERE roleid = ? AND (". join(' OR ', $matchingRoleSqls) .")";
    $matched = $adb->getOne($sql, [$userEntity->roleid]);
    if ($matched) return true;
    return false;
}

// Added by Hieu Nguyen on 2022-08-02 to generate a random string
function generateRandomString($length = 8) {
    $x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    return substr(str_shuffle(str_repeat($x, ceil($length / strlen($x)))), 1, $length);
}

// Added by Hieu Nguyen on 2022-08-02 to convert a string to url-safe base64 format
function toUrlSafeBase64($str) {
    return rtrim(strtr(base64_encode($str), '+/', '-_'), '=');
}

// Added by Hieu Nguyen on 2022-08-02 to generate a PCKE challenge pair for Oauth authorization
function generatePCKEChallenge($length = 43, $algo = 'SHA256') {
    $codeVerifier = generateRandomString($length);
    $hash = hash($algo, $codeVerifier);
    $codeChallenge = toUrlSafeBase64(pack('H*', $hash));
    
    return [
        'code_verifier' => $codeVerifier,
        'code_challenge' => $codeChallenge
    ];
}

// Added by Vu Mai on 2022-09-09 to get comment relation id
function getCommentRelationId ($module) {
	global $adb;

	$sql = "SELECT related_lists.relation_id
	FROM vtiger_relatedlists AS related_lists
	INNER JOIN vtiger_tab AS parent_tab ON (related_lists.tabid = parent_tab.tabid)
	INNER JOIN vtiger_tab AS related_tab ON (related_lists.related_tabid = related_tab.tabid)
	WHERE parent_tab.name = ? AND related_tab.name = 'ModComments'";
	$params = [$module];
	$result = $adb->getOne($sql, $params);

	return $result;
}

// Added by Hieu on 2022-12-26 to get Vtiger Request object
function getRequest() {
    static $request = null;

    if (empty($request)) {
        $request = new Vtiger_Request($_REQUEST, $_REQUEST);
    }

    return $request;
}


// [Security] Added by Hieu Nguyen on 2022-12-21 to escape dangerous string from request before putting into the query to prevent SQL Injection
function escapeStringForSql($adb, $value) {
    if (is_array($value)) {
        foreach ($value as $key => $val) {
            if (is_array($val)) {
                $value[$key] = escapeStringForSql($adb, $val);
            }
            else {
                $value[$key] = $adb->sql_escape_string($val);
            }
        }

        return $value;
    }
    else {
        return $adb->sql_escape_string($value);
    }
}

// Added by Hieu Nguyen on 2023-01-04 to check if an entity record is exist in table vtiger_crmentity or not
function isEntityRecordExists($recordId, $entityType) {
    global $adb;
    $cacheKey = "{$recordId}-{$entityType}";
    static $cache = [];
    if (isset($cache[$cacheKey])) return $cache[$cacheKey];
    $sql = "SELECT 1 FROM vtiger_crmentity WHERE crmid = ? AND setype = ? AND deleted = 0";
    $result = $adb->getOne($sql, [$recordId, $entityType]);
    $cache[$cacheKey] = !empty($result);
    return $cache[$cacheKey];
}

// Added by Hieu Nguyen on 2023-01-09 to get all groups containing any user in list
function getGroupsByUsers(array $userIds) {
    global $adb;
    if (empty($userIds)) return [];
    $userIdsStr = escapeStringForSql($adb, join(', ', $userIds));
    $sql = "SELECT GROUP_CONCAT(DISTINCT groupid) FROM vtiger_users2group WHERE userid IN ({$userIdsStr})";
    $groupIdsStr = $adb->getOne($sql);
    if (empty($groupIdsStr)) return []; // Prevent return array with 1 element contain empty value after explode when the result is empty

    $groupIds = explode(',', $groupIdsStr);
    return $groupIds;
}