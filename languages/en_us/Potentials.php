<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
$languageStrings = array(
	// Basic Strings
	'Potentials' => 'Opportunities',
	'SINGLE_Potentials' => 'Opportunity',
	'LBL_ADD_RECORD' => 'Add Opportunity',
	'LBL_RECORDS_LIST' => 'Opportunities List',

	// Blocks
	'LBL_OPPORTUNITY_INFORMATION' => 'Opportunity Details',

	//Field Labels
	'Potential No' => 'Opportunity Number',
	'Amount' => 'Amount',
	'Next Step' => 'Next Step',
	'Sales Stage' => 'Sales Stage',
	'Probability' => 'Probability',
	'Campaign Source' => 'Campaign Source',
	'Forecast Amount' => 'Weighted Revenue',
	'Related To' => 'Organization Name',
	'Contact Name' => 'Contact Name',
        'Type' => 'Type',
	
	//Dashboard widgets
	'Funnel' => 'Sales Funnel',
	'Potentials by Stage' => 'Opportunities by Stage',
	'Total Revenue' => 'Revenue by Salesperson',
	'Top Potentials' => 'Top Opportunities',
	'Forecast' => 'Sales Forecast',

	//Added for Existing Picklist Strings

	'Prospecting'=>'Prospecting',
	'Qualification'=>'Qualification',
	'Needs Analysis'=>'Needs Analysis',
	'Value Proposition'=>'Value Proposition',
	'Id. Decision Makers'=>'Identify Decision Makers',
	'Perception Analysis'=>'Perception Analysis',
	'Proposal/Price Quote'=>'Proposal/Quotation',
	'Negotiation/Review'=>'Negotiation/Review',
	'Closed Won'=>'Closed Won',
	'Closed Lost'=>'Closed Lost',

	'--None--'=>'--None--',
	'Existing Business'=>'Existing Business',
	'New Business'=>'New Business',
	'LBL_EXPECTED_CLOSE_DATE_ON' => 'Expected to close on',

	//widgets headers
	'LBL_RELATED_CONTACTS' => 'Related Contacts',
	'LBL_RELATED_PRODUCTS' => 'Related Products',
    
    //Convert Potentials
    'LBL_CONVERT_POTENTIAL' => 'Convert Opportunity',
	'LBL_CREATE_PROJECT' => 'Create Project',
    'LBL_POTENTIALS_FIELD_MAPPING' => 'Opportunities Field Mapping',
    'LBL_CONVERT_POTENTIALS_ERROR' => 'You have to enable Project to convert the Opportunity',
    'LBL_POTENTIALS_FIELD_MAPPING_INCOMPLETE' => 'Opportunities Field Mapping is incomplete(Settings > Module Manager > Opportunities > Opportunities Field Mapping)',
    
    //Potentials Custom Field Mapping
	'LBL_CUSTOM_FIELD_MAPPING'=> 'Opportunity to Project mapping',

	// Begin for Potential result and probability
	'0' => '0%',
    '10' => '10%',
    '20' => '20%',
    '30' => '30%',
    '40' => '40%',
    '50' => '50%',
    '70' => '70%',
    '60' => '60%',
    '80' => '80%',
    '90' => '90%',
    '100' => '100%',
	'LBL_POTENTIAL_RESULT' => 'Result',
	'LBL_POTENTIAL_LOST_REASON' => 'Lost Reason',
    'price_higher_than_budget' => 'Price is higher than budget',
    'poor_features' => 'Poor features',
    'unfriendly_ui_ux' => 'Unfriendly UI/UX',
    'customer_has_no_plan' => 'Customer has no plan',
    'customer_selected_another_partner' => 'Customer selected another partner',
    'requirement_not_suite_with_crm' => 'Requirements are not suite with CRM',
	// End fo Potential result and probability
    
    // [Core] Added by Phu Vo on 2020.12.14
    'Proposal' => 'Proposal',
    'Quotation' => 'Quotation',
    'Pending' => 'Pending',
    'Negotiation or Review' => 'Negotiation or Review', // Modified by Phu Vo on 2020.12.14
    // End Phu Vo
    
    // Added by Hieu Nguyen on 2021-08-04
    'LBL_PROGRESS_BAR_VISITED_NODE_TOOLTIP' => 'Sales Stage: %node_value\nPrevious stage: %prev_value\nUpdated By: %updated_by\nUpdated Time: %updated_time',
    'LBL_PROGRESS_BAR_WON_RESULT_TOOLTIP' => 'Closed Won at stage %sales_stage',
    'LBL_PROGRESS_BAR_LOST_RESULT_TOOLTIP' => 'Closed Lost at stage %sales_stage with reason: %lost_reason',
    // End Hieu Nguyen

    // Added by Phu Vo on 2021.08.10
    'LBL_LOST_REASON_DESCRIPTION' => 'Lost Reason Description',
    'LBL_ACTUAL_CLOSING_DATE' => 'Actual Closing Date',
	// End Phu Vo
);

$jsLanguageStrings = array(
	'JS_SELECT_PROJECT_TO_CONVERT_LEAD' => 'Conversion requires selection of Project',
    
    // Added by Phu Vo on 2021.09.08 to add sales stage confirmation message
    'JS_SALES_STAGE_REVERT_CONFIRMATION_MSG' => 'This Potential is marked as %sales_stage, update sales stage will cancel previous result. Do you want to perform this action?',
    // End Phu Vo
);