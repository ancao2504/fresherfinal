<?php

/*
	Util_Helper
	Author: Hieu Nguyen
	Date: 2021-10-28
	Purpose: to provide util functions for Workflows
*/

class Settings_Workflows_Util_Helper {

	static function isCustomerModule($moduleName) {
		return in_array($moduleName, ['CPTarget', 'Leads', 'Contacts']);
	}

	static function getRelatedCustomerFields($moduleName) {
		global $adb;
		$sql = "SELECT DISTINCT f.fieldname AS name, f.fieldlabel as label
			FROM vtiger_field AS f
			INNER JOIN vtiger_relatedlists as r ON (r.relationfieldid = f.fieldid)
			INNER JOIN vtiger_tab AS lt ON (lt.tabid = r.tabid)
			INNER JOIN vtiger_tab AS rt ON (rt.tabid = r.related_tabid)
			WHERE rt.tablabel = ? AND r.relationtype = '1:N'
			AND lt.tablabel IN ('CPTarget', 'Leads', 'Contacts')";
		$result = $adb->pquery($sql, [$moduleName]);
		$fields = [];

		while ($row = $adb->fetchByAssoc($result)) {
			$fields[] = $row;
		}

		return $fields;
	}

	static function isCustomerOrCustomerRelatedModule($moduleName) {
		// Return true when selected module is also the customer module
		if (self::isCustomerModule($moduleName)) {
			return true;
		}

		// Otherwise, check if selected module has any related customer field
		if (!empty(self::getRelatedCustomerFields($moduleName))) {
			return true;
		}

		return false;
	}

	static function isInventoryModule($moduleName) {
		global $inventoryModules;
		return in_array($moduleName, $inventoryModules);
	}

	static function isZaloOAMessageWorkflowSupported($moduleName) {
		return self::isCustomerOrCustomerRelatedModule($moduleName);
	}

	static function isAddToMarketingListWorkflowSupported($moduleName) {
		return self::isCustomerOrCustomerRelatedModule($moduleName);
	}

	static function isAssignCustomerTagsWorkflowSupported($moduleName) {
		return self::isCustomerOrCustomerRelatedModule($moduleName) || self::isInventoryModule($moduleName);
	}

	static function isUnlinkCustomerTagsWorkflowSupported($moduleName) {
		return self::isCustomerOrCustomerRelatedModule($moduleName) || self::isInventoryModule($moduleName);
	}

	static function isUpdateMauticStageWorkflowSupported($moduleName) {
		return self::isCustomerOrCustomerRelatedModule($moduleName);
	}
}
