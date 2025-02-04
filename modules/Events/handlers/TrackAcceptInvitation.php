<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Events_TrackAcceptInvitation_Handler {

    // Modified by Hieu Nguyen to handle accepting invitation from both users and contacts
	function acceptInvitation($data) {
		$eventId = $data['event_id'];
		$inviteeId = $data['invitee_id'];
		$inviteeType = $data['invitee_type'];

        try {
            $eventRecordModel = Events_Record_Model::getInstanceById($eventId, 'Events');
        } 
        catch (Exception $ex) {
            die('Error: can not access or the related activity is deleted!');
        }
        
        if (!Events_Invitation_Helper::isInvitee($inviteeId, $inviteeType, $eventId)) {
            die('Something wrong or this link is expired!');
        }

        if (!Events_Invitation_Helper::isInvitationAccepted($inviteeId, $inviteeType, $eventId)) {
            Events_Invitation_Helper::updateInvitationStatus($inviteeId, $inviteeType, $eventId, 'Accepted');
        }

		echo 'Invitation status has been updated - Thank you!';
	}

}

?>