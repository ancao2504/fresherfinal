<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_Multipicklist_UIType extends Vtiger_Base_UIType {

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return <String> - Template Name
	 */
	public function getTemplateName() {
		return 'uitypes/MultiPicklist.tpl';
	}

    // Implemented by Hieu Nguyen on 2020-05-21 to decode string values into array values
    public static function decodeValues($stringValues = '') {
        if (empty($stringValues)) return [];    // Prevent to return array with 1 empty element
        return explode(' |##| ', $stringValues);
    }

    // Implemented by Hieu Nguyen on 2020-05-21 to encode array values into string values
    public static function encodeValues($arrayValues = []) {
        return implode(' |##| ', $arrayValues);
    }

    // Implemented by Hieu Nguyen on 2020-05-21 to remove specific values from a string values
    public static function removeValues($stringValues = '', $valuesToRemove = []) {
        $arrayValues = self::decodeValues($stringValues);
        $remainingValues = array_diff($arrayValues, $valuesToRemove);

        return self::encodeValues($remainingValues);
    }

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param <Object> $value
	 * @return <Object>
	 */
	public function getDisplayValue($value) {

		$moduleName = $this->get('field')->getModuleName();
		$value = explode(' |##| ', $value);
		foreach ($value as $key => $val) {
			$value[$key] = vtranslate($val, $moduleName);
		}

		return join(', ', array_filter($value));
	}
    
    public function getDBInsertValue($value) {
		if(is_array($value)){
            $value = implode(' |##| ', $value);
        }
        return $value;
	}
    
    
    public function getListSearchTemplateName() {
        return 'uitypes/MultiSelectFieldSearchView.tpl';
    }
}