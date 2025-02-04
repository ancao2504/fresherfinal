<?php

/*
	Class Reports_TargetList_Helper
	Author: Hieu Nguyen
	Date: 2021-07-16
	Purpose: to handle logic for Add To Target List and Remove From Target List buttons
*/

class Reports_TargetList_Helper {

	static function getTargetLists() {
        global $adb;

        $sql = "SELECT t.cptargetlistid AS id, t.name
            FROM vtiger_cptargetlist AS t
            INNER JOIN vtiger_crmentity AS te ON (te.crmid = t.cptargetlistid AND te.deleted = 0)";
        $result = $adb->pquery($sql, []);
        $targetLists = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $targetLists[] = decodeUTF8($row);
        }

        return $targetLists;
    }

    private static function getSqlCountRecords($reportRecordModel) {
        $advFilterSql = $reportRecordModel->getAdvancedFilterSQL();
        $sqlReportRecords = $reportRecordModel->getReportSQL($advFilterSql, 'PDF');
        $sqlCountRecords = $reportRecordModel->generateCountQuery($sqlReportRecords);
        return $sqlCountRecords;
    }

	public static function addToTargetList($reportRecordModel, $targetListId) {
        global $adb;
        $customerType = $reportRecordModel->getPrimaryModule();
        $sqlCountRecords = self::getSqlCountRecords($reportRecordModel);
        $customerCount = $adb->getOne($sqlCountRecords, []);
        
        if ($customerCount == 0) {
            return 'NO_RECORD';
        }
        
        $sqlGetCustomerIds = str_replace('SELECT count(*) AS count', 'SELECT vtiger_crmentity.crmid', $sqlCountRecords);
        $queryResult = $adb->pquery($sqlGetCustomerIds, []);
        $result = [
            'total' => intval($customerCount),
            'success' => 0,
            'exists' => 0,
            'error' => 0,
        ];

        while ($row = $adb->fetchByAssoc($queryResult)) {
            try {
                $sqlAddToTargetList = "INSERT INTO vtiger_crmentityrel (crmid, module, relcrmid, relmodule) VALUES (?, ?, ?, ?)";
                $success = $adb->pquery($sqlAddToTargetList, [$targetListId, 'CPTargetList', $row['crmid'], $customerType]);

                if ($success) {
                    $result['success'] += 1;
                }
                else if (strpos($adb->database->ErrorMsg(), 'Duplicate') !== false) {
                    $result['exists'] += 1;
                }
                else {
                    $result['error'] += 1;
                }
            }
            catch (Exception $ex) {
                $result['error'] += 1;
            }
        }

        return $result;
	}

	public static function removeFromTargetList($reportRecordModel, $targetListId) {
        global $adb;
        $sqlCountRecords = self::getSqlCountRecords($reportRecordModel);
        $customerCount = $adb->getOne($sqlCountRecords, []);
        
        if ($customerCount == 0) {
            return 'NO_RECORD';
        }
        
        $sqlGetCustomerIds = str_replace('SELECT count(*) AS count', 'SELECT vtiger_crmentity.crmid', $sqlCountRecords);
        $queryResult = $adb->pquery($sqlGetCustomerIds, []);
        $result = [
            'total' => intval($customerCount),
            'success' => 0,
            'error' => 0,
        ];

        while ($row = $adb->fetchByAssoc($queryResult)) {
            try {
                $sqlAddToTargetList = "DELETE FROM vtiger_crmentityrel WHERE crmid = ? AND relcrmid = ?";
                $success = $adb->pquery($sqlAddToTargetList, [$targetListId, $row['crmid']]);

                if ($success) {
                    $result['success'] += 1;
                }
                else {
                    $result['error'] += 1;
                }
            }
            catch (Exception $ex) {
                $result['error'] += 1;
            }
        }

        return $result;
	}
}