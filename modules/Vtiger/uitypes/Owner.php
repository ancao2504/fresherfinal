<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_Owner_UIType extends Vtiger_Base_UIType {

    // Implemented by Hieu Nguyen on 2019-05-22
    static function getOwnerIdsHash($ownerId) {
        if (empty($ownerId)) return;

        // Owned by user
        if (self::getOwnerType($ownerId) === 'User') {
            return Vtiger_CustomOwnerField_Helper::generateOwnerIdsHash([$ownerId]);
        }
        // Owned by group
        else {
            // Owned by a normal group
            if (!Vtiger_CustomOwnerField_Helper::isCustomGroup($ownerId)) {
                return Vtiger_CustomOwnerField_Helper::generateOwnerIdsHash([$ownerId]);
            }
            // Owned by a custom group
            else {
                return Vtiger_CustomOwnerField_Helper::getGroupMemberIdsHash($ownerId);
            }
        }
    }

    // Implemented by Hieu Nguyen on 2019-05-22
    static function getCurrentOwners($ownerId, $withUniqueInfo = true) {
        if (empty($ownerId)) return;
        static $cache = [];
        $cacheKey = $ownerId .'-'. (int)$withUniqueInfo;
        if (!empty($cache[$cacheKey])) return $cache[$cacheKey];
        $owners = [];

        // Modified by Phu Vo on 2019.06.20 => Process in case ownerId received come from select2 value directly
        if (
            strpos($ownerId, Vtiger_CustomOwnerField_Helper::USER_ID_PREFIX) !== false // It has User
            || strpos($ownerId, Vtiger_CustomOwnerField_Helper::GROUP_ID_PREFIX) !== false // It has Group
        ) {
            $owners = self::getSelectedOwnersFromOwnersString($ownerId, $withUniqueInfo); // [CustomOwnerField][tMNQPUJD] Modified by Phu Vo on 2019.11.22 to handle unique info
            $cache[$cacheKey] = $owners;
            return $owners;
        }
        // End Phu Vo

        // Owned by user
        if (self::getOwnerType($ownerId) === 'User') {
            $owners = [Vtiger_CustomOwnerField_Helper::getOwnerUser($ownerId, '', $withUniqueInfo)];
        }
        // Owned by group
        else {
            // Owned by a normal group
            if (!Vtiger_CustomOwnerField_Helper::isCustomGroup($ownerId)) {
                $owners = [Vtiger_CustomOwnerField_Helper::getOwnerGroup($ownerId)];
            }
            // Owned by a custom group
            else {
                global $adb;

                $userNameConcatSql = Vtiger_CustomOwnerField_Helper::getUserNameConcatForSql('u');
                $sql = "SELECT u.id, TRIM({$userNameConcatSql}) AS name, u.email1 AS email, 'User' As type
                    FROM vtiger_users AS u
                    INNER JOIN vtiger_users2group AS ug ON (ug.userid = u.id)
                    WHERE ug.groupid = '{$ownerId}'
                    UNION ALL
                    SELECT g.groupid AS id, g.groupname AS name, '', 'Group' AS type
                    FROM vtiger_groups AS g
                    INNER JOIN vtiger_group2grouprel AS gg ON (gg.containsgroupid = g.groupid)
                    WHERE gg.groupid = '{$ownerId}'";
                $result = $adb->pquery($sql, []);

                while ($row = $adb->fetchByAssoc($result)) {
                    $row = decodeUTF8($row);

                    if ($row['type'] == 'Group') {
                        $owners[] = Vtiger_CustomOwnerField_Helper::getOwnerGroup($row['id'], $row['name']);
                    }
                    else {
                        $userLabel = $row['name'];

                        if ($withUniqueInfo) {
                            $userLabel = Vtiger_CustomOwnerField_Helper::generateOwnerUserName($row['name'], $row['email']);
                        }

                        $owners[] = Vtiger_CustomOwnerField_Helper::getOwnerUser($row['id'], $userLabel);
                    }
                }
            }
        }

        $cache[$cacheKey] = $owners;
        return $owners;
    }

    // Implemented by Hieu Nguyen on 2019-05-24
    static function getCurrentOwnersForDisplay($ownerId, $withUniqueInfo = true, $forListView = false) {
        $owners = self::getCurrentOwners($ownerId, $withUniqueInfo);

        if ($forListView && function_exists('renderCurrentOwnersForListView')) {
            return renderCurrentOwnersForListView($owners);
        }

        $displayOwners = [];

        foreach ($owners as $owner) {
            $displayOwners[] = $owner['text'];
        }

        return join(', ', $displayOwners);
    }

    // Implemented by Hieu Nguyen on 2019-05-31
    static function getSelectedOwnersForSearchView($selectedIds) {
        return self::getSelectedOwnersFromOwnersString($selectedIds);
    }

    // Implemented by Hieu Nguyen on 2019-05-31
    static function getSelectedOwnersFromOwnersString($ownersString, $withUniqueInfo = true) { // [CustomOwnerField][tMNQPUJD] Modified by Phu Vo on 2019.11.22 to handle unique info
        // [CustomOwnerField][tMNQPUJD] Modified by Phu Vo on 2019.11.22 to remove group label from string
        $ownersString = trim(str_replace('Custom Group:', '', $ownersString));
        // End Phu Vo

        $ownerIds = explode(',', $ownersString);
        $owners = [];

        foreach ($ownerIds as $ownerId) {
            if (empty($ownerId)) continue;

            // This is a user
            if (strpos($ownerId, Vtiger_CustomOwnerField_Helper::USER_ID_PREFIX) !== false) {
                $ownerId = str_replace(Vtiger_CustomOwnerField_Helper::USER_ID_PREFIX, '', $ownerId);
                $owners[] = Vtiger_CustomOwnerField_Helper::getOwnerUser($ownerId, '', $withUniqueInfo); // [CustomOwnerField][tMNQPUJD] Modified by Phu Vo on 2019.11.22 to handle unique info
            }
            // This is a group
            else {
                $ownerId = str_replace(Vtiger_CustomOwnerField_Helper::GROUP_ID_PREFIX, '', $ownerId);
                $owners[] = Vtiger_CustomOwnerField_Helper::getOwnerGroup($ownerId, '', $withUniqueInfo); // [CustomOwnerField][tMNQPUJD] Modified by Phu Vo on 2019.11.22 to handle unique info
            }
        }

        return $owners;
    }

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return <String> - Template Name
	 */
	public function getTemplateName() {
		return 'uitypes/Owner.tpl';
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param <Object> $value
	 * @return <Object>
	 */
	public function getDisplayValue($value) {
        // Modified by Hieu Nguyen on 2019-05-22
		/*if (self::getOwnerType($value) === 'User') {
			$userModel = Users_Record_Model::getCleanInstance('Users');
			$userModel->set('id', $value);
			$detailViewUrl = $userModel->getDetailViewUrl();
            $currentUser = Users_Record_Model::getCurrentUserModel();
            if(!$currentUser->isAdminUser()){
                return getOwnerName($value);
            }
		} else {
            $currentUser = Users_Record_Model::getCurrentUserModel();
            if(!$currentUser->isAdminUser()){
                return getOwnerName($value);
            }
            $recordModel = new Settings_Groups_Record_Model();
            $recordModel->set('groupid',$value);
			$detailViewUrl = $recordModel->getDetailViewUrl();
		}
		return "<a href=" .$detailViewUrl. ">" .getOwnerName($value). "</a>";*/

        $fieldModel = $this->get('field');

        // Display quick edit template in detail view for assign_user_id field
        if ($fieldModel->get('name') == 'assigned_user_id' && $_REQUEST['view'] != 'MergeRecord') { // Do not render quick edit template in Merge Records modal
            $viewer = new Vtiger_Viewer();
            $viewer->assign('FIELD_NAME', 'assigned_user_id');
            $viewer->assign('FIELD_VALUE', $value);
            $quickEditTemplate = $viewer->fetch('modules/Vtiger/tpls/CustomOwnerFieldQuickEdit.tpl');
            $displayValue = self::getCurrentOwnersForDisplay($value, false);

            return $quickEditTemplate . $displayValue;
        }
        
        return self::getCurrentOwnersForDisplay($value, false);
        // End Hieu Nguyen
	}

	/**
	 * Function to get Display value for RelatedList
	 * @param <String> $value
	 * @return <String>
	 */
	public function getRelatedListDisplayValue($value) {
		return $value;
	}

	/**
	 * Function to know owner is either User or Group
	 * @param <Integer> userId/GroupId
	 * @return <String> User/Group
	 */
	public static function getOwnerType($id) {
		$db = PearDatabase::getInstance();

		$result = $db->pquery('SELECT 1 FROM vtiger_users WHERE id = ?', array($id));
		if ($db->num_rows($result) > 0) {
			return 'User';
		}
		return 'Group';
	}
    
    public function getListSearchTemplateName() {
        return '../../modules/Vtiger/tpls/CustomOwnerFieldSearchView.tpl';
    }

    /**
     * Method return owner filter template path use for widget home
     * @author Phu Vo on (Added: 2019.06.20, Updated: 2019.06.25)
     */
    public static function getDashboardFilterTemplateName() {
        return 'modules/Vtiger/tpls/CustomOwnerFieldDashboardFilter.tpl';
    }

    /**
     * Method return owners as request string
     * @author Phu Vo (Added: 2019.11.28)
     */
    public static function getCurrentOwnersAsFilterParam($ownerId) {
        $result = '';
        $owners = self::getCurrentOwners($ownerId, false);

        foreach ($owners as $index => $owner) {
            if ($index > 0) $result .= ', ';
            $result .= $owner['id'];
        }

        return $result;
    }
}