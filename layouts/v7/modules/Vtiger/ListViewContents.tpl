{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* modules/Vtiger/views/List.php *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{include file="PicklistColorMap.tpl"|vtemplate_path:$MODULE}

{* Modified by Hieu Nguyen on 2018-08-23 to load custom CSS and JS *}
{assign var="CUSTOM_CSS_FILE" value="modules/$MODULE/resources/ListView.css"}
{assign var="CUSTOM_JS_FILE" value="modules/$MODULE/resources/ListView.js"}

{if file_exists($CUSTOM_CSS_FILE)}
    <link type="text/css" rel="stylesheet" href="{$CUSTOM_CSS_FILE}"/>
{/if}

{if file_exists($CUSTOM_JS_FILE)}
    <script type="text/javascript" src="{$CUSTOM_JS_FILE}"></script> 
{/if}
{* End Hieu Nguyen *}

{* Added by Hieu Nguyen on 2019-11-05 to load module custom listview header *}
{assign var="CUSTOM_HEADER" value="modules/$MODULE/tpls/ListViewCustomHeader.tpl"}

{if file_exists($CUSTOM_HEADER)}
    {include file=$CUSTOM_HEADER}
{/if}
{* End Hieu Nguyen *}

<div class="col-sm-12 col-xs-12 ">
	{if $MODULE neq 'EmailTemplates' && $SEARCH_MODE_RESULTS neq true}
		{assign var=LEFTPANELHIDE value=$CURRENT_USER_MODEL->get('leftpanelhide')}
		<div class="essentials-toggle" title="{vtranslate('LBL_LEFT_PANEL_SHOW_HIDE', 'Vtiger')}">
			<span class="essentials-toggle-marker far {if $LEFTPANELHIDE eq '1'}fa-chevron-right{else}fa-chevron-left{/if} cursorPointer"></span>
		</div>
	{/if}
	<input type="hidden" name="view" id="view" value="{$VIEW}" />
	<input type="hidden" name="cvid" value="{$VIEWID}" />
	<input type="hidden" name="pageStartRange" id="pageStartRange" value="{$PAGING_MODEL->getRecordStartRange()}" />
	<input type="hidden" name="pageEndRange" id="pageEndRange" value="{$PAGING_MODEL->getRecordEndRange()}" />
	<input type="hidden" name="previousPageExist" id="previousPageExist" value="{$PAGING_MODEL->isPrevPageExists()}" />
	<input type="hidden" name="nextPageExist" id="nextPageExist" value="{$PAGING_MODEL->isNextPageExists()}" />
	<input type="hidden" name="alphabetSearchKey" id="alphabetSearchKey" value= "{$MODULE_MODEL->getAlphabetSearchField()}" />
	<input type="hidden" name="Operator" id="Operator" value="{$OPERATOR}" />
	<input type="hidden" name="totalCount" id="totalCount" value="{$LISTVIEW_COUNT}" />
	<input type='hidden' name="pageNumber" value="{$PAGE_NUMBER}" id='pageNumber'>
	<input type='hidden' name="pageLimit" value="{$PAGING_MODEL->getPageLimit()}" id='pageLimit'>
	<input type="hidden" name="noOfEntries" value="{$LISTVIEW_ENTRIES_COUNT}" id="noOfEntries">
	<input type="hidden" name="currentSearchParams" value="{Vtiger_Util_Helper::toSafeHTML(Zend_JSON::encode($SEARCH_DETAILS))}" id="currentSearchParams" />
	<input type="hidden" name="currentTagParams" value="{Vtiger_Util_Helper::toSafeHTML(Zend_JSON::encode($TAG_DETAILS))}" id="currentTagParams" />
	<input type="hidden" name="noFilterCache" value="{$NO_SEARCH_PARAMS_CACHE}" id="noFilterCache" >
	<input type="hidden" name="orderBy" value="{$ORDER_BY}" id="orderBy">
	<input type="hidden" name="sortOrder" value="{$SORT_ORDER}" id="sortOrder">
	<input type="hidden" name="list_headers" value='{$LIST_HEADER_FIELDS}'/>
	<input type="hidden" name="tag" value="{$CURRENT_TAG}" />
	<input type="hidden" name="folder_id" value="{$FOLDER_ID}" />
	<input type="hidden" name="folder_value" value="{$FOLDER_VALUE}" />
	<input type="hidden" name="viewType" value="{$VIEWTYPE}" />
	<input type="hidden" name="app" id="appName" value="{$SELECTED_MENU_CATEGORY}">
	<input type="hidden" id="isListViewQuickEditSupported" value="{if $MODULE_MODEL->isListViewQuickEditAllowed()}yes{else}no{/if}" />  {* Refactored function name by Hieu Nguyen on 2020-01-16 *}
	{if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
		<input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}' />
	{/if}
	{if !$SEARCH_MODE_RESULTS}
		{include file="ListViewActions.tpl"|vtemplate_path:$MODULE}
	{/if}

	<div id="table-content" class="table-container">
		<form name='list' id='listedit' action='' onsubmit="return false;">
			<table id="listview-table" class="table {if $LISTVIEW_ENTRIES_COUNT eq '0'}listview-table-norecords {/if} listview-table ">
				<thead>
					<tr class="listViewContentHeader">
						<th>
							{if !$SEARCH_MODE_RESULTS}
					<div class="table-actions">
						<div class="dropdown" style="float:left;">
							<span class="input dropdown-toggle" data-toggle="dropdown" title="{vtranslate('LBL_CLICK_HERE_TO_SELECT_ALL_RECORDS',$MODULE)}">
								<input class="listViewEntriesMainCheckBox" type="checkbox">
							</span>
						</div>
						{if $MODULE_MODEL->isFilterColumnEnabled()}
							<div id="listColumnFilterContainer">
								<div class="listColumnFilter {if $CURRENT_CV_MODEL and !($CURRENT_CV_MODEL->isCvEditable())}disabled{/if}"  
									 {if $CURRENT_CV_MODEL->isCvEditable()}
										 title="{vtranslate('LBL_CLICK_HERE_TO_MANAGE_LIST_COLUMNS',$MODULE)}"
									 {else}
										 {if $CURRENT_CV_MODEL->get('viewname') eq 'All' and !$CURRENT_USER_MODEL->isAdminUser()} 
											 title="{vtranslate('LBL_SHARED_LIST_NON_ADMIN_MESSAGE',$MODULE)}"
										 {elseif !$CURRENT_CV_MODEL->isMine()}
											 {assign var=CURRENT_CV_USER_ID value=$CURRENT_CV_MODEL->get('userid')}
											 {if !Vtiger_Functions::isUserExist($CURRENT_CV_USER_ID)}
												 {assign var=CURRENT_CV_USER_ID value=Users::getRootAdminId()}
											 {/if}
											 title="{vtranslate('LBL_SHARED_LIST_OWNER_MESSAGE',$MODULE, getUserFullName($CURRENT_CV_USER_ID))}"
										 {/if}
									 {/if}
									 {if $MODULE eq 'Documents'}style="width: 10%;"{/if}
									 data-toggle="tooltip" data-placement="bottom" data-container="body">
									<i class="far fa-th-large"></i>
								</div>
							</div>
						{/if}

                        {* Added by Hieu Nguyen on 2020-03-10 to show kanban shortcut *}
                        {assign var="KANBAN_CONFIG" value=getGlobalVariable('kanbanConfig')}

                        {if in_array($MODULE, $KANBAN_CONFIG.enabled_modules)}
                            <div class="kanban-shortcut">
                                <a href="index.php?module=CPKanban&view=List&source_module={$MODULE}&app=OPERATION" target="_blank" data-toggle="tooltip" title="{vtranslate('CPKanban', 'CPKanban')}"> {* Modified by Phu Vo on 2021.12.14 *}
                                    <i class="far fa-columns"></i>
                                </a>
                            </div>
                        {/if}
                        {* End Hieu Nguyen *}
					</div>
				{elseif $SEARCH_MODE_RESULTS}
					{vtranslate('LBL_ACTIONS',$MODULE)}
				{/if}
				</th>
				{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
					{if $SEARCH_MODE_RESULTS || ($LISTVIEW_HEADER->getFieldDataType() eq 'multipicklist')}
						{assign var=NO_SORTING value=1}
					{else}
						{assign var=NO_SORTING value=0}
					{/if}
					<th {if $COLUMN_NAME eq $LISTVIEW_HEADER->get('name')} nowrap="nowrap" {/if}>
						<a href="#" class="{if $NO_SORTING}noSorting{else}listViewContentHeaderValues{/if}" {if !$NO_SORTING}data-nextsortorderval="{if $COLUMN_NAME eq $LISTVIEW_HEADER->get('name')}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-columnname="{$LISTVIEW_HEADER->get('name')}"{/if} data-field-id='{$LISTVIEW_HEADER->getId()}'>
							{if !$NO_SORTING}
								{if $COLUMN_NAME eq $LISTVIEW_HEADER->get('name')}
									<i class="far fa-sort {$FASORT_IMAGE}"></i>
								{else}
									<i class="far fa-sort customsort"></i>
								{/if}
							{/if}
							&nbsp;{vtranslate($LISTVIEW_HEADER->get('label'), $LISTVIEW_HEADER->getModuleName())}&nbsp;
						</a>
						{if $COLUMN_NAME eq $LISTVIEW_HEADER->get('name')}
							<a href="#" class="removeSorting"><i class="far fa-remove"></i></a>
							{/if}
					</th>
				{/foreach}
				</tr>

				{if $MODULE_MODEL->isQuickSearchEnabled() && !$SEARCH_MODE_RESULTS}
					<tr class="searchRow">
						{* Modified by Phu Vo on 2019.08.28 to add clear filter button *}
						<th class="inline-search-btn">
							<div class="table-actions flex-actions">
								<i class="far fa-eraser clearFilters" onclick="Vtiger_List_Js.clearFilters()" aria-hidden="true" data-toggle="tooltip" title="{vtranslate("LBL_CLEAR_FILTERS", $MODULE)}"></i>
								<button class="btn btn-success btn-sm" data-trigger="listSearch">{vtranslate("LBL_SEARCH",$MODULE)}</button>
							</div>
						</th>
						{* End Phu Vo *}
					{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
						<th>
							{assign var=FIELD_UI_TYPE_MODEL value=$LISTVIEW_HEADER->getUITypeModel()}
							{include file=vtemplate_path($FIELD_UI_TYPE_MODEL->getListSearchTemplateName(),$MODULE) FIELD_MODEL= $LISTVIEW_HEADER SEARCH_INFO=$SEARCH_DETAILS[$LISTVIEW_HEADER->getName()] USER_MODEL=$CURRENT_USER_MODEL}
							<input type="hidden" class="operatorValue" value="{$SEARCH_DETAILS[$LISTVIEW_HEADER->getName()]['comparator']}">
						</th>
					{/foreach}
					</tr>
				{/if}
				</thead>
				<tbody class="overflow-y">
					{foreach item=LISTVIEW_ENTRY from=$LISTVIEW_ENTRIES name=listview}
						{assign var=DATA_ID value=$LISTVIEW_ENTRY->getId()}
						{assign var=DATA_URL value=$LISTVIEW_ENTRY->getDetailViewUrl()}
						{if $SEARCH_MODE_RESULTS && $LISTVIEW_ENTRY->getModuleName() == "ModComments"}
							{assign var=RELATED_TO value=$LISTVIEW_ENTRY->get('related_to_model')}
							{assign var=DATA_ID value=$RELATED_TO->getId()}
							{assign var=DATA_URL value=$RELATED_TO->getDetailViewUrl()}
						{/if}
						<tr class="listViewEntries" data-id='{$DATA_ID}' data-recordUrl='{$DATA_URL}&app={$SELECTED_MENU_CATEGORY}' id="{$MODULE}_listView_row_{$smarty.foreach.listview.index+1}" {if $MODULE eq 'Calendar'}data-recurring-enabled='{$LISTVIEW_ENTRY->isRecurringEnabled()}'{/if}>
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
										{* Modified by Phu Vo on 2021.27.07 to remove spaces between HTML elements *}
										{strip}
											<span class="value">
												{if ($LISTVIEW_HEADER->isNameField() eq true or $LISTVIEW_HEADER->get('uitype') eq '4') and $MODULE_MODEL->isListViewNameFieldNavigationEnabled() eq true }
													<a href="{$LISTVIEW_ENTRY->getDetailViewUrl()}&app={$SELECTED_MENU_CATEGORY}">{$LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME)}</a>
													{if $MODULE eq 'Products' &&$LISTVIEW_ENTRY->isBundle()}
														&nbsp;-&nbsp;<i class="mute">{vtranslate('LBL_PRODUCT_BUNDLE', $MODULE)}</i>
													{/if}
												{* Added by Phu Vo on 2021.09.30 to display full_name field as navigation link *}
												{else if $LISTVIEW_HEADERNAME == 'full_name'}
													<a href="{$LISTVIEW_ENTRY->getDetailViewUrl()}&app={$SELECTED_MENU_CATEGORY}">{$LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME)}</a>
												{* End by Phu Vo *}
												{else if $MODULE_MODEL->getName() eq 'Documents' && $LISTVIEW_HEADERNAME eq 'document_source'}
													{$LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME)}
												{else}
													{if $LISTVIEW_HEADER->get('uitype') eq '72'}
														{assign var=CURRENCY_SYMBOL_PLACEMENT value={$CURRENT_USER_MODEL->get('currency_symbol_placement')}}
														{if $CURRENCY_SYMBOL_PLACEMENT eq '1.0$'}
															{$LISTVIEW_ENTRY_VALUE}{$LISTVIEW_ENTRY->get('currencySymbol')}
														{else}
															{$LISTVIEW_ENTRY->get('currencySymbol')}{$LISTVIEW_ENTRY_VALUE}
														{/if}

													{* Added by Hieu Nguyen on 2020-12-02 to translate salutation *}
													{else if strpos($LISTVIEW_HEADER->get('name'), 'salutationtype') !== false}
														{vtranslate($LISTVIEW_ENTRY_VALUE)}
													{* End Hieu Nguyen *}
													
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
													{* Added by Hieu Nguyen on 2019-05-24 to display custom owner field *}
													{else if $LISTVIEW_HEADER->getFieldDataType() eq 'owner'}
														{Vtiger_Owner_UIType::getCurrentOwnersForDisplay($LISTVIEW_ENTRY_RAWVALUE, false, true)}
													{* End Hieu Nguyen *}

													{* Added by Hieu Nguyen on 2019-09-30 to support click-to-call. Modified by Phu Vo on 2019.11.25 to pass record id *}
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
													{* End Hieu Nguyen *}

													{* Added by Phu Vo on 2020.03.04 to support google map link with Calendar location field *}
													{else if ($MODULE === 'Calendar' || $MODULE === 'Events') && $LISTVIEW_HEADER->get('name') === 'location' }
														{if $LISTVIEW_ENTRY_VALUE}
															<a href="javascript:void(0)" onclick="GoogleMaps.showMaps('{$LISTVIEW_ENTRY_VALUE}')">{$LISTVIEW_ENTRY_VALUE}</a>
														{/if}
													{* End Phu Vo *}

													{else}
														{$LISTVIEW_ENTRY_VALUE}
													{/if}
												{/if}
											</span>
                                        {/strip}
                                        {* End Phu Vo *}
                                    </span>
                                    {if $LISTVIEW_ENTRY->isEditable() && $LISTVIEW_HEADER->isEditable() eq 'true' && $LISTVIEW_HEADER->isAjaxEditable() eq 'true'}	{* Modified by Hieu Nguyen on 2022-10-11 to prevent Quick Edit when record is not editable *}
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
                                    {assign var=SINGLE_MODULE value="SINGLE_$MODULE"}
                                    <!-- Modified by Kelvin Thang -- Date: 2018-08-03-->
                                    <!--{vtranslate('LBL_NO')} {vtranslate($MODULE, $MODULE)} {vtranslate('LBL_FOUND')}.-->
                                    {vtranslate('LBL_NO_RECORDS_FOUND')}.
                                    {if $IS_CREATE_PERMITTED}
                                        <a href="{$MODULE_MODEL->getCreateRecordUrl()}"> {vtranslate('LBL_CREATE')}</a>
                                        {if Users_Privileges_Model::isPermitted($MODULE, 'Import') && $LIST_VIEW_MODEL->isImportEnabled()}
                                            {vtranslate('LBL_OR', $MODULE)}
                                            <a href="#" onclick="return Vtiger_Import_Js.triggerImportAction()">{vtranslate('LBL_IMPORT', $MODULE)}</a>
                                            {vtranslate($MODULE, $MODULE)}
                                        {else}
                                            {vtranslate($SINGLE_MODULE, $MODULE)}
                                        {/if}
                                    {/if}
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
