<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Accounts_Record_Model extends Vtiger_Record_Model {
		/**
	 * Function to get the summary information for module
	 * @return <array> - values which need to be shown as summary
	 */
	public function getSummaryInfo() {
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$projectTaskInstance = Vtiger_Module_Model::getInstance('ProjectTask');
		if($userPrivilegesModel->hasModulePermission($projectTaskInstance->getId())) {
			$adb = PearDatabase::getInstance();

			$query ='SELECT smownerid,enddate,projecttaskstatus,projecttaskpriority
					FROM vtiger_projecttask
							INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_projecttask.projecttaskid
								AND vtiger_crmentity.deleted=0
							WHERE vtiger_projecttask.projectid = ? ';

			$result = $adb->pquery($query, array($this->getId()));

			$tasksOpen = $taskCompleted = $taskDue = $taskDeferred = $numOfPeople = 0;
			$highTasks = $lowTasks = $normalTasks = $otherTasks = 0;
			$currentDate = date('Y-m-d');
			$inProgressStatus = array('Open', 'In Progress');
			$usersList = array();

			while($row = $adb->fetchByAssoc($result)) {
				$projectTaskStatus = $row['projecttaskstatus'];
				switch($projectTaskStatus){
					case 'Open'		: $tasksOpen++;		break;
					case 'Deferred'	: $taskDeferred++;	break;
					case 'Completed': $taskCompleted++;	break;
				}
				$projectTaskPriority = $row['projecttaskpriority'];
				switch($projectTaskPriority){
					case 'high' : $highTasks++;break;
					case 'low' : $lowTasks++;break;
					case 'normal' : $normalTasks++;break;
					default : $otherTasks++;break;
				}

				if(!empty($row['enddate']) && (strtotime($row['enddate']) < strtotime($currentDate)) &&
						(in_array($row['projecttaskstatus'], $inProgressStatus))) {
					$taskDue++;
				}
				$usersList[] = $row['smownerid'];
			}

			$usersList = array_unique($usersList);
			$numOfPeople = count($usersList);

			$summaryInfo['projecttaskstatus'] =  array(
													'LBL_TASKS_OPEN'	=> $tasksOpen,
													'Progress'			=> $this->get('progress'),
													'LBL_TASKS_DUE'		=> $taskDue,
													'LBL_TASKS_COMPLETED'=> $taskCompleted,
			);

			$summaryInfo['projecttaskpriority'] =  array(
													'LBL_TASKS_HIGH'	=> $highTasks,
													'LBL_TASKS_NORMAL'	=> $normalTasks,
													'LBL_TASKS_LOW'		=> $lowTasks,
													'LBL_TASKS_OTHER'	=> $otherTasks,
			);
		}

		return $summaryInfo;
	}


	/**
	 * Function returns the details of Accounts Hierarchy
	 * @return <Array>
	 */
	function getAccountHierarchy() {
		$focus = CRMEntity::getInstance($this->getModuleName());
		$hierarchy = $focus->getAccountHierarchy($this->getId());
		$i=0;
		foreach($hierarchy['entries'] as $accountId => $accountInfo) {
			preg_match('/<a href="+/', $accountInfo[0], $matches);
			if($matches != null) {
				preg_match('/[.\s]+/', $accountInfo[0], $dashes);
				preg_match("/<a(.*)>(.*)<\/a>/i",$accountInfo[0], $name);

				$recordModel = Vtiger_Record_Model::getCleanInstance('Accounts');
				$recordModel->setId($accountId);
				$hierarchy['entries'][$accountId][0] = $dashes[0]."<a href=".$recordModel->getDetailViewUrl().">".$name[2]."</a>";
			}
		}
		return $hierarchy;
	}

	/**
	 * Function returns the url for create event
	 * @return <String>
	 */
	function getCreateEventUrl() {
		$calendarModuleModel = Vtiger_Module_Model::getInstance('Calendar');
		return $calendarModuleModel->getCreateEventRecordUrl().'&parent_id='.$this->getId();
	}

	/**
	 * Function returns the url for create todo
	 * @retun <String>
	 */
	function getCreateTaskUrl() {
		$calendarModuleModel = Vtiger_Module_Model::getInstance('Calendar');
		return $calendarModuleModel->getCreateTaskRecordUrl().'&parent_id='.$this->getId();
	}

	/**
	 * Function to check duplicate exists or not
	 * @return <boolean>
	 */
	public function checkDuplicate() {
		$db = PearDatabase::getInstance();

		$query = "SELECT 1 FROM vtiger_crmentity WHERE setype = ? AND label = ? AND deleted = 0";
                $params = array($this->getModule()->getName(), decode_html($this->getName())); 

		$record = $this->getId();
		if ($record) {
			$query .= " AND crmid != ?";
			array_push($params, $record);
		}

		$result = $db->pquery($query, $params);
		if ($db->num_rows($result)) {
			return true;
		}
		return false;
	}

	/**
	 * Function to get List of Fields which are related from Accounts to Inventory Record.
	 * @return <array>
	 */
	public function getInventoryMappingFields() {
		return array(
				//Billing Address Fields
				array('parentField'=>'bill_city', 'inventoryField'=>'bill_city', 'defaultValue'=>''),
				array('parentField'=>'bill_street', 'inventoryField'=>'bill_street', 'defaultValue'=>''),
				array('parentField'=>'bill_state', 'inventoryField'=>'bill_state', 'defaultValue'=>''),
				array('parentField'=>'bill_code', 'inventoryField'=>'bill_code', 'defaultValue'=>''),
				array('parentField'=>'bill_country', 'inventoryField'=>'bill_country', 'defaultValue'=>''),
				array('parentField'=>'bill_pobox', 'inventoryField'=>'bill_pobox', 'defaultValue'=>''),

				//Shipping Address Fields
				array('parentField'=>'ship_city', 'inventoryField'=>'ship_city', 'defaultValue'=>''),
				array('parentField'=>'ship_street', 'inventoryField'=>'ship_street', 'defaultValue'=>''),
				array('parentField'=>'ship_state', 'inventoryField'=>'ship_state', 'defaultValue'=>''),
				array('parentField'=>'ship_code', 'inventoryField'=>'ship_code', 'defaultValue'=>''),
				array('parentField'=>'ship_country', 'inventoryField'=>'ship_country', 'defaultValue'=>''),
				array('parentField'=>'ship_pobox', 'inventoryField'=>'ship_pobox', 'defaultValue'=>'')
		);
	}

	/**
	 * Extended and Modifiied by Phu Vo on 2019.09.16 to prevent delete personal Account
	 */
	public function delete() {
		if (self::isPersonalAccount($this->getId())) return;
		parent::delete();
	}

	/**
	 * Extended and Modifiied by Phu Vo on 2019.09.16 to prevent delete personal Account
	 */
	public function isDeletable() {
		if (self::isPersonalAccount($this->getId())) return false;
		return parent::isDeletable();
	}

	/**
	 * Extended and Modifiied by Phu Vo on 2019.09.16 to prevent delete personal Account
	 */
	public function isEditable() {
		if (self::isPersonalAccount($this->getId())) return false;
		return parent::isEditable();
	}

	/**
	 * Check if this record is personal account
	 * @param Number recordId
	 * @return Boolean True for personal account
	 * @author Phu Vo (2019.09.16)
	 */
	static function isPersonalAccount($recordId) {
		global $adb;

		$sql = "SELECT 1 FROM vtiger_account AS a 
			INNER JOIN vtiger_crmentity AS e ON (a.accountid = e.crmid AND e.deleted = 0) 
			WHERE a.accountid = ? AND a.account_no = 'PACC'
		";
		$result = $adb->pquery($sql, [$recordId]);

		return $adb->fetchByAssoc($result) ? true : false;
	}

	// Added by Phuc on 2020.06.26 to save type
	static function updateAccountType($accountId, $type) {
		$db = PearDatabase::getInstance();
		$sql = "UPDATE vtiger_account SET account_type = ? WHERE accountid = ?";
		$result = $db->pquery($sql, [$type, $accountId]);
	}
	static function getAccountsByType($type) {
		global $adb; 
		$sql = "SELECT accountid, accountname, annual_revenues, leadsource, phone, email1 FROM vtiger_account AS a 
			INNER JOIN vtiger_crmentity AS e ON (a.accountid = e.crmid AND e.deleted = 0) WHERE a.account_type = ?";
		$result = $adb->pquery($sql, [$type]); // Thực hiện truy vấn với tham số
	
		return $result; 
	}
	static function deleteAccounts($accountId) {
		$db = PearDatabase::getInstance();
	
		// Thực hiện cập nhật trạng thái xóa trên bảng vtiger_crmentity
		$sqlDelete = "UPDATE vtiger_crmentity SET deleted = 1 WHERE crmid = ?";
		$resultDelete = $db->pquery($sqlDelete, [$accountId]);
	
		// Kiểm tra nếu truy vấn thành công và có ít nhất một bản ghi bị ảnh hưởng
		$isDeleted = $resultDelete && $db->getAffectedRowCount($resultDelete) > 0;
	
		return [
			'check' => $isDeleted,
			'message' => $isDeleted ? 'Record deleted successfully.' : 'No record found to delete.'
		];
	}
	static function summaryLeadByIdAccount($accountId) {
		global $adb; // Sử dụng đối tượng PearDatabase toàn cục
	
		// Truy vấn SQL để đếm số lượng Lead theo accountId
		$sql = "SELECT DISTINCT vtiger_crmentity.crmid,vtiger_contactdetails.salutation, vtiger_crmentity.label, vtiger_contactdetails.accountid, vtiger_contactdetails.title, vtiger_contactdetails.mobile, vtiger_contactdetails.email, vtiger_contactdetails.phone, vtiger_contactdetails.remark, vtiger_crmentity.smownerid FROM vtiger_contactdetails INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid LEFT JOIN vtiger_account ON vtiger_account.accountid = vtiger_contactdetails.accountid INNER JOIN vtiger_contactaddress ON vtiger_contactdetails.contactid = vtiger_contactaddress.contactaddressid INNER JOIN vtiger_contactsubdetails ON vtiger_contactdetails.contactid = vtiger_contactsubdetails.contactsubscriptionid INNER JOIN vtiger_customerdetails ON vtiger_contactdetails.contactid = vtiger_customerdetails.customerid INNER JOIN vtiger_contactscf ON vtiger_contactdetails.contactid = vtiger_contactscf.contactid LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid = vtiger_users.id  WHERE vtiger_crmentity.deleted = 0 AND vtiger_contactdetails.accountid =?";
	
		// Thực hiện truy vấn
		$result = $adb->pquery($sql, [$accountId]);
	
		// Kiểm tra kết quả
		$count = $adb->getRowCount($result);
		return $count;
		
	}
	static function summaryPotentialByIdAccount($accountId) {
		global $adb; // Sử dụng đối tượng PearDatabase toàn cục
	
		// Truy vấn SQL để đếm số lượng Lead theo accountId
		$sql = "SELECT DISTINCT vtiger_crmentity.crmid,vtiger_potential.potentialname, vtiger_potential.related_to, vtiger_potential.amount, vtiger_potential.sales_stage, vtiger_potential.contact_id, vtiger_potential.nextstep, vtiger_potential.probability, vtiger_potential.closingdate, vtiger_potential.rating, vtiger_potential.rating_description, vtiger_potential.actual_closing_date, vtiger_crmentity.smownerid FROM vtiger_potential INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_potential.potentialid LEFT JOIN vtiger_account ON vtiger_account.accountid = vtiger_potential.related_to INNER JOIN vtiger_potentialscf ON vtiger_potential.potentialid = vtiger_potentialscf.potentialid LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid = vtiger_users.id LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid  WHERE vtiger_crmentity.deleted = 0 AND (vtiger_potential.related_to = ? OR vtiger_potential.contact_id IN (2217,2221))";
	
		// Thực hiện truy vấn
		$result = $adb->pquery($sql, [$accountId]);
	
		// Kiểm tra kết quả
		$count = $adb->getRowCount($result);
		return $count;
		
	}
	static function summarySaleOrderByIdAccount($accountId) {
		global $adb; // Sử dụng đối tượng PearDatabase toàn cục
	
		// Truy vấn SQL để đếm số lượng Lead theo accountId
		$sql = "SELECT DISTINCT vtiger_crmentity.crmid,vtiger_salesorder.subject, vtiger_salesorder.accountid, vtiger_salesorder.contactid, vtiger_salesorder.total, vtiger_salesorder.sostatus, vtiger_salesorder.salesorder_shipping_status, vtiger_salesorder.salesorder_payment_status, vtiger_salesorder.leadsource, vtiger_salesorder.is_sign_contract, vtiger_crmentity.smownerid FROM vtiger_salesorder INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_salesorder.salesorderid LEFT OUTER JOIN vtiger_quotes ON vtiger_quotes.quoteid = vtiger_salesorder.quoteid LEFT OUTER JOIN vtiger_account ON vtiger_account.accountid = vtiger_salesorder.accountid LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid LEFT JOIN vtiger_invoice_recurring_info ON vtiger_invoice_recurring_info.start_period = vtiger_salesorder.salesorderid LEFT JOIN vtiger_salesordercf ON vtiger_salesordercf.salesorderid = vtiger_salesorder.salesorderid LEFT JOIN vtiger_sobillads ON vtiger_sobillads.sobilladdressid = vtiger_salesorder.salesorderid LEFT JOIN vtiger_soshipads ON vtiger_soshipads.soshipaddressid = vtiger_salesorder.salesorderid LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid = vtiger_users.id  WHERE vtiger_crmentity.deleted = 0 AND (vtiger_salesorder.accountid = ? OR vtiger_salesorder.contactid IN (2217,2221,2223))";
	
		// Thực hiện truy vấn
		$result = $adb->pquery($sql, [$accountId]);
	
		// Kiểm tra kết quả
		$count = $adb->getRowCount($result);
		return $count;
		
	}
	static function getTotalSalesByAccountId($accountId) {
		global $adb; // Sử dụng đối tượng PearDatabase toàn cục
	
		// Truy vấn SQL để tính tổng doanh số theo accountId
		$sql = "SELECT 
					SUM(vtiger_salesorder.total) AS total_sales
				FROM 
					vtiger_salesorder 
				INNER JOIN 
					vtiger_crmentity 
				ON 
					vtiger_crmentity.crmid = vtiger_salesorder.salesorderid
				WHERE 
					vtiger_crmentity.deleted = 0 
					AND vtiger_salesorder.accountid = ?";
	
		// Thực hiện truy vấn
		$result = $adb->pquery($sql, [$accountId]);
	
		// Lấy giá trị tổng doanh số
		$row = $adb->fetchByAssoc($result);
		return $row['total_sales'] ?? 0;
	}
	
	// Ended by Phuc
}
