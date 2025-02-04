<?php

/*
    Save Action
    Author: Hieu Nguyen
    Date: 2020-01-09
    Purpose: handle save ajax action
*/

class Documents_SaveAjax_Action extends Vtiger_SaveAjax_Action {

	public function getRecordModelFromRequest(Vtiger_Request $request) {
		$recordModel = parent::getRecordModelFromRequest($request);
		$fieldName = $request->get('field');

        // Allow to save raw HTML for document content
        if ($fieldName == 'notecontent') {
            $recordModel->set($fieldName, $request->getRaw('value', null));
        }

		return $recordModel;
	}
}