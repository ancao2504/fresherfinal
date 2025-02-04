<?php

/*
	Class OpenApiHandler
	Author: Hieu Nguyen
	Date: 2022-12-28
	Purpose: provide APIs for Open API to allow any client to integrate with CRM via RESTful APIs
*/

require_once('include/utils/RestfulApiUtils.php');
require_once('libraries/PHP-JWT/src/JWT.php');
require_once('libraries/PHP-JWT/src/ExpiredException.php');
use \Firebase\JWT\JWT;
use \Firebase\JWT\ExpiredException;

class OpenApiHandler extends RestfulApiUtils {

	const JWT_ALGO = 'HS256';
	const JWT_VALID_TIME = 3600;
	const LIST_PAGING_ROWS_DEFAULT = 10;
	const LIST_PAGING_ROWS_MAX = 100;
	const LIST_SORTABLE_FIELDS = ['createdtime', 'modifiedtime'];
	const SAVE_UNSUPPORTED_FIELDS = ['starred', 'tags', 'createdtime', 'createdby', 'modifiedtime', 'modifiedby', 'source'];
	const ACTIONS_REQUIRED_MODULE_NAME = ['metadata', 'list', 'retrieve', 'create', 'update', 'delete'];

	protected static function _checkHttpMethod($method) {
		if ($_SERVER['REQUEST_METHOD'] != $method) {
			self::setResponse(400, 'Expected HTTP Method for this API is ' . $method);
		}
	}

	// Generate access token using access key. When user change access key then all generated access tokens will forced to invalid!
	protected static function _generateAccessToken($userId, $accessKey) {
		$accessDomain = $_SERVER['SERVER_NAME'];
		$createdTime = time();
		$expireTime = $createdTime + self::JWT_VALID_TIME;
			
		$payload = [
			'exp' => $expireTime,
			'domain' => $accessDomain,	// More secure with access domain
			'user_id' => $userId,
		];

		$accessToken = $userId .'.'. JWT::encode($payload, $accessKey, self::JWT_ALGO);
		$result = [
			'access_token' => $accessToken,
			'created_time' => $createdTime,
			'expire_time' => $expireTime,
		];

		return $result;
	}

	// API /auth: return access token
	static function auth(Vtiger_Request $request) {
		checkAccessForbiddenFeature('OpenAPI');
		self::_checkHttpMethod('GET');
		$username = $request->get('username');
		$accessKeyMd5 = $request->get('access_key_md5');

		if (empty($username) || empty($accessKeyMd5)) {
			self::setResponse(400);
		}

		// Check username
		$userEntity = CRMEntity::getInstance('Users');
		$userId = $userEntity->retrieve_user_id($username);

		if (empty($userId)) {
			self::setResponse(401);
		}

		// Check access key
		$userEntity->retrieve_entity_info($userId, 'Users');
		$accessKey = $userEntity->column_fields['accesskey'];

		if (md5($accessKey) == $accessKeyMd5) {
			self::_setAuthSession($userId);
			vglobal('current_user', $userEntity);

			// Track the login history
			$userModuleModel = Users_Module_Model::getInstance('Users');
			$userModuleModel->saveLoginHistory($username);

			// Issue access token
			$accessToken = self::_generateAccessToken($userId, $accessKey);
			self::setResponse(200, $accessToken);
		}
		else {
			self::setResponse(401);
		}
	}

	// Parse access token from request
	protected static function _parseAccessToken($accessToken) {
		list($userId, $token) = explode('.', $accessToken, 2);
		return ['user_id' => $userId, 'token' => $token];
	}

	static function checkSession($accessToken) {
		checkAccessForbiddenFeature('OpenAPI');

		// Parse access token
		$accessToken = self::_parseAccessToken($accessToken);
		$userId = $accessToken['user_id'];
		$token = $accessToken['token'];

		// Get user info
		$user = CRMEntity::getInstance('Users');
		$user->retrieveCurrentUserInfoFromFile($userId);
		
		if (empty($user->id)) {
			session_destroy();
			self::setResponse(401);
		}

		// Verify access token
		$accessDomain = $_SERVER['SERVER_NAME'];

		try {
			$payload = JWT::decode($token, $user->accesskey, [self::JWT_ALGO]);
		}
		catch (ExpiredException $e) {
			session_destroy();
			self::setResponse(401, 'Token expired!');
		}

		if ($payload->domain != $accessDomain) {
			session_destroy();
			self::setResponse(401, 'Token\'s domain mismatched!');
		}

		if ($payload->user_id != $userId) {
			session_destroy();
			self::setResponse(401, 'Token\'s user_id mismatched!');
		}

		self::_setAuthSession($userId);
		vglobal('current_user', $user);
	}

	// API /me: return current user info
	static function me(Vtiger_Request $request) {
		global $current_user;
		self::_checkHttpMethod('GET');

		$response = [
			'success' => 1,
			'data' => [
				'id' => $current_user->id,
				'username' => $current_user->user_name,
				'first_name' => $current_user->first_name,
				'last_name' => $current_user->last_name,
				'email1' => $current_user->email1,
			]
		];

		self::setResponse(200, $response);
	}

	protected static function _getModulePermissions($moduleModel) {
		global $current_user;
		$moduleName = $moduleModel->getName();
		if (isForbiddenFeature("Module{$moduleName}")) return null;

		$userPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$actionModels = Vtiger_Action_Model::getAllBasic(true);
		$roleId = $current_user->roleid;
		$roleRecordModel = Settings_Roles_Record_Model::getInstanceById($roleId);
		$profileId = $roleRecordModel->getDirectlyRelatedProfileId();
		$roleProfiles = [];

		if ($profileId) {
			$roleProfiles = [Settings_Profiles_Record_Model::getInstanceById($profileId)];
		}
		else {
			$roleProfiles = $roleRecordModel->getProfiles();
		}
		
		$permission = null;

		foreach ($roleProfiles as $profileRecordModel) {
			if (!$userPriviligesModel->hasModulePermission($moduleModel->getId())) {
				$permission = 0;
			}
			else {
				if (!isset($permission)) {
					$permission = [];
				}

				foreach ($actionModels as $actionModel) {
					$actionName = $actionModel->getName();

					if (!$profileRecordModel->hasModuleActionPermission($moduleName, $actionModel)) {
						$permission[$actionName] = 0;
					}
					else {
						$permission[$actionName] = 1;
					}
				}
			}
		}

		return $permission;
	}

	// API /permission: return current user info
	static function permission(Vtiger_Request $request) {
		self::_checkHttpMethod('GET');
		$moduleName = $request->getModule();

		// Get specified module permission
		if (!empty($moduleName)) {
			$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
			$permissions = self::_getModulePermissions($moduleModel);
			$response = ['success' => 1, 'module_permissions' => ["{$moduleName}" => $permissions]];
			self::setResponse(200, $response);
		}
		// Get all modules permission
		else {
			$entityModules = Vtiger_Module_Model::getEntityModules();
			$modulesPermissions = [];
			
			foreach ($entityModules as $moduleModel) {
				$permissions = self::_getModulePermissions($moduleModel);

				if ($permissions !== null) {
					$moduleName = $moduleModel->getName();
					$modulesPermissions[$moduleName] = $permissions;
				}
			}

			$response = ['success' => 1, 'module_permissions' => $modulesPermissions];
			self::setResponse(200, $response);
		}
	}

	// API /metadata: get metadata of the specified module
	static function metadata(Vtiger_Request $request) {
		self::_checkHttpMethod('GET');
		$moduleName = $request->getModule();

		// Validate request
		if (empty($moduleName)) {
			self::setResponse(400);
		}

		// Check permission
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$userPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		if (isForbiddenFeature("Module{$moduleName}") || !$userPriviligesModel->hasModulePermission($moduleModel->getId())) {
			self::setResponse(200, ['success' => 0, 'message' => 'Access denied!']);
		}

		// Process
		$moduleFields = $moduleModel->getFields();
		$metadata = [
			'field_list' => [],
			'picklist_options' => [],
			'picklist_dependencies' => Vtiger_DependencyPicklist::getPicklistDependencyDatasource($moduleName),
		];

		foreach ($moduleFields as $fieldName => $fieldModel) {
			if (in_array($fieldName, self::SAVE_UNSUPPORTED_FIELDS)) continue;

			$metadata['field_list'][$fieldName] = [
				'required' => $fieldModel->isMandatory() ? 1 : 0,
				'readonly' => $fieldModel->isReadonly() ? 1 : 0,
				'label_key' => $fieldModel->get('label'),
				'label_display' => vtranslate($fieldModel->get('label'), $moduleName),
			];

			if (in_array($fieldModel->getFieldDataType(), ['picklist', 'multipicklist'])) {
				$metadata['picklist_options'][$fieldName] = self::_getPicklistValues($moduleName, $fieldName);
			}
		}

		$response = ['success' => 1, 'metadata' => $metadata];
		self::setResponse(200, $response);
	}

	// API /list: return list records of the specified module
	static function list(Vtiger_Request $request) {
		global $adb;
		self::_checkHttpMethod('GET');
		$moduleName = $request->getModule();
		
		// Validate request
		if (empty($moduleName)) {
			self::setResponse(400);
		}

		// Check permission
		if (!Users_Privileges_Model::isPermitted($moduleName, 'ListView')) {
			self::setResponse(200, ['success' => 0, 'message' => 'Access denied!']);
		}

		$offset = $request->get('offset', 0);
		$maxRows = $request->get('max_rows', self::LIST_PAGING_ROWS_DEFAULT);

		if ($maxRows > self::LIST_PAGING_ROWS_MAX) {
			$result = ['success' => 0, 'message' => 'Max rows cannot be greater than ' . self::LIST_PAGING_ROWS_MAX];
			self::setResponse(200, $result);
		}

		// Process
		$listQuery = self::_getSqlByCvId($moduleName, 'all', []);
		
		// Split query to components
		$queryComponents = preg_split('/ FROM /i', $listQuery, 2);
		$select = $queryComponents[0] . ' ';
		$fromAndWhere = 'FROM '. $queryComponents[1] . ' ';

		// Sorting
		$orderBy = "ORDER BY vtiger_crmentity.createdtime DESC ";

		if (!empty($request->get('sort_column'))) {
			$sortColumn = $request->get('sort_column');
			$sortOrder = $request->get('sort_order');

			if (!in_array($sortColumn, self::LIST_SORTABLE_FIELDS)) {
				self::setResponse(400, ['success' => 0, 'message' => "Sort column {$sortColumn} is not supported!"]);
			}

			if (!in_array($sortOrder, ['ASC', 'DESC'])) {
				self::setResponse(400, ['success' => 0, 'message' => 'Sort order must be ASC or DESC!']);
			}

			$sortColumn = escapeStringForSql($adb, $sortColumn);
			$sortOrder = escapeStringForSql($adb, $sortOrder);
			$orderBy = "ORDER BY vtiger_crmentity.{$sortColumn} {$sortOrder} ";
		}

		// Paging
		$offset = escapeStringForSql($adb, $offset);
		$maxRows = escapeStringForSql($adb, $maxRows);
		$paginate = "LIMIT {$offset}, {$maxRows} ";

		// Main query
		$sqlParams = [];
		$sql = $select . $fromAndWhere . $orderBy . $paginate;
		
		$result = $adb->pquery($sql, $sqlParams);
		$entryList = [];
		$count = 0;

		// Fetch rows
		$moduleEntity = CRMEntity::getInstance($moduleName);

		while ($row = $adb->fetchByAssoc($result)) {
			if ($moduleEntity->isPerson) {
				$row['full_name'] = getFullNameFromArray($moduleName, $row);
			}

			self::_resolveOwnersName($row);
			$entryList[] = decodeUTF8($row);
			$count++;
		}

		// Count total
		$sqlTotalCount = "SELECT COUNT(1) AS total_count {$fromAndWhere}";
		$totalCount = $adb->getOne($sqlTotalCount, $sqlParams);

		// Respond
		$response = self::_getResponseWithPaging($entryList, $offset, $count, $totalCount);
		self::setResponse(200, $response);
	}

	// API /relatedList: get related records of the specified record
	static function relatedList(Vtiger_Request $request) {
		global $adb;
		self::_checkHttpMethod('GET');
		$parentModuleName = $request->get('parent_module');
		$parentRecordId = $request->get('parent_record');
		$relationId = $request->get('relation_id');

		// Validate request
		if (empty($parentModuleName) || empty($parentRecordId) || empty($relationId)) {
			self::setResponse(400);
		}
		
		// Check permission
		if (!Users_Privileges_Model::isPermitted($parentModuleName, 'ListView')) {
			self::setResponse(200, ['success' => 0, 'message' => 'Access denied!']);
		}

		// Process
		$offset = $request->get('offset', 0);
		$maxRows = $request->get('max_rows', self::LIST_PAGING_ROWS_DEFAULT);

		if ($maxRows > self::LIST_PAGING_ROWS_MAX) {
			$result = ['success' => 0, 'message' => 'Max rows cannot be greater than ' . self::LIST_PAGING_ROWS_MAX];
			self::setResponse(200, $result);
		}

		try {
			$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentRecordId, $parentModuleName);
		}
		catch (Exception $e) {
			global $app_strings;

			if ($e->getMessage() == $app_strings['LBL_RECORD_NOT_FOUND']) {
				self::setResponse(200, ['success' => 0, 'message' => 'Parent record not found!']);
			}
		}

		$relationModel = Vtiger_Relation_Model::getInstanceFromId($relationId);

		if (empty($relationModel)) {
			$result = ['success' => 0, 'message' => 'Related list not found!'];
			self::setResponse(200, $result);
		}

		$listQuery = $relationModel->getQuery($parentRecordModel);
		
		// Split query to components
		$queryComponents = preg_split('/ FROM /i', $listQuery, 2);
		$select = $queryComponents[0] . ' ';
		$fromAndWhere = 'FROM '. $queryComponents[1] . ' ';

		// Sorting
		$orderBy = "ORDER BY vtiger_crmentity.createdtime DESC ";

		if (!empty($request->get('sort_column'))) {
			$sortColumn = $request->get('sort_column');
			$sortOrder = $request->get('sort_order');

			if (!in_array($sortColumn, self::LIST_SORTABLE_FIELDS)) {
				self::setResponse(400, ['success' => 0, 'message' => "Sort column {$sortColumn} is not supported!"]);
			}

			if (!in_array($sortOrder, ['ASC', 'DESC'])) {
				self::setResponse(400, ['success' => 0, 'message' => 'Sort order must be ASC or DESC!']);
			}

			$sortColumn = escapeStringForSql($adb, $sortColumn);
			$sortOrder = escapeStringForSql($adb, $sortOrder);
			$orderBy = "ORDER BY vtiger_crmentity.{$sortColumn} {$sortOrder} ";
		}

		// Paging
		$offset = escapeStringForSql($adb, $offset);
		$maxRows = escapeStringForSql($adb, $maxRows);
		$paginate = "LIMIT {$offset}, {$maxRows} ";

		// Main query
		$sqlParams = [];
		$sql = $select . $fromAndWhere . $orderBy . $paginate;
		
		$result = $adb->pquery($sql, $sqlParams);
		$entryList = [];
		$count = 0;

		// Fetch rows
		$relatedModuleEntity = $relationModel->getRelationModuleModel()->entity;

		while ($row = $adb->fetchByAssoc($result)) {
			if ($relatedModuleEntity->isPerson) {
				$row['full_name'] = getFullNameFromArray($parentModuleName, $row);
			}

			self::_resolveOwnersName($row);
			$entryList[] = decodeUTF8($row);
			$count++;
		}

		// Count total
		$sqlTotalCount = "SELECT COUNT(1) AS total_count {$fromAndWhere}";
		$totalCount = $adb->getOne($sqlTotalCount, $sqlParams);

		// Respond
		$response = self::_getResponseWithPaging($entryList, $offset, $count, $totalCount);
		self::setResponse(200, $response);
	}

	// API /retrieve: retrieve a record in the specified module
	static function retrieve(Vtiger_Request $request) {
		self::_checkHttpMethod('GET');
		$moduleName = $request->getModule();
		$recordId = $request->get('record');

		// Validate request
		if (empty($moduleName) || empty($recordId)) {
			self::setResponse(400);
		}

		// Check permission
		if (!Users_Privileges_Model::isPermitted($moduleName, 'DetailView', $recordId)) {
			self::setResponse(200, ['success' => 0, 'message' => 'Access denied!']);
		}

		// Process
		try {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
			$recordData = $recordModel->getData();
			self::_resolveOwnersName($recordData);

			// Respond
			self::setResponse(200, ['success' => 1, 'data' => $recordData]);
		}
		// Handle error
		catch (Exception $e) {
			global $app_strings;
			saveLog('OPEN_API', '[OpenApiHandler::retrieve] Exception: '. $e->getMessage(), $e->getTrace());

			if ($e->getMessage() == $app_strings['LBL_RECORD_NOT_FOUND']) {
				self::setResponse(200, ['success' => 0, 'message' => 'Record not found!']);
			}

			if ($e->getMessage() == $app_strings['LBL_RECORD_DELETE']) {
				self::setResponse(200, ['success' => 0, 'message' => 'Record already deleted!']);
			}

			self::setResponse(200, ['success' => 0, 'message' => 'Error retrieving record!']);
		}
	}

	protected static function _saveRecord($moduleName, array $data, array $virtualFields = []) {
		global $adb;
		$virtualFields = array_merge($virtualFields, ['remove_image']);
		$recordId = $data['record'];
		unset($data['record']);

		try {
			if (!empty($recordId)) {
				$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
				$recordModel->set('mode', 'edit');
			}
			else {
				$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
				$recordModel->set('source', 'OPEN API');
			}

			// Check for non matching fields and read-only fields
			$moduleFields = $recordModel->getModule()->getFields();
			$nonMatchingFields = [];
			$readonlyFields = [];

			foreach ($data as $fieldName => $value) {
				$fieldModel = $moduleFields[$fieldName];

				// Field not exist
				if (!$fieldModel) {
					// Only warning when this field is not a virtual field
					if (!in_array($fieldName, $virtualFields)) {
						$nonMatchingFields[] = $fieldName;
					}
				}
				// Field is read-only by user role
				else if ($fieldModel->isReadOnly() || in_array($fieldName, self::SAVE_UNSUPPORTED_FIELDS)) {
					$readonlyFields[] = $fieldName;
					continue;
				}

				// Set field value for saving
				$recordModel->set($fieldName, $value);
			}

			// Stop here when no field matching
			if (count($nonMatchingFields) == count($data)) {
				return ['success' => 0, 'message' => 'No field matching!'];
			}

			// To remove old record image at crmentity save logic
			if ($data['remove_image'] && !empty($recordId)) {
				$_REQUEST['imgDeleted'] = true;	
				
				$getImageIdsSql = "SELECT attachmentsid AS id FROM vtiger_seattachmentsrel WHERE crmid = ?";
				$result = $adb->pquery($getImageIdsSql, [$recordId]);

				while ($row = $adb->fetchByAssoc($result)) {
					$recordModel->deleteImage($row['id']);
				}
			}

			$_REQUEST = array_merge($_REQUEST, $data);	// Some core logic require the field value in the $_REQUEST
			$recordModel->save();

			// Respond
			$response = ['success' => 1, 'record_id' => $recordModel->getId()];

			// Response with warning
			if (!empty($nonMatchingFields) || !empty($readonlyFields)) {
				$warnings = [];

				if (!empty($nonMatchingFields)) {
					$warnings[] = 'These fields are not matching: ' . join(', ', $nonMatchingFields) . '.';
				}

				if (!empty($readonlyFields)) {
					$warnings[] = 'These fields are read-only: ' . join(', ', $readonlyFields) . '.';
				}

				$response['warning'] = join(' ', $warnings);
				saveLog('OPEN_API', '[OpenApiHandler::_saveRecord] Warning: '. $response['warning']);
			}

			return $response;
		}
		// Handle error
		catch (Exception $e) {
			global $app_strings;
			saveLog('OPEN_API', '[OpenApiHandler::_saveRecord] Exception: '. $e->getMessage(), $e->getTrace());

			if ($e->getMessage() == $app_strings['LBL_RECORD_NOT_FOUND']) {
				return ['success' => 0, 'message' => 'Record not found!'];
			}

			if ($e->getMessage() == $app_strings['LBL_RECORD_DELETE']) {
				return ['success' => 0, 'message' => 'Record already deleted!'];
			}
			
			return ['success' => 0, 'message' => 'Error saving record!'];
		}
	}

	static function _saveInventoryRecord($moduleName, array $data) {
		global $adb;
		$lineItems = $data['line_items'];

		// Check line items
		if (empty($lineItems) && empty($data['record'])) {
			self::setResponse(400, 'Line items must be specified when creating new inventory record!');
		}

		// Check each item
		$warnings = [];

		if (!empty($lineItems)) {
			foreach ($lineItems as $item) {
				if (empty($item['id']) || empty($item['type']) || empty($item['quantity']) || empty($item['price'])) {
					self::setResponse(400, 'These fields are required for item info: id, type, quantity, price!');
				}

				if (!in_array($item['type'], ['Product', 'Service'])) {
					self::setResponse(400, 'Item type must be Product or Service!');
				}
			}

			$nonExistProductIds = [];
			$nonExistServiceIds = [];

			foreach ($lineItems as $item) {
				if ($item['type'] == 'Product' && !isEntityRecordExists($item['id'], 'Products')) {
					$nonExistProductIds[] = $item['id'];
				}

				if ($item['type'] == 'Service' && !isEntityRecordExists($item['id'], 'Services')) {
					$nonExistServiceIds[] = $item['id'];
				}
			}

			if (!empty($nonExistProductIds)) {
				$warnings[] = 'Non exist product ids: ' . join(', ', $nonExistProductIds) . '.';
			}

			if (!empty($nonExistServiceIds)) {
				$warnings[] = 'Non exist service ids: ' . join(', ', $nonExistServiceIds) . '.';
			}
		}

		// Save record first
		$data['taxtype'] = !empty($data['taxtype']) ? $data['taxtype'] : 'group';
		$virtualFields = ['line_items', 'taxtype', 'adjustment', 'subtotal', 'total'];
		$response = self::_saveRecord($moduleName, $data, $virtualFields);

		// Then save ammounts and line items
		if ($response['success'] === 1) {
			$recordId = $response['record_id'];

			// Save amounts
			$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
			$sql = "UPDATE {$moduleModel->basetable} 
				SET taxtype = ?, subtotal = ?, discount_percent = ?, discount_amount = ?, pre_tax_total = ?, adjustment = ?, total = ? 
				WHERE {$moduleModel->basetableid} = ?";
			$params = [$data['taxtype'], $data['subtotal'], $data['discount_percent'], $data['discount_amount'], $data['pre_tax_total'], $data['adjustment'], $data['total'], $recordId];
			$adb->pquery($sql, $params);

			// Save line items
			if (!empty($lineItems)) {
				self::_saveInventoryLineItems($adb, $recordId, $lineItems);
			}

			// Commit transaction
			$adb->query('commit;');
		}

		// Warning if no item specified when updating record
		if (empty($lineItems) && !empty($data['record'])) {
			$warnings[] = ' No line item specified. Existing line items will be remained!';
		}

		if (!empty($warnings)) {
			$response['warning'] .= ' ' . join(' ', $warnings);
			$response['warning'] = trim($response['warning']);
		}

		self::setResponse(200, $response);
	}

	static function _saveInventoryLineItems($adb, $recordId, array $lineItems) {
		// Get items tax and cost info
		$sql = "SELECT pt.productid, pt.taxpercentage, it.taxname, p.purchase_cost
			FROM vtiger_inventorytaxinfo AS it 
			INNER JOIN vtiger_producttaxrel AS pt ON (it.taxid = pt.taxid)
			INNER JOIN vtiger_products AS p ON (p.productid = pt.productid)
			UNION ALL
			SELECT pt.productid, pt.taxpercentage, it.taxname, s.purchase_cost
			FROM vtiger_inventorytaxinfo AS it 
			INNER JOIN vtiger_producttaxrel AS pt ON (it.taxid = pt.taxid)
			INNER JOIN vtiger_service AS s ON (s.serviceid = pt.productid)";
		$result = $adb->pquery($sql);
		$inventoryInfo = [
			'product_tax' => [],
			'purchase_cost' => []
		];

		while ($row = $adb->fetchByAssoc($result)) {
			if (!isset($inventoryInfo['product_tax'][$row['productid']])) {
				$inventoryInfo['product_tax'][$row['productid']] = [];
			}

			$inventoryInfo['product_tax'][$row['productid']][$row['taxname']] = $row['taxpercentage'];
			$inventoryInfo['purchase_cost'][$row['productid']] = $row['purchase_cost'];
		}

		// Delete all product link to this sales order
		$adb->pquery("DELETE FROM vtiger_inventoryproductrel WHERE id = ?", [$recordId]);

		// Then insert new line items
		foreach ($lineItems as $item) {
			if ($item['quantity'] <= 0) continue;
			
			// Calculate item amount
			$itemAmount = $item['price'] * $item['quantity'];

			// Calculate purchase cost
			$purchaseCost = $inventoryInfo['purchase_cost'][$item['id']] * $item['quantity'];

			// Insert item
			$params = array_merge(
				[
					'id' => $recordId,
					'productid' => $item['id'],
					'sequence_no' => $item['sequence_no'],
					'section_num' => $item['section_num'],
					'section_name' => $item['section_name'],
					'quantity' => $item['quantity'],
					'listprice' => $item['price'],
					'discount_percent' => $item['discount_percent'],
					'discount_amount' => $item['discount_amount'],
					'comment' => $item['comment'],
					'description' => $item['description'],
					'purchase_cost' => $purchaseCost,
					'margin' => $itemAmount - $purchaseCost
				],
				$inventoryInfo['product_tax'][$item['id']] ?: []
			);

			$sql = $adb->sql_insert_data('vtiger_inventoryproductrel', $params);
			$adb->query($sql);
		}
	}

	// Do custom logic here!
	static function _handleSaveRecord($moduleName, array $data) {
		global $inventoryModules, $validationConfig;

		// Check valid file upload
		if (!empty($_FILES)) {
			foreach ($_FILES as $fieldName => $fileInfo) {
				$allowedFileExts = $validationConfig['allowed_upload_file_exts'];
				if ($fieldName == 'imagename') $allowedFileExts = ['png', 'jpg', 'jpeg', 'gif'];
				$fileNames = is_array($fileInfo['name']) ? $fileInfo['name'] : [$fileInfo['name']];

				foreach ($fileNames as $fileName) {
					$fileExt = strtolower(end(explode('.', $fileName)));
					
					if (!in_array($fileExt, $allowedFileExts)) {
						self::setResponse(200, ['success' => 0, 'message' => 'Unsupported file: ' . $fileName]);
					}
				}
			}
		}

		// Check picklist options
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$picklistFieldModels = $moduleModel->getFieldsByType(['picklist', 'multipicklist'], false);

		foreach ($data as $fieldName => $fieldValue) {
			if (isset($picklistFieldModels[$fieldName])) {
				$fieldModel = $picklistFieldModels[$fieldName];
				$picklistType = $fieldModel->getFieldDataType();
				$picklistOptions = $fieldModel->getPicklistValues();
				$supportedValues = array_keys($picklistOptions);

				if (!$fieldModel->isMandatory() && empty($fieldValue)) {
					continue;	// Allow to save empty value for non required picklist field
				}

				if ($picklistType == 'picklist') {
					if ($moduleName == 'Calendar' && $fieldName == 'activitytype') {
						$supportedValues[] = 'Task';	// Support option Task for Calendar
					}

					if (!in_array($fieldValue, $supportedValues)) {
						$message = "This picklist value for {$fieldName} is not valid: " . $fieldValue . '.';
						self::setResponse(200, ['success' => 0, 'message' => $message]);
					}
				}
				else if ($picklistType == 'multipicklist') {
					$invalidOptions = array_diff(getMultiPicklistValues($fieldValue), $supportedValues);

					if (!empty($invalidOptions)) {
						$message = "These picklist values for {$fieldName} are not valid: " . join(', ', $invalidOptions) . '.';
						self::setResponse(200, ['success' => 0, 'message' => $message]);
					}
				}
			}
		}

		// Check relation field value
		$relationFieldModels = $moduleModel->getFieldsByType('reference');

		foreach ($relationFieldModels as $fieldName => $fieldModel) {
			$relatedRecordId = $data[$fieldName];
			if (empty($relatedRecordId)) continue;
			$relatedModules = $fieldModel->getReferenceList();

			if (!isRecordExists($relatedRecordId)) {
				$message = "The related record ID for {$fieldName} does not exist.";
				self::setResponse(200, ['success' => 0, 'message' => $message]);
			}

			$relatedRecordType = getSalesEntityType($relatedRecordId);

			if (!in_array($relatedRecordType, $relatedModules)) {
				$message = "The related record ID for {$fieldName} is not valid.";
				self::setResponse(200, ['success' => 0, 'message' => $message]);
			}
		}

		// For inventory
		if (in_array($moduleName, $inventoryModules)) {
			return self::_saveInventoryRecord($moduleName, $data);
		}

		// For other entity types
		return self::_saveRecord($moduleName, $data);
	}

	// API /create: create a new record in the specified module
	static function create(Vtiger_Request $request) {
		self::_checkHttpMethod('POST');
		$moduleName = $request->getModule();
		$data = $request->get('data');
		
		// Validate request
		if (empty($moduleName) || empty($data)) {
			self::setResponse(400);
		}

		// Check permission
		if (!Users_Privileges_Model::isPermitted($moduleName, 'CreateView')) {
			self::setResponse(200, ['success' => 0, 'message' => 'Access denied!']);
		}

		// Check required fields
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$requiredFields = $moduleModel->getRequiredFields();
		$missingRequiredFields = array_diff(array_keys($requiredFields), array_keys($data));

		if (!empty($missingRequiredFields)) {
			$message = 'These fields are required: ' . join(', ', $missingRequiredFields) . '.';
			self::setResponse(200, ['success' => 0, 'message' => $message]);
		}

		// Check required fields value
		foreach ($requiredFields as $fieldName => $_) {
			$fieldValue = $data[$fieldName];

			if (empty($fieldValue)) {
				self::setResponse(200, ['success' => 0, 'message' => "Value for {$fieldName} is required."]);
			}
		}

		// Check required fields for Calendar
		if ($moduleName == 'Calendar') {
			if (empty($data['activitytype'])) {
				self::setResponse(200, ['success' => 0, 'message' => 'Calendar activitytype must be on of the follow values: Task, Call, Meeting.']);
			}

			if ($data['activitytype'] == 'Task') {
				unset($data['eventstatus']);	// Prevent saving wrong status field for Task

				if (empty($data['taskstatus'])) {
					self::setResponse(200, ['success' => 0, 'message' => 'Value for taskstatus is required for new Task.']);
				}
			}

			if ($data['activitytype'] != 'Task') {
				unset($data['taskstatus']);		// Prevent saving wrong status field for Event

				if (empty($data['eventstatus'])) {
					self::setResponse(200, ['success' => 0, 'message' => 'Value for eventstatus is required for new '. $data['activitytype'] .'.']);
				}
			}
		}

		// Prevent putting record id in api /create
		if (isset($data['record'])) {
			unset($data['record']);
		}

		$response = self::_handleSaveRecord($moduleName, $data);
		self::setResponse(200, $response);
	}

	// API /update: update an existing record in the specified module
	static function update(Vtiger_Request $request) {
		self::_checkHttpMethod('POST');
		$moduleName = $request->getModule();
		$recordId = $request->get('record');
		$data = $request->get('data');
		
		// Validate request
		if (empty($moduleName) || empty($recordId) || empty($data)) {
			self::setResponse(400);
		}

		// Check permission
		if (!Users_Privileges_Model::isPermitted($moduleName, 'EditView', $recordId)) {
			self::setResponse(200, ['success' => 0, 'message' => 'Access denied!']);
		}

		// Put record id into the data array
		$data['record'] = $recordId;

		$response = self::_handleSaveRecord($moduleName, $data);
		self::setResponse(200, $response);
	}

	// API /delete: delete a record in the specified module
	static function delete(Vtiger_Request $request) {
		self::_checkHttpMethod('POST');
		$moduleName = $request->getModule();
		$recordId = $request->get('record');

		// Validate request
		if (empty($moduleName) || empty($recordId)) {
			self::setResponse(400);
		}

		// Check permission
		if (!Users_Privileges_Model::isPermitted($moduleName, 'Delete', $recordId)) {
			self::setResponse(200, ['success' => 0, 'message' => 'Access denied!']);
		}

		// Process
		try {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
			$recordModel->delete();

			// Respond
			self::setResponse(200, ['success' => 1]);
		}
		// Handle error
		catch (Exception $e) {
			global $app_strings;
			saveLog('OPEN_API', '[OpenApiHandler::delete] Exception: '. $e->getMessage(), $e->getTrace());

			if ($e->getMessage() == $app_strings['LBL_RECORD_NOT_FOUND']) {
				self::setResponse(200, ['success' => 0, 'message' => 'Record not found!']);
			}

			if ($e->getMessage() == $app_strings['LBL_RECORD_DELETE']) {
				self::setResponse(200, ['success' => 0, 'message' => 'Record already deleted!']);
			}

			self::setResponse(200, ['success' => 0, 'message' => 'Error deleting record!']);
		}
	}
}