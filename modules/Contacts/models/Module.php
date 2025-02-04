<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ************************************************************************************/

class Contacts_Module_Model extends Vtiger_Module_Model {
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
	 * Function returns the Calendar Events for the module
	 * @param <Vtiger_Paging_Model> $pagingModel
	 * @return <Array>
	 */
	public function getCalendarActivities($mode, $pagingModel, $user, $recordId = false) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$db = PearDatabase::getInstance();

		if (!$user) {
			$user = $currentUser->getId();
		}

		$nowInUserFormat = Vtiger_Datetime_UIType::getDisplayDateTimeValue(date('Y-m-d H:i:s'));
		$nowInDBFormat = Vtiger_Datetime_UIType::getDBDateTimeValue($nowInUserFormat);
		list($currentDate, $currentTime) = explode(' ', $nowInDBFormat);

        // Modified by Hieu Nguyen on 2020-12-23 to add keyword DISTINCT and replace INNER JOIN with LEFT JOIN to display both related and invited activities
		$query = "SELECT DISTINCT vtiger_crmentity.crmid, ce.crmid AS contact_id, vtiger_crmentity.smownerid, vtiger_crmentity.main_owner_id, vtiger_crmentity.setype, vtiger_activity.* 
            FROM vtiger_activity
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_activity.activityid)
            LEFT JOIN vtiger_cntactivityrel ON (vtiger_cntactivityrel.activityid = vtiger_activity.activityid)
            LEFT JOIN vtiger_crmentity AS ce ON (vtiger_cntactivityrel.contactid = ce.crmid AND ce.deleted = 0)
            LEFT JOIN vtiger_groups ON (vtiger_groups.groupid = vtiger_crmentity.smownerid)"; // Updated by Phuc on 2020.07.09 to change table for event
        // End Hieu Nguyen

		$query .= Users_Privileges_Model::getNonAdminAccessControlQuery('Calendar');

        // Modified query condition by Hieu Nguyen on 2020-12-25 to show active activities only
		$query .= " WHERE vtiger_crmentity.deleted = 0
            AND (vtiger_activity.activitytype NOT IN ('Emails'))
            AND (vtiger_activity.status is NULL OR vtiger_activity.status NOT IN ('Completed', 'Deferred', 'Cancelled'))
            AND (vtiger_activity.eventstatus is NULL OR vtiger_activity.eventstatus NOT IN ('Held', 'Not Held', 'Cancelled'))";

        // Added by Hieu Nguyen on 2020-12-24 to show customer's accepted invitation
        $extraJoin = " LEFT JOIN vtiger_invitees ON (vtiger_activity.activityid = vtiger_invitees.activityid AND vtiger_invitees.status = 'Accepted')";
        $query = appendFromClauseToQuery($query, $extraJoin);
        // End Hieu Nguyen

		if (!$currentUser->isAdminUser() && !$recordId) { // Modified condition by Hieu Nguyen on 2020-12-24 to skip extra condition in Upcoming Activities at SummaryView
			$moduleFocus = CRMEntity::getInstance('Calendar');
			$condition = $moduleFocus->buildWhereClauseConditionForCalendar();
			if($condition) {
				$query .= ' AND '.$condition;
			}
		}

        // Modified by Hieu Nguyen on 2020-12-24 to display both related and invited activities
        $params = [];

		if ($recordId) {
			$query .= " AND (vtiger_cntactivityrel.contactid = ? OR vtiger_invitees.inviteeid = ?)";
            $params[] = $recordId;
            $params[] = $recordId;
		} 
        elseif ($mode == 'upcoming') {
			$query .= " AND CASE WHEN vtiger_activity.activitytype = 'Task' THEN due_date >= '{$currentDate}' ELSE CONCAT(due_date, ' ', time_end) >= '{$nowInDBFormat}' END";
		} 
        elseif ($mode == 'overdue') {
			$query .= " AND CASE WHEN vtiger_activity.activitytype = 'Task' THEN due_date < '{$currentDate}' ELSE CONCAT(due_date, ' ', time_end) < '{$nowInDBFormat}' END";
		}
        // End Hieu Nguyen

		if($user != 'all' && $user != '') {
			$query .= " AND vtiger_crmentity.smownerid = ?";
			array_push($params, $user);
		}

		$query .= " ORDER BY date_start, time_start LIMIT ". $pagingModel->getStartIndex() .", ". ($pagingModel->getPageLimit()+1);

        // Modified by Hieu Nguyen on 2020-12-25 to make the block of code below reusable and easy to maintain
        $parentRecordModel = Vtiger_Record_Model::getInstanceById($recordId, 'Contacts');
		return $this->getRelatedActivityRecordsForWidget($db, $query, $params, $currentUser, $pagingModel, $parentRecordModel);
        // End Hieu Nguyen
	}

	/**
	 * Function returns query for module record's search
	 * @param <String> $searchValue - part of record name (label column of crmentity table)
	 * @param <Integer> $parentId - parent record id
	 * @param <String> $parentModule - parent module name
	 * @return <String> - query
	 */
	function getSearchRecordsQuery($searchValue, $searchFields, $parentId=false, $parentModule=false) {
		if($parentId && $parentModule == 'Accounts') {
			$query = "SELECT ".implode(',',$searchFields)." FROM vtiger_crmentity
						INNER JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
						WHERE deleted = 0 AND vtiger_contactdetails.accountid = $parentId AND label like '%$searchValue%'";
			return $query;
		} else if($parentId && $parentModule == 'Potentials') {
			$query = "SELECT ".implode(',',$searchFields)." FROM vtiger_crmentity
						INNER JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
						LEFT JOIN vtiger_contpotentialrel ON vtiger_contpotentialrel.contactid = vtiger_contactdetails.contactid
						LEFT JOIN vtiger_potential ON vtiger_potential.contact_id = vtiger_contactdetails.contactid
						WHERE deleted = 0 AND (vtiger_contpotentialrel.potentialid = $parentId OR vtiger_potential.potentialid = $parentId)
						AND label like '%$searchValue%'";
			
				return $query;
		} else if ($parentId && $parentModule == 'HelpDesk') {
            $query = "SELECT ".implode(',',$searchFields)." FROM vtiger_crmentity
                        INNER JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
                        INNER JOIN vtiger_troubletickets ON vtiger_troubletickets.contact_id = vtiger_contactdetails.contactid
                        WHERE deleted=0 AND vtiger_troubletickets.ticketid  = $parentId  AND label like '%$searchValue%'";

            return $query;
        } else if($parentId && $parentModule == 'Campaigns') {
            $query = "SELECT ".implode(',',$searchFields)." FROM vtiger_crmentity
                        INNER JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
                        INNER JOIN vtiger_campaigncontrel ON vtiger_campaigncontrel.contactid = vtiger_contactdetails.contactid
                        WHERE deleted=0 AND vtiger_campaigncontrel.campaignid = $parentId AND label like '%$searchValue%'";

            return $query;
        } else if($parentId && $parentModule == 'Vendors') {
            $query = "SELECT ".implode(',',$searchFields)." FROM vtiger_crmentity
                        INNER JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
                        INNER JOIN vtiger_vendorcontactrel ON vtiger_vendorcontactrel.contactid = vtiger_contactdetails.contactid
                        WHERE deleted=0 AND vtiger_vendorcontactrel.vendorid = $parentId AND label like '%$searchValue%'";

            return $query;
        } else if ($parentId && $parentModule == 'Quotes') {
            $query = "SELECT ".implode(',',$searchFields)." FROM vtiger_crmentity
                        INNER JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
                        INNER JOIN vtiger_quotes ON vtiger_quotes.contactid = vtiger_contactdetails.contactid
                        WHERE deleted=0 AND vtiger_quotes.quoteid  = $parentId  AND label like '%$searchValue%'";

            return $query;
        } else if ($parentId && $parentModule == 'PurchaseOrder') {
            $query = "SELECT ".implode(',',$searchFields)." FROM vtiger_crmentity
                        INNER JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
                        INNER JOIN vtiger_purchaseorder ON vtiger_purchaseorder.contactid = vtiger_contactdetails.contactid
                        WHERE deleted=0 AND vtiger_purchaseorder.purchaseorderid  = $parentId  AND label like '%$searchValue%'";

            return $query;
        } else if ($parentId && $parentModule == 'SalesOrder') {
            $query = "SELECT ".implode(',',$searchFields)." FROM vtiger_crmentity
                        INNER JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
                        INNER JOIN vtiger_salesorder ON vtiger_salesorder.contactid = vtiger_contactdetails.contactid
                        WHERE deleted=0 AND vtiger_salesorder.salesorderid  = $parentId  AND label like '%$searchValue%'";

            return $query;
        } else if ($parentId && $parentModule == 'Invoice') {
            $query = "SELECT ".implode(',',$searchFields)." FROM vtiger_crmentity
                        INNER JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
                        INNER JOIN vtiger_invoice ON vtiger_invoice.contactid = vtiger_contactdetails.contactid
                        WHERE deleted=0 AND vtiger_invoice.invoiceid  = $parentId  AND label like '%$searchValue%'";

            return $query;
        }

		return parent::getSearchRecordsQuery($searchValue,$searchFields,$parentId, $parentModule);
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
			$queryGenerator->addCondition('activitytype', 'Emails', 'n', QueryGenerator::$AND);
			$query = $queryGenerator->getQuery();

			// Added by Hieu Nguyen on 2022-01-10 to show related activities stored in table vtiger_cntactivityrel in case related Contact field is not add to the list
			if (strpos($query, 'vtiger_cntactivityrel') === false) {
				$extraJoin = " INNER JOIN vtiger_cntactivityrel ON (vtiger_cntactivityrel.activityid = vtiger_activity.activityid)";
				$query = appendFromClauseToQuery($query, $extraJoin);
			}
			// End Hieu Nguyen

			// Modified by Hieu Nguyen on 2022-01-10 to show related activities that this customer was accepted the invitation
			$extraJoin = " LEFT JOIN vtiger_invitees ON (vtiger_invitees.activityid = vtiger_activity.activityid AND vtiger_invitees.status = 'Accepted')";
			$query = appendFromClauseToQuery($query, $extraJoin);
			// End Hieu Nguyen

			// Modified WHERE condition to show related activities from both vtiger_cntactivityrel and vtiger_invitees
			$query .= " AND (vtiger_cntactivityrel.contactid = {$recordId} OR vtiger_invitees.inviteeid = {$recordId})";
			// End Hieu Nguyen

			// Split query to components
			$queryComponents = preg_split('/ FROM /i', $query);

			// Add activity id as crmid column
			$queryComponents[0] .= ',vtiger_crmentity.crmid';
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
		if (in_array($sourceModule, array('Campaigns', 'Potentials', 'Vendors', 'Products', 'Services', 'Emails'))
				|| ($sourceModule === 'Contacts' && $field === 'contact_id' && $record)) {
			switch ($sourceModule) {
				case 'Campaigns'	: $tableName = 'vtiger_campaigncontrel';	$fieldName = 'contactid';	$relatedFieldName ='campaignid';	break;
				case 'Potentials'	: $tableName = 'vtiger_contpotentialrel';	$fieldName = 'contactid';	$relatedFieldName ='potentialid';	break;
				case 'Vendors'		: $tableName = 'vtiger_vendorcontactrel';	$fieldName = 'contactid';	$relatedFieldName ='vendorid';		break;
				case 'Products'		: $tableName = 'vtiger_seproductsrel';		$fieldName = 'crmid';		$relatedFieldName ='productid';		break;
			}

			if ($sourceModule === 'Services') {
				$condition = " vtiger_contactdetails.contactid NOT IN (SELECT relcrmid FROM vtiger_crmentityrel WHERE crmid = '$record' UNION SELECT crmid FROM vtiger_crmentityrel WHERE relcrmid = '$record') ";
			} elseif ($sourceModule === 'Emails') {
				$condition = ' vtiger_contactdetails.emailoptout = 0';
			} elseif ($sourceModule === 'Contacts' && $field === 'contact_id') {
				$condition = " vtiger_contactdetails.contactid != '$record'";
			} else {
				$condition = " vtiger_contactdetails.contactid NOT IN (SELECT $fieldName FROM $tableName WHERE $relatedFieldName = '$record')";
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
    
    public function getDefaultSearchField(){
        return "lastname";
    }
    
}