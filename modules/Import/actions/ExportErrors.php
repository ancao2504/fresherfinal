<?php

/*
    Action ExportErrors
    Author: Hieu Nguyen
    Date: 2019-01-15
    Purpose: to export failed records into excel file
*/

class Import_ExportErrors_Action extends Vtiger_Action_Controller {

	public function process(Vtiger_Request $request) {
        require_once('include/ExcelHelper.php');
		$user = Users_Record_Model::getCurrentUserModel();
        $targetModule = $request->get('target_module');
        $errorType = $request->get('error_type');
		$failedRecords = Import_Data_Action::getImportDetails($user, $targetModule);
        
        // Generate excel data
        ini_set('max_input_vars', 1000000);
        $data = [];

        // Header row
        foreach ($failedRecords['headers'] as $field) {
            $data[0][] = vtranslate($field->get('label'), $targetModule);
        }

        if ($errorType == 'failed') {
            $data[0][] = vtranslate('LBL_ERROR_REASONS', 'Import');
        }

        // Data rows
        foreach ($failedRecords[$errorType] as $index => $record) {
            $rowNum = $index + 1;
            $data[$rowNum] = [];

            foreach ($failedRecords['headers'] as $field) {
                $data[$rowNum][] = decodeUTF8($record->get($field->getName()));
            }

            if ($errorType == 'failed') {
                $data[$rowNum][] = decodeUTF8($record->get('error_reasons'));
            }
        }

        // Export
        $fileName = "{$targetModule}_Import_{$errorType}";
        $savedFile = ExcelHelper::exportToExcel($data, $fileName, false, true);

        header("Location: {$savedFile}"); 
	}
}
