<?php

/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_LayoutEditor_Block_Action extends Settings_Vtiger_Index_Action {
    
    public function __construct() {
        $this->view = 'DetailView'; // Added by Hieu Nguyen on 2019-03-01
        $this->exposeMethod('copyLayoutFromEditView');  // Added by Hieu Nguyen on 2018-08-10
        $this->exposeMethod('save');
        $this->exposeMethod('updateSequenceNumber');
        $this->exposeMethod('delete');
    }

    // Added by Hieu Nguyen on 2018-08-10
    public function copyLayoutFromEditView(Vtiger_Request $request) {
        $moduleName = $request->get('sourceModule');
        $sourceModule = Vtiger_Module::getInstance($moduleName);
        $moduleId = $sourceModule->getId();
        
        $db = PearDatabase::getInstance();

        // Delete all custom blocks in detail view
        $query = "SELECT blocklabel FROM vtiger_blocks WHERE tabid = ? AND iscustom = 1";
        $result = $db->pquery($query, [$moduleId]);

        while ($row = $db->fetchByAssoc($result)) {
            $query = "DELETE FROM vtiger_blocks WHERE tabid = ? AND blocklabel = ?";
            $db->pquery($query, [$moduleId, $row['blocklabel']]);

            // Remove deleted detail view block from register file
            Vtiger_BlockAndField_Helper::removeBlockFromRegisterFile($moduleName, $row['blocklabel'], $this->view);
        }

        // Copy all custom blocks from edit view to detail view
        $query = "SELECT * FROM vtiger_editview_blocks WHERE tabid = ? AND iscustom = 1";
        $result = $db->pquery($query, [$moduleId]);

        while ($row = $db->fetchByAssoc($result)) {
            $query = "INSERT INTO vtiger_blocks(blockid, tabid, blocklabel, sequence, show_title, visible, create_view, edit_view, detail_view, display_status, iscustom)
                VALUES(
                    {$db->getUniqueID('vtiger_blocks')}, {$row['tabid']}, '{$row['blocklabel']}', {$row['sequence']}, {$row['show_title']}, {$row['visible']}, 
                    {$row['create_view']}, {$row['edit_view']}, {$row['detail_view']}, {$row['display_status']}, {$row['iscustom']}
                )";

            $db->pquery($query);

            // Sync new detail view block into register file
            Vtiger_BlockAndField_Helper::syncBlockToRegisterFile($sourceModule, $row['blocklabel'], $this->view);
        }

        // Copy all default blocks settings from edit view to detail view (matching by block label)
        $query = "UPDATE vtiger_blocks AS dvb
            INNER JOIN vtiger_editview_blocks AS evb ON (evb.tabid = dvb.tabid AND evb.iscustom = 0 AND evb.blocklabel = dvb.blocklabel)
            SET dvb.sequence = evb.sequence, dvb.show_title = evb.show_title, dvb.visible = evb.visible, dvb.create_view = evb.create_view, 
                dvb.edit_view = evb.edit_view, dvb.detail_view = evb.detail_view, dvb.display_status = evb.display_status
            WHERE dvb.tabid = ? AND dvb.iscustom = 0";
        $db->pquery($query, [$moduleId]);
        
        // Set all fields in detail view as hidden
        $query = "UPDATE vtiger_field SET presence = 1 WHERE tabid = ?";
        $db->pquery($query, [$moduleId]);

        // Copy all field layout from edit view to detail view
        $query = "UPDATE vtiger_field SET block = editview_block, sequence = editview_sequence, presence = editview_presence WHERE tabid = ?";
        $db->pquery($query, [$moduleId]);

        // Set the right block for fields in detail view (matching by block label)
        $query = "UPDATE vtiger_field AS f 
            INNER JOIN vtiger_editview_blocks AS evb ON (evb.blockid = f.editview_block AND evb.tabid = f.tabid)
            INNER JOIN vtiger_blocks AS dvb ON (dvb.blocklabel = evb.blocklabel AND dvb.tabid = f.tabid)
            SET f.block = dvb.blockid WHERE f.tabid = ?";
        $db->pquery($query, [$moduleId]);

        // Sync changed attributes of detail view blocks and fields into register file
        $registerFileContents = Vtiger_BlockAndField_Helper::readRegisterFiles($moduleName);

        $query = "SELECT blocklabel, sequence, show_title, visible, display_status FROM vtiger_blocks WHERE tabid = ?";
        $result = $db->pquery($query, [$moduleId]);

        while ($row = $db->fetchByAssoc($result)) {
            $blockAttributesFromDb = $row;
            $blockAttributesFromRegisterFile = $registerFileContents['detailViewBlocks'][$row['blocklabel']];
            $changedAttributes = array_diff_assoc($blockAttributesFromDb, $blockAttributesFromRegisterFile);

            if (!empty($changedAttributes)) {
                Vtiger_BlockAndField_Helper::saveBlockAttributesToRegisterFile($moduleName, $row['blocklabel'], $changedAttributes, $this->view);
            }
        }

        $query = "SELECT f.fieldname, f.sequence, f.presence, dvb.blocklabel AS detailview_block_name
            FROM vtiger_field AS f
            INNER JOIN vtiger_blocks as dvb ON (dvb.blockid = f.block)
            WHERE f.tabid = ?";
        $result = $db->pquery($query, [$moduleId]);

        while ($row = $db->fetchByAssoc($result)) {
            $fieldAttributesFromDb = $row;
            $fieldAttributeFromRegisterFile = $registerFileContents['fields'][$row['fieldname']];
            $changedAttributes = array_diff_assoc($fieldAttributesFromDb, $fieldAttributeFromRegisterFile);

            if (!empty($changedAttributes)) {
                Vtiger_BlockAndField_Helper::saveFieldAttributesToRegisterFile($moduleName, $row['fieldname'], $changedAttributes);
            }
        }

        Vtiger_AdminAudit_Helper::saveLog('LayoutEditor', 'Copy Layout From EditView');

        $response = new Vtiger_Response();
        $response->setResult(array('success' => 1));
        $response->emit();
    }
    // End Hieu Nguyen

    public function save(Vtiger_Request $request) {
        $blockId = $request->get('blockid');
        $sourceModule = $request->get('sourceModule');
        $modueInstance = Vtiger_Module_Model::getInstance($sourceModule);

        if(!empty($blockId)) {
            $blockInstance = Settings_LayoutEditor_Block_Model::getInstance($blockId);
            $oldBlockInstance = clone $blockInstance;   // Added by Hieu Nguyen on 2021-08-02 for tracking

            // Modified by Hieu Nguyen on 2018-08-01 to support updating multiple properties
            $isDuplicate = false;
            
            if (isset($_POST['display_status'])) {
                $blockInstance->set('display_status', $request->get('display_status'));
            }
            
            if (isset($_POST['label'])) {
                $blockInstance->set('label', $request->get('label'));
                $isDuplicate = Vtiger_Block_Model::checkDuplicate(trim($request->get('label')), $modueInstance->getId(), $blockId);
            }
            // End Hieu Nguyen
        } else {
            $blockInstance = new Settings_LayoutEditor_Block_Model();
            $blockInstance->set('label', $request->get('label'));
			$blockInstance->set('iscustom', '1');
             //Indicates block id after which you need to add the new block
            $beforeBlockId = $request->get('beforeBlockId');
            if(!empty($beforeBlockId)) {
                $beforeBlockInstance = Vtiger_Block_Model::getInstance($beforeBlockId);
				$beforeBlockSequence = $beforeBlockInstance->get('sequence');
				$newBlockSequence = ($beforeBlockSequence+1);
				//To give sequence one more than prev block 
                $blockInstance->set('sequence', $newBlockSequence);
				//push all other block down so that we can keep new block there
				Vtiger_Block_Model::pushDown($beforeBlockSequence, $modueInstance->getId());

                // Added by Hieu Nguyen on 2021-07-01 to save sequence of pushed down blocks into register file
                $blockSequenceList = Vtiger_Block_Model::getAllBlockSequenceList($modueInstance->getId());

                foreach ($blockSequenceList as $id => $sequence) {  // Do not create variable named 'blockId' here to avoid error!
                    if ($sequence > $newBlockSequence) {  // Sequence > New block sequence: it is the pushed down block
                        $pushedDownBlockInstance = Vtiger_Block_Model::getInstance($id);
                        Vtiger_BlockAndField_Helper::saveBlockAttributesToRegisterFile($sourceModule, $pushedDownBlockInstance->get('label'), ['sequence' => $sequence], $this->view);
                    }
                }
                // End Hieu Nguyen
            }
			$isDuplicate = Vtiger_Block_Model::checkDuplicate(trim($request->get('label')), $modueInstance->getId());
        }

		$response = new Vtiger_Response();
		if (!$isDuplicate) {
			try{
				$id = $blockInstance->save($modueInstance);
				$responseInfo = array('id'=>$id,'label'=>$blockInstance->get('label'),'isCustom'=>$blockInstance->isCustomized(), 'beforeBlockId'=>$beforeBlockId, 'isAddCustomFieldEnabled'=>$blockInstance->isAddCustomFieldEnabled());
				if(empty($blockId)) {
					//if mode is create add all blocks sequence so that client will place the new block correctly
					$responseInfo['sequenceList'] = Vtiger_Block_Model::getAllBlockSequenceList($modueInstance->getId());
                }
                
                // Added by Hieu Nguyen on 2021-06-14 to track for label changes
                require_once('include/utils/LangUtils.php');
                $mode = empty($blockId) ? 'create' : 'edit';
                $labelKey = trim($request->get('label'));
                $labelDisplayEn = trim($request->get('labelDisplayEn'));
                $labelDisplayVn = trim($request->get('labelDisplayVn'));

                if ($mode == 'create' || $request->get('labelDisplayEnChanged') == '1') {
                    $languageStrings = [$labelKey => $labelDisplayEn];
                    LangUtils::writeModStrings($languageStrings, [], $sourceModule, 'en_us');
                }

                if ($mode == 'create' || $request->get('labelDisplayVnChanged') == '1') {
                    $languageStrings = [$labelKey => $labelDisplayVn];
                    LangUtils::writeModStrings($languageStrings, [], $sourceModule, 'vn_vn');
                }

                global $current_user;
                $responseInfo['labelDisplay'] = ($current_user->language == 'vn_vn') ? $labelDisplayVn : $labelDisplayEn;
                // End Hieu Nguyen

                // Modified by Hieu Nguyen on 2021-08-02
                $updatedView = $_REQUEST['layouteditor_tab'] == 'editViewTab' ? 'EditView' : 'DetailView';

                if ($mode == 'create') {
                    Vtiger_BlockAndField_Helper::syncBlockToRegisterFile($modueInstance, $labelKey, $this->view);
                    Vtiger_AdminAudit_Helper::saveLog('LayoutEditor', "Create Block {$labelKey} In {$updatedView}", $blockInstance);
                }
                else {
                    Vtiger_AdminAudit_Helper::saveLog('LayoutEditor', "Update Block {$labelKey} In {$updatedView}", ['old_block' => $oldBlockInstance, 'new_block' => $blockInstance]);
                }
                // End Hieu Nguyen

				$response->setResult($responseInfo);
			} catch(Exception $e) {
				$response->setError($e->getCode(),$e->getMessage());
			}
		} else {
			$response->setError('502', vtranslate('LBL_DUPLICATES_EXIST', $request->getModule(false)));
		}
        $response->emit();
    }
    
    // Modified by Hieu Nguyen on 2021-07-01 to save changes into register file
    public function updateSequenceNumber(Vtiger_Request $request) {
        $response = new Vtiger_Response();

        try {
            $moduleName = $request->get('selectedModule');
            $moduleId = getTabId($moduleName);
            $newSequenceList = $request->get('sequence');
            $curSequenceList = Vtiger_Block_Model::getAllBlockSequenceList($moduleId);

            Vtiger_Block_Model::updateSequenceNumber($newSequenceList, $moduleName);
            $response->setResult(['success' => true]);

            // Save changes to register file
            foreach ($newSequenceList as $blockId => $newSequence) {
                if ($newSequence != $curSequenceList[$blockId]) {
                    $blockInstance = Vtiger_Block_Model::getInstance($blockId);
                    Vtiger_BlockAndField_Helper::saveBlockAttributesToRegisterFile($moduleName, $blockInstance->get('label'), ['sequence' => $newSequence], $this->view);
                }
            }

            // Save audit log
            $updatedView = $_REQUEST['layouteditor_tab'] == 'editViewTab' ? 'EditView' : 'DetailView';
            Vtiger_AdminAudit_Helper::saveLog('LayoutEditor', "Move Block In {$updatedView}", ['old_sequence' => $curSequenceList, 'new_sequence' => $newSequenceList]);
        }
        catch(Exception $ex) {
            $response->setError($ex->getCode(), $ex->getMessage());
        }

        $response->emit();
    }
    
    
    public function delete(Vtiger_Request $request) {
        $response = new Vtiger_Response();
        $blockId = $request->get('blockid');

        // Modified by Hieu Nguyen on 2021-07-29 to translate error message
        $moduleName = $request->getModule(false);
        $blockInstance = Settings_LayoutEditor_Block_Model::getInstance($blockId);

        if (!$blockInstance->isDeletable()) {   // Check if this block can be deleted by customer, dev or R&D
            
            $developerTeam = checkDeveloperTeam();
            $message = vtranslate('LBL_DELETE_SYSTEM_BLOCK_ERROR_MSG', $moduleName);

            if ($developerTeam == 'DEV') {
                $message = vtranslate('LBL_DELETE_SYSTEM_BLOCK_ERROR_MSG_FOR_DEV', $moduleName);
            }

            $response->setError('502', $message);
            $response->emit();
            return;
        }

        if (Vtiger_Block_Model::checkFieldsExists($blockId)) {
            $response->setError('502', vtranslate('LBL_DELETE_CUSTOM_BLOCK_CONTAINS_FIELDS_ERROR_MSG', $moduleName));
            $response->emit();
            return;
        }
        // End Hieu Nguyen

        try{
            $sourceModule = $blockInstance->get('module')->name;
            $blockLabel = $blockInstance->get('label');
            $blockInstance->delete();   // Modified by Hieu Nguyen on 2021-07-01 to prevent all belonging fields to be deleted with the block

            // Removed the logic by Hieu Nguyen on 2018-07-31 to prevent error
            //Settings_LayoutEditor_Module_Model::removeLabelFromLangFile($sourceModule, $blockLabel);
            // End Hieu Nguyen

            // Added by Hieu Nguyen on 2019-03-01
            Vtiger_BlockAndField_Helper::removeBlockFromRegisterFile($sourceModule, $blockLabel, $this->view);
            // End Hieu Nguyen

            // Added by Hieu Nguyen on 2021-08-02 to save audit log
            $updatedView = $_REQUEST['layouteditor_tab'] == 'editViewTab' ? 'EditView' : 'DetailView';
            Vtiger_AdminAudit_Helper::saveLog('LayoutEditor', "Delete Block {$blockLabel} in {$updatedView}", $blockInstance);
            // End Hieu Nguyen

            $response->setResult(array('success'=>true));
        }catch(Exception $e) {
            $response->setError($e->getCode(),$e->getMessage());
        }
        $response->emit();
    }
    
    public function validateRequest(Vtiger_Request $request) {
        $request->validateWriteAccess();
    }

}