<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
ini_set("auto_detect_line_endings", true);

class Import_CSVReader_Reader extends Import_FileReader_Reader {

	public function arrayCombine($key, $value) { 
		$combine = array(); 
		$dup = array(); 
		for($i=0;$i<count($key);$i++) { 
			if(array_key_exists($key[$i], $combine)){ 
				if(!$dup[$key[$i]]) $dup[$key[$i]] = 1;
				$key[$i] = $key[$i]."(".++$dup[$key[$i]].")";
			} 
			$combine[$key[$i]] = $value[$i]; 
		} 
		return $combine; 
	}

	/**
	 * Modified By Kelvin Thang
	 * Date: 2018-06-28
	 * @param bool $hasHeader
	 * @return array
	 */
	public function getFirstRowData($hasHeader=true) {
		global $default_charset;

		$fileHandler = $this->getFileHandler();

		$headers = array();
		$firstRowData = array();
		$currentRow = 0;

        $delimiter = $this->request->get('delimiter');
        if(empty($delimiter)){
            $this->request->set('delimiter', ",");
            $delimiter = ",";
        }

        while($data = fgetcsv($fileHandler, 0, $delimiter, "\"", "\"")) {
			if($currentRow == 0 || ($currentRow == 1 && $hasHeader)) {
				if($hasHeader && $currentRow == 0) {
					foreach($data as $key => $value) {
						if($key == 0){
							$value = str_replace('"', "", strip_tags(html_entity_decode($value, ENT_QUOTES, 'UTF-8')));
							$value = substr($value,3);
						}

						$headers[$key] = trim($this->convertCharacterEncoding(strip_tags(decode_html($value)), $this->request->get('file_encoding'), $default_charset));

					}
				} else {
					foreach($data as $key => $value) {
						$firstRowData[$key] = trim($this->convertCharacterEncoding(strip_tags(decode_html($value)), $this->request->get('file_encoding'), $default_charset));
					}
					break;
				}
			}
			$currentRow++;
		}

		if($hasHeader) {
			$noOfHeaders = count($headers);
			$noOfFirstRowData = count($firstRowData);
			// Adjust first row data to get in sync with the number of headers
			if($noOfHeaders > $noOfFirstRowData) {
				$firstRowData = array_merge($firstRowData, array_fill($noOfFirstRowData, $noOfHeaders-$noOfFirstRowData, ''));
			} elseif($noOfHeaders < $noOfFirstRowData) {
				$firstRowData = array_slice($firstRowData, 0, count($headers), true);
			}
			$rowData = $this->arrayCombine($headers, $firstRowData);
		} else {
			$rowData = $firstRowData;
		}

		unset($fileHandler);

		//--BEGIN: Added by Kelvin Thang on 2020.02.03 to format Data Number Import To User
		if ($rowData) {
			$currencyFields = $this->moduleModel->getFieldsByType('currency');
			$rowData = $this->formatDataNumberImportToUser($rowData, $currencyFields);

			$integerFields = $this->moduleModel->getFieldsByType('integer');
			$rowData = $this->formatDataNumberImportToUser($rowData, $integerFields);

			$doubleFields = $this->moduleModel->getFieldsByType('double');
			$rowData = $this->formatDataNumberImportToUser($rowData, $doubleFields);
		}
		//--END: Added by Kelvin Thang on 2020.02.03 to format Data Number Import To User

		return $rowData;
	}

	public function read() {
		global $default_charset;

		$fileHandler = $this->getFileHandler();
		$status = $this->createTable();
		if(!$status) {
			return false;
		}

		$fieldMapping = $this->request->get('field_mapping');
		$delimiter    = $this->request->get('delimiter');
        if(empty($delimiter)){
            $this->request->set('delimiter', ",");
            $delimiter = ",";
        }
		$hasHeader    = $this->request->get('has_header');
		$fileEncoding = $this->request->get('file_encoding');
		
		// NOTE: Retaining row-read and insert as LOAD DATA command is being disabled by default.
		$i = -1;
		while($data = fgetcsv($fileHandler, 0, $delimiter)) {
			$i++;
			if($hasHeader && $i == 0) continue;
			$mappedData = array();
			$allValuesEmpty = true;
			foreach($fieldMapping as $fieldName => $index) {
				$fieldValue = $data[$index];
				$mappedData[$fieldName] = $fieldValue;
				if($fileEncoding != $default_charset) {
					$mappedData[$fieldName] = $this->convertCharacterEncoding($fieldValue, $fileEncoding, $default_charset);
                    //$mappedData[$fieldName] = trim($this->convertCharacterEncoding(strip_tags(decode_html($fieldValue)), $fileEncoding, $default_charset));
				}
				if(!empty($fieldValue)) $allValuesEmpty = false;
			}
			if($allValuesEmpty) continue;
			$fieldNames = array_keys($mappedData);
			$fieldValues = array_values($mappedData);
			$this->addRecordToDB($fieldNames, $fieldValues);
		}
		unset($fileHandler);
	}

	//-- Added by Kelvin Thang on 2020.02.03 to format Data Number Import To User
	public function formatDataNumberImportToUser($rowData, $fieldsModel) {
		global $current_user;
		$labelDisplay = $current_user->language == 'vn_vn' ? 'labelDisplayVn' : 'labelDisplayEn';

		foreach ($fieldsModel as $keyField => $fieldModel) {
			$labelHeaderDisplay = $fieldModel->$labelDisplay;

			if (array_key_exists($labelHeaderDisplay, $rowData) && $rowData[$labelHeaderDisplay] && $labelHeaderDisplay) {
				$rowData[$labelHeaderDisplay] = $fieldModel->getDisplayValue($rowData[$labelHeaderDisplay]);
			}
		}

		return $rowData;
	}
}
?>
