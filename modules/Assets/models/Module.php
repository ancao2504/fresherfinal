<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Assets_Module_Model extends Vtiger_Module_Model {

	// Added by Phu Vo on 2021.09.25 base on UI UX Request
	public function isQuickCreateSupported() {
		return false;
	}

	public function getQueryByModuleField($sourceModule, $field, $record, $listQuery) {
		if ($sourceModule == 'HelpDesk') {
			$condition = " vtiger_assets.assetsid NOT IN (SELECT relcrmid FROM vtiger_crmentityrel WHERE crmid = '$record' UNION SELECT crmid FROM vtiger_crmentityrel WHERE relcrmid = '$record') ";

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

	/**
	 * Function to check whether the module is summary view supported
	 * @return <Boolean> - true/false
	 */
	public function isSummaryViewSupported() {
		return false;
	}

	/*
	 * Function to get supported utility actions for a module
	 */
	public function getUtilityActionsNames() {
		return array('Import', 'Export', 'DuplicatesHandling');
	}

}
