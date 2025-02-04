<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_Integer_UIType extends Vtiger_Base_UIType {

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return <String> - Template Name
	 */
	public function getTemplateName() {
		return 'uitypes/Number.tpl';
	}

    /**
     * Added by Kelvin Thang
     * Date: 2018-07-26
     * Function to get the Display Value, for the current field type with given DB Insert Value
     * @param <Object> $value
     * @return <Object>
     */
    public function getDisplayValue($value, $record=false, $recordInstance=false) {
        return formatNumberToUser($value);
    }

    /**
     * Added by Kelvin Thang
     * Date: 2018-07-26
     * Function to get the Value of the field in the format, the user provides it on Save
     * @param <Object> $value
     * @return <Object>
     */
    public function getRelatedListDisplayValue($value) {
        return $this->getDisplayValue($value);
    }

	/**
	 * Author: Kelvin Thang
	 * Date: 2020-01-02
	 * Function to get the display value in edit view
	 * @param <String> $value
	 * @return <String>
	 */
	public function getListSearchTemplateName() {
		return 'uitypes/NumberFieldSearchView.tpl';
	}

	/**
	 * Added: Kelvin Thang
	 * Function to get the Value of the field in the format, the user provides it on Save
	 * @param <Object> $value
	 * @return <Object>
	 */
	public function getUserRequestValue($value) {
		return $this->getDisplayValue($value);
	}

	/**
	 * Added: Kelvin Thang
	 * Function to get the DB Insert Value, for the current field type with given User Value
	 * @param <Object> $value
	 * @return <Object>
	 */
	public function getDBInsertValue($value) {
		$uiType = $this->get('field')->get('uitype');

		if ($uiType == 72) {
			return self::convertToDBFormat($value, null, true);
		}
		else {
			return self::convertToDBFormat($value, null, true);
		}
	}

	/**
	 * Added: Kelvin Thang
	 * Function converts User currency format to database format
	 * @param <Object> $value - Currency value
	 * @param <User Object> $user
	 * @param <Boolean> $skipConversion
	 */
	public static function convertToDBFormat($value, $user=null, $skipConversion=false) {
		return CurrencyField::convertToDBFormat($value, $user, $skipConversion);
	}
}