<?php

/**
 * PBXManager_ExternalReport_Helper
 * Author: Phu Vo
 * Date: 2019.12.27
 */

class PBXManager_ExternalReport_Helper {

    /**
     * Static method use to check if input user has permission to enter external report page
     * @param mixed|null $user 
     * @return bool 
     */
    static public function isUserHasPermission($user =  null) {
        if (empty($user)) $user = vglobal('current_user');

        // Alway allow admin
        if (is_admin($user)) return true;
        
        $callCenterConfig = Settings_Vtiger_Config_Model::loadConfig('callcenter_config');
        $externalReportAllowedRoles = $callCenterConfig->external_report_allowed_roles;

        // Check role permision
        if (in_array($user->roleid, $externalReportAllowedRoles)) return true;
        
        // Default false
        return false;
    }
}