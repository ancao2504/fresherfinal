<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class VtigerModuleOperation extends WebserviceEntityOperation {
	protected $tabId;
	protected $isEntity = true;
	protected $partialDescribeFields = null;
	
	public function VtigerModuleOperation($webserviceObject,$user,$adb,$log){
		parent::__construct($webserviceObject,$user,$adb,$log);
		$this->meta = $this->getMetaInstance();
		$this->tabId = $this->meta->getTabId();
	}
	
	protected function getMetaInstance(){
		if(empty(WebserviceEntityOperation::$metaCache[$this->webserviceObject->getEntityName()][$this->user->id])){
			WebserviceEntityOperation::$metaCache[$this->webserviceObject->getEntityName()][$this->user->id]  = new VtigerCRMObjectMeta($this->webserviceObject,$this->user);
		}
		return WebserviceEntityOperation::$metaCache[$this->webserviceObject->getEntityName()][$this->user->id];
	}
	
	// Refactored this function by Hieu Nguyen on 2021-09-14
	public function create($elementType,$element, $ownerPopulated = false) {    // Modified by Hieu Nguyen on 2020-08-04 to support param $ownerPopulated
		$crmObject = new VtigerCRMObject($elementType, false);
		$element = DataTransform::sanitizeForInsert($element,$this->meta);
		
		$success = $crmObject->create($element, $ownerPopulated);	// Modified by Hieu Nguyen on 2020-08-04 to prevent preset main owner id to be overridden while saving from Workflow
		
		if (!$success) {
			throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR, vtws_getWebserviceTranslatedString('LBL_' . WebServiceErrorCode::$DATABASEQUERYERROR));
		}
		
		$id = $crmObject->getObjectId();

		// For import action
		if (CRMEntity::isBulkSaveMode()) {		
			// Return the id only to speed up the import process
			return ['id' => vtws_getId($this->meta->getEntityId(), $id)];
		}
		// For normal create action (Form / API)
		else {
			// Double check to confirm that the record is successfully created
			$success = $crmObject->read($id);

			if (!$success) {
				throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR, vtws_getWebserviceTranslatedString('LBL_' . WebServiceErrorCode::$DATABASEQUERYERROR));
			}
			
			return DataTransform::filterAndSanitize($crmObject->getFields(), $this->meta);
		}
	}
	
	// Modified by Hieu Nguyen on 2022-10-17 to fix bug full_name is empty when this function is call when the saving transation is not finished yet
	public function retrieve($id){
		$ids = vtws_getIdComponents($id);
		$recordId = $ids[1];
		$crmObject = new VtigerCRMObject($this->tabId, true);
		$success = $crmObject->read($recordId);

		if (!$success) {
			throw new WebServiceException(
				WebServiceErrorCode::$DATABASEQUERYERROR,
				vtws_getWebserviceTranslatedString('LBL_'. WebServiceErrorCode::$DATABASEQUERYERROR)
			);
		}

		$data = DataTransform::filterAndSanitize($crmObject->getFields(), $this->meta);

		if ($crmObject->getInstance()->isPerson) {
			$data['full_name'] = $data['label'];
		}

		return $data;
	}
	
    public function relatedIds($id, $relatedModule, $relatedLabel, $relatedHandler=null) {
		$ids = vtws_getIdComponents($id);
        $sourceModule = $this->webserviceObject->getEntityName();		
        global $currentModule;
        $currentModule = $sourceModule;
		$sourceRecordModel = Vtiger_Record_Model::getInstanceById($ids[1], $sourceModule);
		$targetModel       = Vtiger_RelationListView_Model::getInstance($sourceRecordModel, $relatedModule, $relatedLabel);
        $sql = $targetModel->getRelationQuery();

        $relatedWebserviceObject = VtigerWebserviceObject::fromName($adb,$relatedModule);
        $relatedModuleWSId = $relatedWebserviceObject->getEntityId();

		// Rewrite query to pull only crmid transformed as webservice id.
        $sqlFromPart = substr($sql, stripos($sql, ' FROM ')+6);        
        $sql = sprintf("SELECT DISTINCT concat('%sx',vtiger_crmentity.crmid) as wsid FROM %s", $relatedModuleWSId, $sqlFromPart);
                
        $rs = $this->pearDB->pquery($sql, array());
        $relatedIds = array();
		while ($row = $this->pearDB->fetch_array($rs)) {
            $relatedIds[] = $row['wsid'];
		}
		return $relatedIds;
    }
	
	public function update($element){
		$ids = vtws_getIdComponents($element["id"]);
		$element = DataTransform::sanitizeForInsert($element,$this->meta);
		
		$crmObject = new VtigerCRMObject($this->tabId, true);
		$crmObject->setObjectId($ids[1]);
		$error = $crmObject->update($element);
		if(!$error){
			throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR,
					vtws_getWebserviceTranslatedString('LBL_'.
							WebServiceErrorCode::$DATABASEQUERYERROR));
		}
		
		$id = $crmObject->getObjectId();
		
		$error = $crmObject->read($id);
		if(!$error){
			throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR,
				vtws_getWebserviceTranslatedString('LBL_'.
							WebServiceErrorCode::$DATABASEQUERYERROR));
		}
		
		return DataTransform::filterAndSanitize($crmObject->getFields(),$this->meta);
	}
	
	public function revise($element){
		$ids = vtws_getIdComponents($element["id"]);
		$element = DataTransform::sanitizeForInsert($element,$this->meta);

		$crmObject = new VtigerCRMObject($this->tabId, true);
		$crmObject->setObjectId($ids[1]);
		$error = $crmObject->revise($element);
		if(!$error){
			throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR,
					vtws_getWebserviceTranslatedString('LBL_'.
							WebServiceErrorCode::$DATABASEQUERYERROR));
		}

		$id = $crmObject->getObjectId();

		$error = $crmObject->read($id);
		if(!$error){
			throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR,
					vtws_getWebserviceTranslatedString('LBL_'.
							WebServiceErrorCode::$DATABASEQUERYERROR));
		}

		return DataTransform::filterAndSanitize($crmObject->getFields(),$this->meta);
	}

	public function delete($id){
		$ids = vtws_getIdComponents($id);
		$elemid = $ids[1];
		
		$crmObject = new VtigerCRMObject($this->tabId, true);
		
		$error = $crmObject->delete($elemid);
		if(!$error){
			throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR,
					vtws_getWebserviceTranslatedString('LBL_'.
							WebServiceErrorCode::$DATABASEQUERYERROR));
		}
		return array("status"=>"successful");
	}
	
	public function query($q){
		
		$parser = new Parser($this->user, $q);
		$error = $parser->parse();
		
		if($error){
			return $parser->getError();
		}
		
		$mysql_query = $parser->getSql();
		$meta = $parser->getObjectMetaData();
		$this->pearDB->startTransaction();
		$result = $this->pearDB->pquery($mysql_query, array());
		$error = $this->pearDB->hasFailedTransaction();
		$this->pearDB->completeTransaction();
		
		if($error){
			throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR,
					vtws_getWebserviceTranslatedString('LBL_'.
							WebServiceErrorCode::$DATABASEQUERYERROR));
		}
		
		$noofrows = $this->pearDB->num_rows($result);
		$output = array();
		for($i=0; $i<$noofrows; $i++){
			$row = $this->pearDB->fetchByAssoc($result,$i);
			if(!$meta->hasPermission(EntityMeta::$RETRIEVE,$row["crmid"])){
				continue;
			}
			$output[] = DataTransform::sanitizeDataWithColumn($row,$meta);
		}
		
		return $output;
	}
	
	public function describe($elementType){
		$app_strings = VTWS_PreserveGlobal::getGlobal('app_strings');
		$current_user = vtws_preserveGlobal('current_user',$this->user);;
		
		$label = (isset($app_strings[$elementType]))? $app_strings[$elementType]:$elementType;
		$createable = (strcasecmp(isPermitted($elementType,EntityMeta::$CREATE),'yes')===0)? true:false;
		$updateable = (strcasecmp(isPermitted($elementType,EntityMeta::$UPDATE),'yes')===0)? true:false;
		$deleteable = $this->meta->hasDeleteAccess();
		$retrieveable = $this->meta->hasReadAccess();
		$fields = $this->getModuleFields();
		return array(	'label'			=> $label,
						'name'			=> $elementType,
						'createable'	=> $createable,
						'updateable'	=> $updateable,
						'deleteable'	=> $deleteable,
						'retrieveable'	=> $retrieveable,
						'fields'		=> $fields,
						'idPrefix'		=> $this->meta->getEntityId(),
						'isEntity'		=> $this->isEntity,
						'allowDuplicates'=>  $this->meta->isDuplicatesAllowed(),
						'labelFields'	=> $this->meta->getNameFields());
	}
	
	public function describePartial($elementType, $fields=null) {
		$this->partialDescribeFields = $fields;
		$result = $this->describe($elementType);
		$this->partialDescribeFields = null;
		return $result;
	}
	
	function getModuleFields(){
		
		$fields = array();
		$moduleFields = $this->meta->getModuleFields();
		foreach ($moduleFields as $fieldName=>$webserviceField) {
			if(((int)$webserviceField->getPresence()) == 1) {
				continue;
			}
			array_push($fields,$this->getDescribeFieldArray($webserviceField));
		}
		array_push($fields,$this->getIdField($this->meta->getObectIndexColumn()));
		
		return $fields;
	}
	
	function getDescribeFieldArray($webserviceField){
		$default_language = VTWS_PreserveGlobal::getGlobal('default_language');
		
		$fieldLabel = getTranslatedString($webserviceField->getFieldLabelKey(), $this->meta->getTabName());
		
		$typeDetails = array();
		if (!is_array($this->partialDescribeFields)) {
			$typeDetails = $this->getFieldTypeDetails($webserviceField);
		} else if (in_array($webserviceField->getFieldName(), $this->partialDescribeFields)) {
			$typeDetails = $this->getFieldTypeDetails($webserviceField);
		}
		
		//set type name, in the type details array.
		$typeDetails['name'] = $webserviceField->getFieldDataType();
		//Reference module List is missing in DescribePartial api response
		if($typeDetails['name'] === "reference") {
			$typeDetails['refersTo'] = $webserviceField->getReferenceList();
		}
		$editable = $this->isEditable($webserviceField);
		
		$describeArray = array(	'name'		=> $webserviceField->getFieldName(),
								'label'		=> $fieldLabel,
								'mandatory'	=> $webserviceField->isMandatory(),
								'type'		=> $typeDetails,
								'isunique'	=> $webserviceField->isUnique(),
								'nullable'	=> $webserviceField->isNullable(),
								'editable'	=> $editable);
		if($webserviceField->hasDefault()){
			$describeArray['default'] = $webserviceField->getDefault();
		}
		return $describeArray;
	}
	
	function getMeta(){
		return $this->meta;
	}
	
	function getField($fieldName){
		$moduleFields = $this->meta->getModuleFields();
		return $this->getDescribeFieldArray($moduleFields[$fieldName]);
	}
	
}
?>
