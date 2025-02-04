{* Added by Vu Mai on 2022-07-27 to render a single row for outbound routing table *}

{strip}
	<tr>
		<td class="fieldValue input-align-top">
			<input type="text" name="outbound_hotline[]" value="{$HOTLINE_NUMBER}" class="hotline inputElement" data-rule-required="true" data-rule-dynamic-table-duplicate-check="true" />
		</td>
		<td class="fieldValue">
			<select name="outbound_roles[]" class="outbound-roles inputElement" multiple="true" data-rule-required="true">
				{foreach from=$ROLE_LIST key=ROLE_ID item=ROLE}
					<option value="{$ROLE_ID}" {if in_array($ROLE_ID, $SELECTED_ROLE_IDS)}selected{/if}>{$ROLE->get('rolename')}</option>
				{/foreach}
			</select>
		</td>
		<td class="text-center input-align-top">
			<button type="button" class="btn btn-outline-danger btnDelRow" title="{vtranslate('LBL_DELETE', 'Vtiger')}">
				<i class="far fa-trash-alt"></i>
			</button>
		</td>
	</tr>
{/strip}