<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ************************************************************************************/

class HelpDesk_Module_Model extends Vtiger_Module_Model {

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
	 * Function to get Settings links for admin user
	 * @return Array
	 */
	public function getSettingLinks() {
		$settingsLinks = parent::getSettingLinks();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();

		if ($currentUserModel->isAdminUser()) {
			$settingsLinks[] = array(
				'linktype' => 'LISTVIEWSETTING',
				'linklabel' => 'LBL_EDIT_MAILSCANNER',
				'linkurl' =>'index.php?parent=Settings&module=MailConverter&view=List',
				'linkicon' => ''
			);
		}
		return $settingsLinks;
	}


	/**
	 * Function returns Tickets grouped by Status
	 * @param type $data
	 * @return <Array>
	 */
	public function getOpenTickets() {
		$db = PearDatabase::getInstance();
		//TODO need to handle security
		$params = array();
		if(vtws_isRoleBasedPicklist('ticketstatus')) {
			$currentUserModel = Users_Record_Model::getCurrentUserModel();
			$picklistvaluesmap = getAssignedPicklistValues("ticketstatus",$currentUserModel->getRole(), $db);
			if(in_array('Open', $picklistvaluesmap)) $params[] = 'Open';
		}
		if(count($params) > 0) {
		$result = $db->pquery('SELECT count(*) AS count, COALESCE(vtiger_groups.groupname,concat(vtiger_users.first_name, " " ,vtiger_users.last_name)) as name, COALESCE(vtiger_groups.groupid,vtiger_users.id) as id  FROM vtiger_troubletickets
						INNER JOIN vtiger_crmentity ON vtiger_troubletickets.ticketid = vtiger_crmentity.crmid
						LEFT JOIN vtiger_users ON vtiger_users.id=vtiger_crmentity.smownerid AND vtiger_users.status="ACTIVE"
						LEFT JOIN vtiger_groups ON vtiger_groups.groupid=vtiger_crmentity.smownerid
						'.Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()).
						' WHERE vtiger_troubletickets.status = ? AND vtiger_crmentity.deleted = 0 GROUP BY smownerid', $params);
		}
		$data = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);

			// [CustomOwnerField][board:p2YrRooc 199] Modidifed by Phu Vo on 2019.11.22
			$row['name'] = Vtiger_Owner_UIType::getCurrentOwnersForDisplay($row['id'], false);
			// End Phu Vo

			$data[] = $row;
		}
		return $data;
	}

	/**
	 * Function returns Tickets grouped by Status
	 * @param type $data
	 * @return <Array>
	 */
	public function getTicketsByStatus($owner, $dateFilter) {
		$db = PearDatabase::getInstance();

		$ownerSql = $this->getOwnerWhereConditionForDashBoards($owner);
		if(!empty($ownerSql)) {
			$ownerSql = ' AND '.$ownerSql;
		}

		$params = array();
		if(!empty($dateFilter)) {
			$dateFilterSql = ' AND createdtime BETWEEN ? AND ? ';
			//appended time frame and converted to db time zone in showwidget.php
			$params[] = $dateFilter['start'];
			$params[] = $dateFilter['end'];
		}
		if(vtws_isRoleBasedPicklist('ticketstatus')) {
			$currentUserModel = Users_Record_Model::getCurrentUserModel();
			$picklistvaluesmap = getAssignedPicklistValues("ticketstatus",$currentUserModel->getRole(), $db);
			foreach($picklistvaluesmap as $picklistValue) $params[] = $picklistValue;
		}

		$result = $db->pquery('SELECT COUNT(*) as count, CASE WHEN vtiger_troubletickets.status IS NULL OR vtiger_troubletickets.status = "" THEN "" ELSE vtiger_troubletickets.status END AS statusvalue 
							FROM vtiger_troubletickets INNER JOIN vtiger_crmentity ON vtiger_troubletickets.ticketid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted=0
							'.Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()). $ownerSql .' '.$dateFilterSql.
							' INNER JOIN vtiger_ticketstatus ON vtiger_troubletickets.status = vtiger_ticketstatus.ticketstatus 
							WHERE vtiger_troubletickets.status IN ('.generateQuestionMarks($picklistvaluesmap).') 
							GROUP BY statusvalue ORDER BY vtiger_ticketstatus.sortorderid', $params);

		$response = array();

		for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$response[$i][0] = $row['count'];
			$ticketStatusVal = $row['statusvalue'];
			if($ticketStatusVal == '') {
				$ticketStatusVal = 'LBL_BLANK';
			}
			$response[$i][1] = vtranslate($ticketStatusVal, $this->getName());
			$response[$i][2] = $ticketStatusVal;
		}
		return $response;
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
			// Modified by Phu Vo on 2020.02.25 using query generator to get related activities
			global $current_user;

			// We will use query generator to create dynamic select query base on related list config
			$queryGenerator = new EnhancedQueryGenerator('Calendar', $current_user);

			// Contains a mapped table (column) => (fieldname)
			$relatedFields = $relatedModule->getConfigureRelatedListFields();

			// Perform action on query generator with field name (value) from $relatedFields
			$queryGenerator->setFields(array_values($relatedFields));

			// Extra conditions
			$queryGenerator->addCondition('activitytype', 'Emails', 'n', QueryGenerator::$AND);

			// Manual generate query with extra more custom conditions
			$query = $queryGenerator->getQuery();
			$query .= " AND vtiger_seactivityrel.crmid = {$recordId}";

			// Split query to components
			$queryComponents = preg_split('/ FROM /i', $query);

			// Process parent_id field
			if (isset(array_flip($relatedFields)['parent_id'])) {
				$queryComponents[0] = str_replace('vtiger_seactivityrel.crmid', 'vtiger_seactivityrel.crmid AS parent_id', $queryComponents[0]);
			}

			// Add activity id as crmid column
			$queryComponents[0] .= ', vtiger_crmentity.crmid';
			$query = join(' FROM ', $queryComponents);

			$relatedModuleName = $relatedModule->getName();
			$query .= $this->getSpecificRelationQuery($relatedModuleName);
			// End Phu Vo

			// Added by Hieu Nguyen on 2022-01-19 to remove duplicated rows when an activity is related to multiple customer type (Account & Contact & Lead)
			$query .= " GROUP BY vtiger_activity.activityid";
			// End Hieu Nguyen
		} else {
			$query = parent::getRelationQuery($recordId, $functionName, $relatedModule, $relationId);
		}

		return $query;
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
		if (in_array($sourceModule, array('Assets', 'Project', 'ServiceContracts', 'Services'))) {
			$condition = " vtiger_troubletickets.ticketid NOT IN (SELECT relcrmid FROM vtiger_crmentityrel WHERE crmid = '$record' UNION SELECT crmid FROM vtiger_crmentityrel WHERE relcrmid = '$record') ";
			$pos = stripos($listQuery, 'where');

			if ($pos) {
				$split = preg_split('/where/i', $listQuery);

                // Added by Hieu Nguyen on 2019-06-21 to fix bug filter error when apply subquery with sub WHERE
                $split = fixSplittedQueryPartsByWhere($split);
                // End Hieu Nguyen

				$overRideQuery = $split[0] . ' WHERE ' . $split[1] . ' AND ' . $condition;
			} else {
				$overRideQuery = $listQuery . ' WHERE ' . $condition;
			}
			return $overRideQuery;
		}
	}

	 /**
	 * Function to get list of field for header view
	 * @return <Array> list of field models <Vtiger_Field_Model>
	 */
	function getConfigureRelatedListFields(){
        // Added by Hieu Nguyen on 2019-12-26 to support relation list layout editor
        $configuredFields = Settings_LayoutEditor_Module_Model::getModuleLayoutFields($this->getName(), 'RelationList');
        if (!empty($configuredFields)) return $configuredFields;
        // End Hieu Nguyen

		$summaryViewFields = $this->getSummaryViewFieldsList();
		$headerViewFields = $this->getHeaderViewFieldsList();
		$allRelationListViewFields = array_merge($headerViewFields,$summaryViewFields);
		$relatedListFields = array();
		if(count($allRelationListViewFields) > 0) {
			foreach ($allRelationListViewFields as $key => $field) {
				$relatedListFields[$field->get('column')] = $field->get('name');
			}
		}

		if(count($relatedListFields)>0) {
			$nameFields = $this->getNameFields();
			foreach($nameFields as $fieldName){
				if(!$relatedListFields[$fieldName]) {
					$fieldModel = $this->getField($fieldName);
					$relatedListFields[$fieldModel->get('column')] = $fieldModel->get('name');
				}
			}
		}

		return $relatedListFields;
	}
}
