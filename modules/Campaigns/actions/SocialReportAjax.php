<?php

/*
    SocialReportAjax.php
    Author: Phuc Lu
    Date: 2019-08-30
    Purpose: handle report via ajax
*/

class Campaigns_SocialReportAjax_Action  extends Vtiger_Action_Controller {

    function checkPermission(Vtiger_Request $request) {
		return;
    }

    function process(Vtiger_Request $request) {
        $recordId = $request->get('record');
        	
		// Get report data	
		$socialMessageData = Campaigns_SocialReport_Model::getSocialMessageReport($recordId);	
		$socialArticleData = Campaigns_SocialReport_Model::getSocialArticleReport($recordId);
		$campaignResultData = Campaigns_SocialReport_Model::getCampaignResult($recordId);

		$sources = $socialMessageData['sources'];	
		$results = $socialMessageData['results'];	
		$collectedData = $campaignResultData['collected_data'];
		$relatedData = $campaignResultData['outcome'];

		$sourceChartArray = [
			['', '', [ 'role' => 'annotation' ], [ 'role' => 'style' ]],
			[vtranslate('LBL_SOCIAL_REPORT_TARGETS', 'Campaigns'), (int) $sources['targets'],  (int) $sources['targets'], '#81D3F8'],
			[vtranslate('LBL_SOCIAL_REPORT_LEADS', 'Campaigns'), (int) $sources['leads'], (int) $sources['leads'], '#81D3F8'],
			[vtranslate('LBL_SOCIAL_REPORT_CONTACTS', 'Campaigns'), (int) $sources['contacts'], (int) $sources['contacts'], '#81D3F8'],
		];

		$resultDetailChartArray = [
			['', vtranslate('LBL_SOCIAL_REPORT_IN_QUEUE', 'Campaigns'), [ 'role' => 'annotation' ], [ 'role' => 'style' ], vtranslate('LBL_SOCIAL_REPORT_SUCCEED', 'Campaigns'), [ 'role' => 'annotation' ], [ 'role' => 'style' ], vtranslate('LBL_SOCIAL_REPORT_FAILED', 'Campaigns'), [ 'role' => 'annotation' ], [ 'role' => 'style' ]],
			['', (int) $results['queued'],  (int) $results['queued'], '#FFFF80', (int) $results['sent_success'],  (int) $results['sent_success'], '#039D12', (int) $results['sent'] - (int) $results['sent_success'], (int) $results['sent'] - (int) $results['sent_success'], '#EC808D']
		];

		$collectedDataChartArray = [
			['', '', [ 'role' => 'annotation' ], [ 'role' => 'style' ]],			
			[vtranslate('LBL_SOCIAL_REPORT_SEEN', 'Campaigns'), (int) $collectedData['Seen message'],  (int) $collectedData['Seen message'], '#C280FF'],
			[vtranslate('LBL_SOCIAL_REPORT_TARGETS', 'Campaigns'), (int) $collectedData['CPTarget'],  (int) $collectedData['CPTarget'], '#C280FF'],
			[vtranslate('LBL_SOCIAL_REPORT_LEADS', 'Campaigns'), (int) $collectedData['Leads'], (int) $collectedData['Leads'], '#C280FF'],
			[vtranslate('LBL_SOCIAL_REPORT_CONTACTS', 'Campaigns'), (int) $collectedData['Contacts'], (int) $collectedData['Contacts'], '#C280FF'],
		];
		
		$outcomeChartArray = [
			['', '', [ 'role' => 'annotation' ], [ 'role' => 'style' ]],			
			[vtranslate('Potentials', 'Potentials') . " ({$relatedData['Potentials']['records_count']})", (int) $relatedData['Potentials']['amount'], (int) $relatedData['Potentials']['amount'], '#95F204'],
			[vtranslate('Quotes', 'Quotes') . " ({$relatedData['Quotes']['records_count']})", (int) $relatedData['Quotes']['amount'],  (int) $relatedData['Quotes']['amount'], '#95F204'],
			[vtranslate('SalesOrder', 'SalesOrder') . " ({$relatedData['SalesOrder']['records_count']})", (int) $relatedData['SalesOrder']['amount'], (int) $relatedData['SalesOrder']['amount'], '#95F204'],
			[vtranslate('Invoice', 'Invoice') . " ({$relatedData['Invoice']['records_count']})", (int) $relatedData['Invoice']['amount'], (int) $relatedData['Invoice']['amount'], '#95F204'],
		];

		$socialMessageData['source_chart_array'] = $sourceChartArray;
		$socialMessageData['result_detail_chart_array'] = $resultDetailChartArray;
		$campaignResultData['collected_data_chart_array'] = $collectedDataChartArray;
		$campaignResultData['outcome_chart_array'] = $outcomeChartArray;

        $returnValues = [
            'social_message_report' => $socialMessageData,
            'social_article_report' => $socialArticleData,
			'campaign_result' => $campaignResultData
        ];
        
        $response = new Vtiger_Response();
        $response->setResult($returnValues);
		$response->emit();
    }

}