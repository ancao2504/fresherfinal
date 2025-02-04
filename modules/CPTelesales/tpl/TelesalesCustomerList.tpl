{* Added by Vu Mai on 2022-10-26 to render customer table list *}

{strip}
	<div id="listViewContent" class="listViewPageDiv">
		{assign var=RECORD_COUNT value=$TOTAL_RECORD}
		{include file="modules/CPTelesales/tpl/TelesalesCustomerListPagging.tpl" SHOWPAGEJUMP=true}

		<div class="box-body">
			<div id="table-content" class="table-container">
				<table id="listview-table"  class="table listview-table">
					<thead>
						<tr class="listViewHeaders">
							<th></th>

							{foreach key=FIELD_NAME item=FIELD_LABEL from=$CUSTOMER_FIELDS}
								<th class="nowrap">
									<a href="javascript:void(0);" class="listViewContentHeaderValues" data-nextsortorderval="{if $COLUMN_NAME eq $FIELD_NAME}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-fieldname="{$FIELD_NAME}">
										{if $COLUMN_NAME eq $FIELD_NAME}
											<i class="far fa-sort {$FASORT_IMAGE}"></i>
										{else}
											<i class="far fa-sort customsort"></i>
										{/if}
										&nbsp;
										{vtranslate($FIELD_LABEL)}
										&nbsp;{if $COLUMN_NAME eq $FIELD_NAME}<img class="{$SORT_IMAGE}">{/if}&nbsp;
									</a>
									{if $COLUMN_NAME eq $FIELD_NAME}
										<a href="#" class="remove-sorting"><i class="far fa-remove"></i></a>
									{/if}
								</th>	
							{/foreach}
						</tr>
						<tr class="searchRow">
							<th class="inline-search-btn"> 
								<div class="table-actions flex-actions">
									<i class="far fa-eraser clearFilters" aria-hidden="true" data-toggle="tooltip" title="{vtranslate("LBL_CLEAR_FILTERS", $MODULE)}"></i>
									<button class="btn btn-success btn-sm list-search">{vtranslate("LBL_SEARCH",$MODULE)}</button>
								</div>
							</th>
							{foreach key=FIELD_NAME item=FIELD_LABEL from=$CUSTOMER_FIELDS}
								<th>
									{include file="modules/CPTelesales/tpl/FilterFieldTemplate.tpl"}
								</th>
							{/foreach}
							<th></th>
						</tr>
					</thead>
					<tbody>
						{if count($CUSTOMER_LIST) > 0}
							{foreach item=CUSTOMER from=$CUSTOMER_LIST}
								<tr class="listViewEntries" data-id="{$CUSTOMER.campaign_id}" data-customer-id="{$CUSTOMER.customer_id}" data-recordUrl="{$CUSTOMER.detail_url}">
									<td class="related-list-actions">
										<span>
											<a class="mark-star actionImages mr-3 far icon action fa-star {if $CUSTOMER.starred}active{/if}" data-toggle="tooltip" title="{if $CUSTOMER.starred} {vtranslate('LBL_STARRED', $MODULE)} {else} {vtranslate('LBL_NOT_STARRED', $MODULE)}{/if}"></a>
										</span>
										<span class="actionImages">
											<a class="quick-view far fa-eye icon action" data-id="{$CUSTOMER.customer_id}" data-type="{$CUSTOMER.customer_type}" data-toggle="tooltip" title="{vtranslate('LBL_QUICK_VIEW')}"></a>
										</span>
									</td>
									{foreach key=FIELD_NAME item=FIELD_LABEL from=$CUSTOMER_FIELDS}
										{assign var=CUSTOMER_VALUE value=$CUSTOMER[$FIELD_NAME]}
					
										<td class="listViewEntryValue" nowrap {if $FIELD_NAME == 'full_name'}data-field-type="string"{/if}>
											<span class="fieldValue textOverflowEllipsis">
												{if $FIELD_NAME == 'full_name'}
													<span class="value">
														<a href="{$CUSTOMER.detail_url}" target="_blank">{$CUSTOMER_VALUE}</a>
													</span>
												{else if $FIELD_NAME == 'mobile'}
													{$CUSTOMER_VALUE}
													{assign var=CURRENT_USER_ID value=Users_Privileges_Model::getCurrentUserModel()->getId()}
													{assign var=IS_CAMPAIGN_ACTIVE value=$CAMPAIGN_INFO['status'] == 'Active'}
													{assign var=IS_CURRENT_USER_IN_USER_AGENT value=in_array($CURRENT_USER_ID, $USER_AGENT_IDS)}

													{if PBXManager_Logic_Helper::isClick2CallEnabled($CUSTOMER.customer_type) && $IS_CAMPAIGN_ACTIVE && $CURRENT_USER_ID == $CUSTOMER.assigned_user_id && $IS_CURRENT_USER_IN_USER_AGENT}
														{assign var=PHONE_RAW_VALUE value=$CUSTOMER_VALUE}
														<span class="value">
															{PBXManager_Logic_Helper::renderButtonCall($PHONE_RAW_VALUE, $CUSTOMER.customer_id)}
														</span>
													{/if}
												{else if $FIELD_NAME == 'status'}
													{assign var=LABEL value=CPTelesales_Logic_Helper::generateCustomerStatusLabelKey($CAMPAIGN_PURPOSE, $CUSTOMER_VALUE)}
													{assign var=COLOR value=$CUSTOMER_STATUS_LIST[$CUSTOMER_VALUE]['color']}
													{assign var=TEXT_COLOR value=Settings_Picklist_Module_Model::getTextColor($COLOR)}

													<span class="picklist-color" style="background-color:{$COLOR};color:{$TEXT_COLOR}">{vtranslate($LABEL, 'CampaignCustomerStatus')}</span>
												{else if $FIELD_NAME == 'last_call_result'}
													{assign var=TEXT_COLOR value=Settings_Picklist_Module_Model::getTextColor($CALL_RESULT_LIST[$CUSTOMER_VALUE].color)}

													<span class="picklist-color pull-left" style="background-color:{$CALL_RESULT_LIST[$CUSTOMER_VALUE].color};color:{$TEXT_COLOR}">
														{$CALL_RESULT_LIST[$CUSTOMER_VALUE].label}
													</span>
												{else if $FIELD_NAME == 'call_count'}
													{CPTelesales_Telesales_Model::getCallCountAccordingRecord($CUSTOMER.customer_id, $CUSTOMER.customer_type, $CUSTOMER.campaign_id)}	
												{else if $FIELD_NAME == 'salutationtype'}
													{vtranslate($CUSTOMER_VALUE)}
												{else if $FIELD_NAME == 'customer_type'}
													{vtranslate($CUSTOMER_VALUE, $CUSTOMER_VALUE)}
												{else if $FIELD_NAME == 'email'}
													<span class="value">
														<a class="emailField" data-rawvalue="{$CUSTOMER_VALUE}" onclick="Vtiger_Helper_Js.getInternalMailer({$CUSTOMER.customer_id},'email','{$CUSTOMER.customer_type}');">{$CUSTOMER_VALUE}</a>
													</span>
												{else}
													{$CUSTOMER_VALUE}
												{/if}
											</span>
										</td>
									{/foreach}
								</tr>
							{/foreach}
						{else}
						<tr class="emptyRecordsDiv">
							<td colspan="10">
								<div class="emptyRecordsContent">
									{vtranslate('LBL_RECORD_NOT_FOUND')}
								</div>
							</td>
						</tr>	
						{/if}
					</tbody>		
				</table>
			</div>	
		</div>
	</div>	
{/strip}