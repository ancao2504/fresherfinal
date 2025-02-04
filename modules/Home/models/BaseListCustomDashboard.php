<?php

/**
 * BaseListCustomDashboard
 * Author: Phu Vo
 * Date: 2020.08.26
 */

abstract class Home_BaseListCustomDashboard_Model extends Home_BaseCustomDashboard_Model {
    abstract public function getWidgetHeaders($params);

    public function getFieldDisplayValue($value, $fieldName, $moduleName) {
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $fieldModel = Vtiger_Field_Model::getInstance($fieldName, $moduleModel);
        $uitypeModel = Vtiger_Base_UIType::getInstanceFromField($fieldModel);
        $fieldDataType = $fieldModel->getFieldDataType();
        $displayValue = $uitypeModel->getDisplayValue($value);

        if ($fieldDataType == 'picklist') {
            $backgroundColor = Settings_Picklist_Module_Model::getPicklistColorByValue($fieldName, $value);
            if (!empty($backgroundColor)) {
                $textColor = Settings_Picklist_Module_Model::getTextColor($backgroundColor);
                $displayValue = "<span class=\"picklist-color\" style=\"background-color: {$backgroundColor};color: {$textColor}\">{$displayValue}</span>";
            }
            else {
                $displayValue = "<span>{$displayValue}</span>";
            }
        };

        return $displayValue;
    }
}