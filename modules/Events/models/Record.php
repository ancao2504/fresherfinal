<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/**
 * Events Record Model Class
 */
class Events_Record_Model extends Calendar_Record_Model {
    
    protected $inviteesDetails;
    
    /**
	 * Function to get the Edit View url for the record
	 * @return <String> - Record Edit View Url
	 */
	public function getEditViewUrl() {
		$module = $this->getModule();
		return 'index.php?module=Calendar&view='.$module->getEditViewName().'&record='.$this->getId();
	}

	/**
	 * Function to get the Delete Action url for the record
	 * @return <String> - Record Delete Action Url
	 */
	public function getDeleteUrl() {
		$module = $this->getModule();
		return 'index.php?module=Calendar&action='.$module->getDeleteActionName().'&record='.$this->getId();
	}

	/**
     * Funtion to get Duplicate Record Url
     * @return <String>
     */
    public function getDuplicateRecordUrl(){
        $module = $this->getModule();
		return 'index.php?module=Calendar&view='.$module->getEditViewName().'&record='.$this->getId().'&isDuplicate=true';

    }

    public function getRelatedToContactIdList() {
        $adb = PearDatabase::getInstance();
        $query = 'SELECT * from vtiger_cntactivityrel where activityid=?';
        $result = $adb->pquery($query, array($this->getId()));
        $num_rows = $adb->num_rows($result);

        $contactIdList = array();
        for($i=0; $i<$num_rows; $i++) {
            $row = $adb->fetchByAssoc($result, $i);
            $contactIdList[$i] = $row['contactid'];
        }
        return $contactIdList;
    }

    public function getRelatedContactInfo() {
        $contactIdList = $this->getRelatedToContactIdList();
        $relatedContactInfo = array();
        foreach($contactIdList as $contactId) {
            $relatedContactInfo[] = array('name' => decode_html(Vtiger_Util_Helper::toSafeHTML(Vtiger_Util_Helper::getRecordName($contactId))) ,'id' => $contactId);
        }
        return $relatedContactInfo;
     }
     
     public function getRelatedContactInfoFromIds($eventIds){
         $adb = PearDatabase::getInstance();
        $query = 'SELECT vtiger_cntactivityrel.activityid as id, vtiger_cntactivityrel.contactid, vtiger_contactdetails.email FROM vtiger_cntactivityrel INNER JOIN vtiger_contactdetails
                  ON vtiger_contactdetails.contactid = vtiger_cntactivityrel.contactid  WHERE activityid in ('. generateQuestionMarks($eventIds) .')';
        $result = $adb->pquery($query, array($eventIds));
        $num_rows = $adb->num_rows($result);

        $contactInfo = array();
        for($i=0; $i<$num_rows; $i++) {
            $row = $adb->fetchByAssoc($result, $i);
            $contactInfo[$row['id']][] = array('name' => Vtiger_Util_Helper::toSafeHTML(Vtiger_Util_Helper::getRecordName($row['contactid'])),
                                    'email' => $row['email'], 'id' => $row['contactid']);
        }
        return $contactInfo;
     }
     
    // Deleted functions getInviteesDetails, getInvities, updateInvitationStatus by Hieu Nguyen on 2019-11-29 as these logic is already handled by Events_Invitation_Helper

     public function getInviteUserMailData() {
            $adb = PearDatabase::getInstance();

            $return_id = $this->getId();
            $cont_qry = "select * from vtiger_cntactivityrel where activityid=?";
            $cont_res = $adb->pquery($cont_qry, array($return_id));
            $noofrows = $adb->num_rows($cont_res);
            $cont_id = array();
            if($noofrows > 0) {
                for($i=0; $i<$noofrows; $i++) {
                    $cont_id[] = $adb->query_result($cont_res,$i,"contactid");
                }
            }
            $cont_name = '';
            foreach($cont_id as $key=>$id) {
                if($id != '') {
                    $contact_name = Vtiger_Util_Helper::getRecordName($id);
                    $cont_name .= $contact_name .', ';
                }
            }

			$parentId = $this->get('parent_id');
			$parentName = '';
			if($parentId != '') {
				$parentName = Vtiger_Util_Helper::getRecordName($parentId);
			}
			
            $cont_name  = trim($cont_name,', ');
            $mail_data = Array();

            $mail_data['record_id'] = $this->getId(); // Added by Phuc on 2019.11.29 to get id of current record
            $mail_data['user_id'] = $this->get('assigned_user_id');
            $mail_data['subject'] = $this->get('subject');
            $moduleName = $this->getModuleName();
            $mail_data['status'] = (($moduleName=='Calendar')?($this->get('taskstatus')):($this->get('eventstatus')));
            $mail_data['activity_mode'] = (($moduleName=='Calendar')?('Task'):('Events'));
            $mail_data['taskpriority'] = $this->get('taskpriority');
            $mail_data['relatedto'] = $parentName;
            $mail_data['contact_name'] = $cont_name;
            $mail_data['description'] = $this->get('description');
            $mail_data['assign_type'] = $this->get('assigntype');
            $mail_data['group_name'] = getGroupName($this->get('assigned_user_id'));
            $mail_data['mode'] = $this->get('mode');
            //TODO : remove dependency on request;

            // Modified by Hieu Nguyen on 2019-11-25 to support sending email by cronjob
            $value = getaddEventPopupTime($this->get('time_start'), $this->get('time_end'), '24');
            $start_hour = $value['starthour'] .':'. $value['startmin'] .''. $value['startfmt'];

            if ($this->get('activitytype') != 'Task') { // [Calendar] Modified by Phu Vo on 2020.03.19 to support sending email by cronjob
                $end_hour = $value['endhour'] .':'. $value['endmin'] .''. $value['endfmt'];
            }

            $startDate = new DateTimeField($this->get('date_start') .' '. $start_hour);
            $endDate = new DateTimeField($this->get('due_date') .' '. $end_hour);
            // End Hieu Nguyen

            $mail_data['st_date_time'] = $startDate->getDBInsertDateTimeValue();
            $mail_data['end_date_time'] = $endDate->getDBInsertDateTimeValue();
            $mail_data['location']=$this->get('location');
            return $mail_data;
     }
}
