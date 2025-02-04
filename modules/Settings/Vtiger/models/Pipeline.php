<?php

/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Vtiger_Pipeline_Model extends Vtiger_Base_Model {
    
    

    public static function getPipelineList() {
        $db = PearDatabase::getInstance();
        $query = 'SELECT * FROM a_pipeline ORDER BY created_at DESC';
        $result = $db->pquery($query,[]);
        // $pipelines = array();
        // if ($db->num_rows($result) > 0) {
        //     while ($row = $db->fetch_array($result)) {
        //         $pipeline = array(
        //             'id' => $row['id'],
        //             'module' => $row['module'], 
        //             'name' => $row['name'],
        //             'steps_count' => $row['steps_count'],
        //             'status' => $row['status'],
        //             'permissions' => $row['permissions'],
        //             'description' => $row['description'],
        //             'created_by' => $row['created_by'],
        //             'created_at' => $row['created_at']
        //         );
        //         $pipelines[] = $pipeline;
        //     }
        // }
        
        return $result ;
    }
    
    // public function save() {
    //     $db = PearDatabase::getInstance();
    //     $currentUser = Users_Record_Model::getCurrentUserModel();
    //     $currentDate = date('Y-m-d H:i:s');
    //     $checkQuery = 'SELECT 1 FROM '.self::tableName.' WHERE creatorid=?';
    //     $result = $db->pquery($checkQuery,array($currentUser->getId()));
    //     if($db->num_rows($result) > 0) {
    //         $query = 'UPDATE '.self::tableName.' SET announcement=?,time=? WHERE creatorid=?';
    //         $params = array($this->get('announcement'),$db->formatDate($currentDate, true),$currentUser->getId());
    //     }else{
    //         $query = 'INSERT INTO '.self::tableName.' VALUES(?,?,?,?)';
    //         $params = array($currentUser->getId(),$this->get('announcement'),'announcement',$db->formatDate($currentDate, true));
    //     }
    //     $db->pquery($query,$params);
    // }
    
    // public static function getInstanceByCreator(Users_Record_Model $user) {
    //     $db = PearDatabase::getInstance();
    //     $query = 'SELECT * FROM '.self::tableName.' WHERE creatorid=?';
    //     $result = $db->pquery($query,array($user->getId()));
    //     $instance = new self();
    //     if($db->num_rows($result) > 0) {
    //         $row = $db->query_result_rowdata($result,0);
    //         $instance->setData($row);
    //     }
    //     return $instance;
    // }
}