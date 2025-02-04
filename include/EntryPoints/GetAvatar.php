<?php

/**
 * Name: GetAvatar.php
 * Author: Phu Vo
 * Date: 2021.03.18
 * Description: Allow to get record, user avatar using recor id
 */

class GetAvatar extends Vtiger_EntryPoint {

    function process (Vtiger_Request $request) {
        $recordId = $request->get('record');
        $module = $request->get('module');
        
        // Simple validate input
        if (empty($recordId) || empty($module)) return;
        
        try {
            // TODO: Retrieve without using record model to improve performance
            $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $module);
    
            if (empty($recordModel) || empty($recordModel->getId())) return;

            $imageDetails = $recordModel->getImageDetails();
            $imageInfo = $imageDetails[0]; // Simple get first element by now
            $imagePath = $imageInfo['path'] . '_' . $imageInfo['orgname'];
        }
        catch (Exception $ex) {
            // Leave empty for now
        }
        
        if (empty($imageInfo['path']) || !file_exists($imagePath)) {
            $imagePath = 'resources/images/default-user-avatar.png';
        }

        $pathInfos = pathinfo($imagePath);
        
        ob_clean();
        flush();
        
        header('content-type: image/' . $pathInfos['extension']);
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Content-Length: ' . filesize($imagePath));

        readfile($imagePath);

        exit;
    }
}