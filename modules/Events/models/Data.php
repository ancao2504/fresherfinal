<?php

/*
*	Data.php
*	Author: Phuc Lu
*	Date: 2019.11.27
*   Purpose: handle record for Events
*/

class Events_Data_Model {

    // Get invitees by id
    static function getInvitees($eventId, $status = array()) {
        global $adb;
        $invitees = [];

        if (count($status)) {
            $status = implode("','", $status);
            $status = " AND status IN ('{$status}')";
        }
        else {
            $status = '';
        }

        $sql = "SELECT inviteeid, invitee_type, status
            FROM vtiger_invitees 
            WHERE activityid = ? {$status}";        
        $result = $adb->pquery($sql, [$eventId]);

        while ($rs = $adb->fetchByAssoc($result)) {
            $invitees[] = $rs;
        }

        return $invitees;
    }
}