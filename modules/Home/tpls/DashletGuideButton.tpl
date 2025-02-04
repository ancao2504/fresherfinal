{* Added by Hieu Nguyen on 2022-03-10 to render dashlet guide button *}

{strip}
	{assign var="DASHLET_GUIDE" value=Settings_Vtiger_Config_Model::loadConfig('dashlet_guide', true)}
	
	{if $LINK_ID}
		{assign var="GUIDE_CONTENT" value=$DASHLET_GUIDE['Widget_'|cat:$LINK_ID]}
	{/if}

	{if $REPORT_ID}
		{assign var="GUIDE_CONTENT" value=$DASHLET_GUIDE['Report_'|cat:$REPORT_ID]}
	{/if}

	{if $GUIDE_CONTENT != ''}
		<button class="btn btn-link btn-show-dashlet-guide" data-toggle="tooltip" title="{vtranslate('LBL_DASHLET_GUIDE_BTN_TITLE', 'Home')}" data-guide-content="{Vtiger_Util_Helper::toSafeHTML($GUIDE_CONTENT)}"><i class="far fa-info-circle"></i></button>
	{/if}
{/strip}