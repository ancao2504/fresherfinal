{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}

{strip}
	<div class="col-sm-12 col-xs-12 module-action-bar clearfix coloredBorderTop">
		<div class="module-action-content clearfix {$MODULE}-module-action-content">
			<div class="col-lg-7 col-md-7 module-breadcrumb module-breadcrumb-{$smarty.request.view} transitionsAllHalfSecond">
				{assign var=MODULE_MODEL value=Vtiger_Module_Model::getInstance($MODULE)}
				{if $MODULE_MODEL->getDefaultViewName() neq 'List'}
					{assign var=DEFAULT_FILTER_URL value=$MODULE_MODEL->getDefaultUrl()}
				{else}
					{assign var=DEFAULT_FILTER_ID value=$MODULE_MODEL->getDefaultCustomFilter()}
					{if $DEFAULT_FILTER_ID}
						{assign var=CVURL value="&viewname="|cat:$DEFAULT_FILTER_ID}
						{assign var=DEFAULT_FILTER_URL value=$MODULE_MODEL->getListViewUrl()|cat:$CVURL}
					{else}
						{assign var=DEFAULT_FILTER_URL value=$MODULE_MODEL->getListViewUrlWithAllFilter()}
					{/if}
				{/if}

				{* Modified by Hieu Nguyen on 2021-10-25 to disable click on non-entity module name *}
				<a title="{vtranslate($MODULE, $MODULE)}" href="{if $MODULE_MODEL->isEntityModule()}{$DEFAULT_FILTER_URL}&app={$SELECTED_MENU_CATEGORY}{else}javascript:void(0){/if}">
					<h4 class="module-title pull-left text-uppercase">{vtranslate($MODULE, $MODULE)}</h4>&nbsp;&nbsp; {* Modified by Tung Nguyen to remove space because Module title is applied text-overflow ellipsis when width under max-width *}
				</a>
				{* End Hieu Nguyen *}
				
				{* Added by Hieu Nguyen on 2021-10-25 to support custom view title *}
				{if $CUSTOM_TITLE}
					<p class="current-filter-name filter-name pull-left cursorPointer" title="{$CUSTOM_TITLE}">
						<span class="far fa-angle-right pull-left" aria-hidden="true"></span>
						<a href="{$smarty.server.REQUEST_URI}">&nbsp;&nbsp;{$CUSTOM_TITLE}&nbsp;&nbsp;</a>
					</p>
				{/if}
				{* End Hieu Nguyen *}
				
				{if $smarty.session.lvs.$MODULE.viewname}
					{assign var=VIEWID value=$smarty.session.lvs.$MODULE.viewname}
				{/if}

				{* Modified by Hieu Nguyen on 2018-09-24 to show custom view title *}
				{if $VIEWID && in_array($smarty.request.view, array('List', 'Edit', 'Detail'))}
					{foreach item=FILTER_TYPES from=$CUSTOM_VIEWS}
						{foreach item=FILTERS from=$FILTER_TYPES}
							{if $FILTERS->get('cvid') eq $VIEWID}
								{assign var=CVNAME value=$FILTERS->get('viewname')}
								{if $CVNAME == 'All'}{assign var=CVNAME value=vtranslate('All')}{/if}
								{break}
							{/if}
						{/foreach}
					{/foreach}
					<p class="current-filter-name filter-name pull-left cursorPointer" title="{$CVNAME}"><span class="far fa-angle-right pull-left" aria-hidden="true"></span><a href='{$MODULE_MODEL->getListViewUrl()}&viewname={$VIEWID}&app={$SELECTED_MENU_CATEGORY}'>&nbsp;&nbsp;{$CVNAME}&nbsp;&nbsp;</a> </p>
				{/if}
				{assign var=SINGLE_MODULE_NAME value='SINGLE_'|cat:$MODULE}
				{if $RECORD and $smarty.request.view eq 'Edit'}
					<p class="current-filter-name filter-name pull-left "><span class="far fa-angle-right pull-left" aria-hidden="true"></span><a title="{$RECORD->get('label')}">&nbsp;&nbsp;{vtranslate('LBL_EDITING', $MODULE)} : {$RECORD->get('label')} &nbsp;&nbsp;</a></p>
				{else if $smarty.request.view eq 'Edit'}
					<p class="current-filter-name filter-name pull-left "><span class="far fa-angle-right pull-left" aria-hidden="true"></span><a>&nbsp;&nbsp;{vtranslate('LBL_ADDING_NEW', $MODULE)}&nbsp;&nbsp;</a></p>
				{/if}
				{if $smarty.request.view eq 'Detail'}
					<p class="current-filter-name filter-name pull-left"><span class="far fa-angle-right pull-left" aria-hidden="true"></span><a title="{$RECORD->get('label')}">&nbsp;&nbsp;{$RECORD->get('label')} &nbsp;&nbsp;</a></p>
				{/if}

				{assign var=TITLE value=vtranslate(strtoupper("LBL_VIEW_{$smarty.request.view}_TITLE"), $MODULE)}
				{if $TITLE && strpos($TITLE, 'LBL') === false}
					<p class="current-filter-name filter-name pull-left"><span class="far fa-angle-right pull-left" aria-hidden="true"></span><a title="{$TITLE}">&nbsp;&nbsp;{$TITLE}</a></p>
				{/if}
				{* End Hieu Nguyen *}
			</div>
			<div class="col-lg-5 col-md-5 pull-right">
				<div id="appnav" class="navbar-right">
					<ul class="nav navbar-nav">
						{foreach item=BASIC_ACTION from=$MODULE_BASIC_ACTIONS}
							{if $BASIC_ACTION->getLabel() == 'LBL_IMPORT'}
								<li>
									<button id="{$MODULE}_basicAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($BASIC_ACTION->getLabel())}" type="button" class="btn addButton btn-default module-buttons" 
											{if stripos($BASIC_ACTION->getUrl(), 'javascript:')===0}  
												onclick='{$BASIC_ACTION->getUrl()|substr:strlen("javascript:")};'
											{else}
												onclick="Vtiger_Import_Js.triggerImportAction('{$BASIC_ACTION->getUrl()}')"
											{/if}>
										<div class="far {$BASIC_ACTION->getIcon()}" aria-hidden="true"></div>&nbsp;&nbsp;
										{vtranslate($BASIC_ACTION->getLabel(), $MODULE)}
									</button>
								</li>
							{else}
								<li>
									<button id="{$MODULE}_listView_basicAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($BASIC_ACTION->getLabel())}" type="button" class="btn addButton btn-primary module-buttons" 
											{if stripos($BASIC_ACTION->getUrl(), 'javascript:')===0}  
												onclick='{$BASIC_ACTION->getUrl()|substr:strlen("javascript:")};'
											{else} 
												onclick='window.location.href = "{$BASIC_ACTION->getUrl()}&app={$SELECTED_MENU_CATEGORY}"'
											{/if}>
										<div class="far {$BASIC_ACTION->getIcon()}" aria-hidden="true"></div>&nbsp;&nbsp;
										{vtranslate($BASIC_ACTION->getLabel(), $MODULE)}
									</button>
								</li>
							{/if}
						{/foreach}
						{if $MODULE_SETTING_ACTIONS|@count gt 0}
							<li>
								<div class="settingsIcon">
									<button type="button" class="btn btn-default module-buttons dropdown-toggle" data-toggle="dropdown" aria-expanded="false" title="{vtranslate('LBL_SETTINGS', $MODULE)}">
										<span class="far fa-wrench" aria-hidden="true"></span>&nbsp;{vtranslate('LBL_CUSTOMIZE', 'Reports')}&nbsp; <i class="far fa-angle-down"></i>
									</button>
									<ul class="detailViewSetting dropdown-menu">
										<!--Modified by Kelvin Thang - OnlineCRM -->
										{foreach item=SETTING from=$MODULE_SETTING_ACTIONS}
											{* Added by Hieu Nguyen on 2022-05-10 to support access Layout Editor for developer only by config *}
											{assign var='LAYOUT_EDITOR_CONFIG' value=getGlobalVariable('layoutEditorConfig')}

											{if $SETTING->getLabel() == 'LBL_EDIT_FIELDS' && !isDeveloperMode()}
												{if !empty($LAYOUT_EDITOR_CONFIG['modules_allow_developer_only']) && in_array($MODULE_NAME, $LAYOUT_EDITOR_CONFIG['modules_allow_developer_only']) }
													{continue}
												{/if}
											{/if}
											{* End Hieu Nguyen *}

											<li id="{$MODULE_NAME}_listview_advancedAction_{$SETTING->getLabel()}">
												<a href={$SETTING->getUrl()}>
                                                    {* Modified by Hieu Nguyen on 2020-10-23 to show menu icon *}
                                                    {if !empty($SETTING->getIcon())}<i class="{$SETTING->getIcon()}"></i>&nbsp;{/if}
                                                    {vtranslate($SETTING->getLabel(), $MODULE_NAME)}
                                                    {* End Hieu Nguyen *}
                                                </a>
											</li>
										{/foreach}
									</ul>
								</div>
							</li>
						{/if}
					</ul>
				</div>
			</div>
		</div>
		{if $FIELDS_INFO neq null}
			<script type="text/javascript">
				var uimeta = (function () {
					var fieldInfo = {$FIELDS_INFO};
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
						},
					};
				})();
			</script>
		{/if}
	</div>     
{/strip}
