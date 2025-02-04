<?php

/*
*	SocialReport.php
*	Author: Phuc Lu
*	Date: 2019.08.29
*   Purpose: Data handler for Social Report
*/

class Campaigns_SocialReport_Model {

	// Get social message report
    public static function getSocialMessageReport($recordId) {
		global $adb;
		
		$totalTarget = 0;
		$totalSocial = 0;
		$totalSocialByModule = [			
			'CPTarget' => 0,
			'Contacts' => 0,
			'Leads' => 0
		];	

		// Get all contacts, targets, leads in marketing lists of campaign
		$sql = "SELECT cus.setype AS module, COUNT(DISTINCT cus.crmid) AS customer_count, COUNT(DISTINCT vtiger_social_mapping.social_id) AS social_id_count
			FROM vtiger_crmentityrel AS cam2tl
			INNER JOIN vtiger_crmentity AS tl ON (tl.deleted = 0 AND cam2tl.relcrmid = tl.crmid AND cam2tl.relmodule = 'CPTargetList')
			INNER JOIN vtiger_crmentityrel AS tl2cus ON (tl2cus.crmid = tl.crmid AND tl2cus.module = 'CPTargetList')
			INNER JOIN vtiger_crmentity AS cus ON (cus.deleted = 0 AND tl2cus.relcrmid = cus.crmid  AND tl2cus.relmodule IN ('Contacts', 'Leads', 'CPTarget'))
			LEFT JOIN vtiger_social_mapping ON (vtiger_social_mapping.crm_id = cus.crmid AND vtiger_social_mapping.social_channel = 'Zalo')
			WHERE cam2tl.crmid = ? AND cam2tl.module = 'Campaigns'
			GROUP BY module";

		$result = $adb->pquery($sql, [$recordId]);

		while ($row = $adb->fetchByAssoc($result)) {
			$totalTarget += $row['customer_count'];
			$totalSocial += $row['social_id_count'];
			$totalSocialByModule[$row['module']] = $row['social_id_count'];
		}

		$sentMessages = [
			'sent' => 0,
			'success' => 0,
			'failed' => 0,
			'queued' => 0
		];

		// Get message log
		$sql = "SELECT cpsocialmessagelog_status, count(cpsocialmessagelogid) AS records_count 
			FROM vtiger_cpsocialmessagelog
			WHERE related_campaign = ?
			AND cpsocialmessagelog_social_channel = 'Zalo'
			GROUP BY
			cpsocialmessagelog_status";

		$result = $adb->pquery($sql, [$recordId]);

		while ($row = $adb->fetchByAssoc($result)) {
			$sentMessages[$row['cpsocialmessagelog_status']] = $row['records_count'];
		}

		// Message sent will be total of success and failed
		$sentMessages['sent'] = $sentMessages['success'] + $sentMessages['failed'] + $sentMessages['queued'];

		return [
			'sources' => [
				'total' => $totalTarget,
				'formatted_total' => CurrencyField::convertToUserFormat($totalTarget),
				'targets' => $totalSocialByModule['CPTarget'],
				'contacts' => $totalSocialByModule['Contacts'],
				'leads' => $totalSocialByModule['Leads'],
				'zalo' => $totalSocial,
				'formatted_zalo' => CurrencyField::convertToUserFormat($totalSocial),
			],
			'results' => [
				'sent' => $sentMessages['sent'] ,
				'queued' => $sentMessages['queued'],
				'formatted_sent' => CurrencyField::convertToUserFormat($sentMessages['sent']),
				'sent_success' => $sentMessages['success'],
				'formatted_sent_success' => CurrencyField::convertToUserFormat($sentMessages['success']),
				'sent_ratio' => CurrencyField::convertToUserFormat($sentMessages['sent'] / $totalSocial *  100),
				'sent_failed' => $sentMessages['failed'],
				'sent_success_ratio' => CurrencyField::convertToUserFormat($sentMessages['success'] / $sentMessages['sent'] *  100),
			]
		];
	}

	// Get social article report
	public static function getSocialArticleReport($recordId) {
		require_once('data/CRMEntity.php'); // Added by Tung Nguyen on 2022.04.29 to boost performance when load related list n-n
		
		global $adb;

		$cities = '';
		$genders = '';
		$ages = '';
		$platforms = '';

		// Modified by Tung Nguyen on 2022.04.29 to boost performance when load related list n-n
		$temporaryTableCrmentityrel = CRMEntity::setupTemporaryTableCrmentityrel($recordId);

		$sql = "SELECT DISTINCT vtiger_crmentity.crmid, vtiger_cptargetlist.*
		FROM vtiger_cptargetlist
		INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_cptargetlist.cptargetlistid)
		INNER JOIN {$temporaryTableCrmentityrel} ON ({$temporaryTableCrmentityrel}.relcrmid = vtiger_crmentity.crmid)
		WHERE vtiger_crmentity.deleted = 0";
		// Ended by Tung Nguyen

		$result = $adb->pquery($sql, [$recordId, $recordId]);

		// Get filters string
		while ($row = $adb->fetchByAssoc($result)) {
			if ($row['cptargetlist_type'] == 'Zalo') {
				$cities .= ' |##| ' . $row['cptargetlist_zalo_city'];
				$genders .= ' |##| ' . $row['cptargetlist_zalo_gender'];
				$ages .= ' |##| ' . $row['cptargetlist_zalo_age'];
				$platforms .= ' |##| ' . $row['cptargetlist_zalo_platform'];
			}
		}

		// Explore and remove duplicate values
		$cities = array_unique(getMultiPicklistValues($cities));
		$genders = array_unique(getMultiPicklistValues($genders));
		$ages = array_unique(getMultiPicklistValues($ages));
		$platforms = array_unique(getMultiPicklistValues($platforms));

		$citiesLabel = [];
		$gendersLabel = [];
		$agesLabel = [];
		$platformsLabel = [];

		// Translate to label
		foreach ($cities as $city) {
			if ($city != '') {
				$citiesLabel[] = vtranslate($city, 'CPTargetList');
			}
		}

		foreach ($genders as $gender) {
			if ($gender != '') {
				$gendersLabel[] = vtranslate($gender, 'CPTargetList');
			}

			// If gender is all, do not need to show others
			if ($gender == 'zalo_gender_0') {	
				$gendersLabel = [];
				$gendersLabel[] = vtranslate($gender, 'CPTargetList');
				break;
			}
		}

		foreach ($ages as $age) {
			if ($age != '') {
				$agesLabel[] = vtranslate($age, 'CPTargetList');
			}
		}

		foreach ($platforms as $platform) {
			if ($platform != '') {
				$platformsLabel[] = vtranslate($platform, 'CPTargetList');
			}
		}

		// Get articles		
		$articles = [];

		$sql = "SELECT DISTINCT vtiger_crmentity.crmid, vtiger_cpsocialarticle.*
			FROM vtiger_cpsocialarticle
			INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_cpsocialarticle.cpsocialarticleid)
			INNER JOIN {$temporaryTableCrmentityrel} ON ({$temporaryTableCrmentityrel}.relcrmid = vtiger_crmentity.crmid) -- Added by Tung Nguyen on 2022.04.29 to boost performance when load related list n-n
			WHERE vtiger_crmentity.deleted = 0
			AND (vtiger_crmentityrel.crmid = ? OR vtiger_crmentityrel.relcrmid = ?)";

		$result = $adb->pquery($sql, [$recordId, $recordId]);
		
		while ($row = $adb->fetchByAssoc($result)) {
			$articles[$row['crmid']] = $row;
		}

		return [
			'filters' => [
				'cities' => implode(', ', $citiesLabel),
				'genders' => implode(', ', $gendersLabel),
				'ages' => implode(', ', $agesLabel),
				'platforms' => implode(', ', $platformsLabel),
			],
			'articles'=> $articles
		];		
	}

	// Get campaign result
	public static function getCampaignResult($recordId) {
		global $adb;

		$collectedData = [
			'Seen message' => 0,
			'CPTarget' => 0,
			'Leads' => 0,
			'Contacts' => 0,
		];

		// Get seen, target, leads, contacts. Note: now just get seen because comment, like is disabled for Zalo type
		$sql = "SELECT vtiger_cpsocialfeedback.cpsocialfeedback_type AS data_type, COUNT(DISTINCT vtiger_crmentity.crmid) AS records_count
			FROM vtiger_cpsocialfeedback
			INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_cpsocialfeedback.cpsocialfeedbackid)
			WHERE vtiger_crmentity.deleted = 0
			AND vtiger_cpsocialfeedback.related_campaign = ? AND vtiger_cpsocialfeedback.cpsocialfeedback_channel = 'Zalo'
			AND vtiger_cpsocialfeedback.cpsocialfeedback_type IN ('Seen message')
			GROUP BY vtiger_cpsocialfeedback.cpsocialfeedback_type

			UNION ALL

			SELECT 'CPTarget' AS data_type, COUNT(DISTINCT vtiger_crmentity.crmid) AS records_count
			FROM vtiger_cptarget
			INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_cptarget.cptargetid AND vtiger_crmentity.deleted = 0)
			WHERE vtiger_cptarget.related_campaign = ?				
			
			UNION ALL 
			
			SELECT 'Leads' AS data_type, COUNT(DISTINCT vtiger_crmentity.crmid) AS records_count
			FROM vtiger_leaddetails
			INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_leaddetails.leadid AND vtiger_crmentity.deleted = 0)
			WHERE vtiger_leaddetails.related_campaign = ?

			UNION ALL 

			SELECT 'Contacts' AS data_type, COUNT(DISTINCT vtiger_crmentity.crmid) AS records_count
			FROM vtiger_contactdetails
			INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_contactdetails.contactid AND vtiger_crmentity.deleted = 0)
			WHERE vtiger_contactdetails.related_campaign = ?";

		$result = $adb->pquery($sql, [$recordId, $recordId, $recordId, $recordId]);
		
		while ($row = $adb->fetchByAssoc($result)) {
			$collectedData[$row['data_type']] = $row['records_count'];
		}

		// Get count and amount of related data
		$relatedData = [
			'Potentials' => 0,
			'Quotes' => 0,
			'SalesOrder' => 0,
			'Invoice' => 0,
		];

		// Get potentials, quotes, sales orders, invoices
		$sql = "SELECT 'Potentials' AS data_type, COUNT(DISTINCT vtiger_crmentity.crmid) AS records_count, SUM(vtiger_potential.amount) AS amount
			FROM vtiger_potential
			INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_potential.potentialid AND vtiger_crmentity.deleted = 0)
			WHERE vtiger_potential.campaignid = ?

			UNION ALL

			SELECT 'Quotes' AS data_type, COUNT(DISTINCT vtiger_crmentity.crmid) AS records_count, SUM(vtiger_quotes.total) AS amount
			FROM vtiger_quotes
			INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_quotes.quoteid AND vtiger_crmentity.deleted = 0)
			WHERE vtiger_quotes.related_campaign = ?

			UNION ALL

			SELECT 'SalesOrder' AS data_type, COUNT(DISTINCT vtiger_crmentity.crmid) AS records_count, SUM(vtiger_salesorder.total) AS amount
			FROM vtiger_salesorder
			INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_salesorder.salesorderid AND vtiger_crmentity.deleted = 0)
			WHERE vtiger_salesorder.related_campaign = ?

			UNION ALL

			SELECT 'Invoice' AS data_type, COUNT(DISTINCT vtiger_crmentity.crmid) AS records_count, SUM(vtiger_invoice.balance) AS amount
			FROM vtiger_invoice
			INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_invoice.invoiceid AND vtiger_crmentity.deleted = 0)
			WHERE vtiger_invoice.related_campaign = ?";

		$result = $adb->pquery($sql, [$recordId, $recordId, $recordId, $recordId]);
		
		while ($row = $adb->fetchByAssoc($result)) {
			$relatedData[$row['data_type']] = $row;
		}
		
		return [
			'collected_data' => $collectedData,
			'outcome'=> $relatedData
		];	
	}

}