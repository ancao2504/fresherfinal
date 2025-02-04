<?php
/*
*	Fields.php
*	Author: Phuc Lu
*	Date: 2019.11.14
*   Purpose: handle leadstatus picklist
*/

class Leads_Field_Model extends Vtiger_Field_Model {

	public function getPicklistValues() {
		$fieldName = $this->getName();
		$values = parent::getPicklistValues();

		// Get request
		$request = new Vtiger_Request($_REQUEST, $_REQUEST);
		$view = $request->get('view');
		$removedConvertedStatusView = [
			'MassActionAjax',
			'QuickCreateAjax'
		];

		if ($fieldName === "leadstatus" && in_array($view, $removedConvertedStatusView)) {
            unset($values['Converted']);
		}

		return $values;
	}

	// Added by Phuc on 2019.11.25 to remove converted value for field leadstatus info in Detail and List view
	public function getFieldInfo(){
		$fieldName = $this->getName();		
		$info = parent::getFieldInfo();

		// Get request
		$request = new Vtiger_Request($_REQUEST, $_REQUEST);
		$view = $request->get('view');
		$module = $request->get('module');
		$removedConvertedStatusView = [
			'Detail'
		];

		if ($fieldName === "leadstatus" && in_array($view, $removedConvertedStatusView) && $module == 'Leads') {
            unset($info['picklistvalues']['Converted']);
		}

		return $info;
	}
	// Ended by Phuc

	/**
	 * Added by Phu Vo on 2020.02.19
	 * @return mixed 
	 */
	function isEditEnabled() {
		// Disable lead converted block fields edit functionality
		if (in_array($this->getName(), $this->getLeadConvertedFieldNames())) return false;

		return parent::isEditEnabled();
	}

	/**
	 * Added by Phu Vo on 2020.02.20
	 * @param mixed $value 
	 * @param bool $record 
	 * @param bool $recordInstance 
	 * @return void 
	 */
	public function getDisplayValue($value, $record=false, $recordInstance = false) {
		if (in_array($this->getName(), $this->getLeadConvertedFieldNames())) {
			return $this->getLeadConvertedFieldDisplayValue($value);
		}

		return parent::getDisplayValue($value, $record, $recordInstance);
	}

	/**
	 * Function to retrieve display value in edit view
	 * @param <String> $value - value which need to be converted to display value
	 * @return <String> - converted display value
	 */
	public function getEditViewDisplayValue($value) {
		if (in_array($this->getName(), $this->getLeadConvertedFieldNames())) {
			return Vtiger_Functions::getCRMRecordLabel($value);
		}

		return parent::getEditViewDisplayValue($value);
	}

	/**
	 * Added by Phu Vo on 2020.02.20 to get lead converted fields use in other logic
	 * @return string[] 
	 */
	private function getLeadConvertedFieldNames() {
		return [
			'account_converted_id',
			'contact_converted_id',
			'potential_converted_id',
		];
	}

	/**
	 * Added by Phu vo on 2020.02.20 to process display value for lead converted fields
	 * @param mixed $value 
	 * @return string 
	 */
	private function getLeadConvertedFieldDisplayValue($value) {
		if (!empty($value)) {
			switch ($this->getName()) {
				case 'account_converted_id': {
					$detailLink = getRecordDetailUrl($value, 'Accounts');
					$entityLabel = Vtiger_Functions::getCRMRecordLabel($value);
					return "<a href='{$detailLink}'>{$entityLabel}</a>";
				}
				case 'contact_converted_id': {
					$detailLink = getRecordDetailUrl($value, 'Contacts');
					$entityLabel = Vtiger_Functions::getCRMRecordLabel($value);
					return "<a href='{$detailLink}'>{$entityLabel}</a>";
				}
				case 'potential_converted_id': {
					$detailLink = getRecordDetailUrl($value, 'Potentials');
					$entityLabel = Vtiger_Functions::getCRMRecordLabel($value);
					return "<a href='{$detailLink}'>{$entityLabel}</a>";
				}
			}
		}

		return '';
	}
}