<?php

/*+***********************************************************************************
 * FileName: ExportPDF.php
 * Author: Phu Vo
 * Date: 2018.05.13
 * Last Update: 2018.05.13
 * Purpose: Export PDF for Inventory Modules
 *************************************************************************************/

require_once('include/utils/InventoryPDFUtils.php');

/**
 * Action Export PDF for Inventory Modules
 * @author Phu Vo
 */
class Inventory_ExportPDF_Action extends Vtiger_Action_Controller {

	public function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$recordId = $request->get('record');

		if(!Users_Privileges_Model::isPermitted($moduleName, 'DetailView', $recordId)) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED', $moduleName));
		}
	}

	/**
	 * Main process function, export PDF for inventory modules
	 * @access public
	 */
	public function process(Vtiger_Request $request) {
		// Init Data
		$moduleName = $request->getModule();
		$recordId = $request->get('record');

		// Process Record data
		$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
		
		// Export PDF
		InventoryPDFUtils::exportPDF($recordModel);

		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}
}
