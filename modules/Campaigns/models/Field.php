<?php

/**
 * Campaigns Field Model
 * Author: Phu Vo
 * Date: 2019.08.14
 */

Class Campaigns_Field_Model extends Vtiger_Field_Model {
    
    public function isAjaxEditable() {
        // Prevent ajax edit for campaigntype
        if ($this->getName() === 'campaigntype') return false;

        return parent::isAjaxEditable();
    }

    // Implemented by Hieu Nguyen on 2021-08-19 to hide some options in picklist fields
    public function getPicklistValues() {
		$fieldName = $this->getName();
		$options = parent::getPicklistValues();

		if ($fieldName == 'campaigntype') {
            if (isForbiddenFeature('SMSCampaign')) {
                unset($options['SMS Message']);
            }

            if (isForbiddenFeature('ZaloOACampaign')) {
                unset($options['Zalo OA Message']);
            }

            if (isForbiddenFeature('ZaloZNSCampaign')) {
                unset($options['Zalo ZNS Message']);
            }

            // Added by Vu Mai on 2023-02-16 to hide Telesale option if current user is not admin or Telesales Manager
            if (!Campaigns_Telesales_Model::currentUserCanCreateOrRedistribute()) {
                unset($options['Telesales']);
            }
            // End Vu Mai
		}

		return $options;
	}
}