<?php
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
*
 ********************************************************************************/

/**
 * To get the lists of vtiger_users id who shared their calendar with specified user
 * @param $sharedid -- The shared user id :: Type integer
 * @returns $shared_ids -- a comma seperated vtiger_users id  :: Type string
 */
function getSharedCalendarId($sharedid)
{
	global $adb;
	$query = "SELECT * from vtiger_sharedcalendar where sharedid=?";
	$result = $adb->pquery($query, array($sharedid));
	if($adb->num_rows($result)!=0)
	{
		for($j=0;$j<$adb->num_rows($result);$j++)
			$userid[] = $adb->query_result($result,$j,'userid');
		$shared_ids = implode (",",$userid);
	}
	return $shared_ids;
}

/**
 * To get hour,minute and format
 * @param $starttime -- The date&time :: Type string
 * @param $endtime -- The date&time :: Type string
 * @param $format -- The format :: Type string
 * @returns $timearr :: Type Array
*/
function getaddEventPopupTime($starttime,$endtime,$format)
{
	$timearr = Array();
	list($sthr,$stmin) = explode(":",$starttime);
	list($edhr,$edmin)  = explode(":",$endtime);
	if($format == 'am/pm' || $format == '12')
	{
		$hr = $sthr+0;
		$timearr['startfmt'] = ($hr >= 12) ? "pm" : "am";
		if($hr == 0) $hr = 12;
		$timearr['starthour'] = twoDigit(($hr>12)?($hr-12):$hr);
		$timearr['startmin']  = $stmin;

		$edhr = $edhr+0;
		$timearr['endfmt'] = ($edhr >= 12) ? "pm" : "am";
		if($edhr == 0) $edhr = 12;
		$timearr['endhour'] = twoDigit(($edhr>12)?($edhr-12):$edhr);
		$timearr['endmin']    = $edmin;
		return $timearr;
	}
	if($format == '24')
	{
		$timearr['starthour'] = twoDigit($sthr);
		$timearr['startmin']  = $stmin;
		$timearr['startfmt']  = '';
		$timearr['endhour']   = twoDigit($edhr);
		$timearr['endmin']    = $edmin;
		$timearr['endfmt']    = '';
		return $timearr;
	}
}

// Deleted function getAcceptInvitationUrl, addAcceptEventLink, getActivityDetails by Hieu Nguyen on 2019-11-29 as this logic is already handled in Events_Invitation_Helper

function twoDigit( $no ){
	if($no < 10 && strlen(trim($no)) < 2) return "0".$no;
	else return "".$no;
}

// Deleted function sendInvitation by Hieu Nguyen on 2019-11-29 as this logic is already handled by Events_Service_Model

// Imlemented by Phu Vo on 2019.04.03 to send invite notifiation
function sendInviteNotification($recordModel, $inviteeId, $language, $timezone) {
    require_once('include/utils/NotificationHelper.php');

    // Don't send notification when import
    if($recordModel->entity->isBulkSaveMode()) return; // Modified by Phu Vo on 2019.09.22 use entity isBulkSaveMode instead

    // Don't send notification when mass update
    if(strtoupper($_REQUEST['action']) === 'MASSSAVE') return;

    // Invite send notification goes here
    $id = $recordModel->getId();
    $moduleName = $recordModel->entity->moduleName;
    $status = $recordModel->get('eventstatus');

    if($status === 'Planned') {
        // Check user reference to send notification
        $userNotificationConfig = Users_Preferences_Model::loadPreferences($inviteeId, 'notification_config');
        
        if($userNotificationConfig != null && $userNotificationConfig->receive_assignment_notifications == 1) {                        
            // Added by Hieu Nguyen on 2021-03-29 to delete previous invitation of the same event to prevent duplicate invitation
            global $adb;
            $sql = "DELETE FROM vtiger_notifications WHERE receiver_id = ? AND related_record_id = ? AND extra_data LIKE '%\"action\":\"invite\"%\"accepted\":0%'";
            $adb->pquery($sql, [$inviteeId, $recordModel->getId()]);
            // End Hieu Nguyen
            
            $extraData = [
                'activity_type' => $recordModel->get('activitytype'),
                'action' => 'invite',
				'inviter' => $recordModel->get('modifiedby'),
				'accepted' => 0,
            ];

            $data = [
                'receiver_id' => $inviteeId,
                'type' => 'notification',
                'related_record_id' => $recordModel->get('id'),
                'related_record_name' => $recordModel->get('label'),
                'related_module_name' => $moduleName,
                'extra_data' => $extraData,
            ];

            $data['message'] = translateNotificationMessage($data, $language, $timezone);

            NotificationHelper::sendNotification($data);
        }
    }
}

// Implemented by Phu Vo on 2021.01.04
function acceptInviteNotification($inviteeId, $activityId) {
	global $adb;

	$query = "SELECT * FROM vtiger_notifications WHERE receiver_id = ? AND related_record_id = ? AND related_module_name IN ('Calendar', 'Events')";
	$queryParams = [$inviteeId, $activityId];

	$result = $adb->pquery($query, $queryParams);

	while ($row = $adb->fetchByAssoc($result)) {
		$row = decodeUTF8($row);
		$extraData = json_decode($row['extra_data'], true);

		if ($extraData['action'] != 'invite') continue;
		
		$notificationId = $row['id'];
		$extraData['accepted'] = 1;

		$sql = "UPDATE vtiger_notifications SET extra_data = ? WHERE id = ?";
		$params = [json_encode($extraData), $notificationId];
		$adb->pquery($sql, $params);
	}
}

// User Select Customization
/**
 * Function returns the id of the User selected by current user in the picklist of the ListView or Calendar view of Current User
 * return String -  Id of the user that the current user has selected
 */
function calendarview_getSelectedUserId() {
	global $current_user, $default_charset;
	$only_for_user = htmlspecialchars(strip_tags(vtlib_purifyForSql($_REQUEST['onlyforuser'])),ENT_QUOTES,$default_charset);
	if($only_for_user == '') $only_for_user = $current_user->id;
	return $only_for_user;
}

function calendarview_getSelectedUserFilterQuerySuffix() {
	global $current_user, $adb;
	$only_for_user = calendarview_getSelectedUserId();
	$qcondition = '';
	if(!empty($only_for_user)) {
		if($only_for_user != 'ALL') {
			// For logged in user include the group records also.
			if($only_for_user == $current_user->id) {
				$user_group_ids = fetchUserGroupids($current_user->id);
				// User does not belong to any group? Let us reset to non-existent group
				if(!empty($user_group_ids)) $user_group_ids .= ',';
				else $user_group_ids = '';
				$user_group_ids .= $current_user->id;
				$qcondition = " AND vtiger_crmentity.smownerid IN (" . $user_group_ids .")";
			} else {
				$qcondition = " AND vtiger_crmentity.smownerid = "  . $adb->sql_escape_string($only_for_user);
			}
		}
	}
	return $qcondition;
}

/*
 * Function to generate ICS file to send as attachment with email
 * invitation when a user is invited for an event
 * @params $record Event record
 * @return filename as event name
 */
function generateIcsAttachment($record, $suffix = '') {
	$fileName = str_replace(' ', '_', decode_html($record['subject']));
	
	// Add by Phu Vo on 2019.12.11 to add suffix for file name
	if (!empty($suffix)) $fileName .= '_' . $suffix;
	// Ended by Phu Vo

	// [Calendar] Added by Phu Vo to convert file name to ascii using ununicode and remove special chars
	$fileName = unUnicode($fileName);
	$fileName = preg_replace('/[^A-Za-z0-9\-\_]/', '', $fileName);
	// End Phu Vo

    $assignedUserId = $record['user_id'];
    $userModel = Users_Record_Model::getInstanceById($assignedUserId, 'Users');
    $firstName = $userModel->entity->column_fields['first_name'];
    $lastName = $userModel->entity->column_fields['last_name'];
    $email = $userModel->entity->column_fields['email1'];
    $fp = fopen('test/upload/'.$fileName.'.ics', "w");
    fwrite($fp, "BEGIN:VCALENDAR\nVERSION:2.0\nBEGIN:VEVENT\n");
    fwrite($fp, "ORGANIZER;CN=". decodeUTF8($firstName) ." ". decodeUTF8($lastName) .":MAILTO:".$email."\n");   // Modified by Hieu Nguyen on 2019-11-26 to display correct unicode string
    
    // Modified by Hieu Nguyen on 2019-12-11 to display the right date and time on Gmail Agenda
    fwrite($fp, "DTSTART:". date('Ymd\THis', strtotime($record['st_date_time'])) ."\n");
    fwrite($fp, "DTEND:". date('Ymd\THis', strtotime($record['end_date_time'])) ."\n");
    fwrite($fp, "DTSTAMP:". date('Ymd\THis') ."\n");
    // End Hieu Nguyen

    fwrite($fp, "DESCRIPTION:".$record['description']."\nLOCATION:".$record['location']."\n");
    fwrite($fp, "STATUS:CONFIRMED\nSUMMARY:".$record['subject']."\nEND:VEVENT\nEND:VCALENDAR");
    fclose($fp);
    
    return 'test/upload/'.$fileName.'.ics';
}


// Added by Phuc on 2019.11.28 to replace detail link for content, Refactor by Phu Vo on 2019.12.11
function replaceRecordDetailLink($type, $recordId, $content) {
    global $site_URL;

    if ($type == 'Users') {
        $recordDetailViewLink = "{$site_URL}/index.php?module=Calendar&view=Detail&record={$recordId}";
        $detailLink = "<br /><p><a href='{$recordDetailViewLink}' target='_blank'>" . vtranslate('LBL_RECORD_DETAIL_TEXT', 'Vtiger', 'vn_vn') . '</a></p>';
        $content = str_replace('$detail_link_text_vn$', $detailLink, $content);
        $detailLink = "<br /><p><a href='{$recordDetailViewLink}' target='_blank'>" . vtranslate('LBL_RECORD_DETAIL_TEXT', 'Vtiger', 'en_us') . '</a></p>';
        $content = str_replace('$detail_link_text_en$', $detailLink, $content);
    }
    else {
        $content = str_replace('$detail_link_text_vn$', '', $content);
        $content = str_replace('$detail_link_text_en$', '', $content);
    }

    return $content;
}
// Ended by Phuc

// Added by Hieu Nguyen on 2022-01-18
function getActivityRelatedCustomerAccountId($activityId) {
	global $adb;
	$accountId = $adb->getOne("SELECT related_account FROM vtiger_activity WHERE activityid = ?", [$activityId]);
	return $accountId;
}

// Added by Phu Vo on 2022.01.14
function getActivityRelatedCustomerId($recordModel, $customerType) {
	if ($customerType == 'Accounts') {
		return $recordModel->get('related_account');
	}
	else if ($customerType == 'Contacts') {
		return $recordModel->get('contact_id');
	}
	else if ($customerType == 'Leads') {
		return $recordModel->get('related_lead');
	}
	// Added by Hieu Nguyen on 2022-05-11 to get related Target id from Calendar activity
	else if ($customerType == 'CPTarget') {
		return $recordModel->get('parent_id');
	}
	// End Hieu Nguyen

	return null;
}

// Added by Phu Vo on 2022.01.14
function setActivityRelatedCustomerId(&$recordModel, $customerId, $customerType) {
	if ($customerType == 'Accounts') {
		$recordModel->set('related_account', $customerId);
	}
	else if ($customerType == 'Contacts') {
		$recordModel->set('contact_id', $customerId);
	}
	else if ($customerType == 'Leads') {
		$recordModel->set('related_lead', $customerId);
	}
	// Added by Hieu Nguyen on 2022-05-11 to link Calendar activity for related Target
	else if ($customerType == 'CPTarget') {
		$recordModel->set('parent_id', $customerId);
	}
	// End Hieu Nguyen
}

?>