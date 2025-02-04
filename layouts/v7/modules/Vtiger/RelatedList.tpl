{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{strip}
	{assign var=RELATED_MODULE_NAME value=$RELATED_MODULE->get('name')}
	{include file="PicklistColorMap.tpl"|vtemplate_path:$MODULE LISTVIEW_HEADERS=$RELATED_HEADERS}
	<div class="relatedContainer">
		{assign var=IS_RELATION_FIELD_ACTIVE value="{if $RELATION_FIELD}{$RELATION_FIELD->isActiveField()}{else}false{/if}"}
		<input type="hidden" name="currentPageNum" value="{$PAGING->getCurrentPage()}" />
		<input type="hidden" name="relatedModuleName" class="relatedModuleName" value="{$RELATED_MODULE_NAME}" />
		<input type="hidden" value="{$ORDER_BY}" id="orderBy">
		<input type="hidden" value="{$SORT_ORDER}" id="sortOrder">
		<input type="hidden" value="{$RELATED_ENTIRES_COUNT}" id="noOfEntries">
		<input type='hidden' value="{$PAGING->getPageLimit()}" id='pageLimit'>
		<input type='hidden' value="{$PAGING->get('page')}" id='pageNumber'>
		<input type="hidden" value="{$PAGING->isNextPageExists()}" id="nextPageExist"/>
		<input type='hidden' value="{$TOTAL_ENTRIES}" id='totalCount'>
		<input type='hidden' value="{$TAB_LABEL}" id='tab_label' name='tab_label'>
		<input type='hidden' value="{$IS_RELATION_FIELD_ACTIVE}" id='isRelationFieldActive'>

		{include file="partials/RelatedListHeader.tpl"|vtemplate_path:$RELATED_MODULE_NAME}
		{if $MODULE eq 'Products' && $RELATED_MODULE_NAME eq 'Products' && $TAB_LABEL === 'Product Bundles' && $RELATED_LIST_LINKS}
			<div data-module="{$MODULE}" style = "margin-left:20px">
				{assign var=IS_VIEWABLE value=$PARENT_RECORD->isBundleViewable()}
				<input type="hidden" class="isShowBundles" value="{$IS_VIEWABLE}">
				<label class="showBundlesInInventory checkbox"><input type="checkbox" {if $IS_VIEWABLE}checked{/if} value="{$IS_VIEWABLE}">&nbsp;&nbsp;{vtranslate('LBL_SHOW_BUNDLE_IN_INVENTORY', $MODULE)}</label>
			</div>
		{/if}

		<div class="relatedContents col-lg-12 col-md-12 col-sm-12 table-container">
			<div class="bottomscroll-div">
				<table id="listview-table" class="table listview-table">
					<thead>
						<tr class="listViewHeaders">
							<th style="min-width:100px">
							</th>
							{* Modified by Phu Vo on 2021.11.20 to fix issue calendar date time field take 2 table cell *}
							{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
								{* hide time_start,time_end columns in the list as they are merged with with Start Date and End Date fields *}
								{if $HEADER_FIELD->get('column') eq 'time_start' or $HEADER_FIELD->get('column') eq 'time_end'}
								{else}
									<th class="nowrap">
										{if $HEADER_FIELD->get('column') eq "access_count" or $HEADER_FIELD->get('column') eq "idlists"}
											<a href="javascript:void(0);" class="noSorting">{vtranslate($HEADER_FIELD->get('label'), $RELATED_MODULE_NAME)}</a>
										{else}
											<a href="javascript:void(0);" class="listViewContentHeaderValues" data-nextsortorderval="{if $COLUMN_NAME eq $HEADER_FIELD->get('column')}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-fieldname="{$HEADER_FIELD->get('column')}">
												{if $COLUMN_NAME eq $HEADER_FIELD->get('column')}
													<i class="far fa-sort {$FASORT_IMAGE}"></i>
												{else}
													<i class="far fa-sort customsort"></i>
												{/if}
												&nbsp;
												{vtranslate($HEADER_FIELD->get('label'), $RELATED_MODULE_NAME)}
												&nbsp;{if $COLUMN_NAME eq $HEADER_FIELD->get('column')}<img class="{$SORT_IMAGE}">{/if}&nbsp;
											</a>
											{if $COLUMN_NAME eq $HEADER_FIELD->get('column')}
												<a href="#" class="removeSorting"><i class="far fa-remove"></i></a>
											{/if}
										{/if}
									</th>
								{/if}
							{/foreach}
							{* End Phu Vo *}
						</tr>
						<tr class="searchRow">
							{* Modified by Phu Vo on 2019.08.28 to add clear filter button *}
							<th class="inline-search-btn">
								<div class="table-actions flex-actions">
									<i class="far fa-eraser clearFilters" onclick="Vtiger_RelatedList_Js.clearFilters()" aria-hidden="true" data-toggle="tooltip" title="{vtranslate("LBL_CLEAR_FILTERS", $MODULE)}"></i>
									<button class="btn btn-success btn-sm" data-trigger="relatedListSearch">{vtranslate("LBL_SEARCH",$MODULE)}</button>
								</div>
							</th>
							{* End Phu Vo *}
							{* Modified by Phu Vo on 2021.11.20 to fix issue calendar date time field take 2 table cell *}
							{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
								{if $HEADER_FIELD->get('column') eq 'time_start' or $HEADER_FIELD->get('column') eq 'time_end'}
								{else}
									<th>
										{if $HEADER_FIELD->get('column') eq 'time_start' or $HEADER_FIELD->get('column') eq 'time_end' or $HEADER_FIELD->getFieldDataType() eq 'reference'}
										{else}
											{assign var=FIELD_UI_TYPE_MODEL value=$HEADER_FIELD->getUITypeModel()}
											{include file=vtemplate_path($FIELD_UI_TYPE_MODEL->getListSearchTemplateName(),$RELATED_MODULE_NAME) FIELD_MODEL= $HEADER_FIELD SEARCH_INFO=$SEARCH_DETAILS[$HEADER_FIELD->getName()] USER_MODEL=$USER_MODEL}
											<input type="hidden" class="operatorValue" value="{$SEARCH_DETAILS[$HEADER_FIELD->getName()]['comparator']}">
										{/if}
									</th>
								{/if}
							{/foreach}
							{* End Phu Vo *}
						</tr>
					</thead>
					{foreach item=RELATED_RECORD from=$RELATED_RECORDS}
						<tr class="listViewEntries" data-id='{$RELATED_RECORD->getId()}' 
							{if $RELATED_MODULE_NAME eq 'Calendar'}
								data-recurring-enabled='{$RELATED_RECORD->isRecurringEnabled()}'
								{assign var=DETAILVIEWPERMITTED value=isPermitted($RELATED_MODULE_NAME, 'DetailView', $RELATED_RECORD->getId())}
								{if $DETAILVIEWPERMITTED eq 'yes'}
									data-recordUrl='{$RELATED_RECORD->getDetailViewUrl()}'
								{/if}
							{else}
								data-recordUrl='{$RELATED_RECORD->getDetailViewUrl()}'
							{/if}>
							<td class="related-list-actions">
								<span class="actionImages">
                                    {* Added by Hieu Nguyen on 2021-01-21 to show related record detail *}
									<a name="relationDetail" data-url="{$RELATED_RECORD->getDetailViewUrl()}"><i class="far fa-eye" title="{vtranslate('LBL_DETAILS', $MODULE)}"></i></a>&nbsp;&nbsp;&nbsp;
                                    {* End Hieu Nguyen *}

									{if $IS_EDITABLE && $RELATED_RECORD->isEditable()}
										{if $RELATED_MODULE_NAME eq 'PriceBooks' AND (!empty($RELATED_HEADERS['listprice']) || !empty($RELATED_HEADERS['unit_price']))}
											{if !empty($RELATED_HEADERS['listprice'])}
												{assign var="LISTPRICE" value=CurrencyField::convertToUserFormat($RELATED_RECORD->get('listprice'), null, true)}
											{/if}
										{/if}
										{if $RELATED_MODULE_NAME eq 'PriceBooks'}
											<a data-url="index.php?module=PriceBooks&view=ListPriceUpdate&record={$PARENT_RECORD->getId()}&relid={$RELATED_RECORD->getId()}&currentPrice={$LISTPRICE}"
												class="editListPrice cursorPointer" data-related-recordid='{$RELATED_RECORD->getId()}' data-list-price={$LISTPRICE}
										{else if $MODULE eq 'Products' && $RELATED_MODULE_NAME eq 'Products' && $TAB_LABEL === 'Product Bundles' && $RELATED_LIST_LINKS && $PARENT_RECORD->isBundle()}
											{assign var=quantity value=$RELATED_RECORD->get($RELATION_FIELD->getName())}
											<a class="quantityEdit"
												data-url="index.php?module=Products&view=SubProductQuantityUpdate&record={$PARENT_RECORD->getId()}&relid={$RELATED_RECORD->getId()}&currentQty={$quantity}"
												onclick ="Products_Detail_Js.triggerEditQuantity('index.php?module=Products&view=SubProductQuantityUpdate&record={$PARENT_RECORD->getId()}&relid={$RELATED_RECORD->getId()}&currentQty={$quantity}');if(event.stopPropagation){ldelim}event.stopPropagation();{rdelim}else{ldelim}event.cancelBubble=true;{rdelim}"
										{else}
											<a name="relationEdit" data-url="{$RELATED_RECORD->getEditViewUrl()}"
										{/if}
										><i class="far fa-pen" title="{vtranslate('LBL_EDIT', $MODULE)}"></i></a> &nbsp;&nbsp;
									{/if}

									{if $IS_DELETABLE && $RELATED_RECORD->isDeletable()} {* [Core] Modified by Phu Vo on 2020.08.31 to disable remove relation on undeletable record*}
										<a class="relationDelete"><i title="{vtranslate('LBL_UNLINK', $MODULE)}" class="far fa-unlink"></i></a>
									{/if}

                                    {* Added by Hieu Nguyen on 2019-08-05 for custom row actions in related lists *}
                                    {assign var="CUSTOM_ROW_ACTIONS" value="modules/$MODULE/tpls/RelatedListCustomRowActions.tpl"}

                                    {if file_exists($CUSTOM_ROW_ACTIONS)}
                                        &nbsp;&nbsp; {include file=$CUSTOM_ROW_ACTIONS}
                                    {/if}
                                    {* End Hieu Nguyen *}
								</span>

							</td>
							{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
								{assign var=RELATED_HEADERNAME value=$HEADER_FIELD->get('name')}
								{assign var=RELATED_LIST_VALUE value=$RELATED_RECORD->get($RELATED_HEADERNAME)}
								{* Modified by Phu Vo on 2021.11.20 to fix issue calendar date time field take 2 table cell *}
								{if $HEADER_FIELD->get('column') eq 'time_start' or $HEADER_FIELD->get('column') eq 'time_end'}
								{else}
									<td class="relatedListEntryValues" title="{str_replace('"', '\'', strip_tags($RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)))}" data-field-name="{$RELATED_HEADERNAME}" data-field-type="{$HEADER_FIELD->getFieldDataType()}" nowrap> {* Modified by Phu Vo on 2019.10.10 to display single quotes in title *}   {* Modified by Hieu Nguyen on 2020-01-16 to show field name attribue *}
										<span class="value textOverflowEllipsis">
											{if $RELATED_MODULE_NAME eq 'Documents' && $RELATED_HEADERNAME eq 'document_source'}
												<center>{$RELATED_RECORD->get($RELATED_HEADERNAME)}</center>
											{else}
												{if $HEADER_FIELD->isNameField() eq true or $HEADER_FIELD->get('uitype') eq '4'}
													{* Modified by Hieu Nguyen on 2020-08-31 to display related activities subject and link *}
													{if ($RELATED_MODULE_NAME == 'Calendar' || $RELATED_MODULE_NAME == 'Events') && Calendar_Logic_Model::isRelatedActivityBusy($RELATED_RECORD->getId(), $PARENT_RECORD->getId())}
														{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}
													{else}
														<a href="{$RELATED_RECORD->getDetailViewUrl()}">{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}</a>
													{/if}
													{* End Hieu Nguyen *}
														
												{* Added by Phu Vo on 2021.09.30 to display full_name field as navigation link *}
												{else if $RELATED_HEADERNAME == 'full_name'}
													<a href="{$RELATED_RECORD->getDetailViewUrl()}&app={$SELECTED_MENU_CATEGORY}">{$RELATED_RECORD->get($RELATED_HEADERNAME)}</a>
												{* End by Phu Vo *}
												
												{elseif $RELATED_HEADERNAME eq 'access_count'}
													{$RELATED_RECORD->getAccessCountValue($PARENT_RECORD->getId())}
												{elseif $RELATED_HEADERNAME eq 'time_start' or $RELATED_HEADERNAME eq 'time_end'}
												{elseif $RELATED_MODULE_NAME eq 'PriceBooks' AND ($RELATED_HEADERNAME eq 'listprice' || $RELATED_HEADERNAME eq 'unit_price')}
													{if $RELATED_HEADERNAME eq 'listprice'}
														{assign var="LISTPRICE" value=CurrencyField::convertToUserFormat($RELATED_RECORD->get($RELATED_HEADERNAME), null, true)}
													{/if}
													{CurrencyField::convertToUserFormat($RELATED_RECORD->get($RELATED_HEADERNAME), null, true)}
												{elseif $HEADER_FIELD->get('uitype') eq '71' or $HEADER_FIELD->get('uitype') eq '72'}
													{assign var=CURRENCY_SYMBOL value=Vtiger_RelationListView_Model::getCurrencySymbol($RELATED_RECORD->get('id'), $HEADER_FIELD)}
													{assign var=CURRENCY_VALUE value=CurrencyField::convertToUserFormat($RELATED_RECORD->get($RELATED_HEADERNAME))}
													{if $HEADER_FIELD->get('uitype') eq '72'}
														{assign var=CURRENCY_VALUE value=CurrencyField::convertToUserFormat($RELATED_RECORD->get($RELATED_HEADERNAME), null, true)}
													{/if}
													{if Users_Record_Model::getCurrentUserModel()->get('currency_symbol_placement') eq '$1.0'}
														{$CURRENCY_SYMBOL}{$CURRENCY_VALUE}
													{else}
														{$CURRENCY_VALUE}{$CURRENCY_SYMBOL}
													{/if}
													{if $RELATED_HEADERNAME eq 'listprice'}
														{assign var="LISTPRICE" value=CurrencyField::convertToUserFormat($RELATED_RECORD->get($RELATED_HEADERNAME), null, true)}
													{/if}
												{else if $HEADER_FIELD->getFieldDataType() eq 'picklist'}
													{if $RELATED_MODULE_NAME eq 'Calendar' or $RELATED_MODULE_NAME eq 'Events'}
														{if $RELATED_RECORD->get('activitytype') eq 'Task'}
															{assign var=PICKLIST_FIELD_ID value={$HEADER_FIELD->getId()}}
														{else}
															{if $HEADER_FIELD->getName() eq 'taskstatus'}
																{assign var="EVENT_STATUS_FIELD_MODEL" value=Vtiger_Field_Model::getInstance('eventstatus', Vtiger_Module_Model::getInstance('Events'))}
																{if $EVENT_STATUS_FIELD_MODEL}
																	{assign var=PICKLIST_FIELD_ID value={$EVENT_STATUS_FIELD_MODEL->getId()}}
																{else} 
																	{assign var=PICKLIST_FIELD_ID value={$HEADER_FIELD->getId()}}
																{/if}
															{else}
																{assign var=PICKLIST_FIELD_ID value={$HEADER_FIELD->getId()}}
															{/if}
														{/if}
													{else}
														{assign var=PICKLIST_FIELD_ID value={$HEADER_FIELD->getId()}}
													{/if}
													<span {if !empty($RELATED_LIST_VALUE)} class="picklist-color picklist-{$PICKLIST_FIELD_ID}-{Vtiger_Util_Helper::convertSpaceToHyphen($RELATED_LIST_VALUE)}" {/if}> {$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)} </span>

												{* Added by Hieu Nguyen on 2019-11-22 to display reference field *}
												{else if $HEADER_FIELD->getFieldDataType() eq 'reference'}
													{assign var="RELATED_ID" value=$RELATED_RECORD->get($RELATED_HEADERNAME)} {* Updated by Phuc on 2020.02.18 to avoid case columnname and fieldname are difference *}
													{$RELATED_RECORD->getField($RELATED_HEADERNAME)->getDisplayValue($RELATED_ID)}
												{* End Hieu Nguyen *}

												{* Added by Phu Vo on 2019-06-17 to display custom owner field *}
												{else if $HEADER_FIELD->getFieldDataType() eq 'owner'}
													{assign var=OWNER_RAW_VALUE value=$RELATED_RECORD->getRaw($HEADER_FIELD->get('column'))}
													{Vtiger_Owner_UIType::getCurrentOwnersForDisplay($OWNER_RAW_VALUE, false, true)}
												{* End Phu Vo *}
												
												{* Added by Hieu Nguyen on 2022-07-05 to support click-to-call at Subpanels *}
												{else if $HEADER_FIELD->getFieldDataType() eq 'phone'}
													{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}

													{if PBXManager_Logic_Helper::isClick2CallEnabled($RELATED_MODULE_NAME)}
														{assign var=PHONE_RAW_VALUE value=$RELATED_RECORD->getRaw($HEADER_FIELD->get('column'))}
														{PBXManager_Logic_Helper::renderButtonCall($PHONE_RAW_VALUE, $RELATED_RECORD->getId())}
													{/if}
												{* End Hieu Nguyen *}

												{else}
													{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)} {* Reverted by Phu Vo *}
													{* Documents list view special actions "view file" and "download file" *}
													{if $RELATED_MODULE_NAME eq 'Documents' && $RELATED_HEADERNAME eq 'filename' && isPermitted($RELATED_MODULE_NAME, 'DetailView', $RELATED_RECORD->getId()) eq 'yes'}
														<span class="actionImages">
															{assign var=RECORD_ID value=$RELATED_RECORD->getId()}
															{assign var="DOCUMENT_RECORD_MODEL" value=Vtiger_Record_Model::getInstanceById($RECORD_ID)}
															{if $DOCUMENT_RECORD_MODEL->get('filename') && $DOCUMENT_RECORD_MODEL->get('filestatus')}
																<a name="viewfile" href="javascript:void(0)" data-filelocationtype="{$DOCUMENT_RECORD_MODEL->get('filelocationtype')}" data-filename="{$DOCUMENT_RECORD_MODEL->get('filename')}" onclick="Vtiger_Header_Js.previewFile(event)"><i title="{vtranslate('LBL_VIEW_FILE', $RELATED_MODULE_NAME)}" class="icon-picture alignMiddle"></i></a>&nbsp;
																{/if}
																{if $DOCUMENT_RECORD_MODEL->get('filename') && $DOCUMENT_RECORD_MODEL->get('filestatus') && $DOCUMENT_RECORD_MODEL->get('filelocationtype') eq 'I'}
																<a name="downloadfile" href="{$DOCUMENT_RECORD_MODEL->getDownloadFileURL()}"><i title="{vtranslate('LBL_DOWNLOAD_FILE', $RELATED_MODULE_NAME)}" class="icon-download-alt alignMiddle"></i></a>&nbsp;
																{/if}
														</span>
													{/if}
												{/if}
											{/if}
										</span>
									</td>
								{/if}
								{* End Phu Vo *}
							{/foreach}
						</tr>
					{/foreach}
				</table>
			</div>
		</div>
		<script type="text/javascript">
			var related_uimeta = (function () {
				var fieldInfo = {$RELATED_FIELDS_INFO};
				return {
					field: {
						get: function (name, property) {
							if (name && property === undefined) {
								return fieldInfo[name];
							}
							if (name && property) {
								return fieldInfo[name][property]
							}
						},
						isMandatory: function (name) {
							if (fieldInfo[name]) {
								return fieldInfo[name].mandatory;
							}
							return false;
						},
						getType: function (name) {
							if (fieldInfo[name]) {
								return fieldInfo[name].type
							}
							return false;
						}
					}
				};
			})();
		</script>
	</div>
{/strip}