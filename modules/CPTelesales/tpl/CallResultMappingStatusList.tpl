{*
	Name: CallResultMappingStatusList.tpl
	Author: Vu Mai
	Date: 2022-08-16
	Purpose: Render template for Call Result Mapping Status List
*}

{strip}
	{if !empty($CALL_RESULT_LIST)}
		<table id="call-result-to-status-mapping-table" class="table">
			<thead>
				<tr class="listViewHeaders">
					<th style="width:50%"><span>{vtranslate('LBL_TELESALES_CAMPAIGN_CUSTOMER_IF_THE_CALL_RESULT', 'CPTelesales')}</span></th>
					<th style="width:50%" class="text-center"><span>{vtranslate('LBL_TELESALES_CAMPAIGN_THEN_CUSTOMER_STATUS_IN_TELESALE_CAMPAIGN', 'CPTelesales')}</span></th>
				</tr>
				<tbody id="call-result-to-status-mapping-list">
					{foreach key=CALL_RESULT_KEY item=CALL_RESULT_ITEM from=$CALL_RESULT_LIST}
					{assign var=TEXT_COLOR value=Settings_Picklist_Module_Model::getTextColor($CALL_RESULT_ITEM.color)}

						<tr class="call-result-to-status-mapping-item">
							<td class="textOverflowEllipsis">
									<span class="picklist-color pull-left" style="background-color:{$CALL_RESULT_ITEM.color};color:{$TEXT_COLOR}">
										{$CALL_RESULT_ITEM.label}
									</span>
								</span>
							</td>
							<td class="fieldValue" class="text-center">
								<select name="customer_status" data-call-result="{$CALL_RESULT_KEY}" class="inputElement campaign-purpose-in-mapping-table text-left" >
									{foreach key=KEY item=LABEL from=$CUSTOMER_STATUS_LABEL_KEY_LIST}
										<option value="{$KEY}" {if $KEY == $CALL_RESULT_TO_STATUS_MAPPING_LIST.$CALL_RESULT_KEY}selected{/if}>{$LABEL}</option>
									{/foreach}
								</select>
							</td>
						</tr>
					{/foreach}
				</tbody>
			</thead>
		</table>
	{/if}
{/strip}