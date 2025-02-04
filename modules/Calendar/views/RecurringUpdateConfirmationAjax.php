<?php

/*
    View: Recurring Update Confirmation Ajax
    Author: Hieu Nguyen
    Date: 2020-03-19
    Purpose: to provide confirm view for updating recurring events in both edit and delete modes
*/

class Calendar_RecurringUpdateConfirmationAjax_View extends Vtiger_Index_View {

	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();
        $mode = $request->get('mode');

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $moduleName);

        if ($mode == 'edit') {
            $viewer->view('RecurringEditView.tpl', $moduleName);
        }

        if ($mode == 'delete') {
            $viewer->view('RecurringDeleteView.tpl', $moduleName);
        }
	}
}
