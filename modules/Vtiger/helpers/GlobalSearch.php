<?php

/*
	GlobalSearch_Helper
	Author: Hieu Nguyen
	Date: 2020-07-02
	Purpose: to provide util functions for Global Search
*/

class Vtiger_GlobalSearch_Helper {

    static function getEnabledModules() {
        $configs = Settings_Vtiger_Config_Model::loadConfig('global_search', true);
        $enabledModules = $configs['enabled_modules'];

        foreach ($enabledModules as $moduleName => $searchFields) {
            if (isForbiddenFeature("Module{$moduleName}") || isHiddenModule($moduleName)) {
                unset($enabledModules[$moduleName]);
            }
        }

        return $enabledModules;
    }

    // Provide callback function todo anything with each matched row (like resolve owner name for Mobile API)
    static function search($keyword, $displayFields = [], $processRecordCallback = null) {
        global $adb, $globalSearchConfig;
        vglobal('current_user', Users::getRootAdminUser());
        $enabledModules = self::getEnabledModules();
        $displayFields = !empty($displayFields) ? $displayFields : $globalSearchConfig['default_display_fields'];
        $searchResult = [];
        
        foreach ($enabledModules as $moduleName => $searchFields) {
            $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
            $baseQuery = self::getBaseSearchQuery($keyword, $searchFields, $displayFields, $moduleName, $moduleModel);

            // Get total count
            $totalCountQuery = "SELECT COUNT(DISTINCT crmid) FROM " . end(explode('FROM', $baseQuery));
            $totalCount = $adb->getOne($totalCountQuery, []);
            $searchResult[$moduleName]['total_count'] = $totalCount;

            // Get matched records
            $limitQuery = $baseQuery . " LIMIT {$globalSearchConfig['page_limit']}";
            $result = $adb->pquery($limitQuery, []);
            $searchResult[$moduleName]['records'] = [];

            while ($row = $adb->fetchByAssoc($result)) {
                $row = decodeUTF8($row);
                if ($processRecordCallback) $processRecordCallback($row, $moduleName);

                $searchResult[$moduleName]['records'][] = $row;
            }
        }

        return $searchResult;
    }

    private static function getBaseSearchQuery($keyword, $searchFields, $displayFields, $moduleName, $moduleModel) {
        // Prepare filter conditions
        $searchParams = self::getSearchParams($searchFields, $keyword);
        $filterConditions = Vtiger_Util_Helper::transferListSearchParamsToFilterCondition($searchParams, $moduleModel);

        // Get base query
        $queryGenerator = new EnhancedQueryGenerator($moduleName, vglobal('current_user'));
        $queryGenerator->setFields($displayFields);
        $queryGenerator->parseAdvFilterList($filterConditions);
        $baseQuery = $queryGenerator->getQuery();
        $baseQuery = str_replace('SELECT ', "SELECT vtiger_crmentity.crmid AS id, vtiger_crmentity.label AS name, ", $baseQuery);   // Removed DISTINCT keyword to boost performance

        if ($moduleName == 'Calendar') {
            // Insert join query for vtiger_seactivityrel before it's used
			$extraJoinQuery = "LEFT JOIN vtiger_seactivityrel ON (vtiger_seactivityrel.activityid = vtiger_activity.activityid)";
			$splitKeyword = 'LEFT JOIN vtiger_crmentity vtiger_seactivityrel_seentity';
			$queryParts = explode($splitKeyword, $baseQuery);

            if (count($queryParts) > 1) {   // Only concat extra join query when alias vtiger_seactivityrel_seentity is included 
			    $queryParts[0] .= " {$extraJoinQuery} ";
			    $baseQuery = join($splitKeyword, $queryParts);
            }

            // Add condition to exclude Emails records from the result
            $baseQuery .= " AND activitytype NOT IN ('Emails')";
        }

        return $baseQuery;
    }

    public static function getSearchFieldsByModule($moduleName) {
        $enabledModules = self::getEnabledModules();
        $searchFields = $enabledModules[$moduleName];
        return $searchFields;
    }

    public static function getSearchParams($searchFields, $keyword) {
        $params = [];

        foreach ($searchFields as $fieldName) {
            $params[] = array($fieldName, 'c', $keyword);
        }

        $searchParams = [
            0 => [],        // AND
            1 => $params    // OR
        ];

        return $searchParams;
    }
}