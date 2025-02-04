<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ************************************************************************************/

class Potentials_Module_Model extends Vtiger_Module_Model {

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
	 * Function returns number of Open Potentials in each of the sales stage
	 * @param <Integer> $owner - userid
	 * @return <Array>
	 */
	public function getPotentialsCountBySalesStage($owner, $dateFilter) {
		$db = PearDatabase::getInstance();

		if (!$owner) {
			$currenUserModel = Users_Record_Model::getCurrentUserModel();
			$owner = $currenUserModel->getId();
		} else if ($owner === 'all') {
			$owner = '';
		}

		$params = array();
		if(!empty($owner)) {
			$ownerSql =  ' AND main_owner_id = ? '; // Modified by Hieu Nguyen on 2020-03-20 to filter by main owner
			$params[] = $owner;
		}
		if(!empty($dateFilter)) {
			$dateFilterSql = ' AND closingdate BETWEEN ? AND ? ';
			$params[] = $dateFilter['start'];
			$params[] = $dateFilter['end'];
		}
        if(vtws_isRoleBasedPicklist('sales_stage')) {
            $currentUserModel = Users_Record_Model::getCurrentUserModel();
            $picklistvaluesmap = getAssignedPicklistValues("sales_stage",$currentUserModel->getRole(), $db);
            unset($picklistvaluesmap['Closed Won']);unset($picklistvaluesmap['Closed Lost']);
            foreach($picklistvaluesmap as $picklistValue) $params[] = $picklistValue;
        }
        
		$result = $db->pquery('SELECT COUNT(*) count, vtiger_potential.sales_stage FROM vtiger_potential
						INNER JOIN vtiger_crmentity ON vtiger_potential.potentialid = vtiger_crmentity.crmid
						INNER JOIN vtiger_sales_stage ON vtiger_potential.sales_stage = vtiger_sales_stage.sales_stage 
                        AND deleted = 0 '.Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()). $ownerSql . $dateFilterSql . ' AND vtiger_potential.sales_stage IN ('.  generateQuestionMarks($picklistvaluesmap).') 
					    GROUP BY sales_stage ORDER BY vtiger_sales_stage.sortorderid', $params);
		
		$response = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
            // Dashboard showing UTF8 characters as encoded values
			$saleStage = decode_html($db->query_result($result, $i, 'sales_stage'));
			$response[$i][0] = vtranslate($saleStage, $this->getName());
			$response[$i][1] = $db->query_result($result, $i, 'count');
			$response[$i][2] = vtranslate($saleStage, $this->getName());
            $response[$i]['link'] = $saleStage;
		}
		return $response;
	}

	/**
	 * Function returns number of Open Potentials for each of the sales person
	 * @param <Integer> $owner - userid
	 * @return <Array>
	 */
	public function getPotentialsCountBySalesPerson() {
		$db = PearDatabase::getInstance();
		//TODO need to handle security
		$params = array();
        if(vtws_isRoleBasedPicklist('sales_stage')) {
            $currentUserModel = Users_Record_Model::getCurrentUserModel();
            $picklistvaluesmap = getAssignedPicklistValues("sales_stage",$currentUserModel->getRole(), $db);
            foreach($picklistvaluesmap as $picklistValue) $params[] = $picklistValue;
		}
		
		// Added by Phuc on 2020.04.08 to apply name and fix query condition Bug #511
		$fullNameField = getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');
		$adminACQ = Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName());
		$pickListMap = generateQuestionMarks($picklistvaluesmap);

		$sql = "SELECT COUNT(*) AS count, {$fullNameField} AS last_name, vtiger_potential.sales_stage, vtiger_groups.groupname, vtiger_crmentity.smownerid
			FROM vtiger_potential
			INNER JOIN vtiger_crmentity ON (vtiger_potential.potentialid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted = 0)
			LEFT JOIN vtiger_users ON (vtiger_users.id = vtiger_crmentity.main_owner_id AND vtiger_users.status = 'ACTIVE')
			LEFT JOIN vtiger_groups ON (vtiger_groups.groupid = vtiger_crmentity.smownerid {$adminACQ})
			INNER JOIN vtiger_sales_stage ON (vtiger_potential.sales_stage = vtiger_sales_stage.sales_stage)
			WHERE vtiger_potential.sales_stage IN ({$pickListMap})
			GROUP BY smownerid, sales_stage
			ORDER BY vtiger_sales_stage.sortorderid";

		$result = $db->pquery($sql, $params);
		// Ended by Phuc
		
		$response = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$lastName = decode_html($row['last_name']);
			if(!$lastName) {
				$lastName = decode_html($row['groupname']);
			}

            $response[$i]['smownerid'] = $row['smownerid']; // Added by Phuc on 2020.02.04
            $response[$i]['count'] = $row['count'];
            $response[$i]['last_name'] = $lastName;
            $response[$i]['link'] = decode_html($row['sales_stage']);
            $response[$i]['sales_stage'] = vtranslate(decode_html($row['sales_stage']),  $this->getName());
            //$response[$i][2] = $row['']
        }
		return $response;
	}

	/**
	 * Function returns Potentials Amount for each Sales Person
	 * @return <Array>
	 */
	function getPotentialsPipelinedAmountPerSalesPerson() {
		$db = PearDatabase::getInstance();
		//TODO need to handle security
		$params = array();
        if(vtws_isRoleBasedPicklist('sales_stage')) {
            $currentUserModel = Users_Record_Model::getCurrentUserModel();
            $picklistvaluesmap = getAssignedPicklistValues("sales_stage",$currentUserModel->getRole(), $db);
            unset($picklistvaluesmap['Closed Won']);unset($picklistvaluesmap['Closed Lost']);
            foreach($picklistvaluesmap as $picklistValue) $params[] = $picklistValue;
		}
		// Added vtiger_users.id by Phuc on 2020.03.11 to get filter
		$result = $db->pquery('SELECT sum(amount) AS amount, concat(first_name," ",last_name) as last_name, vtiger_potential.sales_stage, vtiger_users.id FROM vtiger_potential
						INNER JOIN vtiger_crmentity ON vtiger_potential.potentialid = vtiger_crmentity.crmid
						INNER JOIN vtiger_users ON vtiger_users.id=vtiger_crmentity.smownerid AND vtiger_users.status="ACTIVE"
						AND vtiger_crmentity.deleted = 0 '.Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()).
						'INNER JOIN vtiger_sales_stage ON vtiger_potential.sales_stage =  vtiger_sales_stage.sales_stage 
						WHERE vtiger_potential.sales_stage IN ('.generateQuestionMarks($picklistvaluesmap).') 
						GROUP BY smownerid, sales_stage ORDER BY vtiger_sales_stage.sortorderid', $params);
		for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
            $row['link'] = decode_html($row['sales_stage']);
			$row['amount'] = CurrencyField::convertToUserFormat($row['amount'], null, false, true);
            $row['last_name'] = decode_html($row['last_name']);
            $row['sales_stage'] = vtranslate(decode_html($row['sales_stage']),  $this->getName());
			$data[] = $row;
		}
		return $data;
	}

	/**
	 * Function returns Total Revenue for each Sales Person
	 * @return <Array>
	 */
	function getTotalRevenuePerSalesPerson($dateFilter) {
		$db = PearDatabase::getInstance();
		//TODO need to handle security
		$params = array();
		$params[] = 'Closed Won';
		if(!empty($dateFilter)) {
			$dateFilterSql = ' AND createdtime BETWEEN ? AND ? ';
			//appended time frame and converted to db time zone in showwidget.php
			$params[] = $dateFilter['start'];
			$params[] = $dateFilter['end'];
		}

		// [CustomOwnerField] Modified by Phu Vo on 2019.11.28 to get data based on main ownership (fzVirkBs)
		$aclQuery = Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName());

		$sql = "SELECT sum(amount) amount, concat(first_name,' ',last_name) as last_name,vtiger_users.id as id,DATE_FORMAT(closingdate, '%d-%m-%Y') AS closingdate
			FROM vtiger_potential
			INNER JOIN vtiger_crmentity ON vtiger_potential.potentialid = vtiger_crmentity.crmid
			INNER JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.main_owner_id AND vtiger_users.status='ACTIVE'
			AND vtiger_crmentity.deleted = 0 {$aclQuery} WHERE sales_stage = ? {$dateFilterSql} GROUP BY main_owner_id";
		
		$result = $db->pquery($sql, $params);
		// End by Phu Vo

		$data = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$row['amount'] = CurrencyField::convertToUserFormat($row['amount'], null, false, true);
                        $row['last_name'] = decode_html($row['last_name']);
			$data[] = $row;
		}
		return $data;
	}

	/**
	 * Function returns Top Potentials
	 * @return <Array of Vtiger_Record_Model>
	 */
	function getTopPotentials($pagingModel) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$db = PearDatabase::getInstance();
		$query = "SELECT crmid, amount, potentialname, related_to FROM vtiger_potential
						INNER JOIN vtiger_crmentity ON vtiger_potential.potentialid = vtiger_crmentity.crmid
							AND deleted = 0 ".Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName())."
						WHERE sales_stage NOT IN ('Closed Won', 'Closed Lost') AND amount > 0
						ORDER BY amount DESC LIMIT ".$pagingModel->getStartIndex().", ".$pagingModel->getPageLimit()."";
		$result = $db->pquery($query, array());

		$models = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
			$modelInstance = Vtiger_Record_Model::getCleanInstance('Potentials');
			$modelInstance->setId($db->query_result($result, $i, 'crmid'));
			$modelInstance->set('amount', $db->query_result($result, $i, 'amount'));
			$modelInstance->set('potentialname', $db->query_result($result, $i, 'potentialname'));
			$modelInstance->set('related_to', $db->query_result($result, $i, 'related_to'));
			$models[] = $modelInstance;
		}
		return $models;
	}

	/**
	 * Function returns Potentials Forecast Amount
	 * @return <Array>
	 */
	function getForecast($closingdateFilter,$dateFilter) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$db = PearDatabase::getInstance();

		$params = array();
		$params[] = $currentUser->getId();
		if(!empty($closingdateFilter)) {
			$closingdateFilterSql = ' AND closingdate BETWEEN ? AND ? ';
			$params[] = $closingdateFilter['start'];
			$params[] = $closingdateFilter['end'];
		}
		
		if(!empty($dateFilter)) {
			$dateFilterSql = ' AND createdtime BETWEEN ? AND ? ';
			//client is not giving time frame so we are appending it
			$params[] = $dateFilter['start'];
			$params[] = $dateFilter['end'];
		}
		
        // Format and modified query by Hieu Nguyen on 2020-03-20 to filter by main owner
        $query = "SELECT forecast_amount, DATE_FORMAT(closingdate, '%m-%d-%Y') AS closingdate 
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity ON (vtiger_potential.potentialid = vtiger_crmentity.crmid AND deleted = 0 AND main_owner_id = ?)
            WHERE closingdate >= CURDATE() AND sales_stage NOT IN ('Closed Won', 'Closed Lost')
                {$closingdateFilterSql} {$dateFilterSql}";
		$result = $db->pquery($query, $params);
        // End Hieu nguyen

		$forecast = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$forecast[] = $row;
		}
		return $forecast;

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
	 * Function returns Potentials Amount for each Sales Stage
	 * @return <Array>
	 */
	function getPotentialTotalAmountBySalesStage() {
		//$currentUser = Users_Record_Model::getCurrentUserModel();
		$db = PearDatabase::getInstance();

        if(vtws_isRoleBasedPicklist('sales_stage')) {
            $currentUserModel = Users_Record_Model::getCurrentUserModel();
            $picklistValues = getAssignedPicklistValues("sales_stage",$currentUserModel->getRole(), $db);
        }
		$data = array();
		foreach ($picklistValues as $key => $picklistValue) {
			$result = $db->pquery('SELECT SUM(amount) AS amount FROM vtiger_potential
								   INNER JOIN vtiger_crmentity ON vtiger_potential.potentialid = vtiger_crmentity.crmid
								   AND deleted = 0 '.Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()).' WHERE sales_stage = ?', array($picklistValue));
			$num_rows = $db->num_rows($result);
			for($i=0; $i<$num_rows; $i++) {
				$values = array();
				$amount = $db->query_result($result, $i, 'amount');
				if(!empty($amount)){
					$values[0] = CurrencyField::convertToUserFormat($db->query_result($result, $i, 'amount'), null, false, true);
					$values[1] = vtranslate($picklistValue, $this->getName());
                    $values['link'] = $picklistValue;
					$data[] = $values;
				}
				
			}
		}
		return $data;
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
		if (in_array($sourceModule, array('Products', 'Services'))) {
			if ($sourceModule === 'Products') {
				$condition = " vtiger_potential.potentialid NOT IN (SELECT crmid FROM vtiger_seproductsrel WHERE productid = '$record')";
			} elseif ($sourceModule === 'Services') {
				$condition = " vtiger_potential.potentialid NOT IN (SELECT relcrmid FROM vtiger_crmentityrel WHERE crmid = '$record' UNION SELECT crmid FROM vtiger_crmentityrel WHERE relcrmid = '$record') ";
			}

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
	 * Function returns query for module record's search
	 * @param <String> $searchValue - part of record name (label column of crmentity table)
	 * @param <Integer> $parentId - parent record id
	 * @param <String> $parentModule - parent module name
	 * @return <String> - query
	 */
	public function getSearchRecordsQuery($searchValue,$searchFields, $parentId=false, $parentModule=false) {
		if($parentId && in_array($parentModule, array('Accounts', 'Contacts'))) {
			$query = "SELECT ".implode(',',$searchFields)." FROM vtiger_crmentity
						INNER JOIN vtiger_potential ON vtiger_potential.potentialid = vtiger_crmentity.crmid
						WHERE deleted = 0 AND vtiger_potential.related_to = $parentId AND label like '%$searchValue%'";
			return $query;
		}
		return parent::getSearchRecordsQuery($searchValue,$searchFields, $parentId=false, $parentModule=false);
	}
    
    /**
	 * Function returns Settings Links
	 * @return Array
	 */
	public function getSettingLinks() {
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$settingLinks = parent::getSettingLinks();
		
		if($currentUserModel->isAdminUser()) {
			$settingLinks[] = array(
					'linktype' => 'LISTVIEWSETTING',
					'linklabel' => 'LBL_CUSTOM_FIELD_MAPPING',
					'linkurl' => 'index.php?parent=Settings&module=Potentials&view=MappingDetail',
					'linkicon' => '');
			
		}
		return $settingLinks;
	}
    
    /*
     * Function to get supported utility actions for a module
     */
    function getUtilityActionsNames() {
        return array('Import', 'Export', 'DuplicatesHandling');
    }
}