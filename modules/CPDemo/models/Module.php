<?php

class CPDemo_Module_Model extends Vtiger_Module_Model {

    public function getModuleBasicLinks() {
        $basicLinks = parent::getModuleBasicLinks();

        for ($i = 0; $i < count($basicLinks); $i++) {
            // Hide import button
            if ($basicLinks[$i]['linklabel'] == 'LBL_IMPORT') {
                unset($basicLinks[$i]);
            }
        }

        if (Users_Privileges_Model::isPermitted($this->getName(), 'CreateView')) {
            // Show additional button
            $basicLinks[] = array(
                'linktype' => 'BASIC',
                'linklabel' => 'LBL_DEMO_HEADER_BASIC_BUTTON',
                'linkurl' => 'index.php?module=CPDemo&view=DemoView',
                'linkicon' => 'fa-rocket'
            );
        }

        return $basicLinks;
    }


public function getSettingLinks() {
    $settingsLinks = parent::getSettingLinks();
    $currentUserModel = Users_Record_Model::getCurrentUserModel();

    for($i = 0; $i < count($settingsLinks); $i++) {
        // Hide import button
        if($settingsLinks[$i]['linklabel'] == 'LBL_EDIT_WORKFLOWS') {
            unset($settingsLinks[$i]);
        }
    }

    if($currentUserModel->isAdminUser()) {
        // Show additional button
        $settingsLinks[] = array(
            'linktype' => 'LISTVIEWSETTING',
            'linklabel' => 'LBL_DEMO_HEADER_SETTING_BUTTON',
            'linkurl' => 'index.php?module=CPDemo&view=Config&parent=Settings',
            'linkicon' => 'fa-rocket'
        );
    }

    return $settingsLinks;
}

}
