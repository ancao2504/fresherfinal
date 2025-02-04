{* Added by Hieu Nguyen on 2022-11-28 *}

{strip}
	<tr data-id="{$MKT_LIST_INFO.id}" data-mkt-list-info="{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($MKT_LIST_INFO))}">
		<td><a href="index.php?module=CPTargetList&view=Detail&record={$MKT_LIST_INFO.id}" target="_blank">{$MKT_LIST_INFO.name}</a></td>
		<td class="text-right"><span class="total-count">{$MKT_LIST_INFO.statistics.total_customers_count}</span></td>
		<td class="text-right"><span class="distributed-count">{$MKT_LIST_INFO.statistics.distributed_customers_count}</span></td>
		<td class="text-right"><span class="remaining-count">{$MKT_LIST_INFO.statistics.remaining_customers_count}</span></td>
		<td class="text-center">
			{if $MKT_LIST_INFO.statistics.distributed_customers_count == 0}
				<button type="button" class="btn btn-inline btn-remove"><i class="far fa-trash-can redColor"></i></button>
			{/if}
		</td>
	</tr>
{strip}