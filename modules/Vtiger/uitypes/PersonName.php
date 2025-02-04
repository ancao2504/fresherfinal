<?php
/*
    Calss Vtiger_PersonName_UIType
    Author: Hieu Nguyen
    Date: 2020-03-04
    Purpose: to render person name field
*/

class Vtiger_PersonName_UIType extends Vtiger_Base_UIType {

	public function getTemplateName() {
        global $fullNameConfig;
        $fieldModel = $this->get('field');
        $fieldName = $fieldModel->getName();

        // First name only
        if ($fieldName == 'firstname') {
            global $adb;
            $moduleModel = $fieldModel->get('module');

            // In some cases the module model is not assigned in the field modal so we have to get it from block modal instead
            if (empty($moduleModel)) {
                $moduleModel = $fieldModel->get('block')->module;
            }
            
            $sql = "SELECT editview_presence FROM vtiger_field WHERE fieldname = ? AND tabid = ?";
            $lastNamePresence = $adb->getOne($sql, ['lastname', $moduleModel->getId()]);

            // If last name is hidden, add salutation into first name
            if ($lastNamePresence == '1') {
                return 'uitypes/Salutation.tpl';
            }
        }

        // First name display first
        if ($fieldName == 'firstname' && $fullNameConfig['full_name_order'][0] == 'firstname') {
            return 'uitypes/Salutation.tpl';    // Add salutation into first name
        }
        
        // Last name display first
        if ($fieldName == 'lastname' && $fullNameConfig['full_name_order'][0] == 'lastname') {
            return 'uitypes/Salutation.tpl';    // Add salutation into last name
        }
        
        return parent::getTemplateName();
	}

	public function getDetailViewTemplateName() {
        return parent::getDetailViewTemplateName(); // Currently does not support displaying salutation in DetailView
	}
}