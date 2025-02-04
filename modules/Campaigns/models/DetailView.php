<?php

/*
*	DetailView.php
*	Author: Phuc Lu
*	Date: 2019.08.08
*   Purpose: create detail view model for Campaigns
*/

class Campaigns_DetailView_Model extends Vtiger_DetailView_Model {

    public function getDetailViewLinks($linkParams) {
        require_once('libraries/ArrayUtils/ArrayUtils.php');
        $linkModelList = parent::getDetailViewLinks($linkParams);

        // Added by Hieu Nguyen on 2019-08-13 to add button send zalo message
        if ($this->getRecord()->get('campaigntype') == 'Social' && CPSocialIntegration_Config_Helper::isZaloMessageAllowed()) {
            $zaloMessageLink = array(
                'linktype' => 'DETAILVIEW',
                'linklabel' => 'LBL_SOCIAL_INTEGRATION_SEND_ZALO_MESSAGE',
                'linkurl' => 'javascript:triggerComposeSocialMessage("Zalo");',
                'linkicon' => ''
            );

            $linkModelList['DETAILVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($zaloMessageLink);
        }
        // End Hieu Nguyen

        // If type is "Social", display report button
        if ($this->getRecord()->get('campaigntype') == 'Social') {
            $socialReportLink = array(
                'linktype' => 'DETAILVIEWBASIC',
                'linklabel' => 'LBL_SOCIAL_INTEGRATION_VIEW_SOCIAL_REPORT',
                'linkurl' => 'javascript:viewSocialReport()',
                'linkicon' => ''
            );

            $linkModelList['DETAILVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($socialReportLink);
        }

        // SMS Campaign. Added by Hieu Nguyen on 2020-11-13
        if (!isForbiddenFeature('SMSCampaign')) {
            // Send SMS
            if ($this->getRecord()->get('campaigntype') == 'SMS Message') {
                $zaloMessageLink = array(
                    'linktype' => 'DETAILVIEW',
                    'linklabel' => 'LBL_SEND_SMS',
                    'linkurl' => 'javascript:triggerComposeSMSAndOTTMessage("SMS", this);',
                    'linkicon' => ''
                );

                $linkModelList['DETAILVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($zaloMessageLink);
            }
        }
        // End Hieu Nguyen

        // Zalo OA Campaign. Added by Hieu Nguyen on 2022-02-28
        if (!isForbiddenFeature('ZaloOACampaign')) {
            if (
                $this->getRecord()->get('campaigntype') == 'Zalo OA Message' 
                && !isForbiddenFeature('ZaloIntegration')
            ) {
                $zaloMessageLink = array(
                    'linktype' => 'DETAILVIEW',
                    'linklabel' => 'LBL_SEND_ZALO_OA_MESSAGE',
                    'linkurl' => 'javascript:triggerComposeSMSAndOAMessage("Zalo", this);',
                    'linkicon' => ''
                );

                $linkModelList['DETAILVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($zaloMessageLink);
            }
        }
        // End Hieu Nguyen

        // Zalo ZNS Campaign. Added by Hieu Nguyen on 2022-02-28
        if (!isForbiddenFeature('ZaloZNSCampaign')) {
            // Send Zalo ZNS
            $ottGateway = CPOTTIntegration_Gateway_Model::getActiveGateway('Zalo');

            if (
                $this->getRecord()->get('campaigntype') == 'Zalo ZNS Message' 
                && !empty($ottGateway)
            ) {
                $zaloMessageLink = array(
                    'linktype' => 'DETAILVIEW',
                    'linklabel' => 'LBL_SEND_ZALO_OTT_MESSAGE',
                    'linkurl' => 'javascript:triggerComposeSMSAndOTTMessage("Zalo", this);',
                    'linkicon' => ''
                );

                $linkModelList['DETAILVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($zaloMessageLink);
            }
        }
        // End Hieu Nguyen

        // Telesales Campaign. Added by Hieu Nguyen on 2022-11-10
        if (!isForbiddenFeature('TelesalesCampaign')) {
            if ($this->getRecord()->get('campaigntype') == 'Telesales') {
                $campaignId = $this->getRecord()->getId();

                // Modified by Vu Mai on 2023-02-13 to update logic decentralize redistribute and go to Telesale Screen
                // Only Admins and Telesales Managers can be redistribute
                if (Campaigns_Telesales_Model::currentUserCanCreateOrRedistribute()) {
                    $redistributeLink = array(
                        'linktype' => 'DETAILVIEW',
                        'linklabel' => 'LBL_TELESALES_CAMPAIGN_REDISTRIBUTE',
                        'linkurl' => 'javascript:location.href="index.php?module=Campaigns&view=EditTelesalesCampaignForm&record=' . $campaignId .'";',
                        'linkicon' => ''
                    );

                    $linkModelList['DETAILVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($redistributeLink);
                }
                
                // Only Admins, Telesales Managers and users is assigned can be go to Telesale Screen
                if (CPTelesales_Logic_Helper::canAccessTelesalesMainView($campaignId)) {
                    $telesalesLink = array(
                        'linktype' => 'DETAILVIEW',
                        'linklabel' => 'LBL_TELESALES_CAMPAIGN_GO_TO_TELESALES_SCREEN',
                        'linkurl' => 'javascript:location.href="index.php?module=CPTelesales&view=Telesales&mode=getMainView&record=' . $campaignId .'";',
                        'linkicon' => ''
                    );

                    $linkModelList['DETAILVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($telesalesLink);
                } 
                // End Vu Mai

                // Added by Vu Mai on 2022-11-30 to add button link to telesales campaign report
                $reportLink = array(
                    'linktype' => 'DETAILVIEW',
                    'linklabel' => 'LBL_TELESALES_CAMPAIGN_GO_TO_REPORT_SCREEN',
                    'linkurl' => 'javascript:location.href="index.php?module=CPTelesales&view=Report&record=' . $campaignId .'";',
                    'linkicon' => ''
                );

                $linkModelList['DETAILVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($reportLink);
                // End Vu Mai

            }
        }

        return $linkModelList;
    }

    // Added by Phu Vo on 2020.11.13
    public function getDetailViewRelatedLinks() {
        $relatedLinks = parent::getDetailViewRelatedLinks();

        // Added by Phu Vo on 2019.08.14 to show/hide social's subpanel
        if ($this->getRecord()->get('campaigntype') !== 'Social') {
            $removeModules = [
                'CPSocialMessageLog',
                'CPSocialArticle',
                'CPSocialArticleLog',
                'CPSocialFeedback',
            ];

            foreach ($relatedLinks as $key => $relatedLink) {
                if (isset($relatedLink['relatedModuleName']) && in_array($relatedLink['relatedModuleName'], $removeModules)) {
                    unset($relatedLinks[$key]);
                }
            }
        }

        // Added by Phu on 2019.10.04 to handle hide module tabs
		$removeModules = [
            'CPSMSOTTMessageLog',   // Added by Hieu Nguyen on 2020-11-13 to hide Message Log subpanel from Campaign detail
		];

        foreach ($relatedLinks as $key => $relatedLink) {
            if (isset($relatedLink['relatedModuleName']) && in_array($relatedLink['relatedModuleName'], $removeModules)) {
                unset($relatedLinks[$key]);
            }
        }

        // Added by Hieu Nguyen on 2020-11-13 to show/hide message's subpanels
        $messageCampaignTypes = ['SMS Message', 'Zalo Message'];

        if (isForbiddenFeature('SMSCampaign') || !in_array($this->getRecord()->get('campaigntype'), $messageCampaignTypes)) {
            $removeModules = [
                'SMSNotifier',
            ];

            foreach ($relatedLinks as $key => $relatedLink) {
                if (isset($relatedLink['relatedModuleName']) && in_array($relatedLink['relatedModuleName'], $removeModules)) {
                    unset($relatedLinks[$key]);
                }
            }
        }
        // End Hieu Nguyen

        return $relatedLinks;
    }
}