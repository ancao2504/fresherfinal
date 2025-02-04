{* Added by Hieu Nguyen on 2022-09-06 to render show map button *}

{strip}
	{if !isForbiddenFeature('GoogleMapsIntegration')}
		<span>
			<i class="far fa-map-marker"></i>&nbsp;
			<a class="showMap" href="javascript:void(0);" onclick='Vtiger_Index_Js.showMap(this);' data-module='{$RECORD->getModule()->getName()}' data-record='{$RECORD->getId()}'>{vtranslate('LBL_SHOW_MAP', $MODULE_NAME)}</a>
		</span>
	{/if}
{/strip}