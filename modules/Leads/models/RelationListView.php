<?php

/**
 * Lead Relation List View Model
 * Author: Phu Vo
 * Date: 2019.08.07
 */

class Leads_RelationListView_Model extends Vtiger_RelationListView_Model {

    public function getLinks() {
        $links = parent::getLinks();
        $relatedModel = $this->getRelatedModuleModel();

        // Hide all button for related module CPSocialArticleLog and CPSocialMessageLog. Add CPMauticContactHistory by Phuc on 2020.03.24
        $removeAllLinkModules = ['CPSocialArticleLog', 'CPSocialMessageLog', 'CPSocialFeedback', 'CPMauticContactHistory'];

        if (in_array($relatedModel->getName(), $removeAllLinkModules)) {
            return [];
        }

        // Added by Phu Vo on 2020.05.22 to remove all related list view button for module chat message log
        if ($relatedModel->getName() === 'CPChatMessageLog') return [];
        
        // Added by Phu Vo on 2020.05.23 to remove all related list view button for module event registration
        if ($relatedModel->getName() === 'CPEventRegistration') {
            require_once('modules/CPEventRegistration/helpers/Logic.php');
            $manualRegisterLink = CPEventRegistration_Logic_Helper::generateManualRegisterLink($this->getParentRecordModel());

            return [
                'LISTVIEWBASIC' => [$manualRegisterLink],
            ];
        }
        // End Phu Vo

        return $links;
    }
}