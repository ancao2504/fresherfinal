<?php

/**
 * Name: SyncCustomerInfoUtils.php
 * Author: Phu Vo
 * Date: 2020.06.24
 */

class SyncCustomerInfoUtils {

    protected static $configs;

    /** CPTarget => Leads */
    protected static $targetToLeadMappingFields = [
        'salutationtype' => 'salutationtype',
        'firstname' => 'firstname',
        'lastname' => 'lastname',
        'designation' => 'designation',
        'phone' => 'phone',
        'mobile' => 'mobile',
        'fax' => 'fax',
        'email' => 'email',
        'other_email' => 'secondaryemail',
        'website' => 'website',
        'company' => 'company',
        'lane' => 'lane',
        'pobox' => 'pobox',
        'code' => 'code',
        'state' => 'state',
        'city' => 'city',
        'country' => 'country',
        'related_campaign' => 'related_campaign',
        'source' => 'source',
        'tags' => 'tags',
        'description' => 'description',
        'assigned_user_id' => 'assigned_user_id',
        'main_owner_id' => 'main_owner_id',
        'cptarget_business_type' => 'leads_business_type',
        'leadsource' => 'leadsource',
    ];

    /** CPTarget => Contacts */
    protected static $targetToContactMappingFields = [
        'salutationtype' => 'salutationtype',
        'firstname' => 'firstname',
        'lastname' => 'lastname',
        'designation' => 'title',
        'phone' => 'phone',
        'mobile' => 'mobile',
        'fax' => 'fax',
        'email' => 'email',
        'other_email' => 'secondaryemail',
        'lane' => 'mailingstreet',
        'pobox' => 'mailingpobox',
        'code' => 'mailingzip',
        'state' => 'mailingstate',
        'city' => 'mailingcity',
        'country' => 'mailingcountry',
        'related_campaign' => 'related_campaign',
        'source' => 'source',
        'tags' => 'tags',
        'description' => 'description',
        'assigned_user_id' => 'assigned_user_id',
        'main_owner_id' => 'main_owner_id',
        'leadsource' => 'leadsource',
    ];

    /**
     * @return Array
     */
    public static function getConfigs() {
        if (empty(self::$configs)) {
            self::$configs = Settings_Vtiger_Config_Model::loadConfig('sync_customer_info', true);
        }

        return self::$configs;
    }

    /**
     * @return Array
     */
    public static function getConvertMappingFields($moduleName) {
        $mappingFields  = [];

        if ($moduleName == 'Leads') $mappingFields = self::$targetToLeadMappingFields;
        if ($moduleName == 'Contacts') $mappingFields = self::$targetToContactMappingFields;

        return $mappingFields;
    }

    public static function getMappedCustomerInfo($customerInfo, $toModule, array $customMapping = []) {
        $mappingFields = self::getConvertMappingFields($toModule);
        $processedData = [];

        if (!empty($customMapping) && !empty($customMapping[$toModule])) {
            $mappingFields = array_merge($mappingFields, $customMapping[$toModule]);

            // Mapping multiple picklist field value
            foreach ($customMapping[$toModule] as $fieldName => $mappingField) {
                $fieldModel = Vtiger_Field_Model::getInstance($mappingField);

                if (!$fieldModel) continue;

                $value = $customerInfo[$fieldName];

                if ($fieldModel->getFieldDataType() == 'multipicklist' && strpos($value, '|') > 0) {
                    $value = Vtiger_Multipicklist_UIType::encodeValues(explode('|', $value));
                    $customerInfo[$fieldName] = $value;
                }
            }
        }

        foreach ($customerInfo as $fieldName => $fieldValue) {
            $mappedField = $mappingFields[$fieldName];
            if (!empty($mappedField)) {
                $processedData[$mappedField] = $fieldValue;
            }
            else {
                $processedData[$fieldName] = $fieldValue;
            }
        }

        return $processedData;
    }

    public static function convertTarget($recordId, array $toModules = [], array $extraValues = []) {
        checkRecordLimitWhenConvertData('CPTarget');    // Added by Hieu Nguyen on 2022-04-07 to check record limit before convert Target

        // Retrieve record model and check requirement
        $targetRecordModel = Vtiger_Record_Model::getInstanceById($recordId, 'CPTarget');
        return self::convertTargetByRecordModel($targetRecordModel, $toModules, $extraValues);
    }

    public static function convertTargetByRecordModel(Vtiger_Record_Model $targetRecordModel, array $toModules = [], array $extraValues = []) {
        global $adb;

        if (empty($toModules)) $toModules = ['Leads'];
        $email = self::_getEmailFromCustomerInfo($targetRecordModel->getData());
        $phone = self::_getPhoneFromCustomerInfo($targetRecordModel->getData());

        // Check if Target is converted
        if ($targetRecordModel->get('cptarget_status') == 'Converted') {
            throw new AppException(vtranslate('LBL_CONVERT_TARGET_CONVERTED_ERROR_MSG', 'CPTarget'));
        }

        // Check if Target is missing requirement fields
        if (empty($email) && empty($phone)) {
            throw new AppException(vtranslate('LBL_CONVERT_TARGET_MISSING_FIELD_MSG', 'CPTarget'));
        }

        $recordModels = [];

        foreach ($toModules as $moduleName) {

            if (!empty($email)) {
                $recordModel = self::findCustomerRecordModelByEmail($email, $toModules);
            }

            if (empty($recordModel) && !empty($phone)) {
                $recordModel = self::findCustomerRecordModelByPhone($phone, $toModules);
            }

            if (empty($extraValues[$moduleName])) $extraValues[$moduleName] = [];

            // Exist Lead on CRM
            if (!empty($recordModel) && !empty($recordModel->getId())) {
                if (!empty($email)) $recordModel->set('email', $email);
                if (!empty($phone)) $recordModel->set('phone', $phone);
                $recordModel->set('mode', 'edit');
                $recordModel->save();
            }
            else {
                $recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);

                foreach (self::getConvertMappingFields($moduleName) as $sourceField => $targetField) {
                    $recordModel->set($targetField, $targetRecordModel->get($sourceField));
                }

                foreach ($extraValues[$moduleName] as $field => $value) {
                    $recordModel->set($field, $value);
                }
                
                if (!empty($recordModel->get('main_owner_id'))) {
                    $recordModel->set('owner_populated', true);
                }

                // Perform save action
                $recordModel->save();
            }

            $recordModels[$moduleName] = $recordModel;
        }

        $targetRecordModel->set('cptarget_status', 'Converted');
        $targetRecordModel->set('mode', 'edit');
        $targetRecordModel->save();

        // Process transfer Activities (Like convert Lead)
        if (!empty($recordModels)) {
            $transferActivitiesTo = null;

            if (!empty($recordModels['Leads'])) $transferActivitiesTo = $recordModels['Leads'];
            if (!empty($recordModels['Contacts'])) $transferActivitiesTo = $recordModels['Contacts'];

            // Perform transfer action
            if (!empty($transferActivitiesTo)) self::_transferTargetRelatedActivities($targetRecordModel->getId(), $transferActivitiesTo->getId());
        }

        // Trigger event
		$em = new VTEventsManager($adb);
		$em->initTriggerCache();

        $entity = $targetRecordModel->getEntity();
        $entityData = VTEntityData::fromCRMEntity($entity);
        $entityData->entityIds = [];

        foreach ($recordModels as $recordModel) {
            $moduleName = $recordModel->getModuleName();
            $entityData->entityIds[$moduleName] = vtws_getWebserviceEntityId($moduleName, $recordModel->getId());
        }

        $em->triggerEvent('vtiger.cptarget.converttarget', $entityData);

        return $recordModels;
    }

    public static function convertLead($recordId, array $toModules = [], array $extraValues = []) {
        // Retrieve record model and check requirement
        $targetRecordModel = Vtiger_Record_Model::getInstanceById($recordId, 'Leads');
        return self::convertLeadByRecordModel($targetRecordModel, $toModules, $extraValues);
    }

    public static function convertLeadByRecordModel(Leads_Record_Model $leadRecordModel, array $toModules = [], $extraValues = []) {
        require_once('include/Webservices/ConvertLead.php');
        $userModal = Users_Record_Model::getCurrentUserModel();

        // Init data to convert
        $entityValues = [];
        $entityValues['transferRelatedRecordsTo'] = $toModules[0];
        $entityValues['assignedTo'] = $leadRecordModel->get('assigned_user_id');
        $entityValues['leadId'] = vtws_getWebserviceEntityId('Leads', $leadRecordModel->getId());
        $entityValues['imageAttachmentId'] = '';
        $recordModels = [];
        $convertLeadFields = $leadRecordModel->getConvertLeadFields();

        foreach ($toModules as $moduleName) {
            if (vtlib_isModuleActive($moduleName)) {
                $entityValues['entities'][$moduleName] = [];
                $entityValues['entities'][$moduleName]['create'] = true;
                $entityValues['entities'][$moduleName]['name'] = $moduleName;
                $entityValues['entities'][$moduleName]['source'] = 'CRM';

                foreach ($convertLeadFields[$moduleName] as $fieldModel) {
                    if (empty($extraValues[$moduleName])) $extraValues[$moduleName] = [];
                    $fieldName = $fieldModel->getName();
                    $fieldValue = $leadRecordModel->get($fieldName);

                    if ($fieldModel->getFieldDataType() === 'currency') {
                        if ($fieldModel->get('uitype') == 72){
                            // Some of the currency fields like Unit Price, Total , Sub-total - doesn't need currency conversion during save
                            $fieldValue = Vtiger_Currency_UIType::convertToDBFormat($fieldValue, null, true);
                        }
                        else {
                            $fieldValue = Vtiger_Currency_UIType::convertToDBFormat($fieldValue);
                        }
                    }
                    else if ($fieldModel->getFieldDataType() === 'date') {
                        $fieldValue = DateTimeField::convertToDBFormat($fieldValue);
                    }
                    else if ($fieldModel->getFieldDataType() === 'reference' && $fieldValue) {
                        $ids = vtws_getIdComponents($fieldValue);
                        if (count($ids) === 1) {
                            $fieldValue = vtws_getWebserviceEntityId(getSalesEntityType($fieldValue), $fieldValue);
                        }
                    }
                    $entityValues['entities'][$moduleName][$fieldName] = $fieldValue;
                }

                foreach ($extraValues[$moduleName] as $fieldName => $fieldValue) {
                    $entityValues['entities'][$moduleName][$fieldName] = $fieldValue;
                }
            }
        }

        $entityValues = decodeUTF8($entityValues); // Decode when convert to void UTF8 issue

        $entityIds = vtws_convertlead($entityValues, $userModal);

        foreach ($entityIds as $moduleName => $entityId) {
            list($moduleId, $recordId) = explode('x', $entityId);
            $recordModels[$moduleName] = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
        }

        return $recordModels;
    }

    public static function isCustomerMatchedCriteria(array $customerData, array $customMapping = []) {
        $configs = self::getConfigs();
        $criteria = $configs['criteria'];
        $result = false;

        if (empty($criteria)) return $result;

        $email = self::_getEmailFromCustomerInfo($customerData, $customMapping);
        $phone = self::_getPhoneFromCustomerInfo($customerData, $customMapping);

        if ($criteria == 'phone' && !empty($phone))  $result = true;
        if ($criteria == 'email' && !empty($email)) $result = true;
        if ($criteria == 'phone_or_email' && (!empty($phone) || !empty($email))) $result = true;
        if ($criteria == 'phone_and_email' && (!empty($phone) && !empty($email))) $result = true;

        return $result;
    }

    public static function findCustomerRecordModelByCriteria(array $customerData, array $customMapping = []) {
        $configs = self::getConfigs();
        $criteria = $configs['criteria'];

        if (empty($criteria)) return null;

        $matchedCustomer = null;
        $email = self::_getEmailFromCustomerInfo($customerData, $customMapping);
        $phone = self::_getPhoneFromCustomerInfo($customerData, $customMapping);

        if ($criteria == 'phone')  {
            $matchedCustomer = self::findCustomerRecordModelByPhone($phone);
        }
        if ($criteria == 'email') {
            $matchedCustomer = self::findCustomerRecordModelByEmail($email);
        }
        if ($criteria == 'phone_or_email') {
            $matchedCustomer = self::findCustomerRecordModelByEmail($email);
            if (empty($matchedCustomer)) $matchedCustomer = self::findCustomerRecordModelByPhone($phone);
        }
        if ($criteria == 'phone_and_email') {
            $matchedCustomer = self::findCustomerRecordModelByEmail($email);
            if (empty($matchedCustomer)) $matchedCustomer = self::findCustomerRecordModelByPhone($phone);
        }

        return $matchedCustomer;
    }

    public static function findCustomerByPhoneOrEmail($phone, $email, $customerTypes = []) {
        if (empty($phone) && empty($email)) return;

        $matchedCustomer = self::findCustomerRecordModelByPhone($phone, $customerTypes);

        // No match by phone found, check match by email
        if (empty($matchedCustomer) && !empty($email)) {
            $matchedCustomer = self::findCustomerRecordModelByEmail($email, $customerTypes);
        }

        return $matchedCustomer;
    }

    public static function findCustomerByPhoneAndEmail($phone, $email, $customerTypes = []) {
        global $adb;
    
        if (empty($phone) && empty($email))  {
            return [];
        }

        // Init default value
        if (empty($customerTypes)) $customerTypes = ['Contacts', 'Leads', 'CPTarget'];

        $matchedCustomer = null;
        $condition = "";
        $queryParams = [];
        $customerTypesString = "('" . join("', '", $customerTypes) . "')";
        $customerTypesOrder = "FIELD(en.setype, '" . join("', '", $customerTypes) . "')";
    
        $phoneNumber = PBXManager_Logic_Helper::addLeadingZeroToPhoneNumber($phone);
        PBXManager_Data_Model::prepareParamsToFindCustomerByPhoneNumber($phoneNumber, $condition, $queryParams);
        
        $query = "SELECT en.crmid AS id, en.setype AS module
            FROM vtiger_pbxmanager_phonelookup AS pl
            INNER JOIN vtiger_crmentity AS en ON (en.crmid = pl.crmid AND en.setype = pl.setype)
            INNER JOIN vtiger_emailslookup AS em ON (en.crmid = em.crmid AND en.setype = em.setype)
            WHERE
                en.deleted = 0
                AND {$condition}
                AND em.value = ?
                AND en.setype IN {$customerTypesString}
            ORDER BY {$customerTypesOrder}";
        $queryParams[] = $email;
        
        $result = $adb->pquery($query, $queryParams);
        $matchedData = $adb->fetchByAssoc($result);

        if (!empty($matchedData)) {
            $matchedCustomer = Vtiger_Record_Model::getInstanceById($matchedData['id'], $matchedData['module']);
        }

        return $matchedCustomer;
    }

    public static function findCustomerRecordModelByEmail($email, array $customerTypes = []) {
        global $adb;

        if (empty($email)) return null;

        // Init default value
        if (empty($customerTypes)) $customerTypes = ['Contacts', 'Leads', 'CPTarget'];

        $matchedCustomer = null;
        $customerTypesString = "('" . join("', '", $customerTypes) . "')";
        $customerTypesOrder = "FIELD(em.setype, '" . join("', '", $customerTypes) . "')";

        $query = "SELECT em.crmid AS id, em.setype AS module
            FROM vtiger_emailslookup AS em
            INNER JOIN vtiger_crmentity AS en ON (en.crmid = em.crmid AND en.setype = em.setype AND en.deleted = 0)
            WHERE em.value = ? AND em.setype IN {$customerTypesString}
            ORDER BY {$customerTypesOrder}
            LIMIT 1";
        $result = $adb->pquery($query, [$email]);
        $matchedData = $adb->fetchByAssoc($result);

        if (!empty($matchedData)) {
            $matchedCustomer = Vtiger_Record_Model::getInstanceById($matchedData['id'], $matchedData['module']);
        }

        return $matchedCustomer;
    }

    public static function findCustomerRecordModelByPhone($phoneNumber, array $customerTypes = []) {
        global $adb;

        if (empty($phoneNumber)) return null;

        // Init default value
        if (empty($customerTypes)) $customerTypes = ['Contacts', 'Leads', 'CPTarget'];

        $matchedCustomer = null;
        $condition = "";
        $queryParams = [];
        $customerTypesString = "('" . join("', '", $customerTypes) . "')";
        $customerTypesOrder = "FIELD(en.setype, '" . join("', '", $customerTypes) . "')";

        $phoneNumber = PBXManager_Logic_Helper::addLeadingZeroToPhoneNumber($phoneNumber); // Add leading zero number in case the call center provider does not have it
        PBXManager_Data_Model::prepareParamsToFindCustomerByPhoneNumber($phoneNumber, $condition, $queryParams);

        $query = "SELECT pl.crmid AS id, pl.setype AS module
            FROM vtiger_pbxmanager_phonelookup AS pl
            INNER JOIN vtiger_crmentity AS en ON (en.crmid = pl.crmid AND en.setype = pl.setype AND en.deleted = 0)
            WHERE {$condition} AND pl.setype IN {$customerTypesString}
            ORDER BY {$customerTypesOrder}
            LIMIT 1";
        $result = $adb->pquery($query, $queryParams);
        $matchedData = $adb->fetchByAssoc($result);

        if (!empty($matchedData)) {
            $matchedCustomer = Vtiger_Record_Model::getInstanceById($matchedData['id'], $matchedData['module']);
        }

        return $matchedCustomer;
    }

    public static function syncCustomerInfo(array $customerData, Vtiger_Record_Model $existedRecordModel = null, array $customMapping = []) {
        // Process if caller already find matched customer (base on mapping table maybe)
        if (!empty($existedRecordModel) && !empty($existedRecordModel->getId())) {
            $existedRecordModel = self::_processDuplicatedByConfig($existedRecordModel, $customerData, $customMapping);
            return $existedRecordModel;
        }

        // Ignore when customer data did not match criteria
        if (!self::isCustomerMatchedCriteria($customerData, $customMapping)) {
            if (empty($existedRecordModel)) $existedRecordModel = self::_processDidNotMatchCriteria($customerData, $customMapping);
            return $existedRecordModel;
        }

        // Try to find existed customer on CRM
        if ((empty($existedRecordModel) || empty($existedRecordModel->getId())) && self::isCustomerMatchedCriteria($customerData, $customMapping)) {
            $existedRecordModel = self::findCustomerRecordModelByCriteria($customerData, $customMapping);

            // Process duplicate action on existed customer
            if (!empty($existedRecordModel)) {
                $existedRecordModel = self::_processDuplicatedByConfig($existedRecordModel, $customerData, $customMapping);
                return $existedRecordModel;
            }
        }

        // Process when customer data matched criteria and did not exist on CRM yet
        if (empty($existedRecordModel)) {
            $existedRecordModel = self::_processMatchedCriteria($customerData, $customMapping);
            return $existedRecordModel;
        }

        return $existedRecordModel;
    }

    public static function saveTicket($inputSource, $customer, $customerData = [], $extraData = [], $customMapping = []) {
        global $adb;
        
        if (empty($customer)) return;

        $ticket = Vtiger_Record_Model::getCleanInstance('HelpDesk');
        $customerId = $customer->getId();
        $customerType = $customer->getModule()->getName();

        // Initial Data
        $ticketData = [];

        // Title
        $customerName = Vtiger_Functions::getCRMRecordLabel($customerId);
        $inputSource = strtoupper($inputSource);
        $time = $adb->formatDate(date('Y-m-d H:i:s'), true);
        $ticketData['ticket_title'] = "[{$inputSource}] {$customerName} - {$time}";

        // Default values
        $ticketData['ticketpriorities'] = 'Normal';
        $ticketData['ticketstatus'] = 'Open';

        if (empty($extraData['assigned_user_id'])) {
            $ticketData['assigned_user_id'] = $customer->get('assigned_user_id');

            if (empty($extraData['main_owner_id']) && !empty($customer->get('main_owner_id'))) {
                $ticketData['main_owner_id'] = $customer->get('main_owner_id');
                $ticketData['owner_populated'] = true;
            }
        }

        // Assign Parent Id for Contacts
        if ($customerType == 'Contacts' && !empty($customer->get('accountid'))) {
           $ticketData['parent_id'] = $customer->get('accountid');
        }

        // Assign customer relation
        if ($customerType == 'Contacts') {
            $ticketData['contact_id'] = $customerId;
            $ticketData['parent_id'] = $customerId;
        }
        else if ($customerType == 'Leads') {
            $ticketData['related_lead'] = $customerId;
        }

        // Process description
        if (!empty($customerData)) {
            $description = '';
            $time = date('H:i');
            $date = date('d-m-Y');

            $replaceParams = ['%time' => $time, '%date' => $date];

            // It's always vietnamese
            $description .= replaceKeys('Thông tin submit lúc %time ngày %date', $replaceParams);
            
            $customerData = self::getMappedCustomerInfo($customerData, $customer->getModuleName(), $customMapping);
            
            // Unset default value
			unset($customerData['assigned_user_id']);
			unset($customerData['main_owner_id']);
			unset($customerData['owner_populated']);

            foreach ($customerData as $fieldName => $value) {
                $fieldLabel = $fieldName;
                $fieldModel = Vtiger_Field_Model::getInstance($fieldName, $customer->getModule());

                if (!empty($fieldModel)) {
                    $fieldLabel = getTranslatedString($fieldModel->get('label'), $customerType, 'vn_vn'); // Translate to vietnamese

                    if ($fieldModel->getFieldDataType() == 'multipicklist') {
                        $value = Vtiger_Multipicklist_UIType::decodeValues($value);

                        foreach ($value as $key => $val) {
                            $value[$key] = getTranslatedString($val, $customerType, 'vn_vn');
                        }

                        $value = join(', ', array_filter($value));
                    }
                    else if ($fieldModel->getFieldDataType() == 'picklist') {
                        $value = getTranslatedString($value, $customerType, 'vn_vn'); // Translate to vietnamese
                    }
                }

                $description .= "\n";
                $description .= '- ' . trim($fieldLabel) . ': ';
				$description .= trim($value);
            }

            $ticketData['description'] = $description;
        }

        // Assign extra data from input
        foreach ($extraData as $key => $value) {
            if ($key == 'description' && !empty($ticketData['description'])) {
                $ticketData[$key] .= "\n";
                $ticketData[$key] .= $value;
            }
            else {
                $ticketData[$key] = $value;
            }
        }

        $ticketData['source'] = $inputSource;

        // Flag this ticket is for new customer or existing customer
        if (isset($customer->isExistingCustomer) && $customer->isExistingCustomer == true) {
            $ticketData['helpdesk_customer_type'] = 'Existing Customer';
        }
        else {
            $ticketData['helpdesk_customer_type'] = 'New Customer';
        }

        $ticket->setData($ticketData);
        $ticket->save();

        return $ticket;
    }

    static function getAssignedUserIdForCustomer() {
        $configs = self::getConfigs();

        if ($configs['distribution_method'] == 'round_robin') {
            $users = $configs['assigners_distribution'];
            $lastUser = $configs['last_assigner'];
            $nextUser = null;

            $users = explode(',', $users);
            
            foreach ($users as $index => $user) {
                $userId = str_replace('Users:', '', $user);
                $users[$index] = $userId;
                
                if ($index == 0 && empty($lastUser)) {
                    $nextUser = $userId;
                    break;
                }
                if ($userId == $lastUser && $index == count($users) - 1) {
                    $nextUser = str_replace('Users:', '', $users[0]);
                    break;
                }
                if ($userId == $lastUser && $index != count($users) - 1) {
                    $nextUser = str_replace('Users:', '', $users[$index + 1]);
                    break;
                }
            }
    
            // Default
            if (empty($nextUser) && !empty($users[0])) $nextUser = $users[0];
            
            // Update last user
            if (!empty($nextUser)) {
                $configs['last_assigner'] = $nextUser;
                self::$configs['last_assigner'] = $nextUser;
                Settings_Vtiger_Config_Model::saveConfig('sync_customer_info', $configs);
            }
    
            return $nextUser;
        }
    }

    protected static function _processMatchedCriteria(array $customerData, array $customMapping = []) {
        $customerRecordModel = null;
        $configs = self::getConfigs();
        $matchedCriteriaAction = $configs['matched_criteria_action'];

        $customerTypeMapping = [
            'Create Lead' => 'Leads',
            'Create Contact' => 'Contacts',
        ];

        if (in_array($matchedCriteriaAction, array_keys($customerTypeMapping))) {

            $customerRecordModel = self::_createCustomer($customerData, $customerTypeMapping[$matchedCriteriaAction], $customMapping);
        };

        return $customerRecordModel;
    }

    protected static function _processDidNotMatchCriteria(array $customerData, array $customMapping) {
        $customerRecordModel = null;
        $configs = self::getConfigs();
        $didNotMatchCriteriaAction = $configs['not_matched_criteria_action'];

        // Process custom logic from social chat case by case
        if ($didNotMatchCriteriaAction == 'Create Target') {
            $customerRecordModel = self::_createCustomer($customerData, 'CPTarget', $customMapping);
        }
        else if ($didNotMatchCriteriaAction == 'Create Lead') {
            $customerRecordModel = self::_createCustomer($customerData, 'Leads', $customMapping);
        }

        return $customerRecordModel;
    }

    protected static function _processDuplicatedByConfig(Vtiger_Record_Model $customerRecordModel, array $customerData, array $customMapping = []) {
        // Ignore assignment information when update record
        unset($customerData['assigned_user_id']);
        unset($customerData['main_owner_id']);

        $configs = self::getConfigs();
        $duplicatedAction = $configs['duplicated_action'];
        $duplicatedMatchCriteriaAction = $configs['existed_customer_match_criteria'];
        $customerData = self::getMappedCustomerInfo($customerData, $customerRecordModel->getModuleName(), $customMapping);

        /**
         * We will sync own customer profile using Update Strategy:
         * Only consisder to copy new data to current empty data
         */
        if ($duplicatedAction == 'Update') {
            $currentData = $customerRecordModel->getData();

            // Refactored by Hieu Nguyen on 2022-08-29
            foreach ($customerData as $fieldName => $value) {
                if (empty($currentData[$fieldName]) && !empty($value)) $currentData[$fieldName] = $value;
            }
            // End Hieu Nguyen

            $customerRecordModel->setData($currentData);
        }

        /**
         * We will sync own customer profile using Override Strategy:
         * Copy all new data into own current profile
         */
        if ($duplicatedAction == 'Override') {
            $currentData = $customerRecordModel->getData();

            // Refactored by Hieu Nguyen on 2022-08-29
            foreach ($customerData as $fieldName => $value) {
                $currentData[$fieldName] = $value;
            }
            // End Hieu Nguyen

            $customerRecordModel->setData($currentData);
        }

        $customerRecordModel->set('mode', 'edit');
        $customerRecordModel->save();

        // Process on existed customer matched criteria
        if (self::isCustomerMatchedCriteria($customerRecordModel->getData())) {
            if ($duplicatedMatchCriteriaAction == 'Convert') {
                $customerRecordModel = self::_convertCustomerByConfig($customerRecordModel);
            }
        }

        // To make sure we'll get newest customer data from db to void override values from other process
        $customerRecordModel = Vtiger_Record_Model::getInstanceById($customerRecordModel->getId());
        $customerRecordModel->isExistingCustomer = true;

        return $customerRecordModel;
    }

    protected static function _convertCustomerByConfig(Vtiger_Record_Model $customerRecordModel) {
        $configs = self::getConfigs();
        $matchedCriteriaAction = $configs['matched_criteria_action'];
        $recordModuleName = $customerRecordModel->getModuleName();

        $customerTypeMapping = [
            'Create Lead' => 'Leads',
            'Create Contact' => 'Contacts',
        ];

        $toModule = $customerTypeMapping[$matchedCriteriaAction];

        if (empty($toModule)) return $customerRecordModel;
        if ($recordModuleName == 'Contacts') return $customerRecordModel;
        if ($recordModuleName == $toModule) return $customerRecordModel;

        if ($recordModuleName == 'Leads' && $toModule == 'Contacts') {
            $result = self::convertLeadByRecordModel($customerRecordModel, [$toModule]);
            $customerRecordModel = $result[$toModule];
        }
        if ($recordModuleName == 'CPTarget') {
            $result = self::convertTargetByRecordModel($customerRecordModel, [$toModule]);
            $customerRecordModel = $result[$toModule];
        }

        return $customerRecordModel;
    }

    protected static function _createCustomer(array $customerData, $moduleName, array $customMapping = []) {
        global $current_user;

        $configs = self::getConfigs();
        $customerData = self::getMappedCustomerInfo($customerData, $moduleName, $customMapping);
        $customerRecordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
        $customerRecordModel->setData($customerData);

        // Assignement by config
        if (empty($customerData['assigned_user_id'])) {
            $defaultAssignedUser = self::getAssignedUserIdForCustomer();

            if (empty($defaultAssignedUser)) $defaultAssignedUser = $current_user->id;

            $customerRecordModel->set('assigned_user_id', $defaultAssignedUser);
        }

        $customerRecordModel->save();

        // To make sure we'll get newest customer data from db to void override values from other process
        $customerRecordModel = Vtiger_Record_Model::getInstanceById($customerRecordModel->getId());
        $customerRecordModel->isExistingCustomer = false;

        return $customerRecordModel;
    }

    protected static function _getPhoneFromCustomerInfo(array $customerInfo, array $customMapping = []) {
        $phone = '';

        if (empty($phone)) $phone = $customerInfo['phone'];
        if (empty($phone)) $phone = $customerInfo['mobile'];

        if (!empty($customMapping)) {
            foreach ($customMapping as $module => $mapping) {
                if (!empty($phone)) break;

                $mapping = array_flip($mapping);

                if (empty($phone) && !empty($mapping['phone'])) $phone = $customerInfo[$mapping['phone']];
                if (empty($phone) && !empty($mapping['mobile'])) $phone = $customerInfo[$mapping['mobile']];
            }
        }

        return $phone;
    }

    protected static function _getEmailFromCustomerInfo(array $customerInfo, array $customMapping = []) {
        $email = '';

        if (empty($email)) $email = $customerInfo['email'];
        if (empty($email)) $email = $customerInfo['other_email'];
        if (empty($email)) $email = $customerInfo['secondaryemail'];

        if (!empty($customMapping)) {
            foreach ($customMapping as $module => $mapping) {
                if (!empty($email)) break;

                $mapping = array_flip($mapping);

                if (empty($email) && !empty($mapping['email'])) $email = $customerInfo[$mapping['email']];
                if (empty($email) && !empty($mapping['other_email'])) $email = $customerInfo[$mapping['other_email']];
                if (empty($email) && !empty($mapping['secondaryemail'])) $email = $customerInfo[$mapping['secondaryemail']];
            }
        }

        return $email;
    }

    protected static function _transferTargetRelatedActivities($targetId, $relatedId) {
        global $adb;

        if (empty($targetId) || empty($relatedId)) return false;

        $relatedModuleName = getSalesEntityType($relatedId);

        $query = "SELECT sr.activityid, en.setype
            FROM vtiger_seactivityrel AS sr
            INNER JOIN vtiger_crmentity AS en ON (sr.activityid = en.crmid)
            WHERE sr.crmid = ?";
        $result = $adb->pquery($query, [$targetId]);

        if ($result == false) return false;

        while ($row = $adb->fetchByAssoc($result)) {
            $activityId = $row['activityid'];

            if ($row['setype'] == 'Emails' && $relatedModuleName == 'Contacts') {
                $sql = "INSERT INTO vtiger_cntactivityrel (contactid, activityid) VALUES (? , ?)";
                $adb->pquery($sql, [$relatedId, $activityId]);
            }
            else {
                $sql = "INSERT INTO vtiger_seactivityrel (crmid, activityid) VALUES (? , ?)";
                $adb->pquery($sql, [$relatedId, $activityId]);
            }
        }

        $sql = "DELETE FROM vtiger_seactivityrel WHERE crmid = ?";
        $adb->pquery($sql, $targetId);

        return true;
    }
}
