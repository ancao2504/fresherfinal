{* Added by Hieu Nguyen on 2022-02-15 to render button call *}

{strip}
	<a class="btnCall"
		data-value="{$PHONE_NUMBER}"
		record="{$RECORD_ID}"
		title="{vtranslate('LBL_MAKE_CALL', 'PBXManager')}"
		data-toggle="tooltip"
		onclick="Vtiger_PBXManager_Js.registerPBXOutboundCall(this, '{$PHONE_NUMBER}', '{$RECORD_ID}');"
	>
		<i class="far fa-phone"></i>
	</a>
{/strip}