<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_ExportData_Action extends Vtiger_Mass_Action {

	function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$currentUserPriviligesModel->hasModuleActionPermission($moduleModel->getId(), 'Export')) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
		}
	}

	/**
	 * Function is called by the controller
	 * @param Vtiger_Request $request
	 */
	function process(Vtiger_Request $request) {
		$this->ExportData($request);
	}

	private $moduleInstance;
	private $focus;

	/**
	 * Function exports the data based on the mode
	 * @param Vtiger_Request $request
	 */
	function ExportData(Vtiger_Request $request) {
		$db = PearDatabase::getInstance();
		$moduleName = $request->get('source_module');

		$this->moduleInstance = Vtiger_Module_Model::getInstance($moduleName);
		$this->moduleFieldInstances = $this->moduleFieldInstances($moduleName);
		$this->focus = CRMEntity::getInstance($moduleName);

		$query = $this->getExportQuery($request);
		$result = $db->pquery($query, array());

		$translatedHeaders = $this->getHeaders();
		$entries = array();
		for ($j = 0; $j < $db->num_rows($result); $j++) {
			$entries[] = $this->sanitizeValues($db->fetchByAssoc($result, $j));
		}

		$this->output($request, $translatedHeaders, $entries);
	}

	public function getHeaders() {
		$headers = array();
		//Query generator set this when generating the query
		if(!empty($this->accessibleFields)) {
			$accessiblePresenceValue = array(0,2);
			foreach($this->accessibleFields as $fieldName) {
				$fieldModel = $this->moduleFieldInstances[$fieldName];
				// Check added as querygenerator is not checking this for admin users
				$presence = $fieldModel->get('presence');
				if(in_array($presence, $accessiblePresenceValue) && $fieldModel->get('displaytype') != '6') {
					$headers[] = $fieldModel->get('label');
				}
			}
		} else {
			foreach($this->moduleFieldInstances as $field) {
				$headers[] = $field->get('label');
			}
		}

		$translatedHeaders = array();
		foreach($headers as $header) {
			// Added by Hieu Nguyen on 2021-12-09 to fix bug export data for Calendar is not matching with the header columns
			if ($this->moduleInstance->getName() == 'Calendar' && in_array($header, ['Time Start', 'End Time', 'Send Notification'])) {
				continue;
			}
			// End Hieu Nguyen
			
			$translatedHeaders[] = vtranslate(html_entity_decode($header, ENT_QUOTES), $this->moduleInstance->getName());
		}

		$translatedHeaders = array_map('decode_html', $translatedHeaders);
		return $translatedHeaders;
	}

	function getAdditionalQueryModules(){
		return array_merge(getInventoryModules(), array('Products', 'Services', 'PriceBooks'));
	}

	/**
	 * Function that generates Export Query based on the mode
	 * @param Vtiger_Request $request
	 * @return <String> export query
	 */
	function getExportQuery(Vtiger_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$mode = $request->getMode();
		$cvId = $request->get('viewname');
		$moduleName = $request->get('source_module');

		$queryGenerator = new EnhancedQueryGenerator($moduleName, $currentUser);
		$queryGenerator->initForCustomViewById($cvId);
		$fieldInstances = $this->moduleFieldInstances;

		$orderBy = $request->get('orderby');
		$orderByFieldModel = $fieldInstances[$orderBy];
		$sortOrder = $request->get('sortorder');

		if ($mode !== 'ExportAllData') {
			$operator = $request->get('operator');
			$searchKey = $request->get('search_key');
			$searchValue = $request->get('search_value');

			$tagParams = $request->get('tag_params');
			if (!$tagParams) {
				$tagParams = array();
			}

			$searchParams = $request->get('search_params');
			if (!$searchParams) {
				$searchParams = array();
			}

			$glue = '';
			if($searchParams && count($queryGenerator->getWhereFields())) {
				$glue = QueryGenerator::$AND;
			}
			$searchParams = array_merge($searchParams, $tagParams);
			$searchParams = Vtiger_Util_Helper::transferListSearchParamsToFilterCondition($searchParams, $this->moduleInstance);
			$queryGenerator->parseAdvFilterList($searchParams, $glue);

			if($searchKey) {
				$queryGenerator->addUserSearchConditions(array('search_field' => $searchKey, 'search_text' => $searchValue, 'operator' => $operator));
			}

			if ($orderBy && $orderByFieldModel) {
				if ($orderByFieldModel->getFieldDataType() == Vtiger_Field_Model::REFERENCE_TYPE || $orderByFieldModel->getFieldDataType() == Vtiger_Field_Model::OWNER_TYPE) {
					$queryGenerator->addWhereField($orderBy);
				}
			}
		}

		/**
		 *  For Documents if we select any document folder and mass deleted it should delete documents related to that 
		 *  particular folder only
		 */
		if($moduleName == 'Documents'){
			$folderValue = $request->get('folder_value');
			if(!empty($folderValue)){
				 $queryGenerator->addCondition($request->get('folder_id'),$folderValue,'e');
			}
		}

		$accessiblePresenceValue = array(0,2);
		foreach($fieldInstances as $field) {
			// Check added as querygenerator is not checking this for admin users
			$presence = $field->get('presence');
			if(in_array($presence, $accessiblePresenceValue) && $field->get('displaytype') != '6') {
				$fields[] = $field->getName();
			}
		}
		$queryGenerator->setFields($fields);
		$query = $queryGenerator->getQuery();

		$additionalModules = $this->getAdditionalQueryModules();
		if(in_array($moduleName, $additionalModules)) {
			$query = $this->moduleInstance->getExportQuery($this->focus, $query);
		}

		$this->accessibleFields = $queryGenerator->getFields();

		switch($mode) {
			case 'ExportAllData'	:	if ($orderBy && $orderByFieldModel) {
											$query .= ' ORDER BY '.$queryGenerator->getOrderByColumn($orderBy).' '.$sortOrder;
										}
										break;

			case 'ExportCurrentPage' :	$pagingModel = new Vtiger_Paging_Model();
										$limit = $pagingModel->getPageLimit();

										$currentPage = $request->get('page');
										if(empty($currentPage)) $currentPage = 1;

										$currentPageStart = ($currentPage - 1) * $limit;
										if ($currentPageStart < 0) $currentPageStart = 0;

										if ($orderBy && $orderByFieldModel) {
											$query .= ' ORDER BY '.$queryGenerator->getOrderByColumn($orderBy).' '.$sortOrder;
										}
										$query .= ' LIMIT '.$currentPageStart.','.$limit;
										break;

			case 'ExportSelectedRecords' :	$idList = $this->getRecordsListFromRequest($request);
											$baseTable = $this->moduleInstance->get('basetable');
											$baseTableColumnId = $this->moduleInstance->get('basetableid');
											if(!empty($idList)) {
												if(!empty($baseTable) && !empty($baseTableColumnId)) {
													$idList = implode(',' , $idList);
													$query .= ' AND '.$baseTable.'.'.$baseTableColumnId.' IN ('.$idList.')';
												}
											} else {
												$query .= ' AND '.$baseTable.'.'.$baseTableColumnId.' NOT IN ('.implode(',',$request->get('excluded_ids')).')';
											}

											if ($orderBy && $orderByFieldModel) {
												$query .= ' ORDER BY '.$queryGenerator->getOrderByColumn($orderBy).' '.$sortOrder;
											}
											break;


			default :	break;
		}
		return $query;
	}

	/**
	 * Function returns the export type - This can be extended to support different file exports
	 * @param Vtiger_Request $request
	 * @return <String>
	 */
	function getExportContentType(Vtiger_Request $request) {
		$type = $request->get('export_type');
		/*if(empty($type)) {
			return 'text/csv';
		}*/
		return 'xlsx';
	}

	/**
	 * Function that create the exported file
	 * Author: Kelvin Thang
	 * Date: 2018-06-28
	 * @param Vtiger_Request $request
	 * @param <Array> $headers - output file header
	 * @param <Array> $entries - outfput file data
	 */
	function output($request, $headers, $entries) {
		$exportType = $this->getExportContentType($request);
		if($exportType == 'csv'){
			$this->outputToCSV($request, $headers, $entries);
		}else{
			$this->outputToExcel($request, $headers, $entries);
		}

	}

	/**
	 * Function that create the exported file csv
	 * Author: Kelvin Thang
	 * Date: 2018-06-28
	 * @param Vtiger_Request $request
	 * @param <Array> $headers - output file header
	 * @param <Array> $entries - outfput file data
	 */
	function outputToCSV($request, $headers, $entries) {
		$moduleName = $request->get('source_module');
		$fileName = str_replace(' ','_',decode_html(vtranslate($moduleName, $moduleName)));
		// for content disposition header comma should not be there in filename
		$fileName = str_replace(',', '_', $fileName);
		$exportType = $this->getExportContentType($request);
		//$exportType = 'xlsx';

		header("Content-Disposition:attachment;filename=$fileName.csv");
		header("Content-Type:$exportType;charset=UTF-8");
		header("Expires: Mon, 31 Dec 2000 00:00:00 GMT" );
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT" );
		header("Cache-Control: post-check=0, pre-check=0", false );

		$header = implode("\", \"", $headers);
		$header = "\"" .$header;
		$header .= "\"\r\n";
		echo $header;

		foreach($entries as $row) {
			foreach ($row as $key => $value) {
				/* To support double quotations in CSV format
				 * To review: http://creativyst.com/Doc/Articles/CSV/CSV01.htm#EmbedBRs
				 */
				$row[$key] = str_replace('"', '""', $value);
			}
			$line = implode("\",\"",$row);
			$line = "\"" .$line;
			$line .= "\"\r\n";
			echo $line;
		}
	}

	/**
	 * Function that create the exported file Excel
	 * Author: Kelvin Thang
	 * Date: 2018-06-28
	 * @param Vtiger_Request $request
	 * @param <Array> $headers - output file header
	 * @param <Array> $entries - outfput file data
	 */
	function outputToExcel($request, $headers, $entries) {
		require_once("libraries/PHPExcel/PHPExcel.php");

		$moduleName = $request->get('source_module');
		$fileName = str_replace(' ', '_', decode_html(vtranslate($moduleName, $moduleName)));
		$fileName = str_replace(',', '_', $fileName);

		$filePath = 'upload/' . $fileName . '.xlsx';
		if (file_exists($filePath)) unlink($filePath);

		$objPHPExcel = new PHPExcel();

		//--Set document properties
		//-- Modified By Kelvin Thang on 2020-05-07 set properties file
		$objPHPExcel->getProperties()->setCreator($GLOBALS['productName'])
			->setLastModifiedBy($GLOBALS['productName'])
			->setTitle($fileName)
			->setSubject("Office 2007 XLSX Test Document")
			->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
			->setKeywords("office 2007 openxml php")
			->setCategory("Test result file");

		//--Add export data
		$activeSheet = $objPHPExcel->setActiveSheetIndex(0);
		$headerStyles = array(
			'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => 'E1E0F7')),
			'font' => array('bold' => true)
		);

		foreach ($headers as $keyHeader => $valueHeader) {
			$valueHeader = preg_replace('/\\"/', '', $valueHeader);
			$activeSheet->setCellValueExplicitByColumnAndRow($keyHeader, 1, $valueHeader);
			$activeSheet->getStyleByColumnAndRow($keyHeader, 1)->applyFromArray($headerStyles);
		}

        // Added by Hieu Nguyen on 2021-01-22 to fix export format for number and currency fields
        $fieldInfos = getFieldsInfo($request->getModule());
        $inventoryCurrencyFields = ['subtotal', 'total', 'adjustment', 's_h_amount'];
        $shortValueFieldTypes = ['integer', 'double', 'time', 'date', 'datetime', 'currency', 'picklist', 'bool', 'percentage', 'productTax', 'owner', 'multiowner', 'personName'];
        // End Hieu Nguyen

		$rowNum = 2;
		foreach ($entries as $row) {
			$colNum = 0;
			foreach ($row as $key => $value) {
				$value = preg_replace('/\\"/', '', $value);

                // Modified by Hieu Nguyen on 2021-01-22 to fix export format for number and currency fields
                $columnName = PHPExcel_Cell::stringFromColumnIndex($colNum);
                $columnDimension = $activeSheet->getColumnDimension($columnName);
                $columnNumberFormat = $activeSheet->getStyle($columnName)->getNumberFormat();
                $dataType = PHPExcel_Cell_DataType::TYPE_STRING;
                $fieldInfo = $fieldInfos[$key];

                if (!empty($fieldInfo)) {
                    // Set number format for number and currency fields
                    if (in_array($fieldInfo['type'], ['integer', 'double', 'currency'])) {
                        $dataType = PHPExcel_Cell_DataType::TYPE_NUMERIC;
                        $value = Vtiger_Currency_UIType::convertToDBFormat($value, null, true);
                    }

                    // Set full width for columns that have short value
                    if (in_array($fieldInfo['type'], $shortValueFieldTypes)) {
                        $columnDimension->setAutoSize(true);
                        $columnNumberFormat->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                    }
                }
                else {
                    // Set number format for special number and currency fields in Inventory modules
                    if (in_array($key, $inventoryCurrencyFields)) {
                        $dataType = PHPExcel_Cell_DataType::TYPE_NUMERIC;
                        $value = Vtiger_Currency_UIType::convertToDBFormat($value, null, true);

                        // Set full width for these columns
                        $columnDimension->setAutoSize(true);
                        $columnNumberFormat->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                    }
                }

				$activeSheet->setCellValueExplicitByColumnAndRow($colNum, $rowNum, $value, $dataType);
                // End Hieu Nguyen

				$colNum++;
			}
			$rowNum++;
		}

		//-- Rename worksheet
		$objPHPExcel->getActiveSheet()->setTitle($fileName);
		$objPHPExcel->setActiveSheetIndex(0);
		ob_clean();
		ob_end_clean();
		//Redirect output to a client’s web browser (Excel5)
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $fileName . '.xlsx"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save("php://output");
	}

	private $picklistValues;
	private $fieldArray;
	private $fieldDataTypeCache = array();
	/**
	 * this function takes in an array of values for an user and sanitizes it for export
	 * @param array $arr - the array of values
	 */
	function sanitizeValues($arr){
		$db = PearDatabase::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$roleid = $currentUser->get('roleid');
		if(empty ($this->fieldArray)){
			$this->fieldArray = $this->moduleFieldInstances;
			foreach($this->fieldArray as $fieldName => $fieldObj){
				//In database we have same column name in two tables. - inventory modules only
				if($fieldObj->get('table') == 'vtiger_inventoryproductrel' && ($fieldName == 'discount_amount' || $fieldName == 'discount_percent')){
					$fieldName = 'item_'.$fieldName;
					$this->fieldArray[$fieldName] = $fieldObj;
				} else {
					$columnName = $fieldObj->get('column');
					$this->fieldArray[$columnName] = $fieldObj;
				}
			}
		}
		$moduleName = $this->moduleInstance->getName();
		foreach($arr as $fieldName=>&$value){
			if(isset($this->fieldArray[$fieldName])){
				$fieldInfo = $this->fieldArray[$fieldName];
			}else {
				unset($arr[$fieldName]);
				continue;
			}
			//Track if the value had quotes at beginning
			$beginsWithDoubleQuote = strpos($value, '"') === 0;
			$endsWithDoubleQuote = substr($value,-1) === '"'?1:0;

			$value = trim($value,"\"");
			$uitype = $fieldInfo->get('uitype');
			$fieldname = $fieldInfo->get('name');

			if(!$this->fieldDataTypeCache[$fieldName]) {
				$this->fieldDataTypeCache[$fieldName] = $fieldInfo->getFieldDataType();
			}
			$type = $this->fieldDataTypeCache[$fieldName];

			//Restore double quote now.
			if ($beginsWithDoubleQuote) $value = "\"{$value}";
			if($endsWithDoubleQuote) $value = "{$value}\"";
			if($fieldname != 'hdnTaxType' && ($uitype == 15 || $uitype == 16 || $uitype == 33)){
				if(empty($this->picklistValues[$fieldname])){
					$this->picklistValues[$fieldname] = $this->fieldArray[$fieldname]->getPicklistValues();
				}
				// If the value being exported is accessible to current user
				// or the picklist is multiselect type.
				if($uitype == 33 || $uitype == 16 || array_key_exists($value,$this->picklistValues[$fieldname])){
					// NOTE: multipicklist (uitype=33) values will be concatenated with |# delim
					
					// Added by Phuc on 2019.11.18 to load picklist/multipicklist value
					$value = explode('|##|', $value);

					foreach($value as $key => $item) {
						$item = trim($item);
						$value[$key] = vtranslate($item, $moduleName);
					}

					$value = implode(', ', $value);
					// Ended by Phuc
				} else {
					$value = '';
				}
			} elseif($uitype == 52 || $type == 'owner') {
                // Modified by Hieu Nguyen on 2019-05-24 to export custom owner field
                $value = Vtiger_Owner_UIType::getCurrentOwnersForDisplay($value, true);
                // End Hieu Nguyen
			}elseif($type == 'reference'){
				$value = trim($value);
				if(!empty($value)) {
                    // Added by Hieu Nguyen on 2021-05-12 to export the right related user name
                    $relatedModuleName = $fieldInfo->getReferenceList()[0];

                    if ($relatedModuleName == 'Users') {
                        $value = Vtiger_Owner_UIType::getCurrentOwnersForDisplay($value, true);
                        continue;
                    }
                    // End Hieu Nguyen

					$parent_module = getSalesEntityType($value);
					$displayValueArray = getEntityName($parent_module, $value);
					if(!empty($displayValueArray)){
						foreach($displayValueArray as $k=>$v){
							$displayValue = $v;
						}
					}
					if(!empty($parent_module) && !empty($displayValue)){
						$value = $displayValue; // Updated by Phuc on 2019.11.18
					}else{
						$value = "";
					}
				} else {
					$value = '';
				}
			} elseif($uitype == 72 || $uitype == 71) {
				//--Modified by Kelvin Thang on 2020-02-08 fixed display value for field
				$value = Vtiger_Currency_UIType::transformDisplayValue($value, null, true);
			} elseif($uitype == 7 && $fieldInfo->get('typeofdata') == 'N~O' || $uitype == 9 || in_array($type, ['double', 'integer']) ){ //--Modified by Kelvin Thang on 2020-02-08 fixed display value for field
				$value = Vtiger_Currency_UIType::transformDisplayValue($value);
			} elseif($type == 'date') {
				if ($value && $value != '0000-00-00') {
					$value = DateTimeField::convertToUserFormat($value);
				}
			}elseif($type == 'datetime') {
				if ($moduleName == 'Calendar' && in_array($fieldName, array('date_start', 'due_date'))) {
					$timeField = 'time_start';
					if ($fieldName === 'due_date') {
						$timeField = 'time_end';
					}
					$value = $value.' '.$arr[$timeField];
				}
				if (trim($value) && $value != '0000-00-00 00:00:00') {
					$value = Vtiger_Datetime_UIType::getDisplayDateTimeValue($value);
				}
			}
			// Updated by Phuc for checkbox type
			elseif ($uitype == 56) {
				if (empty($value) || !$value) {
					$value = vtranslate('LBL_NO');
				}
				else {
					$value = vtranslate('LBL_YES');
				}
			}
			// Ended by Phuc
			
			if($moduleName == 'Documents' && $fieldname == 'description'){
				$value = strip_tags($value);
				$value = str_replace('&nbsp;','',$value);
				array_push($new_arr,$value);
			}

            // Added by Hieu Nguyen on 2020-12-02 to translate salutation
            if ($fieldName == 'salutation') {
                $value = vtranslate($value);
            }
            // End Hieu Nguyen
		}
		return $arr;
	}

	public function moduleFieldInstances($moduleName) {
		return $this->moduleInstance->getFields();
	}
}