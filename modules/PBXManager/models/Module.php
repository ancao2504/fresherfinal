<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class PBXManager_Module_Model extends Vtiger_Module_Model {

	/**
	 * Function to check whether the module is an entity type module or not
	 * @return <Boolean> true/false
	 */
	public function isQuickCreateSupported() {
		//PBXManager module is not enabled for quick create
		return false;
	}

	/**
	 * Overided to make editview=false for this module
	 */
	public function isPermitted($actionName) {
		if($actionName == 'EditView')
			return false;
		else
			return ($this->isActive() && Users_Privileges_Model::isPermitted($this->getName(), $actionName));
	}

	public function getModuleBasicLinks() {
		$basicLinks = parent::getModuleBasicLinks();
		foreach ($basicLinks as $key => $basicLink) {
			if (in_array($basicLink['linklabel'], array('LBL_ADD_RECORD', 'LBL_IMPORT'))) {
				unset($basicLinks[$key]);
			}
		}

        // Added by Phu Vo on 2019.04.12 Add report button
        global $current_user;
        $serverModel = PBXManager_Server_Model::getInstance();
        $connector = $serverModel->getConnector();

        if($connector != null) {
            $connectorName = $connector->getGatewayName();

            if($connector->hasExternalReport && PBXManager_ExternalReport_Helper::isUserHasPermission()) {
                $basicLinks[] = [
                    'linktype' => 'BASIC',
                    'linklabel' => 'LBL_REPORT',
                    'linkurl' => "index.php?module=PBXManager&view=ExternalReport",
                    'linkicon' => 'fa-bar-chart',
                ];
            }
        }

        // End Phu Vo

		return $basicLinks;
	}

	/**
	 * Function to get Settings links
	 * @return <Array>
	 */
	public function getSettingLinks(){
		if(!$this->isEntityModule()) {
			return array();
		}

		$settingsLinks = array();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		if($currentUser->isAdminUser()) {
			vimport('~~modules/com_vtiger_workflow/VTWorkflowUtils.php');

			$layoutEditorImagePath = Vtiger_Theme::getImagePath('LayoutEditor.gif');
			$editWorkflowsImagePath = Vtiger_Theme::getImagePath('EditWorkflows.png');

			if(VTWorkflowUtils::checkModuleWorkflow($this->getName())) {
				$settingsLinks[] = array(
						'linktype' => 'LISTVIEWSETTING',
						'linklabel' => 'LBL_EDIT_WORKFLOWS',
						'linkurl' => 'index.php?parent=Settings&module=Workflows&view=List&sourceModule='.$this->getName(),
						'linkicon' => $editWorkflowsImagePath
				);
			}

            // Modified by Hieu Nguyen on 2020-10-23
			$settingsLinks[] = array(
                'linktype' => 'LISTVIEWSETTINGS',
                'linklabel' => 'LBL_CONFIG_INTEGRATION_PARAMETERS',
                'linkurl' => 'index.php?parent=Settings&module=PBXManager&view=Index',
                'linkicon' => 'far fa-cogs'
			);
            // End Hieu Nguyen
		}
		return $settingsLinks;
	}

	/**
	 * Funxtion to identify if the module supports quick search or not
	 */
	public function isQuickSearchEnabled() {
		return true;    // Modified by Hieu Nguyen on 2020-09-08 to show search form in ListView header
	}

	public function isListViewNameFieldNavigationEnabled() {
		return false;
	}

	/**
	 * Function to check whether the module is an entity type module or not
	 * @return <Boolean> true/false
	 */
	function getUtilityActionsNames() {
		return array('Import', 'Export', 'Merge');
	}

	public function isWorkflowSupported() {
		return false;
	}

	function isStarredEnabled(){
		return false;
	}

	function isTagsEnabled() {
		return false;
	}
}
?>
