<?php
class Settings_CRM_SaveSystemNotificationsConfig_Action extends Settings_CRM_Basic_Action {
 function validateRequest(CRM_Request $request) {

     $request->validateWriteAccess();
 }
 function process(CRM_Request $request) {
    $config = $request->get('config');
    Settings_CRM_Config_Model::saveConfig('notification_config', $config);
    // Respond
    $responce = new CRM_Response();
    $responce->setResult(['success' => true]);
    $responce->emit();
 }
}