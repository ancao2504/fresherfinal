<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ************************************************************************************/

class Accounts_Module_Model extends Vtiger_Module_Model {

	/**
	 * Function to get the Quick Links for the module
	 * @param <Array> $linkParams
	 * @return <Array> List of Vtiger_Link_Model instances
	 */
	public function getSideBarLinks($linkParams) {
		$parentQuickLinks = parent::getSideBarLinks($linkParams);

		$quickLink = array(
			'linktype' => 'SIDEBARLINK',
			'linklabel' => 'LBL_DASHBOARD',
			'linkurl' => $this->getDashBoardUrl(),
			'linkicon' => '',
		);

		//Check profile permissions for Dashboards
		$moduleModel = Vtiger_Module_Model::getInstance('Dashboard');
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());
		if($permission) {
			$parentQuickLinks['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues($quickLink);
		}
		
		return $parentQuickLinks;
	}

	/**
	 * Function to get list view query for popup window
	 * @param <String> $sourceModule Parent module
	 * @param <String> $field parent fieldname
	 * @param <Integer> $record parent id
	 * @param <String> $listQuery
	 * @return <String> Listview Query
	 */
	public function getQueryByModuleField($sourceModule, $field, $record, $listQuery) {
		if (($sourceModule == 'Accounts' && $field == 'account_id' && $record)
				|| in_array($sourceModule, array('Campaigns', 'Products', 'Services', 'Emails'))) {

			if ($sourceModule === 'Campaigns') {
				$condition = " vtiger_account.accountid NOT IN (SELECT accountid FROM vtiger_campaignaccountrel WHERE campaignid = '$record')";
			} elseif ($sourceModule === 'Products') {
				$condition = " vtiger_account.accountid NOT IN (SELECT crmid FROM vtiger_seproductsrel WHERE productid = '$record')";
			} elseif ($sourceModule === 'Services') {
				$condition = " vtiger_account.accountid NOT IN (SELECT relcrmid FROM vtiger_crmentityrel WHERE crmid = '$record' UNION SELECT crmid FROM vtiger_crmentityrel WHERE relcrmid = '$record') ";
			} elseif ($sourceModule === 'Emails') {
				$condition = ' vtiger_account.emailoptout = 0';
			} else {
				$condition = " vtiger_account.accountid != '$record'";
			}

			$position = stripos($listQuery, 'where');
			if($position) {
				$split = preg_split('/where/i', $listQuery);

                // Added by Hieu Nguyen on 2019-06-21 to fix bug filter error when apply subquery with sub WHERE
                $split = fixSplittedQueryPartsByWhere($split);
                // End Hieu Nguyen

				$overRideQuery = $split[0] . ' WHERE ' . $split[1] . ' AND ' . $condition;
			} else {
				$overRideQuery = $listQuery. ' WHERE ' . $condition;
			}
			return $overRideQuery;
		}
	}

	/**
	 * Function to get relation query for particular module with function name
	 * @param <record> $recordId
	 * @param <String> $functionName
	 * @param Vtiger_Module_Model $relatedModule
	 * @return <String>
	 */
	public function getRelationQuery($recordId, $functionName, $relatedModule, $relationId) {
		if ($functionName === 'get_activities') {
			$focus = CRMEntity::getInstance($this->getName());
			$focus->id = $recordId;
			$entityIds = $focus->getRelatedContactsIds();
			$entityIds = implode(',', $entityIds);

			// Modified by Phu Vo on 2020.02.25 using query generator to get related activities
			global $current_user;

			// We will use query generator to create dynamic select query base on related list config
			$queryGenerator = new EnhancedQueryGenerator('Calendar', $current_user);

			// Contains a mapped table (column) => (fieldname)
			$relatedFields = $relatedModule->getConfigureRelatedListFields();

			// Perform action on query generator with field name (value) from $relatedFields
			$queryGenerator->setFields(array_values($relatedFields));
			$queryGenerator->addCondition('activitytype', 'Emails', 'n', QueryGenerator::$AND);
			$query = $queryGenerator->getQuery();

			// Added by Hieu Nguyen on 2022-01-18 to show related activities stored in table vtiger_cntactivityrel in case related Contact field is not add to the list
			if (strpos($query, 'vtiger_cntactivityrel') === false) {
				$extraJoin = " LEFT JOIN vtiger_cntactivityrel ON (vtiger_cntactivityrel.activityid = vtiger_activity.activityid)";
				$query = appendFromClauseToQuery($query, $extraJoin);
			}
			// End Hieu Nguyen

			// Manual generate query with extra more custom conditions
			$query .= " AND (vtiger_activity.related_account = {$recordId}";	// Modified condition by Hieu Nguyen on 2022-01-19

			// Split query to components
			$queryComponents = preg_split('/ FROM /i', $query);

			// Add activity id as crmid column
			$queryComponents[0] .= ', vtiger_crmentity.crmid';
			$query = join(' FROM ', $queryComponents);

			if($entityIds) {
				$query .= " OR vtiger_cntactivityrel.contactid IN (".$entityIds.")";
				$query .= " OR vtiger_seactivityrel.crmid IN (".$entityIds."))";
			} else {
				$query .= ")";
			}

			$relatedModuleName = $relatedModule->getName();
			$query .= $this->getSpecificRelationQuery($relatedModuleName);
			// End Phu Vo

			// There could be more than one contact for an activity.
			$query .= ' GROUP BY vtiger_activity.activityid';
		} else {
			$query = parent::getRelationQuery($recordId, $functionName, $relatedModule, $relationId);
		}

		return $query;
	}

	/**
	 * Function returns the Calendar Events for the module
	 * @param <String> $mode - upcoming/overdue mode
	 * @param <Vtiger_Paging_Model> $pagingModel - $pagingModel
	 * @param <String> $user - all/userid
	 * @param <String> $recordId - record id
	 * @return <Array>
	 */
	function getCalendarActivities($mode, $pagingModel, $user, $recordId = false) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$db = PearDatabase::getInstance();

		if (!$user) {
			$user = $currentUser->getId();
		}

		$nowInUserFormat = Vtiger_Datetime_UIType::getDisplayDateTimeValue(date('Y-m-d H:i:s'));
		$nowInDBFormat = Vtiger_Datetime_UIType::getDBDateTimeValue($nowInUserFormat);
		list($currentDate, $currentTime) = explode(' ', $nowInDBFormat);

		$focus = CRMEntity::getInstance($this->getName());
		$focus->id = $recordId;
		$entityIds = $focus->getRelatedContactsIds();
		$entityIds = implode(',', $entityIds);

        // Modified query by Hieu Nguyen on 2020-12-25 to add field main_owner_id
		$query = "SELECT DISTINCT vtiger_crmentity.crmid, (CASE WHEN (crmentity2.crmid not like '') THEN crmentity2.crmid ELSE crmentity3.crmid END) AS parent_id, 
                (CASE WHEN (crmentity2.setype not like '') then crmentity2.setype ELSE crmentity3.setype END) AS crmentity2module, vtiger_crmentity.smownerid, 
                vtiger_crmentity.main_owner_id, vtiger_crmentity.setype, vtiger_activity.* FROM vtiger_activity
            INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_activity.activityid
            LEFT JOIN vtiger_seactivityrel ON vtiger_seactivityrel.activityid = vtiger_activity.activityid
            LEFT JOIN vtiger_cntactivityrel ON vtiger_cntactivityrel.activityid = vtiger_activity.activityid
            LEFT JOIN vtiger_crmentity as crmentity2 on (vtiger_seactivityrel.crmid = crmentity2.crmid AND vtiger_seactivityrel.crmid IS NOT NULL AND crmentity2.deleted = 0)
            LEFT JOIN vtiger_crmentity as crmentity3 on (vtiger_cntactivityrel.contactid = crmentity3.crmid AND vtiger_cntactivityrel.contactid IS NOT NULL AND crmentity3.deleted = 0)
            LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid";

		$query .= Users_Privileges_Model::getNonAdminAccessControlQuery('Calendar');

        // Modified query condition by Hieu Nguyen on 2020-12-25 to show active activities only
		$query .= " WHERE vtiger_crmentity.deleted = 0
            AND (vtiger_activity.activitytype NOT IN ('Emails'))
            AND (vtiger_activity.status is NULL OR vtiger_activity.status NOT IN ('Completed', 'Deferred', 'Cancelled'))
            AND (vtiger_activity.eventstatus is NULL OR vtiger_activity.eventstatus NOT IN ('Held', 'Not Held', 'Cancelled'))";

		if (!$currentUser->isAdminUser() && !$recordId) { // Modified condition by Hieu Nguyen on 2020-12-24 to skip extra condition in Upcoming Activities at SummaryView
			$moduleFocus = CRMEntity::getInstance('Calendar');
			$condition = $moduleFocus->buildWhereClauseConditionForCalendar();
			if($condition) {
				$query .= ' AND '.$condition;
			}
		}

		if ($mode === 'upcoming') {
			$query .= " AND CASE WHEN vtiger_activity.activitytype='Task' THEN due_date >= '$currentDate' ELSE CONCAT(due_date,' ',time_end) >= '$nowInDBFormat' END";
		} elseif ($mode === 'overdue') {
			$query .= " AND CASE WHEN vtiger_activity.activitytype='Task' THEN due_date < '$currentDate' ELSE CONCAT(due_date,' ',time_end) < '$nowInDBFormat' END";
		}

		$params = array();

		if ($recordId) {
			$query .= " AND (vtiger_seactivityrel.crmid = ?";
			array_push($params, $recordId);
			if ($entityIds) {
				$query .= " OR vtiger_cntactivityrel.contactid IN (" . $entityIds . "))";
			} else {
				$query .= ")";
			}
		}

		if ($user != 'all' && $user != '') {
			$query .= " AND vtiger_crmentity.smownerid = ?";
			array_push($params, $user);
		}

		$query .= " ORDER BY date_start, time_start LIMIT " . $pagingModel->getStartIndex() . ", " . ($pagingModel->getPageLimit() + 1);

		// Modified by Hieu Nguyen on 2020-12-25 to make the block of code below reusable and easy to maintain
		$parentRecordModel = Vtiger_Record_Model::getInstanceById($recordId, 'Accounts');
		return $this->getRelatedActivityRecordsForWidget($db, $query, $params, $currentUser, $pagingModel, $parentRecordModel);
        // End Hieu Nguyen
	}
}
