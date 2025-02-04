<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_Reference_UIType extends Vtiger_Base_UIType {

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return <String> - Template Name
	 */
	public function getTemplateName() {
		return 'uitypes/Reference.tpl';
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param <Object> $value
	 * @return <Object>
	 */
	public function getReferenceModule($value) {
		$fieldModel = $this->get('field');
		$referenceModuleList = $fieldModel->getReferenceList();
		$referenceEntityType = getSalesEntityType($value);
		if(in_array($referenceEntityType, $referenceModuleList)) {
			return Vtiger_Module_Model::getInstance($referenceEntityType);
		} elseif (in_array('Users', $referenceModuleList)) {
			return Vtiger_Module_Model::getInstance('Users');
		}
		return null;
	}

	/**
	 * Function to get the display value in detail view
	 * @param <Integer> crmid of record
	 * @return <String>
	 */
	public function getDisplayValue($value) {
		$referenceModule = $this->getReferenceModule($value);
		if($referenceModule && !empty($value)) {
			$referenceModuleName = $referenceModule->get('name');
			if($referenceModuleName == 'Users') {
                // Modified by Hieu Nguyen on 2020-07-06 to get user full name according to full name config
				$userFullName = getUserFullName($value);
				return $userFullName;
                // End Hieu Nguyen
			} else {
				$fieldModel = $this->get('field');
				$entityNames = getEntityName($referenceModuleName, array($value));
				$linkValue = "<a href='index.php?module=$referenceModuleName&view=".$referenceModule->getDetailViewName()."&record=$value'
							title='".vtranslate($fieldModel->get('label'), $referenceModuleName).":". $entityNames[$value] ."' "
							. "data-original-title='".vtranslate($referenceModuleName, $referenceModuleName)."'>$entityNames[$value]</a>";
				return $linkValue;
			}
		}
		return '';
	}

	/**
	 * Function to get the display value in edit view
	 * @param reference record id
	 * @return link
	 */
	public function getEditViewDisplayValue($value) {
		$referenceModule = $this->getReferenceModule($value);
		if($referenceModule) {
			$referenceModuleName = $referenceModule->get('name');
			$entityNames = getEntityName($referenceModuleName, array($value));
			return $entityNames[$value];
		}
		return '';
	}

	public function getListSearchTemplateName() {
		$fieldModel = $this->get('field');

        // Modified by Hieu nguyen on 2019-10-21
		if ($fieldModel->get('uitype') == '52' || $fieldModel->get('uitype') == '77') {
			return '../../modules/Vtiger/tpls/CustomUserReferenceFieldSearchView.tpl';
		}
        // End Hieu Nguyen

		return parent::getListSearchTemplateName();
	}

}