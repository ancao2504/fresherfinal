{* Added by Hieu Nguyen on 2019-10-29 to customize location field *}

{strip}
	{if $RECORD->get('location') != '' && !isForbiddenFeature('GoogleMapsIntegration')}
		<span title="{vtranslate('LBL_SHOW_MAP')}">
			<i class="far fa-map-marker"></i>&nbsp;
			<a href="#" onclick="GoogleMaps.showMaps('{$RECORD->get('location')}')">{$RECORD->get('location')}</a>
		</span>
	{/if}
{/strip}