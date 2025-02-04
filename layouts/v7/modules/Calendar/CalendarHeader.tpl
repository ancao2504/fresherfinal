{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}

{strip}
	<input type="hidden" name="is_record_creation_allowed" id="is_record_creation_allowed" value="{$IS_CREATE_PERMITTED}">
	<div class="col-sm-12 col-xs-12 module-action-bar clearfix">
		<div class="module-action-content clearfix coloredBorderTop">
			<div class="col-lg-5 col-md-5">
				<span>
					{assign var="VIEW_HEADER_LABEL" value="LBL_CALENDAR_VIEW"}
					{if $VIEW === 'SharedCalendar'}
						{assign var="VIEW_HEADER_LABEL" value="LBL_SHARED_CALENDAR"}
					{/if}
					<a href='javascript:void(0)'><h4 class="module-title pull-left"><span style="cursor: default;"> {strtoupper(vtranslate($VIEW_HEADER_LABEL, $MODULE))} </span></h4></a>
				</span>
			</div>
			<div class="col-lg-7 col-md-7 pull-right">
				<div id="appnav" class="navbar-right">
					<ul class="nav navbar-nav">
						{* Modified by Hieu Nguyen on 2022-09-06 to display create activity buttons based on user permission *}
						{if $IS_CREATE_PERMITTED}
							{foreach from=$MODULE_MODEL->getModuleBasicLinks(true) key=KEY item=LINK}
								<li>
									<button type="button" id="btn_{$LINK.linklabel}" class="btn addButton btn-primary module-buttons cursorPointer" onclick='{$LINK.linkurl}'>
										<i class="far {$LINK.linkicon}" aria-hidden="true"></i>&nbsp;&nbsp;{vtranslate($LINK.linklabel, $MODULE)}
									</button>
								</li>
							{/foreach}
						{/if}
						{* End Hieu Nguyen *}
						<li>
							<div class="settingsIcon">
								<button type="button" class="btn btn-default module-buttons dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
									<span class="far fa-wrench" aria-hidden="true" title="{vtranslate('LBL_SETTINGS', $MODULE)}"></span>&nbsp;&nbsp;{vtranslate('LBL_CUSTOMIZE', 'Reports')}&nbsp; <i class="far fa-angle-down"></i>
								</button>
								<ul class="detailViewSetting dropdown-menu">
									{foreach item=SETTING from=$MODULE_SETTING_ACTIONS}
										{* Added by Hieu Nguyen on 2022-05-10 to support access Layout Editor for developer only by config *}
										{assign var='LAYOUT_EDITOR_CONFIG' value=getGlobalVariable('layoutEditorConfig')}

										{if $SETTING->getLabel() == 'LBL_EDIT_FIELDS' && !isDeveloperMode()}
											{if !empty($LAYOUT_EDITOR_CONFIG['modules_allow_developer_only']) && in_array($MODULE_NAME, $LAYOUT_EDITOR_CONFIG['modules_allow_developer_only']) }
												{continue}
											{/if}
										{/if}
										{* End Hieu Nguyen *}

										{if $SETTING->getLabel() eq 'LBL_EDIT_FIELDS'}
										{* Updated by Phuc on 2019.10.28 to update label *}
											<li id="{$MODULE_NAME}_listview_advancedAction_{$SETTING->getLabel()}_Events"><a href="{$SETTING->getUrl()}&sourceModule=Events">{vtranslate('LBL_CUSTOMIZE_FOR_EVENT', 'Calendar')}</a></li> 
											<li id="{$MODULE_NAME}_listview_advancedAction_{$SETTING->getLabel()}_Calendar"><a href="{$SETTING->getUrl()}&sourceModule=Calendar">{vtranslate('LBL_CUSTOMIZE_FOR_TASK', 'Calendar')}</a></li>
										{* Ended by Phuc *}
										{else if $SETTING->getLabel() eq 'LBL_EDIT_WORKFLOWS'} 
											{* Modified by Phu Vo on 2020.12.18 to translate label button *}
											<li id="{$MODULE_NAME}_listview_advancedAction_{$SETTING->getLabel()}_WORKFLOWS"><a href="{$SETTING->getUrl()}&sourceModule=Events">{vtranslate('LBL_ACTIVITY_WORKFLOWS',$MODULE_NAME, ['%activity_type' => vtranslate('LBL_EVENTS', $MODULE_NAME)])}</a></li>	
											<li id="{$MODULE_NAME}_listview_advancedAction_{$SETTING->getLabel()}_WORKFLOWS"><a href="{$SETTING->getUrl()}&sourceModule=Calendar">{vtranslate('LBL_ACTIVITY_WORKFLOWS',$MODULE_NAME, ['%activity_type' => vtranslate('LBL_TASKS', 'Calendar')])}</a></li>
											{* End Phu Vo *}
										{else}
											<li id="{$MODULE_NAME}_listview_advancedAction_{$SETTING->getLabel()}"><a href={$SETTING->getUrl()}>{vtranslate($SETTING->getLabel(), $MODULE_NAME)}</a></li> {* Bug #386: Modified by Phu Vo on 2020.03.16 to fix could not translate label *}
										{/if}
									{/foreach}
									<li>
										<a>
											<span id="calendarview_basicaction_calendarsetting" onclick='Calendar_Calendar_Js.showCalendarSettings();' class="cursorPointer">
												{vtranslate('LBL_CALENDAR_SETTINGS', 'Calendar')}
											</span>
										</a>
									</li>
								</ul>
							</div>
						</li>
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

    {* Added by Hieu Nguyen to load custom header template *}
    {include file="modules/Calendar/tpls/CalendarViewCustomHeader.tpl"}
    {* End Hieu Nguyen *}
{/strip}