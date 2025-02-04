<?php
    /*
    *   CustomCode Structure for DetailView
    *   Author: Hieu Nguyen
    *   Date: 2019-08-05
    *   Purpose: customize the layout easily with configurable display params
    */

    $displayParams = array(
        'scripts' => '
			<link type="text/css" rel="stylesheet" href="{vresource_url("modules/Campaigns/resources/DetailView.css")}"></script>
			<script type="text/javascript" src="{vresource_url("modules/Campaigns/resources/DetailView.js")}"></script>
            {include file="modules/Campaigns/tpls/SocialArticleBroadcastPopup.tpl"}
            {include file="modules/Campaigns/tpls/SocialReportPopup.tpl"}
            {include file="modules/Campaigns/tpls/SMSAndOTTMessagePopup.tpl"}
        ',
        'form' => array(
            'hiddenFields' => '
            
            ',
        ),
        'fields' => array(
            // Dont' allow to do quick edit on Campaign Type and Campaign Purpose fields
            'campaigntype' => array(
                'customTemplate' => '<span class="value" data-value="{$RECORD->get("campaigntype")}">{vtranslate($RECORD->get("campaigntype"), "Campaigns")}</span>',
            ),
            'campaigns_message_type' => array(
                'customTemplate' => '<span class="value" data-value="{$RECORD->get("campaigns_message_type")}">{vtranslate($RECORD->get("campaigns_message_type"), "Campaigns")}</span>',
            ),
        ),
    );