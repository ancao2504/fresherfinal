<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ************************************************************************************/

class Project_Module_Model extends Vtiger_Module_Model {

	public function getSideBarLinks($linkParams) {
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$linkTypes = array('SIDEBARLINK', 'SIDEBARWIDGET');
		$links = parent::getSideBarLinks($linkParams);
		$quickLinks = array();

		$projectTaskInstance = Vtiger_Module_Model::getInstance('ProjectTask');
		if($userPrivilegesModel->hasModulePermission($projectTaskInstance->getId())) {
			$quickLinks[] = array(
								'linktype' => 'SIDEBARLINK',
								'linklabel' => 'LBL_TASKS_LIST',
								'linkurl' => $this->getTasksListUrl(),
								'linkicon' => '',
							);
		}

		$projectMileStoneInstance = Vtiger_Module_Model::getInstance('ProjectMilestone');
		if($userPrivilegesModel->hasModulePermission($projectMileStoneInstance->getId())) {
			$quickLinks[] = array(
							'linktype' => 'SIDEBARLINK',
							'linklabel' => 'LBL_MILESTONES_LIST',
							'linkurl' => $this->getMilestonesListUrl(),
							'linkicon' => '',
						  );
		}

		foreach($quickLinks as $quickLink) {
			$links['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues($quickLink);
		}

		return $links;
	}

	public function getTasksListUrl() {
		$taskModel = Vtiger_Module_Model::getInstance('ProjectTask');
		return $taskModel->getListViewUrl();
	}
	public function getMilestonesListUrl() {
		$milestoneModel = Vtiger_Module_Model::getInstance('ProjectMilestone');
		return $milestoneModel->getListViewUrl();
	}

	/*
	 * Function to get supported utility actions for a module
	 */
	function getUtilityActionsNames() {
		return array('Import', 'Export', 'DuplicatesHandling');
	}

	/**
	 * Function to get relation query for particular module with function name
	 * @param <record> $recordId
	 * @param <String> $functionName
	 * @param Vtiger_Module_Model $relatedModule
	 * @return <String>
	 */
    // Cloned from Contacts Module Model by Hieu Nguyen on 2020-03-16 to show correct related activities
    public function getRelationQuery($recordId, $functionName, $relatedModule, $relationId) {
		if ($functionName == 'get_activities') {
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
			$query = $queryComponents[0] .', vtiger_crmentity.crmid FROM '. $queryComponents[1];

			$relatedModuleName = $relatedModule->getName();
			$query .= $this->getSpecificRelationQuery($relatedModuleName);
			// End Phu Vo

			// Added by Hieu Nguyen on 2022-01-19 to remove duplicated rows when an activity is related to multiple customer type (Account & Contact & Lead)
			$query .= " GROUP BY vtiger_activity.activityid";
			// End Hieu Nguyen
		} 
        else {
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
		if ($sourceModule === 'HelpDesk') {
			$condition = " vtiger_project.projectid NOT IN (SELECT relcrmid FROM vtiger_crmentityrel WHERE crmid = '$record' UNION SELECT crmid FROM vtiger_crmentityrel WHERE relcrmid = '$record') ";

			$pos = stripos($listQuery, 'where');
			if ($pos) {
				$split = preg_split('/where/i', $listQuery);

                // Added by Hieu Nguyen on 2019-06-21 to fix bug filter error when apply subquery with sub WHERE
                $split = fixSplittedQueryPartsByWhere($split);
                // End Hieu Nguyen

				$overRideQuery = $split[0].' WHERE '.$split[1].' AND '.$condition;
			} else {
				$overRideQuery = $listQuery.' WHERE '.$condition;
			}
			return $overRideQuery;
		}
	}

}