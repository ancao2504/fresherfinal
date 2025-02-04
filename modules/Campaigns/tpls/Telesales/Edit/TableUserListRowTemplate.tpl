{* Added by Hieu Nguyen on 2022-11-28 *}
{* Modified by Vu Mai on 2022 on 2022-12-08 to restyle according to mockup *}

{strip}
	<tr data-user-id="{$USER_INFO.id}" data-user-info="{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($USER_INFO))}">
		<td>{$USER_INFO.name}</td>
		<td class="text-right"><span class="already-called-count">{$USER_INFO.statistics.already_called_count}</span></td>
		<td class="text-right"><span class="not-called-count">{$USER_INFO.statistics.not_called_count}</span></td>
		<td class="text-right">
			<span class="all-distributed-count">{$USER_INFO.statistics.all_distributed_count}</span>
			<span class="error-msg redColor"></span>
		</td>
		<td class="actions no-wrap">
			<button type="button" class="btn btn-inline btn-remove" title="{vtranslate('LBL_EDIT_TELESALES_CAMPAIGN_WIZARD_PANEL_SELECT_USERS_BTN_REMOVE_USER_TITLE', $MODULE_NAME)}"><i class="far fa-trash-can redColor mr-3"></i></button>&nbsp;

			{if $USER_INFO.statistics.not_called_count > 0}
				<button type="button" class="btn btn-inline btn-transfer" title="{vtranslate('LBL_EDIT_TELESALES_CAMPAIGN_WIZARD_PANEL_SELECT_USERS_BTN_TRANSFER_DATA_TITLE', $MODULE_NAME)}"><i class="far fa-circle-arrow-right"></i></button>
			{/if}
		</td>
	</tr>
{strip}