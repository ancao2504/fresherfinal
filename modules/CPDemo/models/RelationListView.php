<?php

class CPDemo_RelationListView_Model extends Vtiger_RelationListView_Model {

    public function getLinks() {
        $parentModel = $this->getParentRecordModel();
        $relationModel = $this->getRelationModel();
        $relatedModuleName = $relationModel->getRelationModuleModel()->getName();
        $headerLinks = parent::getLinks();

        if ($relatedModuleName == 'Contacts') {
            unset($headerLinks[0]); // Hide button select
            unset($headerLinks[1]); // Hide button create
            $headerLinks = []; // Remove all buttons
        }

        if (Users_Privileges_Model::isPermitted('SMSNotifier', 'CreateView')) {
            // Show additional button
            $newLink = [
                'linktype' => 'LISTVIEWBASIC',
                'linklabel' => vtranslate('LBL_DEMO_RELATED_LIST_BASIC_BUTTON', 'Contacts'),
                'linkurl' => 'javascript:alert("Hello World!");',
                'linkicon' => ''
            ];

            $headerLinks['LISTVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($newLink);
        }

        return $headerLinks;
    }
}
?>
