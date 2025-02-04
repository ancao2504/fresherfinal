<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
/**
 * Inventory Module Model Class
 */
class Inventory_Module_Model extends Vtiger_Module_Model {

	/**
	 * Function to check whether the module is an entity type module or not
	 * @return <Boolean> true/false
	 */
	public function isQuickCreateSupported(){
		//SalesOrder module is not enabled for quick create
		return false;
	}
	
	/**
	 * Function to check whether the module is summary view supported
	 * @return <Boolean> - true/false
	 */
	public function isSummaryViewSupported() {
		return true;
	}

	public function isCommentEnabled() {
		return true;
	}

	static function getAllCurrencies() {
		return getAllCurrencies();
	}

	static function getAllProductTaxes() {
		$taxes = array();
		$availbleTaxes = getAllTaxes('available');
		foreach ($availbleTaxes as $taxInfo) {
			if ($taxInfo['method'] === 'Deducted') {
				continue;
			}
			$taxInfo['compoundon'] = Zend_Json::decode(html_entity_decode($taxInfo['compoundon']));
			$taxInfo['regions'] = Zend_Json::decode(html_entity_decode($taxInfo['regions']));
			$taxes[$taxInfo['taxid']] = $taxInfo;
		}
		return $taxes;
	}

	static function getAllShippingTaxes() {
		return Inventory_Charges_Model::getChargeTaxesList();
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
	 * Function returns export query
	 * @param <String> $where
	 * @return <String> export query
	 */
    // Modified by Hieu Nguyen on 2020-11-06 to fix bug can not export list SO after filter by owner
	public function getExportQuery($focus, $query) {
		$baseTableName = $focus->table_name;
		$queryParts = preg_split('/ FROM /i', $query);

        $selectClause = array_shift($queryParts);
        $fromAndWhereClause = implode(' FROM ', $queryParts);
		$selectColumns = explode(',', $selectClause);

        // Modify select columns
		foreach ($selectColumns as &$column) {
			if (trim($column) == 'vtiger_inventoryproductrel.discount_amount') {
				$column = ' vtiger_inventoryproductrel.discount_amount AS item_discount_amount';
			}
            else if (trim($column) == 'vtiger_inventoryproductrel.discount_percent') {
				$column = ' vtiger_inventoryproductrel.discount_percent AS item_discount_percent';
			}
            else if (trim($column) == "{$baseTableName}.currency_id") {
				$column = ' vtiger_currency_info.currency_name AS currency_id';
			}
		}

        $selectClause = implode(',', $selectColumns);

        // Modify FROM clause
		$fromAndWhereParts = preg_split('/ INNER JOIN /i', $fromAndWhereClause);
		$fromAndWhereParts[0] .= " LEFT JOIN vtiger_currency_info ON (vtiger_currency_info.id = {$baseTableName}.currency_id)";
		$fromAndWhereClause = implode(' INNER JOIN ', $fromAndWhereParts);

		$query = "{$selectClause} FROM {$fromAndWhereClause}";
		return $query;
	}

	/*
	 * Function to get supported utility actions for a module
	 */
	function getUtilityActionsNames() {
		return array('Import', 'Export');
	}
}
