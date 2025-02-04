<?php

/**
 * PBXManager_CallPopup_Model handle data for Call Popup
 * @package CallPopup
 * @author Phu Vo
 */

class PBXManager_CallPopup_Model {

    static $tabModuleMapping = [
        'call-list' => 'Calendar.Call',
        'salesorder-list' => 'SalesOrder',
        'ticket-list' => 'HelpDesk',
    ];

    static function getRelatedListViewData($request) {
        $tab = $request->get('tab');
        $customerId = $request->get('customer_id');
        $customerType = $request->get('customer_type');
        $mapping = self::$tabModuleMapping[$tab];

        $data = [];
        $mappingString = str_replace('.', '', $mapping);
        $getDataMethodName = "getRelated{$mappingString}Data";

        if (method_exists(get_class(), $getDataMethodName)) {
            $data = get_class()::$getDataMethodName($customerId, $customerType);
        }

        return $data;
    }

    static function getRelatedListViewCount($request) {
        $tab = $request->get('tab');
        $customerId = $request->get('customer_id');
        $customerType = $request->get('customer_type');
        $group = str_replace('-list', '', $tab);

        $counts = self::getRelatedListCounts($customerId, $customerType, $group);

        return $counts[$group];
    }

    static function getFieldModelsFromDataRows($moduleName, $dataRows) {
        $fields = self::getHeadersFromDataRows($dataRows);
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);

        $fieldModels = [];

        foreach ($fields as $field) {
            $fieldModels[$field] = Vtiger_Field_Model::getInstance($field, $moduleModel);
        }

        return $fieldModels;
    }

    static function getHeadersFromDataRows($dataRows) {
        $headers = array_keys($dataRows[0]);

        // Remove id column from header
        $idIndex = array_search('id', $headers);
        if ($idIndex > -1) array_splice($headers, $idIndex, 1);

        return $headers;
    }

    // Modified by Vu Mai on 2023-03-03 to add field related_campaign
    static function getRelatedCalendarCallData($customerId, $customerType) {
        global $adb;

        $aclQuery = CRMEntity::getListViewSecurityParameter('Events');
        $sqlParams = [];

        if ($customerType == 'Contacts') {
            $sql = "SELECT vtiger_crmentity.crmid AS id, a.events_call_direction, a.subject,
                    CONCAT(a.date_start, ' ', a.time_start) AS date_start,
                    a.eventstatus, a.related_campaign, vtiger_crmentity.description, vtiger_crmentity.smownerid AS assigned_user_id
                FROM vtiger_activity AS a
                INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = a.activityid AND vtiger_crmentity.deleted = 0)
                INNER JOIN vtiger_cntactivityrel r ON (r.activityid = a.activityid AND r.contactid = ?)
                WHERE a.activitytype = 'Call' {$aclQuery}
                ORDER BY a.date_start DESC
                LIMIT 5";
    
            $sqlParams[] = $customerId;
        }
        else {
            $sql = "SELECT vtiger_crmentity.crmid AS id, a.events_call_direction, a.subject,
                    CONCAT(a.date_start, ' ', a.time_start) AS date_start,
                    a.eventstatus, a.related_campaign, vtiger_crmentity.description, vtiger_crmentity.smownerid AS assigned_user_id
                FROM vtiger_activity AS a
                INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = a.activityid AND vtiger_crmentity.deleted = 0)
                INNER JOIN vtiger_seactivityrel r ON (r.activityid = a.activityid AND r.crmid = ?)
                WHERE a.activitytype = 'Call' {$aclQuery}
                ORDER BY a.date_start DESC
                LIMIT 5";
    
            $sqlParams[] = $customerId;
        }

        $result = $adb->pquery($sql, $sqlParams);
        $rowData = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $rowData[] = $row;
        }

        return $rowData;
    }

    static function getRelatedSalesOrderData($customerId, $customerType) {
        global $adb;

        if ($customerType === 'Leads') return [];

        $aclQuery = CRMEntity::getListViewSecurityParameter('SalesOrder');
        $sqlParams = [];

        if ($customerType == 'Accounts') {
            $sql = "SELECT vtiger_crmentity.crmid AS id, s.salesorder_no, s.duedate, s.sostatus,
                    s.total AS hdnGrandTotal, vtiger_crmentity.smownerid AS assigned_user_id
                FROM vtiger_salesorder AS s
                INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = s.salesorderid AND vtiger_crmentity.deleted = 0)
                WHERE s.accountid = ? {$aclQuery}
                ORDER BY s.duedate DESC
                LIMIT 5";
    
            $sqlParams[] = $customerId;
        }
        else {
            $sql = "SELECT vtiger_crmentity.crmid AS id, s.salesorder_no, s.duedate, s.sostatus,
                    s.total AS hdnGrandTotal, vtiger_crmentity.smownerid AS assigned_user_id
                FROM vtiger_salesorder AS s
                INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = s.salesorderid AND vtiger_crmentity.deleted = 0)
                WHERE s.contactid = ? {$aclQuery}
                ORDER BY s.duedate DESC
                LIMIT 5";
    
            $sqlParams[] = $customerId;
        }
        
        $result = $adb->pquery($sql, $sqlParams);
        $rowData = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $item = [
                'id' => $row['id'],
                'salesorder_no' => $row['salesorder_no'],
                'duedate' => $row['duedate'],
                'sostatus' => $row['sostatus'],
                'hdnGrandTotal' => $row['hdngrandtotal'],
                'assigned_user_id' => $row['assigned_user_id'],
            ];

            $rowData[] = $item;
        }

        return $rowData;
    }

    static function getRelatedHelpDeskData($customerId, $customerType) {
        global $adb;

        if ($customerType === 'Leads') return [];

        $aclQuery = CRMEntity::getListViewSecurityParameter('SalesOrder');
        $sqlParams = [];

        if ($customerType == 'Accounts') {

            $sql = "SELECT vtiger_crmentity.crmid AS id, t.ticket_no, vtiger_crmentity.createdtime,
                    t.status AS ticketstatus, vtiger_crmentity.description, vtiger_crmentity.smownerid AS assigned_user_id
                FROM vtiger_troubletickets AS t
                INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = t.ticketid AND vtiger_crmentity.deleted = 0)
                WHERE t.parent_id = ? {$aclQuery}
                ORDER BY vtiger_crmentity.createdtime DESC
                LIMIT 5";
    
            $sqlParams[] = $customerId;
        }
        else {

            $sql = "SELECT vtiger_crmentity.crmid AS id, t.ticket_no, vtiger_crmentity.createdtime,
                    t.status AS ticketstatus, vtiger_crmentity.description, vtiger_crmentity.smownerid AS assigned_user_id
                FROM vtiger_troubletickets AS t
                INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = t.ticketid AND vtiger_crmentity.deleted = 0)
                WHERE t.contact_id = ? {$aclQuery}
                ORDER BY vtiger_crmentity.createdtime DESC
                LIMIT 5";
    
            $sqlParams[] = $customerId;
        }

        $result = $adb->pquery($sql, $sqlParams);

        $rowData = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $rowData[] = $row;
        }

        return $rowData;
    }

    static private function _getModuleName($mapping) {
        $mappingData = explode('.', $mapping);
        $moduleName = $mappingData[0];

        $eventActivities = ['Call', 'Meeting'];

        if ($moduleName === 'Calendar' && in_array($mappingData[1], $eventActivities)) {
            $moduleName = 'Events';
        }

        return $moduleName;
    }

    static function getmoduleNameFromRequest(Vtiger_Request $request) {
        $tab = $request->get('tab');
        $mapping = self::$tabModuleMapping[$tab];

        return self::_getModuleName($mapping);
    }

    static function getFaqsByKeyword($keyword) {
        global $adb;

        $keywordString = "%{$keyword}%";
        $aclQuery = CRMEntity::getListViewSecurityParameter('Faq');

        $sql = "SELECT f.*
            FROM vtiger_faq AS f
            INNER JOIN vtiger_crmentity ON (f.id = vtiger_crmentity.crmid AND vtiger_crmentity.deleted = 0)
            WHERE f.question LIKE ? OR f.answer LIKE ? {$aclQuery}
            ORDER BY vtiger_crmentity.modifiedtime DESC
            LIMIT 3";
        $result = $adb->pquery($sql, [$keywordString, $keywordString]);

        $faqs = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $faqs[] = $row;
        }

        return $faqs;
    }

    static function getFaqsCountByKeyword($keyword) {
        global $adb;

        $aclQuery = CRMEntity::getListViewSecurityParameter('Faq');
        $filterCountSql = "SELECT DISTINCT COUNT(id)
            FROM vtiger_faq AS f
            INNER JOIN vtiger_crmentity ON (f.id = vtiger_crmentity.crmid AND vtiger_crmentity.deleted = 0)
            WHERE f.question LIKE ? OR f.answer LIKE ? {$aclQuery} ";

        $keywordString = "%{$keyword}%";

        // Filterd result
        $filterCount = $adb->getOne($filterCountSql, [$keywordString, $keywordString]);

        return $filterCount;
    }

    static function getFaqs($data) {
        global $adb;

        $faqs = [];
        $filterCount = 0;
        $aclQuery = CRMEntity::getListViewSecurityParameter('Faq');

        if (!empty($data['keyword'])) {
            $keywordString = "%{$data['keyword']}%";

            $filterSql = "SELECT f.*
                FROM vtiger_faq AS f
                INNER JOIN vtiger_crmentity ON (f.id = vtiger_crmentity.crmid AND vtiger_crmentity.deleted = 0)
                WHERE f.question LIKE ? OR f.answer LIKE ? {$aclQuery}
                ORDER BY vtiger_crmentity.modifiedtime DESC ";

            // Generate limit query string
            $limitString = '';
            if (!empty($data['length'])) $limitString .= "LIMIT {$data['length']} ";
            if (!empty($data['start'])) $limitString .= "OFFSET {$data['start']} ";

            $filterCountSql = "SELECT DISTINCT COUNT(id)
                FROM vtiger_faq AS f
                INNER JOIN vtiger_crmentity ON (f.id = vtiger_crmentity.crmid AND vtiger_crmentity.deleted = 0)
                WHERE f.question LIKE ? OR f.answer LIKE ? {$aclQuery} ";

            // Filterd result
            $filterCount = $adb->getOne($filterCountSql, [$keywordString, $keywordString]);

            // Process query result
            $queryResult = $adb->pquery($filterSql . $limitString, [$keywordString, $keywordString]);

            $indexStart = $data['start'] + 1;

            while ($row = $adb->fetchByAssoc($queryResult)) {
                $row['number'] = $indexStart++ . '.';
                $row['question'] = "
                    <a href='javascript:void(0)' class='openFaqModel faq-link' data-id='{$row['id']}' title='{$row['question']}' data-use_footer='true'>{$row['question']}</a>
                    <p class='short-answer'>{$row['answer']}</p>
                ";
                $faqs[] = $row;
            }
        }

        $result = [
            'draw' => intval($data['draw']),
            'recordsTotal' => intval($filterCount),
            'recordsFiltered' => intval($filterCount),
            'data' => $faqs,
            'length' => intval($data['length']),
            'offset' => intval($data['start']),
        ];

        return $result;
    }

    static function getCustomerEmail($customerId, $customerType) {
        global $adb;

        $sql = "";

        // Get sql base on customer type
        if ($customerType === 'Contacts') {
            $sql = "SELECT email FROM vtiger_contactdetails WHERE contactid = ?";
        }
        else if ($customerType === 'Leads') {
            $sql = "SELECT email FROM vtiger_leaddetails WHERE leadid = ?";
        }

        return $adb->getOne($sql, [$customerId]);
    }

    static function getRelatedListCounts($customerId, $customerType, $group = 'all') {
        global $adb;
        $counts = [];

        // Process case by case
        if (($group == 'all' || $group == 'call') && $customerType != 'Contacts') {
            $aclQuery = CRMEntity::getListViewSecurityParameter('Events');
            $sql = "SELECT DISTINCT COUNT(a.activityid)
                FROM vtiger_activity AS a
                INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = a.activityid AND vtiger_crmentity.deleted = 0)
                INNER JOIN vtiger_seactivityrel AS r ON (r.activityid = a.activityid AND r.crmid = ?)
                WHERE a.activitytype = 'Call' {$aclQuery} ";
            $counts['call'] = $adb->getOne($sql, [$customerId]);
        }
        
        if (($group == 'all' || $group == 'call') && $customerType == 'Contacts') {
            $aclQuery = CRMEntity::getListViewSecurityParameter('Events');
            $sql = "SELECT DISTINCT COUNT(a.activityid)
                FROM vtiger_activity AS a
                INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = a.activityid AND vtiger_crmentity.deleted = 0)
                INNER JOIN vtiger_cntactivityrel r ON (r.activityid = a.activityid AND r.contactid = ?)
                WHERE a.activitytype = 'Call' {$aclQuery} ";
            $counts['call'] = $adb->getOne($sql, [$customerId]);
        }

        if (($group == 'all' || $group == 'salesorder') && $customerType === 'Contacts') {
            $aclQuery = CRMEntity::getListViewSecurityParameter('SalesOrder');
            $sql = "SELECT DISTINCT COUNT(s.salesorderid)
                FROM vtiger_salesorder AS s
                INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = s.salesorderid AND vtiger_crmentity.deleted = 0)
                WHERE s.contactid = ? {$aclQuery} ";
            $counts['salesorder'] = $adb->getOne($sql, [$customerId]);
        }

        if (($group == 'all' || $group == 'salesorder') && $customerType === 'Accounts') {
            $aclQuery = CRMEntity::getListViewSecurityParameter('SalesOrder');
            $sql = "SELECT DISTINCT COUNT(s.salesorderid)
                FROM vtiger_salesorder AS s
                INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = s.salesorderid AND vtiger_crmentity.deleted = 0)
                WHERE s.accountid = ? {$aclQuery} ";
            $counts['salesorder'] = $adb->getOne($sql, [$customerId]);
        }

        if (($group == 'all' || $group == 'ticket') && $customerType === 'Contacts') {
            $aclQuery = CRMEntity::getListViewSecurityParameter('HelpDesk');
            $sql = "SELECT DISTINCT COUNT(ticketid)
                FROM vtiger_troubletickets AS t
                INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = t.ticketid AND vtiger_crmentity.deleted = 0)
                WHERE t.contact_id = ? {$aclQuery} ";
            $counts['ticket'] = $adb->getOne($sql, [$customerId]);
        }

        if (($group == 'all' || $group == 'ticket') && $customerType === 'Accounts') {
            $aclQuery = CRMEntity::getListViewSecurityParameter('HelpDesk');
            $sql = "SELECT DISTINCT COUNT(ticketid)
                FROM vtiger_troubletickets AS t
                INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = t.ticketid AND vtiger_crmentity.deleted = 0)
                WHERE t.parent_id = ? {$aclQuery} ";
            $counts['ticket'] = $adb->getOne($sql, [$customerId]);
        }

		// Added by Vu Mai on 2022-09-12 to get comment count
		if (($group == 'all' || $group == 'comment')) {
            $aclQuery = CRMEntity::getListViewSecurityParameter('ModComments');
			$sql = "SELECT DISTINCT COUNT(modcommentsid)
				FROM vtiger_modcomments AS c
				INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = c.modcommentsid AND vtiger_crmentity.deleted = 0)
				WHERE c.related_to = ? {$aclQuery} ";
			$counts['comment'] = $adb->getOne($sql, [$customerId]);
		}
		// End Vu Mai

        return $counts;
    }

    static function getCustomers($data) {
        global $adb;

        // Generate contact query string
        $contactAclQuery = CRMEntity::getListViewSecurityParameter('Contacts');

        $contactSelectSql = "SELECT c.contactid AS customer_id,
                vtiger_crmentity.setype AS customer_type,
                c.salutation AS salutation,
                TRIM(CONCAT(c.firstname, ' ', c.lastname)) AS customer_name,
                vtiger_crmentity.smownerid AS assigned_user_id,
                vtiger_crmentity.main_owner_id AS main_owner_id,
                ua.phone_crm_extension AS assigned_user_ext_ua,
                um.phone_crm_extension AS assigned_user_ext_um,
                c.accountid AS account_id,
                a.accountname AS account_name,
                c.mobile AS customer_number,
                vtiger_crmentity.createdtime AS createdtime
            FROM vtiger_contactdetails AS c
            LEFT JOIN vtiger_contactsubdetails AS s ON (s.contactsubscriptionid = c.contactid)
            LEFT JOIN vtiger_contactaddress AS ca ON (ca.contactaddressid = c.contactid)
            INNER JOIN vtiger_crmentity ON (c.contactid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted = 0)
            LEFT JOIN vtiger_users AS ua ON (vtiger_crmentity.smownerid = ua.id)
            LEFT JOIN vtiger_users AS um ON (vtiger_crmentity.main_owner_id = um.id)
            LEFT JOIN vtiger_account AS a ON (a.accountid = c.accountid)
            WHERE 1 = 1 {$contactAclQuery} ";

        $contactCountSql = "SELECT c.contactid AS customer_id
            FROM vtiger_contactdetails AS c
            LEFT JOIN vtiger_contactsubdetails AS s ON (s.contactsubscriptionid = c.contactid)
            LEFT JOIN vtiger_contactaddress AS ca ON (ca.contactaddressid = c.contactid)
            INNER JOIN vtiger_crmentity ON (c.contactid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted = 0)
            LEFT JOIN vtiger_account AS a ON (a.accountid = c.accountid)
            WHERE 1 = 1 {$contactAclQuery} ";

        // Implement search params [later]
        $contactExtraWhere = '';
        $contactParams = [];
        if (!empty($data['customer_name'])) {
            $contactExtraWhere .= "AND CONCAT(c.salutation, ' ', c.firstname, ' ', c.lastname) LIKE ? ";
            $contactParams[] = "%{$data['customer_name']}%";
        }
        if (!empty($data['customer_number'])) {
            $phoneFields = ['phone' => 'c', 'mobile' => 'c' , 'homephone' => 's', 'otherphone' => 's'];
            $contactExtraWhere .= "AND (" . self::getMultiLikeFieldConditionQuery($phoneFields, $data['customer_number'], $contactParams) . ") ";
        }
        if (!empty($data['customer_email'])) {
            $emailFields = ['email' => 'c', 'secondaryemail' => 'c'];
            $contactExtraWhere .= "AND (" . self::getMultiLikeFieldConditionQuery($emailFields, $data['customer_email'], $contactParams) . ") ";
        }
        if (!empty($data['customer_address'])) {
            $contactExtraWhere .= "AND (CONCAT(ca.mailingstreet, ' ', ca.mailingpobox, ' ', ca.mailingcity, ' ', ca.mailingzip, ' ', ca.mailingcountry) LIKE ? ";
            $contactParams[] = "%{$data['customer_address']}%";
            $contactExtraWhere .= "OR CONCAT(ca.otherstreet, ' ', ca.otherpobox, ' ', ca.othercity, ' ', ca.otherzip, ' ', ca.othercountry) LIKE ?) ";
            $contactParams[] = "%{$data['customer_address']}%";
        }

        // Generate lead query string
        $leadAclQuery = CRMEntity::getListViewSecurityParameter('Leads');

        $leadSelectSql = "SELECT l.leadid AS customer_id,
                vtiger_crmentity.setype AS customer_type,
                l.salutation AS salutation,
                TRIM(CONCAT(l.firstname, ' ', l.lastname)) AS customer_name,
                vtiger_crmentity.smownerid AS assigned_user_id,
                vtiger_crmentity.main_owner_id AS main_owner_id,
                ua.phone_crm_extension AS assigned_user_ext_ua,
                um.phone_crm_extension AS assigned_user_ext_um,
                '' AS account_id,
                l.company AS account_name,
                la.mobile AS customer_number,
                vtiger_crmentity.createdtime AS createdtime
            FROM vtiger_leaddetails AS l
            INNER JOIN vtiger_crmentity ON (l.leadid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted = 0)
            LEFT JOIN vtiger_leadaddress AS la ON (la.leadaddressid = l.leadid)
            LEFT JOIN vtiger_users AS ua ON (vtiger_crmentity.smownerid = ua.id)
            LEFT JOIN vtiger_users AS um ON (vtiger_crmentity.main_owner_id = um.id)
            WHERE 1 = 1 {$leadAclQuery} ";

        $leadCountSql = "SELECT l.leadid AS customer_id
            FROM vtiger_leaddetails AS l
            INNER JOIN vtiger_crmentity ON (l.leadid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted = 0)
            LEFT JOIN vtiger_leadaddress AS la ON (la.leadaddressid = l.leadid)
            WHERE 1 = 1 {$leadAclQuery} ";

        // Implement search params [later]
        $leadExtraWhere = '';
        $leadParams = [];
        if (!empty($data['customer_name'])) {
            $leadExtraWhere .= "AND CONCAT(l.salutation, ' ', l.firstname, ' ', l.lastname) LIKE ? ";
            $leadParams[] = "%{$data['customer_name']}%";
        }
        if (!empty($data['customer_number'])) {
            $phoneFields = ['phone' => 'la', 'mobile' => 'la'];
            $leadExtraWhere .= "AND (" . self::getMultiLikeFieldConditionQuery($phoneFields, $data['customer_number'], $leadParams) . ") ";
        }
        if (!empty($data['customer_email'])) {
            $emailFields = ['email' => 'l', 'secondaryemail' => 'l'];
            $leadExtraWhere .= "AND (" . self::getMultiLikeFieldConditionQuery($emailFields, $data['customer_email'], $leadParams) . ") ";
        }
        if (!empty($data['customer_address'])) {
            $leadExtraWhere .= "AND CONCAT(la.lane, ' ', la.pobox, ' ', la.city, ' ', la.state, ' ', la.code, ' ', la.country) LIKE ? ";
            $leadParams[] = "%{$data['customer_address']}%";
        }

        // Generate limit query string
        $limitString = '';
        if (!empty($data['length'])) $limitString .= "LIMIT {$data['length']} ";
        if (!empty($data['start'])) $limitString .= "OFFSET {$data['start']} ";

        // Filter Contact and Lead with generated conditions
        $filterSql = "({$contactSelectSql}{$contactExtraWhere}) UNION ({$leadSelectSql}{$leadExtraWhere}) ";
        $filterSql .= "ORDER BY createdtime DESC ";
        $filterSql .= $limitString;

        $customerQueryResult = $adb->pquery($filterSql, array_merge($contactParams, $leadParams));
        $customers = [];

        while ($row = $adb->fetchByAssoc($customerQueryResult)) {
            // Decode
            $row = decodeUTF8($row);

            // Process customer name
            $row['customer_name'] = trim(vtranslate($row['salutation'], 'Contacts') . ' ' . $row['customer_name']);
            unset($row['salutation']);

            // Assign to new variable
            $customer = $row;
            $customerData = $row;

            // Generate useful information to assign with call popup
            if (!empty($row['main_owner_id']) && $row['main_owner_id'] > 0) {
                $customerData['assigned_user_id'] = $row['main_owner_id'];
                $customerData['assigned_user_name'] = trim(getOwnerName($row['main_owner_id']));
                $customerData['assigned_user_ext'] = $row['assigned_user_ext_um'];
                $customerData['assigned_user_type'] = 'Users';
            }
            else {
                $customerData['assigned_user_id'] = $row['assigned_user_id'];
                $customerData['assigned_user_name'] = trim(getOwnerName($row['assigned_user_id']));
                $customerData['assigned_user_ext'] = $row['assigned_user_ext_ua'];
                $customerData['assigned_user_type'] = vtws_getOwnerType($row['assigned_user_id']);
            }

            $customerData['account_name'] = $row['account_name'] ?? '';

            $customerDataString = json_encode($customerData);

            // Generate more information for display on datatable
            $customerDetailLink = getRecordDetailUrl($row['customer_id'], $row['customer_type']);

            $customer['customer_type'] = vtranslate('SINGLE_' . $row['customer_type'], $row['customer_type']);
            $customer['customer_name'] = "<a target='_black' href='{$customerDetailLink}'>{$row['customer_name']}</a>";
            $customer['assigned_user_name'] = Vtiger_Owner_UIType::getCurrentOwnersForDisplay($row['assigned_user_id'], false);
            $customer['action'] = '';

            // Genereate action button
            $customer['action'] = "<a href='javascript:void(0)' class='syncCustomerInfo btn btn-primary' data-info='{$customerDataString}'>" . vtranslate('LBL_SELECT') . "</a>";

            // Generate account detail link base on id
            if (!empty($row['account_id'])) {
                $accountDetailLink = getRecordDetailUrl($row['account_id'], 'Accounts');
                $customer['account_name'] = "<a target='_black' href='{$accountDetailLink}'>{$row['account_name']}</a>";
            }

            // Assign to output result
            $customers[] = $customer;
        }

        // Filtered Count
        $countSql = "SELECT COUNT(customer.customer_id) FROM (({$contactCountSql}{$contactExtraWhere}) UNION ({$leadCountSql}{$leadExtraWhere})) AS customer ";
        $customerCount = $adb->getOne($countSql, array_merge($contactParams, $leadParams));

        $result = [
            'draw' => intval($data['draw']),
            'recordsTotal' => intval($customerCount),
            'recordsFiltered' => intval($customerCount),
            'data' => $customers,
            'length' => intval($data['length']),
            'offset' => intval($data['start']),
        ];

        return $result;
    }

    static function getMultiLikeFieldConditionQuery($columns, $queryValue, &$queryParams) {
        $query = '';

        $index = 0;

        foreach ($columns as $key => $value) {
            $aliasTable = '';
            $column = $value;

            if (!is_numeric($key)) {
                $aliasTable = $value;
                $column = $key;
            }

            if ($index > 0) $query .= 'OR ';

            if (empty($aliasTable)) {
                $query .= "{$column} LIKE ? ";
            }
            else {
                $query .= "{$aliasTable}.{$column} LIKE ? ";
            }

            $queryParams[] = "%{$queryValue}%";

            $index++;
        }

        return $query;
    }

    static function saveCustomer($data) {
        require_once('modules/PBXManager/BaseConnector.php');
        global $current_user;

        if ($data['customer_type'] === 'Leads') {
            $customer = VTiger_Record_Model::getCleanInstance('Leads');
            $customer->set('company', $data['company']);
            $customer->set('lane', $data['primary_address_street']);
            $customer->set('city', $data['primary_address_city']);
            $customer->set('state', $data['primary_address_state']);
            $customer->set('country', $data['primary_address_country']);
        }
        else {
            $customer = VTiger_Record_Model::getCleanInstance('Contacts');
            $customer->set('account_id', $data['account_id']);
            $customer->set('mailingstreet', $data['primary_address_street']);
            $customer->set('mailingcity', $data['primary_address_city']);
            $customer->set('mailingstate', $data['primary_address_state']);
            $customer->set('mailingcountry', $data['primary_address_country']);
        }

        $customer->set('salutationtype', $data['salutationtype']);
        $customer->set('firstname', $data['firstname']);
        $customer->set('lastname', $data['lastname']);
        $customer->set('birthday', $data['birthday']);
        $customer->set('mobile', $data['mobile']);
        $customer->set('email', $data['email']);
        $customer->set('title', $data['title']);
        $customer->set('department', $data['department']);
        $customer->set('description', $data['description']);
        $customer->set('leadsource', 'Cold Call');
        $customer->save();

        if ($customer->get('id')) {
            // Send customer info to all clients
            $msg = [
                'call_id' => $data['pbx_call_id'],
                'receiver_id' => $current_user->id,
                'customer_number' => $data['mobile'],
                'customer_id' => $customer->get('id'),
                'customer_name' => $customer->get('label'),
                'customer_type' => $data['customer_type'],
                'customer_avatar' => '', // No data for now
                'assigned_user_id' => $current_user->id,
                'assigned_user_name' => trim(getOwnerName($current_user->id)),
                'assigned_user_ext' => $current_user->phone_crm_extension,
                'account_id' => $data['account_id'],
                'account_name' => !empty($data['account_id']) ? getAccountName($data['account_id']) : '',
            ];

            PBXManager_Base_Connector::forwardToCallCenterBridge($msg);

            // Return msg as response
            return $msg;
        }
    }

    static function getCustomerRelatedProducts($customerId) {
        global $adb;

        $customerType = Vtiger_Functions::getCRMRecordType($customerId);

        $sql = "SELECT p.productid AS id, e.label AS label
                FROM vtiger_products AS p
                INNER JOIN vtiger_crmentity AS e ON (e.crmid = p.productid AND e.deleted = 0)
                INNER JOIN vtiger_seproductsrel AS pc ON (pc.productid = p.productid AND pc.setype = ?)
                WHERE pc.crmid = ?";

        $result = $adb->pquery($sql, [$customerType, $customerId]);

        $products = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $products[] = [
                'id' => $row['id'],
                'text' => $row['label'],
            ];
        }

        return $products;
    }

    static function getCustomerRelatedServices($customerId) {
        global $adb;

        $sql = "SELECT s.serviceid AS id, e.label AS label
                FROM vtiger_service AS s
                INNER JOIN vtiger_crmentity AS e ON (e.crmid = s.serviceid AND e.deleted = 0)
                INNER JOIN vtiger_crmentityrel AS r ON (r.relcrmid = e.crmid OR r.crmid = e.crmid)
                WHERE r.crmid  = ? OR r.relcrmid = ?";

        $result = $adb->pquery($sql, [$customerId, $customerId]);

        $services = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $services[] = [
                'id' => $row['id'],
                'text' => $row['label'],
            ];
        }

        return $services;
    }

    static function saveCallLog($data) {
        global $adb, $current_user; // Added by Vu Mai on 2022-11-10

        // [START] Prepare some useful information
        $startDateTime = date('Y-m-d H:i:s', $data['start_time'] / 1000);
        list($startDate, $startTime) = explode(' ', $startDateTime);
        $endDateTime = date('Y-m-d H:i:s', $data['end_time'] / 1000);
        list($endDate, $endTime) = explode(' ', $endDateTime);

        // When call duration less than 60 second, let make it to 60
        if (strtotime($endDateTime) - strtotime($startDateTime) < 60) {
            $endDateTime = date('Y-m-d H:i:s', strtotime($startDateTime) + 60);
            list($endDate, $endTime) = explode(' ', $endDateTime);
        }
        // End Phu Vo

        if (!empty($data['call_log_id'])) {
            $callLog = Vtiger_Record_Model::getInstanceById($data['call_log_id'], 'Events');
            $callLog->set('mode', 'edit');
        }
        else {
            $callLog = Vtiger_Record_Model::getCleanInstance('Events');
        }
        // [END] Prepare some useful information

        // Populate and save call log data
        $callLog->set('module', 'Events');
        $callLog->set('action', 'SaveAjax');
        $callLog->set('activitytype', 'Call');
        $callLog->set('eventstatus', 'Held');
        $callLog->set('subject', $data['subject']);
        $callLog->set('date_start', DateTimeField::convertToUserFormat($startDate));
        $callLog->set('time_start', $startTime);
        $callLog->set('due_date', DateTimeField::convertToUserFormat($endDate));
        $callLog->set('time_end', $endTime);
        $callLog->set('description', $data['description']);
        $callLog->set('events_call_direction', ucwords(strtolower($data['direction'])));
        $callLog->set('pbx_call_id', $data['pbx_call_id']);
        $callLog->set('events_call_purpose', $data['events_call_purpose']);
        $callLog->set('events_inbound_call_purpose', $data['events_inbound_call_purpose']);
        $callLog->set('events_call_result', $data['events_call_result']);
        $callLog->set('visibility', $data['visibility']);
        $callLog->set('assigned_user_id', $current_user->id);
        $callLog->set('main_owner_id', $current_user->id);
        $callLog->set('events_call_purpose_other', $data['events_call_purpose_other']);
        $callLog->set('events_inbound_call_purpose_other', $data['events_inbound_call_purpose_other']);

        setActivityRelatedCustomerId($callLog, $data['customer_id'], $data['customer_type']);

        if ($data['customer_type'] == 'Accounts' && !empty($data['contact_id'])) {
            setActivityRelatedCustomerId($callLog, $data['contact_id'], 'Contacts');
        }

        // Added by Vu Mai on 2022-11-10 to link target_record_id to parent_id 
        if ($data['target_module'] == 'SalesOrder' || $data['target_module'] == 'HelpDesk' || $data['target_module'] == 'Potentials') {
            $callLog->set('parent_id', $data['target_record_id']);
        }
        // End Vu Mai

        // Save Call Log
        $callLog->save();

        // Added by Vu Mai on 2022-11-10 to link activity with telesales campaign 
        if (!empty($data['campaign_id'])) {
            $adb->pquery("UPDATE vtiger_activity SET related_campaign = ? WHERE activityid = ?", [$data['campaign_id'], $callLog->getId()]);
		}

        return $callLog;
    }

    static function saveCallBackRecord($data) {
        global $current_user;

        $callBack = Vtiger_Record_Model::getCleanInstance('Events');
        $callBack->set('module', 'Events');
        $callBack->set('action', 'SaveAjax');
        $callBack->set('activitytype', 'Call');
        $callBack->set('eventstatus', 'Planned');
        $callBack->set('subject', vtranslate('LBL_CALL_POPUP_PLANNED_CALL_BACK', 'PBXManager') . ' - ' . $data['subject']);
        $callBack->set('description', $data['description']);
        $callBack->set('events_call_direction', 'Outbound');
        $callBack->set('events_call_purpose', $data['events_call_purpose']);
        $callBack->set('visibility', $data['visibility']);
        $callBack->set('assigned_user_id', $current_user->id);
        $callBack->set('main_owner_id', $current_user->id);

        setActivityRelatedCustomerId($callBack, $data['customer_id'], $data['customer_type']);

        if ($data['customer_type'] == 'Accounts' && !empty($data['contact_id'])) {
            $callBack->set('contact_id', $data['contact_id']);
            $_REQUEST['contact_id'] = $data['contact_id'];
        }

        // Generate callback start date
        if (!empty($data['call_back_time_other']) && $data['call_back_time_other'] == 'on') {
            // User pick date and time themself
            $callBack->set('time_start', $data['time_start']);
            $callBack->set('date_start', $data['date_start']);

            $startTime = strtotime("{$data['date_start']} {$data['time_start']}");
            $startDateTime = new DateTimeField(date('Y-m-d H:i:s', $startTime));
            $endTime = strtotime($startDateTime->getDisplayDate() . ' ' . $startDateTime->getDisplayTime() . '+5 minutes');
            $endDateTime = new DateTimeField(date('Y-m-d H:i:s', $endTime));

            $callBack->set('time_end', $endDateTime->getDisplayTime());
            $callBack->set('due_date', $endDateTime->getDisplayDate());
        }
        else {
            // User choose specific day
            if ($data['select_moment'] === 'this_afternoon') {
                $startDate = new DateTimeField();
                $startTimeString = $data['select_time'] + 12;
            }
            else {
                $isPM = $data['select_moment'] === 'next_afternoon';
                $tomorrowTime = strtotime(date('Y-m-d H:i:s') . '+1 day');
                $startDate = new DateTimeField(date('Y-m-d H:i:s', $tomorrowTime));
                $startTimeString = $data['select_time'] + ($isPM ? 12 : 0);
            }

            // Reprocess start date time
            $startDateTime = new DateTimeField($startDate->getDisplayDate() . ' ' . $startTimeString . ':00:00');

            $callBack->set('time_start', $startDateTime->getDisplayTime());
            $callBack->set('date_start', $startDateTime->getDisplayDate());

            // Process end date time
            $endTime = strtotime($startDateTime->getDisplayDate() . ' ' . $startDateTime->getDisplayTime() . '+5 minutes');
            $endDateTime = new DateTimeField(date('Y-m-d H:i:s', $endTime));

            $callBack->set('time_end', $endDateTime->getDisplayTime());
            $callBack->set('due_date', $endDateTime->getDisplayDate());
        }

        // Save
        return $callBack->save();
    }

    static function updateCustomer(&$data) {
        $createNewCustomer = false;

        if ($data['customer_type'] == 'Accounts' && !empty($data['contact_id'])) {
            $customer = Vtiger_Record_Model::getInstanceById($data['contact_id'], 'Contacts');
            $customer->set('mode', 'edit');
            $data['account_id'] = $data['customer_id'];
        }
        else if ($data['customer_type'] == 'Accounts' && empty($data['contact_id'])) {
            $customer = Vtiger_Record_Model::getCleanInstance('Contacts');
            $customer->set('leadsource', 'Cold Call');
            $data['account_id'] = $data['customer_id'];
        }
        else if (!empty($data['customer_id']) && $data['customer_type'] != 'Accounts') {
            $customer = Vtiger_Record_Model::getInstanceById($data['customer_id'], $data['customer_type']);
            $customer->set('mode', 'edit');
        }
        else {
            // Default create new Leads for now
            $customer = Vtiger_Record_Model::getCleanInstance('Leads');
            $customer->set('leadsource', 'Cold Call');
            $createNewCustomer = true;
        }

        $customer->set('salutationtype', $data['salutationtype']);
        $customer->set('firstname', $data['firstname']);
        $customer->set('lastname', $data['lastname']);
        $customer->set('mobile', $data['mobile_phone']);
        $customer->set('email', $data['email']);
        $customer->set('account_id', $data['account_id']);
        

        // Handle data for Leads
        if ($customer->getModuleName() == 'Leads') {
            $customer->set('company', $data['company']);
        }

        // Save customer with assigned data
        $customer->save();

        // Assign customer type and customer id in case create absolutely new customer
        if ($createNewCustomer == true) {
            $data['customer_type'] = $customer->getModuleName();
            $data['customer_id'] = $customer->getId();
        }

        // Assign contact id in case create new contact on account
        if ($data['customer_type'] == 'Accounts' && $customer->getModuleName() == 'Contacts') {
            $data['contact_id'] = $customer->getId();
        }

        return $customer;
    }

    static function updateContactAccountRelationship($contactId, $accountId) {
        $customer = Vtiger_Record_Model::getInstanceById($contactId, 'Contacts');
        $customer->set('mode', 'edit');

        if ($customer->get('account_id') != $accountId) {
            $customer->set('account_id', $accountId);
            $customer->save();
        }
        
        return $customer;
    }

    static function updateProductServiceCustomerRelation($data) {
        // Update relationship for contact if popup related to account and have contact selected
        if ($data['customer_type'] == 'Accounts' && !empty($data['contact_id'])) {
            self::processProductServiceRelationDiffs($data['contact_id'], 'Contacts', $data['product_ids'], $data['service_ids']);
        }
        else { // Normal logic
            self::processProductServiceRelationDiffs($data['customer_id'], $data['customer_type'], $data['product_ids'], $data['service_ids']);
        }
    }

    static function processProductServiceRelationDiffs($customerId, $customerType, $productIds, $serviceIds) {
        // Get new product ids and service ids use to update or remove relation
        $productIds = explode(',', $productIds);
        $serviceIds = explode(',', $serviceIds);

        // Fetch some useful infomation
        $customerModuleModel = Vtiger_Module_Model::getInstance($customerType);
        $productModuleModel = Vtiger_Module_Model::getInstance('Products');
        $serviceModuleModel = Vtiger_Module_Model::getInstance('Services');
        $relatedProducts = self::getCustomerRelatedProducts($customerId);
        $relatedServices = self::getCustomerRelatedServices($customerId);

        // Retrieve relation model
        $productRelationModel = Vtiger_Relation_Model::getInstance($customerModuleModel, $productModuleModel);
        $serviceRelationModel = Vtiger_Relation_Model::getInstance($customerModuleModel, $serviceModuleModel);

        $preProductIds = [];
        $preServiceIds = [];

        foreach($relatedProducts as $relatedProduct) {
            $preProductIds[] = $relatedProduct['id'];
        }

        foreach ($relatedServices as $relatedService) {
            $preServiceIds[] = $relatedService['id'];
        }

        // Remove unused product relation
        foreach ($preProductIds as $preProductId) {
            if (!in_array($preProductId, $productIds)) {
                $productRelationModel->deleteRelation($customerId, $preProductId);
            }
        }

        // Remove unused service relation
        foreach ($preServiceIds as $preServiceId) {
            if (!in_array($preServiceId, $serviceIds)) {
                $serviceRelationModel->deleteRelation($customerId, $preServiceId);
            }
        }

        foreach ($productIds as $productId) {
            $productRelationModel->addRelation($customerId, $productId);
        }

        foreach ($serviceIds as $serviceId) {
            $serviceRelationModel->addRelation($customerId, $serviceId);
        }
    }

    static function getAjaxSelect2Options($moduleName, $keyword) {
        global $adb;

        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $meta = $moduleModel->getModuleMeta()->getMeta();
        $columnFieldMapping = $meta->getFieldColumnMapping();

        // Nessessary fields
        $baseTable = $moduleModel->get('basetable');
        $idColumn = $moduleModel->get('basetableid');
        $nameFields = $moduleModel->getNameFields();

        // Nessessary columns
        $nameColumns = [];
        foreach($nameFields as $field) {
            $nameColumns[] = $columnFieldMapping[$field];
        }

        // Generate sql
        $nameConcatSql = self::_getNameSqlConcat($nameColumns, $moduleName, 'bt');
        $sql = "SELECT bt.{$idColumn} AS id, TRIM({$nameConcatSql}) AS name
            FROM {$baseTable} AS bt
            INNER JOIN vtiger_crmentity AS e ON (e.crmid = bt.{$idColumn} AND e.deleted = 0)
            WHERE TRIM({$nameConcatSql}) LIKE ?";
        $params = ["%{$keyword}%"];

        $queryResult = $adb->pquery($sql, $params);

        $result = [];

        // Get result
        while ($row = $adb->fetchByAssoc($queryResult)) {
            $result[] = [
                'id' => $row['id'],
                'text' => $row['name'],
            ];
        }

        $result = decodeUTF8($result);

        return $result;
    }

    static function getCustomerInfo($customerId, $customerType) {
        $customerRecordModel = Vtiger_Record_model::getInstanceById($customerId, $customerType);
        $customerData = $customerRecordModel->getData();

        // Fetch some use info from customer data
        if (!empty($customerData['account_id'])) {
            $customerData['account_id_display'] = Vtiger_Functions::getCRMRecordLabel($customerData['account_id']);
        }

        // Fetch related products and services
        $customerData['product_ids'] = PBXManager_CallPopup_Model::getCustomerRelatedProducts($customerId);
        $customerData['services_ids'] = PBXManager_CallPopup_Model::getCustomerRelatedServices($customerId);

        return $customerData;
    }

    protected static function _getNameSqlConcat($nameFields, $moduleName, $tableAlias = '') {
        $nameParams = [];

        if (!empty($tableAlias)) {
            foreach ($nameFields as $field) {
                $nameParams[$field] = "{$tableAlias}.{$field}";
            }
        }
        else {
            foreach ($nameFields as $field) {
                $nameParams[$field] = $field;
            }
        }

        return trim(getSqlForNameInDisplayFormat($nameParams, $moduleName));
    }

    static function getTransferableList($data) {
        global $adb, $current_user;

        $result = [];
        $filters = $data['filter'];
        $filterCount = 0;
        $fullNameField = getSqlForNameInDisplayFormat(array('first_name'=>'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
        $extraWhere = '';

        $filterSql = "SELECT {$fullNameField} AS display_name, vtiger_users.email1 AS email,
            vtiger_role.rolename AS role, vtiger_users.phone_crm_extension AS ext
            FROM vtiger_users
            INNER JOIN vtiger_user2role ON vtiger_user2role.userid = vtiger_users.id
            INNER JOIN vtiger_role ON vtiger_user2role.roleid = vtiger_role.roleid
            WHERE vtiger_users.deleted = 0 AND vtiger_users.status='ACTIVE' AND vtiger_users.phone_crm_extension <> '' AND vtiger_users.id <> ? ";

        // Process filter
        if (!empty($filters['display_name'])) {
            $extraWhere .= "AND {$fullNameField} LIKE '%{$filters['display_name']}%' ";
        }
        if (!empty($filters['email'])) {
            $extraWhere .= "AND vtiger_users.email1 LIKE '%{$filters['email']}%' ";
        }
        if (!empty($filters['role'])) {
            $extraWhere .= "AND vtiger_role.rolename LIKE '%{$filters['role']}%' ";
        }
        if (!empty($filters['ext'])) {
            $extraWhere .= "AND vtiger_users.phone_crm_extension LIKE '%{$filters['ext']}%' ";
        }

        if (!empty($extraWhere)) $filterSql .= $extraWhere;
        if (!empty($data['length'])) $filterSql .= "LIMIT {$data['length']} ";
        if (!empty($data['start'])) $filterSql .= "OFFSET {$data['start']} ";

        $countSql = "SELECT DISTINCT COUNT(id)
            FROM vtiger_users
            INNER JOIN vtiger_user2role ON vtiger_user2role.userid = vtiger_users.id
            INNER JOIN vtiger_role ON vtiger_user2role.roleid = vtiger_role.roleid
            WHERE vtiger_users.deleted = 0 AND vtiger_users.status='ACTIVE' AND vtiger_users.phone_crm_extension <> '' AND vtiger_users.id <> ? ";

        if (!empty($extraWhere)) $countSql .= $extraWhere;
        
        $queryParams = [$current_user->id];

        // Filterd result
        $filterCount = $adb->getOne($countSql, $queryParams);

        // Process query result
        $queryResult = $adb->pquery($filterSql, $queryParams);

        while ($row = $adb->fetchByAssoc($queryResult)) {
            $row = decodeUTF8($row);
            $result[] = $row;
        }

        $return = [
            'draw' => intval($data['draw']),
            'recordsTotal' => intval($filterCount),
            'recordsFiltered' => intval($filterCount),
            'data' => $result,
            'length' => intval($data['length']),
            'offset' => intval($data['start']),
        ];

        return $return;
    }

	// Added by Vu Mai on 2022-11-08 to update customer status in telesale campaign according to call result
	static function updateCustomerInfoInTelesalesCampaign ($data) {
		global $adb;
		if (empty($data['campaign_id'])) return;

		// Get campaign info
		$campaignId = $data['campaign_id'];
		$campaignInfo = Campaigns_Telesales_Model::getCampaignInfo($campaignId);

		// Get call result to status mapping list
		$callResultToStatusMappingList = CPTelesales_Config_Helper::loadConfigByTableType($campaignInfo['purpose'], 'call_result_to_status_mapping');

		// Prepare customer status value
		$callResult = $data['events_call_result'];
		$status = $callResultToStatusMappingList[$callResult];

        // Prepare last call time
        $lastCallTime = date('Y-m-d H:i:s');

		// Update customer status
		$sql = "UPDATE vtiger_telesales_campaign_distribution 
			SET status = ?, last_call_time = ?, last_call_result = ?
			WHERE campaign_id = ? AND customer_id = ?";
		$adb->pquery($sql, [$status, $lastCallTime, $data['events_call_result'], $data['campaign_id'], $data['customer_id']]);

        return $status;
	}
}
