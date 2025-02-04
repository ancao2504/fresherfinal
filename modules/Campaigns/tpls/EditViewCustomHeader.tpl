{* Added by Hieu Nguyen on 2022-10-24 *}

{strip}
	{* Display when creating new record only *}
	{if $RECORD->getId() == '' && $RECORD->get('campaigntype') == 'Telesales'}
		{include file="modules/Campaigns/tpls/NewTelesalesCampaignWizard.tpl"}
	{/if}
{/strip}