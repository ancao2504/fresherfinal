<?php

class CPDemo_ListView_Model extends Vtiger_ListView_Model {
    public function getAdvancedLinks() {
        $advancedLinks = parent::getAdvancedLinks();
        $moduleModel = $this->getModule();
    
        for ($i = 0; $i < count($advancedLinks); $i++) {
            // Hide a button
            if ($advancedLinks[$i]['linklabel'] == 'LBL_EXPORT') {
                unset($advancedLinks[$i]);
            }
        }
    
        if (Users_Privileges_Model::isPermitted($moduleModel->getName(), 'CreateView')) {
            // Show additional button
            $advancedLinks[] = array(
                'linktype' => 'LISTVIEW',
                'linklabel' => 'LBL_DEMO_LISTVIEW_ADVANCED_BUTTON',
                'linkurl' => 'javascript:alert("Hello World!");',
            );
        }
    
        return $advancedLinks;
    }
    public function getListViewMassActions($linkParams) {
        $massActions = parent::getListViewMassActions($linkParams);
        $moduleModel = $this->getModule();
    
        for ($i = 0; $i < count($massActions['LISTVIEWMASSACTION']); $i++) {
            // Hide a button
            if ($massActions['LISTVIEWMASSACTION'][$i]->linklabel == 'LBL_DELETE') {
                unset($massActions['LISTVIEWMASSACTION'][$i]);
            }
        }
    
        if (Users_Privileges_Model::isPermitted($moduleModel->getName(), 'CreateView')) {
            // Show additional button
            $button = array(
                'linktype' => 'LISTVIEWMASSACTION',
                'linklabel' => 'LBL_DEMO_LISTVIEW_MASS_ACTION_BUTTON',
                'linkurl' => 'javascript:alert("Hello World!");',
            );
    
            $massActions['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($button);
        }
    
        return $massActions;
    }
    
    
}
