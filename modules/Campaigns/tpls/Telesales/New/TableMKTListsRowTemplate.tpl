{* Added by Hieu Nguyen on 2022-11-09 *}

{strip}
	<tr data-id="{$MKT_LIST_INFO.id}">
		<td><a href="index.php?module=CPTargetList&view=Detail&record={$MKT_LIST_INFO.id}" target="_blank">{$MKT_LIST_INFO.name}</a></td>
		<td>{$MKT_LIST_INFO.description}</td>
		<td>{$MKT_LIST_INFO.status}</td>
		<td class="text-right"><span class="customers-count">{$MKT_LIST_INFO.customers_count}</span></td>
		<td class="text-center"><button type="button" class="btn btn-inline btn-remove"><i class="far fa-trash-can redColor"></i></button></td>
	</tr>
{strip}