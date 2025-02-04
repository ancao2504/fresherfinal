<?php

/*
*	HelpDesksHandler.php
*	Author: Phuc Lu
*	Date: 2020.06.29
*/

class HelpDesksHandler extends VTEventHandler {

	function handleEvent($eventName, $entityData) {
		if ($entityData->getModuleName() != 'HelpDesk') return;

		if ($eventName === 'vtiger.entity.beforesave') {
			$this->setDefaultValues($entityData);
			$this->handleStatusTimeline($entityData);
		}

		if ($eventName === 'vtiger.entity.aftersave') {
			$this->saveStatusChangeLog($entityData);
			$this->setSLATime($entityData);
			$this->handleSendTicketReceivedEmail($entityData);
			$this->handleSendProcessedTicketEmail($entityData);
			$this->handleSendSurveyEmail($entityData);
			$this->handleSendAssignmentEmail($entityData);
		}
	}

	// Added by Tin Bui on 2022.03.15 - Set ticket default values
	function setDefaultValues(&$entityData) {
		if ($entityData->isNew() && empty($entityData->get('ticketstatus'))) {
			$entityData->set('ticketstatus', 'Open');
		}
		if ($entityData->isNew() && empty($entityData->get('helpdesk_survey_status'))) {
			$entityData->set('helpdesk_survey_status', 'not_yet_sent_mail');
		}
		if ($entityData->isNew() && empty($entityData->get('is_send_survey'))) {
			$entityData->set('is_send_survey', 1);
		}
	}

	// Added by Tin Bui on 2022.03.15 - Handle save time when update ticket status
	function handleStatusTimeline(&$entityData) {
		global $adb;
		$status = $entityData->get('ticketstatus');

		if ($entityData->isNew() || $status == $entityData->oldStatus) return;

		$currentDate = date('Y-m-d H:i:s');
		$isDatetimeFieldUpdated = false;

		// Just save firstime update status
		if ($status == 'Assgined' && empty($entityData->get('assignment_date'))) {
			$entityData->set('assignment_date', $adb->formatDate($currentDate, true));
			$isDatetimeFieldUpdated = true;
		}

		if ($status == 'In Progress' && empty($entityData->get('process_start_date'))) {
			$entityData->set('process_start_date', $adb->formatDate($currentDate, true));
			$isDatetimeFieldUpdated = true;
		}

		// Save everytime update status
		if ($status == 'Wait Close') {
			$entityData->set('process_end_date', $adb->formatDate($currentDate, true));
			$isDatetimeFieldUpdated = true;
		}

		if ($isDatetimeFieldUpdated) {
			$entityData->focus->isWorkFlowFieldUpdate = true;
		}
	}

	// Added by Tin Bui on 2022.03.15 - Save status change log
	function saveStatusChangeLog($entityData) {
		$status = $entityData->get('ticketstatus');
		$oldStatus = $entityData->oldStatus ?? '';
		
		if ($status != $oldStatus) {
			HelpDesk_SLAUtils_Helper::saveTicketChangeStatusLog($entityData->getId(), $oldStatus, $status);
		}
	}

	// Added by Tin Bui on 2022.03.15 - Calculate time for SLA fields
	function setSLATime($entityData) {
		if (isForbiddenFeature('SLAManagement')) return;
		
		global $adb;

		$status = $entityData->get('ticketstatus');
		$oldStatus = $entityData->oldStatus ?? '';
		
		if ($status != $oldStatus) {
			$totalWaitingAssignTime = HelpDesk_SLAUtils_Helper::getTotalWaitingAssignTime($entityData->getId());
			$totalProcessTime = HelpDesk_SLAUtils_Helper::getTotalProcessingTime($entityData->getId());
			$totalTime = HelpDesk_SLAUtils_Helper::getTotalTime($entityData->getId());

			$totalWaitingAssignTime = !empty($totalWaitingAssignTime) ? $totalWaitingAssignTime : 0;
			$totalProcessTime = !empty($totalProcessTime) ? $totalProcessTime : 0;
			$totalTime = !empty($totalTime) ? $totalTime : 0;

			$sql = "UPDATE vtiger_troubletickets 
				SET total_waiting_for_assignment_time = ?,
					total_process_time = ?,
					total_time = ?
				WHERE ticketid = ?";
			$adb->pquery($sql, [
				$totalWaitingAssignTime,
				$totalProcessTime,
				$totalTime,
				$entityData->getId()
			]);
		}

		if (!empty($entityData->get('related_cpslacategory'))) {
			$stardardProcessingTime = HelpDesk_SLAUtils_Helper::getStandardProcessingTimeInMinute($entityData->get('related_cpslacategory'));
			$sql = "UPDATE vtiger_troubletickets SET sla_total_process_time = ?";
			$params = [$stardardProcessingTime];

			// Over SLA check
			if ($stardardProcessingTime > 0 && $totalProcessTime > $stardardProcessingTime) {
				$sql .= " , over_sla = ?";
				$params[] = 1;
			}

			$sql .= " WHERE ticketid = ?";
			$params[] = $entityData->getId();
			
			$adb->pquery($sql, $params);
		}
	}

	// Added by Tin Bui on 2022.03.15
	function handleSendSurveyEmail($entityData) {
		if (isForbiddenFeature('CustomerSurveyOnTicket')) return;

		$status = $entityData->get('ticketstatus');
		$surveyStatus = $entityData->get('helpdesk_survey_status');
		$isSendSurvey = $entityData->get('is_send_survey') == 'on' || $entityData->get('is_send_survey') == 1;

		if ($isSendSurvey && $status == 'Closed' && ($surveyStatus == 'not_yet_sent_mail' || $surveyStatus == 'customer_reopen_ticket' || empty($surveyStatus))) {
			$success = HelpDesk_SurveyUtils_Helper::sendSurveyEmail($entityData);

			if ($success) {
				global $adb;
				$sql = "UPDATE vtiger_troubletickets SET helpdesk_survey_status = 'sent_mail' WHERE ticketid = ?";
				$adb->pquery($sql, [$entityData->getId()]);
			}
		}
	}

	// Added by Tin Bui on 2022.03.30
	function handleSendTicketReceivedEmail($entityData) {
		if (!$entityData->isNew()) return;

		$request = new Vtiger_Request($_REQUEST, $_REQUEST);
		$ticketId = $entityData->getId();
		$sendImmediately = false;
		$ccEmails = array_filter(explode(' |##| ', $entityData->get('helpdesk_related_emails')));

		// If user enter email content when create ticket, send it to customer 
		if (!empty($entityData) && !empty($request->get('isSendReply'))) {
            $emailContent = $request->getRaw('emailContent');
            HelpDesk_EmailUtils_Helper::sendReplyEmail($ticketId, $emailContent, $ccEmails, $sendImmediately);
        }
		// If user not enter email content, send default email
		else {
			HelpDesk_EmailUtils_Helper::sendReplyEmail($ticketId, '', $ccEmails, true, $sendImmediately);
		}
	}

	// Added by Tin Bui on 2022.03.30
	function handleSendAssignmentEmail($entityData) {
		$vtEntityDelta = new VTEntityDelta();
        $oldData = $vtEntityDelta->getOldEntity('HelpDesk', $entityData->getId());
		$newAssignee = $entityData->get('main_owner_id');

		if ($entityData->isNew() || !empty($oldData) && $newAssignee != $oldData->get('main_owner_id')) {
			HelpDesk_EmailUtils_Helper::sendAssignmentEmail($entityData->getId());
		}
	}

	// Added by Tin Bui on 2022.03.31
	function handleSendProcessedTicketEmail($entityData) {
		$status = $entityData->get('ticketstatus');
		$oldStatus = $entityData->oldStatus;

		if (!empty($oldStatus) && $status != $oldStatus && $status == 'Wait Close') {
			HelpDesk_EmailUtils_Helper::sendProcessedTicketEmail($entityData->getId());
		}
	}
}