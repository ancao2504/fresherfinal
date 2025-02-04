<?php
/*
*	PopupDatatable.php
*	Author: Tin Bui
*	Date: 2022.03.16
*   Purpose: View controller for emailtemplate datatable popup
*/

class EmailTemplates_PopupDatatable_View extends Vtiger_Popup_View {
    
    function process (Vtiger_Request $request) {
		$viewer = $this->getViewer ($request);
		$moduleName = $this->getModule($request);
		$companyDetails = Vtiger_CompanyDetails_Model::getInstanceById();
		$companyLogo = $companyDetails->getLogo();

		$viewer->assign('COMPANY_LOGO', $companyLogo);
        $viewer->assign('MODULE', $moduleName);
        $viewer->display("modules/EmailTemplates/tpls/PopupDatatable.tpl");
	}
}