{* Added by Vu Mai on 2022-07-27 to render a single row for inbound routing table *}

{strip}
	<tr>
		<td class="fieldValue">
			<input type="text" name="inbound_hotline[]" value="{$HOTLINE_NUMBER}" class="hotline inputElement" data-rule-required="true" data-rule-dynamic-table-duplicate-check="true" />
		</td>
		<td class="fieldValue">
			<select name="inbound_role[]" class="inbound-role inputElement" data-fieldtype="picklist" data-rule-required="true">
				{foreach from=$ROLE_LIST key=ROLE_ID item=ROLE}
					<option value="{$ROLE_ID}" {if $ROLE_ID == $SELECTED_ROLE_ID}selected{/if}>{$ROLE->get('rolename')}</option>
				{/foreach}
			</select>
		</td>
		<td class="text-center">
			<button type="button" class="btn btn-outline-danger btnDelRow" title="{vtranslate('LBL_DELETE', 'Vtiger')}">
				<i class="far fa-trash-alt"></i>
			</button>
		</td>
	</tr>
{/strip}