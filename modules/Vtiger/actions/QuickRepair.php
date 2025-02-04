<?php
	/*
	*   Action QuicRepair
	*   Author: Hieu Nguyen
	*   Date: 2018-07-16
	*   Purpose: to trigger running quick repair extensions code
	*/

	class Vtiger_QuickRepair_Action extends Vtiger_Action_Controller {

		public function checkPermission(Vtiger_Request $request) {
			$userModal = Users_Record_Model::getCurrentUserModel();

			if (!$userModal->isAdminUser()) {
				throw new AppException(vtranslate('LBL_PERMISSION_DENIED', $moduleName));
			}
		}

		public function process(Vtiger_Request $request) {
			echo '<strong>Repairing...</strong><br/>';

			if ($request->get('mode') == 'GenerateBlocksAndFieldsRegister') {
				$this->generateBlockAndFieldsRegister();
				exit;
			}

			if ($request->get('mode') == 'GenerateMainOwnerFields') {
				$this->generateMainOwnerFields();
				exit;
			}

			if ($request->get('mode') == 'RepairSMSLogs') {
				$this->repairSMSLogs();
				exit;
			}

			if ($request->get('mode') == 'RepairReportFolderCode') {
				$this->repairReportFolderCode();
				exit;
			}

			if ($request->get('mode') == 'InitCustomMenu') {
				$this->InitCustomMenu();
				exit;
			}

			$this->clearCaches();
			$this->generateCacheDir();
			$this->generateTabDataFiles();
			$this->repairUserPrivilegeFiles();
			$this->loadModuleBuilder();
			$this->loadExtensions();
			$this->loadHandlers();
			$this->repairRoles();
			$this->repairBlocksAndFields();
			$this->loadRelationships();
			$this->fixMissingRelationships(); // Added by Phu Vo on 2020
			$this->fixMissingFields();
			$this->reloadPackageFeatures();
			$this->repairFullNameFields();
			$this->repairReferenceModuleForActivity();  //-- Added By Kelvin Thang - on 2019-10-30 - fix Missing Reference Module For Activity

			echo '<br/>Done!';
			echo '<br/><br/><a href="javascript:history.back();">&#x226A; Go back</a>';
		}

		private function clearCaches() {
			array_map('unlink', array_filter((array) glob('cache/templates_c/v7/*')));
			array_map('unlink', array_filter((array) glob('test/templates_c/v7/*')));
		}

		private function generateCacheDir() {
			echo 'Generating cache dir structure...<br/>';

			$paths = array(
				'cache/images',
				'cache/import',
				'cache/templates_c/v7',
				'cache/upload',
				'test/contact',
				'test/logo',
				'test/migration',
				'test/product',
				'test/templates_c/v7',
				'test/upload',
				'test/user',
				'test/vtlib',
				'test/wordtemplatedownload',
			);

			foreach ($paths as $path) {
				mkdir($path, 0777, true);
			}
		}

		private function generateTabDataFiles() {
			echo 'Generating tab data files...<br/>';
			create_parenttab_data_file();
			create_tab_data_file();
		}

		private function repairUserPrivilegeFiles() {
			echo 'Repairing user privilege files...<br/>';

			require_once('modules/Users/CreateUserPrivilegeFile.php');
			global $adb;

			$query = "SELECT id FROM vtiger_users WHERE deleted = ?";
			$result = $adb->pquery($query, [0]);

			while ($row = $adb->fetchByAssoc($result)) {
				$id = $row['id'];

				if (!file_exists("user_privileges/user_privileges_{$id}.php")) {
					createUserPrivilegesfile($id);
				}
			
				if (!file_exists("user_privileges/sharing_privileges_{$id}.php")) {
					createUserSharingPrivilegesfile($id);
				}
			}
		}

		private function loadExtensions() {
			echo 'Refreshing extensions...<br/>';

			// Fetch all extension files
			$pattern = 'modules/*/Extensions.php';
			
			foreach (glob($pattern) as $extFile) {
				include_once($extFile); // Load and excute the logic
			}
		}

		private function loadHandlers() {
			require_once('vtlib/Vtiger/Event.php');

			echo 'Refreshing event handlers...<br/>';

			if (!Vtiger_Event::hasSupport()) {
				return;
			}

			// Fetch all handler register files
			$pattern = 'modules/*/HandlersRegister.php';
			
			foreach (glob($pattern) as $registerFile) {
				global $registeredEvents, $handlerName, $batchHandlerName;
				include_once($registerFile);
				
				$moduleName = str_replace(array('modules/', '/HandlersRegister.php'), '', $registerFile);
				$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

				foreach ($registeredEvents as $eventName) {
					if (strpos($eventName, 'batchevent') === false) {
						Vtiger_Event::register($moduleModel, $eventName, $handlerName, 'modules/'. $moduleName .'/handlers/'. $handlerName .'.php');
					}
					else {
						Vtiger_Event::register($moduleModel, $eventName, $batchHandlerName, 'modules/'. $moduleName .'/handlers/'. $batchHandlerName .'.php');
					}
				}
			}
		}

		private function loadModuleBuilder() {
			echo '<strong>'.vtranslate('LBL_MODULE_BUILDER_START').'</strong><br/>';

			require_once('include/ModuleBuilder/ModuleBuilder.php');

			ModuleBuilder::build();

			echo '<strong>'.vtranslate('LBL_MODULE_BUILDER_DONE').'</strong><br/>';

		}

		private function repairRoles() {
			echo '<strong>Repairing permissions...</strong><br/>';

			global $adb;

			// Insert missing records in table vtiger_def_org_field
			$sql = "INSERT INTO vtiger_def_org_field(tabid, fieldid, visible, readonly)
				SELECT tabid, fieldid, 0 AS visible, 0 AS readonly 
				FROM vtiger_field
				WHERE (tabid, fieldid) NOT IN (
					SELECT tabid, fieldid FROM vtiger_def_org_field
				)
				ORDER BY fieldid";
			$adb->pquery($sql, []);

			// Insert missing records in table vtiger_profile2field
			$sql = "INSERT INTO vtiger_profile2field(profileid, tabid, fieldid, visible, readonly)
				SELECT p.profileid, f.tabid, f.fieldid, 0 AS visible, 0 AS readonly 
				FROM vtiger_field AS f
				INNER JOIN vtiger_profile AS p
				WHERE (p.profileid, f.tabid, f.fieldid) NOT IN (
					SELECT profileid, tabid, fieldid FROM vtiger_profile2field
				)
				ORDER BY f.fieldid";
			$adb->pquery($sql, []);

			// Insert missing records in table vtiger_role2picklist
			$sqlGetPickLists = "SELECT picklistid, name FROM vtiger_picklist";
			$picklistsResult = $adb->pquery($sqlGetPickLists, []);

			while ($picklist = $adb->fetchByAssoc($picklistsResult)) {
				$picklistId = $picklist['picklistid'];
				$picklistTableName = "vtiger_{$picklist['name']}";

				$sql = "INSERT INTO vtiger_role2picklist(roleid, picklistvalueid, picklistid, sortid)
					SELECT r.roleid, pd.picklist_valueid, {$picklistId}, pd.sortorderid
					FROM {$picklistTableName} AS pd
					INNER JOIN vtiger_role AS r
					WHERE (r.roleid, pd.picklist_valueid, {$picklistId}) NOT IN (
						SELECT roleid, picklistvalueid, picklistid FROM vtiger_role2picklist
					)
					ORDER BY pd.picklist_valueid";
				$adb->pquery($sql, []);
			}
		}

		private function generateBlockAndFieldsRegister() {
			echo '<strong>Generating blocks and fields register files...</strong><br/>';

			$entityModules = Vtiger_BlockAndField_Helper::getEntityModules();
			$fileType = Vtiger_BlockAndField_Helper::getFileTypeForSaving();

			foreach ($entityModules as $moduleName => $moduleDef) {
				Vtiger_BlockAndField_Helper::syncToRegisterFile($moduleDef, $fileType);
			}

			echo '<br/>Done!';
			echo '<br/><br/><a href="javascript:history.back();">&#x226A; Go back</a>';
		}

		private function generateMainOwnerFields() {
			global $adb;

			echo '<strong>Generating main owner fields...</strong><br/>';

			// Insert main_owner_id field into vtiger_field table
			$sqlGetMissingModules = "SELECT DISTINCT b.blockid, t.tabid
				FROM vtiger_blocks AS b
				INNER JOIN vtiger_tab AS t ON(t.tabid = b.tabid AND t.isentitytype = 1)
				WHERE sequence = 1 AND t.tabid NOT IN (
					SELECT DISTINCT tabid FROM vtiger_field WHERE fieldname = 'main_owner_id'
				)
				ORDER BY t.tabid";
			$result = $adb->pquery($sqlGetMissingModules);

			while ($row = $adb->fetchByAssoc($result)) {
				$block = Vtiger_Block::getInstance($row['blockid']);

				$mainOwnerId = new Vtiger_Field();
				$mainOwnerId->name = 'main_owner_id';
				$mainOwnerId->label = 'LBL_MAIN_OWNER_ID';
				$mainOwnerId->table = 'vtiger_crmentity';
				$mainOwnerId->column = 'main_owner_id';
				$mainOwnerId->uitype = 53;
				$mainOwnerId->typeofdata = 'V~O';
				$block->addField($mainOwnerId);
			}

			echo '<br/>Done!';
			echo '<br/><br/><a href="javascript:history.back();">&#x226A; Go back</a>';
		}

		private function repairBlocksAndFields() {
			echo '<strong>Syncing blocks and fields...</strong><br/>';

			$entityModules = Vtiger_BlockAndField_Helper::getEntityModules();

			foreach ($entityModules as $moduleDef) {
				$createdFields = Vtiger_BlockAndField_Helper::syncToDatabase($moduleDef);

				if (!empty($createdFields)) {
					echo '&nbsp;&nbsp;&nbsp;&nbsp;+ New fields for '. $moduleDef['name'] .': '. join(', ', $createdFields) .'<br/>';
				}
			}
		}

		private function loadRelationships() {
			echo '<strong>Repairing relationships...</strong><br/>';

			require_once('include/utils/RelationshipUtils.php');
			RelationshipUtils::repairRelationships();
		}

		/** Implemented by Phu Vo on 2020.08.12 */
		private function fixMissingRelationships() {
			echo '<strong>Repairing missing relationships...</strong><br/>';

			require_once('include/utils/RelationshipUtils.php');
			RelationshipUtils::fixMissingRelationships();
		}

		private function fixMissingFields() {
			global $adb;

			echo '<strong>Fixing missing fields...</strong><br/>';

			// Insert missing field into vtiger_field table
			$sqlGetMissingModules = "SELECT DISTINCT b.blockid, t.tabid
				FROM vtiger_blocks AS b
				INNER JOIN vtiger_tab AS t ON(t.tabid = b.tabid AND t.isentitytype = 1)
				WHERE sequence = 1 AND t.tabid NOT IN (
					SELECT DISTINCT tabid FROM vtiger_field WHERE fieldname = 'createdby'
				)
				ORDER BY t.tabid";
			$result = $adb->pquery($sqlGetMissingModules);

			while ($row = $adb->fetchByAssoc($result)) {
				$block = Vtiger_Block::getInstance($row['blockid']);

				$createdBy = new Vtiger_Field();
				$createdBy->name = 'createdby';
				$createdBy->label = 'LBL_CREATED_BY';
				$createdBy->table = 'vtiger_crmentity';
				$createdBy->column = 'smcreatorid';
				$createdBy->uitype = 52;
				$createdBy->typeofdata = 'V~O';
				$block->addField($createdBy);
			}

			// Hide these fields from editview
			$adb->pquery("UPDATE vtiger_field SET editview_presence = 1 WHERE fieldname = 'createdby'", []);
		}

		private function reloadPackageFeatures() {
			echo '<strong>Refreshing package features...</strong><br/>';
			
			reloadPackageFeatures();
		}

		private function repairFullNameFields() {
			global $adb, $fullNameConfig;
			echo '<strong>Repairing full name fields...</strong><br/>';

			// For person modules
			$personFullName = join(',', $fullNameConfig['full_name_order']);

			$sqlUpdatePersonModules = "UPDATE vtiger_entityname SET fieldname = ? WHERE fieldname LIKE '%,%' AND modulename != 'Users'";
			$adb->pquery($sqlUpdatePersonModules, [$personFullName]);

			// For user module
			$userFullNameFields = [];

			foreach ($fullNameConfig['full_name_order'] as $fieldName) {
				$userFullNameFields[] = str_replace('name', '_name', $fieldName);
			}
			
			$userFullName = join(',', $userFullNameFields);

			$sqlUpdateUserModule = "UPDATE vtiger_entityname SET fieldname = ? WHERE modulename = 'Users'";
			$adb->pquery($sqlUpdateUserModule, [$userFullName]);

			// Update required field
			if ($fullNameConfig['required_field'] == 'firstname') {
				$adb->pquery("UPDATE vtiger_field SET typeofdata = 'V~M' WHERE fieldname = 'firstname' OR fieldname = 'first_name'", []);
				$adb->pquery("UPDATE vtiger_field SET typeofdata = 'V~O' WHERE fieldname = 'lastname' OR fieldname = 'last_name'", []);
			}
			else {
				$adb->pquery("UPDATE vtiger_field SET typeofdata = 'V~M' WHERE fieldname = 'lastname' OR fieldname = 'last_name'", []);
				$adb->pquery("UPDATE vtiger_field SET typeofdata = 'V~O' WHERE fieldname = 'firstname' OR fieldname = 'first_name'", []);
			}
		}

		/*
		*   Author: Kelvin Thang
		*   Date: 2019-10-30
		*   Purpose: fix Missing Reference Module
		*/
		private function repairReferenceModuleForActivity() {
			echo '<strong>Refreshing Reference Module...</strong>';

			global $adb;

			//-- Get UI Type field parent_id in Calendar
			$calendarInstance = Vtiger_Module::getInstance('Calendar');
			$fieldCalendarInstance = Vtiger_Field::getInstance('parent_id', $calendarInstance);
			$fieldTypeId = $adb->getOne("SELECT fieldtypeid FROM vtiger_ws_fieldtype WHERE uitype = ?", [$fieldCalendarInstance->uitype]);

			//-- unset vtiger_ws_referencetype
			$queryDeleteReferenceType = "DELETE vtiger_ws_referencetype.*
				FROM vtiger_ws_referencetype
				LEFT JOIN vtiger_tab ON (type = name)
				WHERE source = 'custom' AND isentitytype = 1 AND fieldtypeid = ?";
			$adb->pquery($queryDeleteReferenceType, [$fieldTypeId]);

			//-- Get Modules Enable Activities
			$sqlGetModulesEnableActivities = "SELECT DISTINCT vtiger_relatedlists.tabid, parent_tab.name AS parent_name
				FROM vtiger_relatedlists
				INNER JOIN vtiger_tab AS related_tab ON (related_tab.tabid = vtiger_relatedlists.related_tabid AND related_tab.presence != 1)
				INNER JOIN vtiger_tab AS parent_tab ON (parent_tab.tabid = vtiger_relatedlists.tabid AND parent_tab.presence != 1)
				WHERE parent_tab.source = 'custom' AND parent_tab.isentitytype = 1 AND related_tabid = ? AND vtiger_relatedlists.name = ?";

			$resultGetModulesEnableActivities = $adb->pquery($sqlGetModulesEnableActivities, [$calendarInstance->getId(), 'get_activities']);
			$valueUpdate = [];

			while ($row = $adb->fetchByAssoc($resultGetModulesEnableActivities)) {
				if (!isHiddenModule($row['parent_name'])) { // Refactored by Hieu Nguyen on 2021-08-18
					$valueUpdate[] = "{$fieldTypeId} , '{$row['parent_name']}'";
				}
			}

			if (count($valueUpdate) > 0) {
				$stringUpdate = implode('), (', $valueUpdate);
				$insertReferenceModule = "INSERT INTO vtiger_ws_referencetype(fieldtypeid, type) VALUES({$stringUpdate})";
				$adb->pquery($insertReferenceModule);
			}

			echo '<strong> Done.</strong><br/>';
		}

		private function repairSMSLogs() {
			global $adb;
			echo '<strong>Repairing SMS Logs...</strong>';

			$sql = "SELECT sms.smsnotifierid, sms.message, st.customer_id, st.tonumber AS phone_number, st.status, st.statusmessage, 
					(CASE WHEN st.smsmessageid LIKE '' THEN st.statusid ELSE st.smsmessageid END) AS tracking_id,
					e.createdtime, e.smcreatorid AS created_by, e.smownerid AS assigned_user_id, e.main_owner_id
				FROM vtiger_smsnotifier AS sms
				INNER JOIN vtiger_crmentity AS e ON (e.crmid = sms.smsnotifierid AND deleted = 0)
				INNER JOIN vtiger_smsnotifier_status AS st ON (st.smsnotifierid = sms.smsnotifierid)
				WHERE (CASE WHEN st.smsmessageid LIKE '' THEN st.statusid ELSE st.smsmessageid END) NOT IN (
					SELECT tracking_id FROM vtiger_cpsmsottmessagelog
				)";
			$result = $adb->pquery($sql, []);

			while ($row = $adb->fetchByAssoc($result)) {
				$userRecordModel = Users_Record_Model::getInstanceById($row['created_by'], 'Users');
				$sentDateTimeParts = explode(' ', $row['createdtime']);
				$sentDate = $sentDateTimeParts[0];
				$sentTime = $sentDateTimeParts[1];
				$status = strtolower($row['status']);
				if ($status == '') $status = 'failed';
				if ($status == 'processing') $status = 'dispatched';
				if ($status == 'delivered') $status = 'success';

				// Convert old SMS status log into SMS & OTT Message Log
				$messageLog = Vtiger_Record_Model::getCleanInstance('CPSMSOTTMessageLog');
				$messageLog->set('name', "[sms] {$userRecordModel->get('user_name')} - {$row['phone_number']} - {$row['createdtime']}");
				$messageLog->set('related_customer', $row['customer_id']);
				$messageLog->set('phone_number', $row['phone_number']);
				$messageLog->set('related_sms_ott_notifier', $row['smsnotifierid']);
				$messageLog->set('content', $row['message']);
				$messageLog->set('content_hash', md5($row['message']));
				$messageLog->set('sms_ott_message_type', 'SMS');
				$messageLog->set('queue_status', $status);
				$messageLog->set('scheduled_send_date', $sentDate);
				$messageLog->set('scheduled_send_time', $sentTime);
				$messageLog->set('attempt_count', (in_array($status, ['dispatched', 'success']) ? 1 : 3));
				$messageLog->set('last_attempt_time', $sentTime);
				$messageLog->set('tracking_id', $row['tracking_id']);
				$messageLog->set('description', $row['statusmessage']);
				$messageLog->set('source', 'CRM');
				$messageLog->set('createdby', $row['created_by']);
				$messageLog->set('createdtime', $row['createdtime']);
				$messageLog->set('modifiedby', $row['created_by']);
				$messageLog->set('modifiedtime', $row['createdtime']);
				$messageLog->set('assigned_user_id', $row['assigned_user_id']);
				$messageLog->set('main_owner_id', $row['main_owner_id']);

				$messageLog->save();
				saveDateTimeFields($messageLog);
			}

			echo '<strong> Done.</strong>';
		}

		function repairReportFolderCode() {
			global $reportsSubMenusConfig, $adb;

			foreach ($reportsSubMenusConfig as $reportSubMenu) {
				if ($reportSubMenu['code'] == 'AllReports') continue;
				parse_str($reportSubMenu['url'], $urlParams);
				$sql = "UPDATE vtiger_reportfolder SET code = ? WHERE folderid = ?";
				$adb->pquery($sql, [$reportSubMenu['code'], $urlParams['viewname']]);
			}

			echo '<strong> Done.</strong>';
		}

		function initCustomMenu() {
			Settings_MenuEditor_Structure_Model::initCustomMenu();
			echo '<strong> Done.</strong>';
		}
	}
