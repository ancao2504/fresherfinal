<?php

/*
    Save Action
    Author: Hieu Nguyen
    Date: 2020-01-09
    Purpose: handle save action
*/

class Documents_Save_Action extends Vtiger_Save_Action {

	public function getRecordModelFromRequest(Vtiger_Request $request) {
		$recordModel = parent::getRecordModelFromRequest($request);

        // Allow to save raw HTML for document content
        if ($request->has('notecontent')) {
            $recordModel->set('notecontent', $request->getRaw('notecontent', null));
        }

		return $recordModel;
	}
}