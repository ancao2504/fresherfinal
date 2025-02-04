<?php
/**
 * @author Tin Bui
 * @email tin.bui@onlinecrm.vn
 * @create date 2022.03.16
 * @desc Ticket view controller for modals
 */

class HelpDesk_ModalAjax_View extends Vtiger_View_Controller {

    function checkPermission() {
        return true;
    }

    function process(Vtiger_Request $request) {
        $mode = lcfirst($request->get('mode'));

        if (method_exists($this, $mode)) {
            $this->$mode($request);
        }
        else {
            throw new AppException(vtranslate('LBL_HANDLER_NOT_FOUND'));
        }
    }

    function openStatusModal(Vtiger_Request $request) {
        try {
            $ticketId = $request->get('record');
            
            if (!empty($ticketId) && isRecordExists($ticketId)) {
                $ticketRecord = HelpDesk_Record_Model::getInstanceById($ticketId);
                $statusStructure = HelpDesk_GeneralUtils_Helper::getUpdateStatusRecordStucture($ticketRecord);
                $currentUser = Users_Record_Model::getCurrentUserModel();

                $viewer = $this->getViewer($request);
                $viewer->assign('STATUS_STRUCTURE', $statusStructure);
                $viewer->assign('MODULE', $request->get('module'));
                $viewer->assign('RECORD_ID', $ticketId);
                $viewer->assign('RECORD', $ticketRecord);
                $viewer->assign('USER_MODEL', $currentUser);
                $viewer->display("modules/HelpDesk/tpls/Modals/StatusModal.tpl");
            }
        }
        catch (Exception $e) {
            return false;
        }
    }

    function openSLAModal(Vtiger_Request $request) {
        try {
            $ticketId = $request->get('record');
            
            if (!empty($ticketId) && isRecordExists($ticketId)) {
                $ticketRecord = HelpDesk_Record_Model::getInstanceById($ticketId);
                $structure = HelpDesk_GeneralUtils_Helper::getOverSLARecordStucture($ticketRecord);
                $currentUser = Users_Record_Model::getCurrentUserModel();

                $viewer = $this->getViewer($request);
                $viewer->assign('STRUCTURE', $structure);
                $viewer->assign('MODULE', $request->get('module'));
                $viewer->assign('RECORD_ID', $ticketId);
                $viewer->assign('USER_MODEL', $currentUser);
                $viewer->display('modules/HelpDesk/tpls/Modals/OverSLAModal.tpl');
            }
        }
        catch (Exception $e) {
            return false;
        }
    }
}
