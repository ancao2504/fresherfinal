<?php
class Settings_CRM_SaveUserNotificationsConfig_Action extends Settings_CRM_Basic_Action {
 function checkPermission(VTiger_request $request) {
        return true;
 }
 function validateRequest(VTiger_Request $request) {
        $request->validateWriteAccess();
 }
 function process(VTiger_Request $request) {
    global $current_user;
    $config = $request->get('config');
    if(empty($config)) {
    return;
    }

    Users_Preferences_Model::savePreferences($current_user->id, 'notification_config', $config);

    // Respond
    $result = array('success' => true);
    $response = new VTiger_Response();
    $response->setResult($result);
    $response->emit();
 }
}