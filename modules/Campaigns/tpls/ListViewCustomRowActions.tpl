{*
	ListViewCustomRowActions.tpl
	Author: Vu Mai
	Date: 2023-02-14
*}

{strip}
	{if $LISTVIEW_ENTRY->get('campaigntype') == 'Telesales' && CPTelesales_Logic_Helper::canAccessTelesalesMainView($LISTVIEW_ENTRY->getId())}
		<span>
			<a class="far icon far fa-headset" 
				href="index.php?module=CPTelesales&view=Telesales&mode=getMainView&record={$LISTVIEW_ENTRY->getId()}" 
				style="font-size: 16px; margin-top: 3px;" 
				title="Telesales">
			</a>
		</span>
	{/if}
{/strip}