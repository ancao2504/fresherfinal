<?php

/**
 * Name: DownloadFile.php
 * Author: Phu Vo
 * Date: 2021.03.18
 * Description: Allow to get record, user attachment file using recor id
 */

require_once('include/Webservice/SalesAppApiHandler.php');

class DownloadFile extends Vtiger_EntryPoint {

    var $clientType = null;

    function checkPermission (Vtiger_Request $request) {
        $this->clientType = $request->get('client');

        if ($this->clientType == 'Mobile') {
            $this->_checkMobileToken($request);
        }
        else {
            $this->_handleException('Client type are not supported', 403);
        }
    }

    function process (Vtiger_Request $request) {
        global $adb;

        $this->checkPermission($request);

        $recordId = $request->get('record');
        $module = $request->get('module');
        
        // TODO: Handle exception
        if (empty($recordId) || empty($module)) return;
        
        if (!in_array($module, ['Documents', 'Emails', 'ModComments'])) return;
        
        try {
            
            if ($module == 'Emails') {
                $attachmentId = $request->get('attachment_id');
                $query = "SELECT * FROM vtiger_attachments WHERE attachmentsid = ?" ;
                $result = $adb->pquery($query, [$attachmentId]);

                if ($adb->num_rows($result) == 1) {
                    $row = $adb->fetchByAssoc($result, 0);
                    $fileType = $row['type'];
                    $name = $row['name'];
                    $filepath = $row['path'];
                    $name = decode_html($name);
                    $saved_filename = $attachmentId . '_' . $name;
                    $disk_file_size = filesize($filepath . $saved_filename);
                    $filesize = $disk_file_size + ($disk_file_size % 1024);
                    $fileContent = fread(fopen($filepath.$saved_filename, 'r'), $filesize);

                    header("Content-type: {$fileType}");
                    header('Pragma: public');
                    header('Cache-Control: private');
                    header('Content-Disposition: attachment; filename=$name');
                    header('Content-Description: PHP Generated Data');
                    
                    echo $fileContent;
                }
            }
            else {
                // TODO: Retrieve without using record model to improve performance
                $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $module);
        
                if (empty($recordModel) || empty($recordModel->getId())) return;
                
                //Download the file
                $recordModel->downloadFile();

                //Update the Download Count
                if (method_exists($recordModel, 'updateDownloadCount')) $recordModel->updateDownloadCount();
            }
            
        }
        catch (Exception $ex) {
            $this->_handleException($ex->getMessage(), $ex->getCode());
        }
    }

    protected function _checkMobileToken(Vtiger_Request $request) {
        $token = $request->get('token');
        SalesAppApiHandler::checkSession($token);
    }
    
    protected function _handleException($message, $code = 0) {
        if ($this->clientType == 'Mobile') {
            $data = ['success' => 0, 'message' => $message];
            SalesAppApiHandler::setResponse($code, $data);
        }
        else {
            if (!function_exists('http_response_code')) {
                header('HTTP/1.1 '. $code);
            }
            else {
                http_response_code($code);
            }

            echo $message;

            exit;
        }
    }
}