<?php

class CPDemo_DetailView_Model extends Vtiger_DetailView_Model {

    public function getDetailViewLinks($linkParams) {
        $linkModelList = parent::getDetailViewLinks($linkParams);
        $currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        $moduleModel = $this->getModule();

        for($i = 0; $i < count($linkModelList['DETAILVIEWBASIC']); $i++) {
            // Modify a basic button
            if($linkModelList['DETAILVIEWBASIC'][$i]->linklabel == 'LBL_EDIT') {
                $linkModelList['DETAILVIEWBASIC'][$i]->linkurl = 'javascript:alert("Modified Basic Button!");';
            }
        }

        if($currentUserModel->hasModulePermission($moduleModel->getId())) {
            // Show additional basic button
            $button = array(
                'linktype' => 'DETAILVIEWBASIC',
                'linklabel' => 'LBL_DEMO_DETAILVIEW_BASIC_BUTTON',
                'linkurl' => 'javascript:alert("Hello World!");',
            );

            $linkModelList['DETAILVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($button);
        }

        for ($i = 0; $i < count($linkModeList['DETAILVIEW']); $i++) {
            // Hide an advanced button
            if ($linkModeList['DETAILVIEW'][$i]->linklabel == 'LBL_DUPLICATE') {
                unset($linkModeList['DETAILVIEW'][$i]);
            }
        
            // Modify an advanced button
            if ($linkModeList['DETAILVIEW'][$i]->linklabel == 'LBL_DELETE') {
                $linkModeList['DETAILVIEW'][$i]->linkurl = "javascript:alert('Modified Advanced Button!');";
            }
        }
        
        if ($currentUserModel->hasModuleActionPermission($moduleModel->getId(), 'EditView')) {
            // Show additional advanced button
            $button = array(
                'linktype' => 'DETAILVIEW',
                'linklabel' => 'LBL_DEMO_DETAILVIEW_ADVANCED_BUTTON',
                'linkurl' => "javascript:alert('Hello World!');"
            );
        
            $linkModeList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($button);
        }
        

        return $linkModelList;
    }
}
?>
