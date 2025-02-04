<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Contacts_RelationListView_Model extends Vtiger_RelationListView_Model {

    // Added by Phu Vo on 2019.08.07 to delete all buttons for related module CPSocialArticleLog and CPSocialMessageLog
    public function getLinks() {
        $links = parent::getLinks();
        $relatedModel = $this->getRelatedModuleModel();

        // delete all button for related module CPSocialArticleLog and CPSocialMessageLog. Add CPMauticContactHistory by Phuc on 2020.03.24
        $removeAllLinkModules = ['CPSocialArticleLog', 'CPSocialMessageLog', 'CPSocialFeedback', 'CPMauticContactHistory'];

        if (in_array($relatedModel->getName(), $removeAllLinkModules)) {
            return [];
        }

        // Added by Phu Vo on 2020.05.22 to remove all related list view button for module chat message log
        if ($relatedModel->getName() === 'CPChatMessageLog') return [];

        // Added by Hieu Nguyen on 2022-02-15 to render button manual event registration
        if ($relatedModel->getName() === 'CPEventRegistration') {
            require_once('modules/CPEventRegistration/helpers/Logic.php');
            $manualRegisterLink = CPEventRegistration_Logic_Helper::generateManualRegisterLink($this->getParentRecordModel());

            return [
                'LISTVIEWBASIC' => [$manualRegisterLink],
            ];
        }
        // End Hieu Nguyen

        return $links;
    }
}