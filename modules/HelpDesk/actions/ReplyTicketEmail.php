<?php
/**
 * @author Tin Bui
 * @email tin.bui@onlinecrm.vn
 * @create date 2022.03.16
 * @desc: Ticket reply email action
 */

class HelpDesk_ReplyTicketEmail_Action extends Vtiger_Action_Controller {
    
    public function checkPermission(Vtiger_Request $request) {
		return true;
	}

	public function process(Vtiger_Request $request) { 
        $ticketId =  $request->get('ticket_id');
        $success = false;
        
        if (!empty($ticketId) && isRecordExists($ticketId)) {
            $emailContent = $request->getRaw('emailContent');
            $ccEmails = array_filter(explode(', ', $request->get('emailCC', '')));
            $success = HelpDesk_EmailUtils_Helper::sendReplyEmail($ticketId, $emailContent, $ccEmails);
        }

        $success = true;
          
        // Set response
        $response = new Vtiger_Response();
        $response->setResult([
            'success' => $success
        ]);
        
        $response->emit();
    }
}