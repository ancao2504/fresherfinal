{* Added by Vu Mai on 2023-02-27 to render record table list *}

{strip}
	{foreach key=index item=jsModel from=$SCRIPTS}
		<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
	{/foreach}

	<input type="hidden" id="pageStartRange" value="{$PAGING_MODEL->getRecordStartRange()}" />
	<input type="hidden" id="pageEndRange" value="{$PAGING_MODEL->getRecordEndRange()}" />
	<input type="hidden" id="previousPageExist" value="{$PAGING_MODEL->isPrevPageExists()}" />
	<input type="hidden" id="nextPageExist" value="{$PAGING_MODEL->isNextPageExists()}" />
	<input type="hidden" id="totalCount" value="{$LISTVIEW_COUNT}" />
	<input type="hidden" value="{$ORDER_BY}" id="orderBy">
	<input type="hidden" value="{$SORT_ORDER}" id="sortOrder">
	<input type="hidden" id="totalCount" value="{$LISTVIEW_COUNT}" />
	<input type='hidden' value="{$PAGE_NUMBER}" id='pageNumber'>
	<input type='hidden' value="{$PAGING_MODEL->getPageLimit()}" id='pageLimit'>
	<input type="hidden" value="{$LISTVIEW_ENTRIES_COUNT}" id="noOfEntries">
	<input type="hidden" value="{$QUICK_PREVIEW_ENABLED}">

	<div id="listViewContent" class="listViewPageDiv">
		{assign var=RECORD_COUNT value=$LISTVIEW_ENTRIES_COUNT}
		{include file="modules/CPTelesales/tpl/TelesalesCustomerListPagging.tpl" SHOWPAGEJUMP=true}

		<div class="box-body">
			<div id="table-content" class="table-container">
				<form name='list' id='listedit' action='' onsubmit="return false;">
					<table id="listview-table" class="table {if $LISTVIEW_ENTRIES_COUNT eq '0'}listview-table-norecords {/if} listview-table ">
						<thead>
							<tr class="listViewContentHeader">
								<th></th>

								{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
									{if $SEARCH_MODE_RESULTS || ($LISTVIEW_HEADER->getFieldDataType() eq 'multipicklist')}
										{assign var=NO_SORTING value=1}
									{else}
										{assign var=NO_SORTING value=0}
									{/if}

									<th {if $COLUMN_NAME eq $LISTVIEW_HEADER->get('name')} nowrap="nowrap" {/if}>
										<a href="#" class="{if $NO_SORTING || $LISTVIEW_HEADER->get('displaytype') == 'TelesalesField'}noSorting nowrap{else}listViewContentHeaderValues{/if}" {if !$NO_SORTING}data-nextsortorderval="{if $COLUMN_NAME eq $LISTVIEW_HEADER->get('name')}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-fieldname="{$LISTVIEW_HEADER->get('name')}"{/if} data-field-id='{$LISTVIEW_HEADER->getId()}'>
											{if !$NO_SORTING && $LISTVIEW_HEADER->get('displaytype') != 'TelesalesField'}
												{if $COLUMN_NAME eq $LISTVIEW_HEADER->get('name')}
													<i class="far fa-sort {$FASORT_IMAGE}"></i>
												{else}
													<i class="far fa-sort customsort"></i>
												{/if}
											{/if}
											&nbsp;{vtranslate($LISTVIEW_HEADER->get('label'), $LISTVIEW_HEADER->getModuleName())}&nbsp;
										</a>
										{if $COLUMN_NAME eq $LISTVIEW_HEADER->get('name')}
											<a href="#" class="remove-sorting"><i class="far fa-remove"></i></a>
											{/if}
									</th>
								{/foreach}
							</tr>

							{if $MODULE_MODEL->isQuickSearchEnabled() && !$SEARCH_MODE_RESULTS}
								<tr class="searchRow">
									<th class="inline-search-btn">
										<div class="table-actions flex-actions">
											<i class="far fa-eraser clearFilters" onclick="Vtiger_List_Js.clearFilters()" aria-hidden="true" data-toggle="tooltip" title="{vtranslate("LBL_CLEAR_FILTERS", $MODULE)}"></i>
											<button class="btn btn-success btn-sm list-search">{vtranslate("LBL_SEARCH",$MODULE)}</button>
										</div>
									</th>
									{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
										<th>
											{if $LISTVIEW_HEADER->get('displaytype') != 'TelesalesField' && $LISTVIEW_HEADER->get('name') != $STATUS_FIELD}
												{assign var=FIELD_UI_TYPE_MODEL value=$LISTVIEW_HEADER->getUITypeModel()}
												{include file=vtemplate_path($FIELD_UI_TYPE_MODEL->getListSearchTemplateName(),$MODULE) FIELD_MODEL= $LISTVIEW_HEADER SEARCH_INFO=$SEARCH_DETAILS[$LISTVIEW_HEADER->getName()] USER_MODEL=$CURRENT_USER_MODEL}
												<input type="hidden" class="operatorValue" value="{$SEARCH_DETAILS[$LISTVIEW_HEADER->getName()]['comparator']}">
											{/if}
										</th>
									{/foreach}
								</tr>
							{/if}
						</thead>
						<tbody class="overflow-y">
							{foreach item=LISTVIEW_ENTRY from=$LISTVIEW_ENTRIES name=listview}
								{assign var=DATA_ID value=$LISTVIEW_ENTRY->getId()}
								{assign var=DATA_URL value=$LISTVIEW_ENTRY->getDetailViewUrl()}

								<tr class="listViewEntries" data-id='{$DATA_ID}' data-target-module="{$MODULE}" data-recordUrl='{$DATA_URL}&app={$SELECTED_MENU_CATEGORY}' id="{$MODULE}_listView_row_{$smarty.foreach.listview.index+1}" {if $MODULE eq 'Calendar'}data-recurring-enabled='{$LISTVIEW_ENTRY->isRecurringEnabled()}'{/if}>
									<td class = "listViewRecordActions">
										{include file="ListViewRecordActions.tpl"|vtemplate_path:$MODULE}
									</td>

									{if ($LISTVIEW_ENTRY->get('document_source') eq 'Google Drive' && $IS_GOOGLE_DRIVE_ENABLED) || ($LISTVIEW_ENTRY->get('document_source') eq 'Dropbox' && $IS_DROPBOX_ENABLED)}
										<input type="hidden" name="document_source_type" value="{$LISTVIEW_ENTRY->get('document_source')}">
									{/if}

									{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
										{assign var=LISTVIEW_HEADERNAME value=$LISTVIEW_HEADER->get('name')}
										{assign var=LISTVIEW_ENTRY_RAWVALUE value=$LISTVIEW_ENTRY->getRaw($LISTVIEW_HEADER->get('column'))}

										{if $LISTVIEW_HEADER->getFieldDataType() eq 'currency' || $LISTVIEW_HEADER->getFieldDataType() eq 'text'}
											{assign var=LISTVIEW_ENTRY_RAWVALUE value=$LISTVIEW_ENTRY->getTitle($LISTVIEW_HEADER)}
										{/if}

										{assign var=LISTVIEW_ENTRY_VALUE value=$LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME)}

										<td class="listViewEntryValue" data-name="{$LISTVIEW_HEADER->get('name')}" title="{$LISTVIEW_ENTRY->getTitle($LISTVIEW_HEADER)}" data-rawvalue="{$LISTVIEW_ENTRY_RAWVALUE}" data-field-type="{$LISTVIEW_HEADER->getFieldDataType()}">
											<span class="fieldValue">
												{strip}
													<span class="value">
														{assign var=LAST_CALL_INFO value=CPTelesales_Telesales_Model::getLastCallInfoAccordingRecord($DATA_ID, $MODULE)}

														{if ($LISTVIEW_HEADER->isNameField() eq true or $LISTVIEW_HEADER->get('uitype') eq '4') and $MODULE_MODEL->isListViewNameFieldNavigationEnabled() eq true }
															<a href="{$LISTVIEW_ENTRY->getDetailViewUrl()}&app={$SELECTED_MENU_CATEGORY}">{$LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME)}</a>

															{if $MODULE eq 'Products' &&$LISTVIEW_ENTRY->isBundle()}
																&nbsp;-&nbsp;<i class="mute">{vtranslate('LBL_PRODUCT_BUNDLE', $MODULE)}</i>
															{/if}

														{else if $LISTVIEW_HEADERNAME == 'full_name'}
															<a href="{$LISTVIEW_ENTRY->getDetailViewUrl()}&app={$SELECTED_MENU_CATEGORY}">{$LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME)}</a>

														{else if $MODULE_MODEL->getName() eq 'Documents' && $LISTVIEW_HEADERNAME eq 'document_source'}
															{$LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME)}
														{else if $LISTVIEW_HEADERNAME == 'call_count'}
															{CPTelesales_Telesales_Model::getCallCountAccordingRecord($DATA_ID, $MODULE)}
														{else if $LISTVIEW_HEADERNAME == 'last_call_time'}
															{$LAST_CALL_INFO.date_start}
														{else if $LISTVIEW_HEADERNAME == 'last_call_result'}
															{assign var=TEXT_COLOR value=Settings_Picklist_Module_Model::getTextColor($CALL_RESULT_LIST[$LAST_CALL_INFO.events_call_result].color)}

															<span class="picklist-color pull-left" style="background-color:{$CALL_RESULT_LIST[$LAST_CALL_INFO.events_call_result].color};color:{$TEXT_COLOR}">
																{$CALL_RESULT_LIST[$LAST_CALL_INFO.events_call_result].label}
															</span>
														{else}
															{if $LISTVIEW_HEADER->get('uitype') eq '72'}
																{assign var=CURRENCY_SYMBOL_PLACEMENT value={$CURRENT_USER_MODEL->get('currency_symbol_placement')}}

																{if $CURRENCY_SYMBOL_PLACEMENT eq '1.0$'}
																	{$LISTVIEW_ENTRY_VALUE}{$LISTVIEW_ENTRY->get('currencySymbol')}
																{else}
																	{$LISTVIEW_ENTRY->get('currencySymbol')}{$LISTVIEW_ENTRY_VALUE}
																{/if}


															{else if strpos($LISTVIEW_HEADER->get('name'), 'salutationtype') !== false}
																{vtranslate($LISTVIEW_ENTRY_VALUE)}
															{else if $LISTVIEW_HEADER->get('uitype') eq '71'}
																{assign var=CURRENCY_SYMBOL value=$LISTVIEW_ENTRY->get('userCurrencySymbol')}

																{if $LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME) neq NULL}
																	{CurrencyField::appendCurrencySymbol($LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME), $CURRENCY_SYMBOL)}
																{/if}
															{else if $LISTVIEW_HEADER->getFieldDataType() eq 'picklist'}
																{if $LISTVIEW_ENTRY->get('activitytype') eq 'Task'}
																	{assign var=PICKLIST_FIELD_ID value={$LISTVIEW_HEADER->getId()}}
																{else}
																	{if $LISTVIEW_HEADER->getName() eq 'taskstatus'}
																		{assign var="EVENT_STATUS_FIELD_MODEL" value=Vtiger_Field_Model::getInstance('eventstatus', Vtiger_Module_Model::getInstance('Events'))}
																		{if $EVENT_STATUS_FIELD_MODEL}
																			{assign var=PICKLIST_FIELD_ID value={$EVENT_STATUS_FIELD_MODEL->getId()}}
																		{else}
																			{assign var=PICKLIST_FIELD_ID value={$LISTVIEW_HEADER->getId()}}
																		{/if}
																	{else}
																		{assign var=PICKLIST_FIELD_ID value={$LISTVIEW_HEADER->getId()}}
																	{/if}
																{/if}

																<span {if !empty($LISTVIEW_ENTRY_VALUE)} class="picklist-color picklist-{$PICKLIST_FIELD_ID}-{Vtiger_Util_Helper::convertSpaceToHyphen($LISTVIEW_ENTRY_RAWVALUE)}" {/if}> {$LISTVIEW_ENTRY_VALUE} </span>
															{else if $LISTVIEW_HEADER->getFieldDataType() eq 'multipicklist'}
																{include file="layouts/v7/modules/Vtiger/uitypes/MultiPicklistFormatedValue.tpl" RAW_VALUES_STRING=$LISTVIEW_ENTRY->getRaw($LISTVIEW_HEADER->getName()) FIELD_MODEL=$LISTVIEW_HEADER}
															{else if $LISTVIEW_HEADER->getFieldDataType() eq 'owner'}
																{Vtiger_Owner_UIType::getCurrentOwnersForDisplay($LISTVIEW_ENTRY_RAWVALUE, false, true)}
															{else if $LISTVIEW_HEADER->getFieldDataType() eq 'phone'}
																<span class="value text-nowrap" data-field-type="phone">
																	<span>{$LISTVIEW_ENTRY_VALUE}</span>

																{if PBXManager_Logic_Helper::isRelateModuleField($LISTVIEW_HEADERNAME)}
																	{assign var='RELATED_MODULE_NAME' value=PBXManager_Logic_Helper::getModuleNameFromRelateModuleFieldName($LISTVIEW_HEADERNAME)}
																	{assign var='RELATED_RECORD_ID' value=PBXManager_Logic_Helper::getRecordIdFromRelateModuleFieldName($LISTVIEW_HEADERNAME, $LISTVIEW_ENTRY)}

																	{if PBXManager_Logic_Helper::isClick2CallEnabled($RELATED_MODULE_NAME)}
																		{PBXManager_Logic_Helper::renderButtonCall($LISTVIEW_ENTRY_VALUE, $RELATED_RECORD_ID)}
																	{/if}
																{else}
																	{if PBXManager_Logic_Helper::isClick2CallEnabled($MODULE)}
																		{PBXManager_Logic_Helper::renderButtonCall($LISTVIEW_ENTRY_RAWVALUE, $LISTVIEW_ENTRY->getId())}
																	{/if}
																{/if}
																</span>


															{else if ($MODULE === 'Calendar' || $MODULE === 'Events') && $LISTVIEW_HEADER->get('name') === 'location' }
																{if $LISTVIEW_ENTRY_VALUE}
																	<a href="javascript:void(0)" onclick="GoogleMaps.showMaps('{$LISTVIEW_ENTRY_VALUE}')">{$LISTVIEW_ENTRY_VALUE}</a>
																{/if}
															{else}
																{$LISTVIEW_ENTRY_VALUE}
															{/if}
														{/if}
													</span>
												{/strip}
											</span>

											{if $LISTVIEW_ENTRY->isEditable() && $LISTVIEW_HEADER->isEditable() eq 'true' && $LISTVIEW_HEADER->isAjaxEditable() eq 'true'}
												<span class="hide edit">
												</span>
											{/if}
										</td>
									{/foreach}
								</tr>
							{/foreach}
							
							{if $LISTVIEW_ENTRIES_COUNT eq '0'}
								<tr class="emptyRecordsDiv">
									{assign var=COLSPAN_WIDTH value={count($LISTVIEW_HEADERS)}+1}
									<td colspan="{$COLSPAN_WIDTH}">
										<div class="emptyRecordsContent">
											{vtranslate('LBL_NO')} {vtranslate($MODULE, $MODULE)} {vtranslate('LBL_FOUND')}.
										</div>
									</td>
								</tr>
							{/if}
						</tbody>
					</table>
				</form>
			</div>
			<div id="scroller_wrapper" class="bottom-fixed-scroll">
				<div id="scroller" class="scroller-div"></div>
			</div>
		</div>
	</div>
{/strip}