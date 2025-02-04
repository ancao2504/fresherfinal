{* Added by Vu Mai on 2022-11-04 to render call script template*}

{strip}
	<div class="container">
		{if $CAMPAIGN_INFO['call_script']}
			<span id="call-script" class="fancyScrollbar">{$CAMPAIGN_INFO['call_script']}</span>
		{else}
			<span class="text-center campaign-script-not-setup-msg">{vtranslate('LBL_TELESALES_CAMPAIGN_TELESALES_CAMPAIGN_SCRIPT_NOT_SET_UP_YET', 'CPTelesales')}</span>
			{if Campaigns_Telesales_Model::currentUserCanCreateOrRedistribute()}
				<a href="{CPTelesales_Logic_Helper::getTelesalesCampaignEditUrl($RECORD)}" target="_blank" class="text-primary text-center">{vtranslate('LBL_TELESALES_CAMPAIGN_TELESALES_CAMPAIGN_SCRIPT_SET_UP', 'CPTelesales')}&nbsp;<i class="far fa-chevron-right"></i></a> 
			{/if}
		{/if}
	</div>	
{/strip}