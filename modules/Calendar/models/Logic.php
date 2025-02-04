<?php

/*
    Calendar_Logic_Model
    Author: Hieu Nguyen
    Date: 2020-08-31
    Purpose: Provide utils function to handle logic related to Calendar
 */

class Calendar_Logic_Model {

    public static function isAtActivitiesRelatedList() {
        $result = false;

        if ($_REQUEST['relatedModule'] == 'Calendar' || $_REQUEST['action'] == 'RelatedRecordsAjax') {
            $result = true;
        }

        if ($_REQUEST['module'] != 'Calendar' && $_REQUEST['requestMode'] == 'summary') {
            $result = true;
        }

        return $result;
    }

    public static function isRelatedActivityBusy($recordId, $parentId) {
        global $current_user;
        $parentRecordOwnerIds = getRecordOwnerId($parentId, true);
        $relatedActivitiesDisplayConfig = Calendar_Data_Model::getDisplayConfigForActivitiesRelatedList();

        // Customer's owner can access all related activities when the config is enabled
        if ($parentRecordOwnerIds['main_owner_id'] == $current_user->id && $relatedActivitiesDisplayConfig['main_owner_full_access'] == '1') {
            return false;
        }

        // Task record has no status busy
        $activityData = getFullRecordData($recordId, 'Calendar');
        if ($activityData['activitytype'] == 'Task') return false;

        // User accepted activity invitation can access the record detail
        if (
            Events_Invitation_Helper::isInvitee($current_user->id, 'Users', $recordId) 
            && Events_Invitation_Helper::isInvitationAccepted($current_user->id, 'Users', $recordId)
        ) {
            return false;
        }

        $activityData['activityid'] = $recordId;
        $recordBusy = Calendar_Data_Model::isRecordBusy($activityData, 'SharedCalendar', $activityData['main_owner_id'], $current_user->id);

        return $recordBusy;
    }
}