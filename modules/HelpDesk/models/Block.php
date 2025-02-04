<?php
/**
 * @author Tin Bui
 * @email tin.bui@onlinecrm.vn
 * @create date 2022.08.31
 */

class HelpDesk_Block_Model extends Vtiger_Block_Model {

	public function getFields() {
		if ($this->label == 'LBL_SLA_INFORMATIONS' && isForbiddenFeature('SLAManagement')) {
			return [];
		}

		if ($this->label == 'LBL_RATING_INFORMATIONS' && isForbiddenFeature('CustomerSurveyOnTicket')) {
			return [];
		}
		
		return parent::getFields();
	}
}
