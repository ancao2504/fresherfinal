{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
{* modules/Calendar/views/ViewTypes.php *}
{strip}
<div class="sidebar-widget-contents" name='calendarViewTypes'>
	<div id="calendarview-feeds">
		<ul class="list-group feedslist">
		{foreach item=VIEWINFO from=$VIEWTYPES name=calendarview} {* Modified by Hieu Nguyen on 2019-11-14 to display all saved calendar views *}
			<li class="activitytype-indicator calendar-feed-indicator container-fluid" style="background-color: {$VIEWINFO['color']};">
				{* Modified by Phu Vo on 2021.05.21 to change Calendar feed indicator style *}
				<input type="checkbox" {if $VIEWINFO['visible'] == '1'}checked{/if} class="toggleCalendarFeed cursorPointer" data-calendar-view-id="{$VIEWINFO['id']}"
					data-calendar-sourcekey="{$VIEWINFO['module']}_{$VIEWINFO['fieldname']}{if $VIEWINFO['conditions']['name'] neq ''}_{$VIEWINFO['conditions']['name']}{/if}" data-calendar-feed="{$VIEWINFO['module']}" 
					data-calendar-feed-color="{$VIEWINFO['color']}" data-calendar-fieldlabel="{vtranslate($VIEWINFO['fieldlabel'], $VIEWINFO['module'])}" 
					data-calendar-fieldname="{$VIEWINFO['fieldname']}" title="{vtranslate($VIEWINFO['module'],$VIEWINFO['module'])}" data-calendar-type="{$VIEWINFO['type']}" 
					data-calendar-feed-textcolor="white" data-calendar-feed-conditions='{$VIEWINFO['conditions']['rules']}'\
				/>
				
				{* [Calendar] Request #421: Modified by Phu Vo on 2020.03.16 to translate case by case for module Calendar*}
				{if $VIEWINFO['module'] == 'Calendar'}
					{assign var=PREFIX value=vtranslate('Tasks', $VIEWINFO['module'])}
				{else}
					{assign var=PREFIX value=vtranslate($VIEWINFO['module'], $VIEWINFO['module'])}
				{/if}

				{if $VIEWINFO['conditions']['name'] neq ''}
					{assign var=INDICATOR_TITLE value=sprintf("%s (%s)", $PREFIX, vtranslate($VIEWINFO['conditions']['name'],$MODULE))}
				{else}
					{assign var=INDICATOR_TITLE value=$PREFIX}
				{/if}

				{if $VIEWINFO['fieldlabel'] neq ''}
					{assign var=INDICATOR_LABEL value=sprintf("%s - %s", $INDICATOR_TITLE, vtranslate($VIEWINFO['fieldlabel'], $VIEWINFO['module']))}
				{else}
					{assign var=INDICATOR_LABEL value=$INDICATOR_TITLE}
				{/if}

				<span data-toggle="tooltip" title="{$INDICATOR_LABEL}">{$INDICATOR_LABEL}</span>
				{* End Phu Vo *}
				<span class="activitytype-actions pull-right">
					<button class="btn btn-link dropdown-toggle" data-toggle="dropdown">
						<i class="far fa-ellipsis-v"></i>
					</button>
					<ul class="dropdown-menu" role="menu">
						<li>
							<a href="javascript:void(0)" class="editCalendarFeedColor cursorPointer">
								<i class="far fa-pen"></i> {vtranslate('LBL_EDIT_FEED')}
							</a>
						</li>
						<li>
							<a href="javascript:void(0)" class="redColor deleteCalendarFeed cursorPointer">
								<i class="far fa-trash-alt"></i> {vtranslate('LBL_DELETE_FEED')}
							</a>
						</li>
					</ul>
				</span>
				{* End Phu Vo *}
			</li>
		{/foreach}
		</ul>

		{assign var=INVISIBLE_CALENDAR_VIEWS_EXISTS value='false'}
		{if $ADDVIEWS}
			{assign var=INVISIBLE_CALENDAR_VIEWS_EXISTS value='true'}
		{/if}
		<input type="hidden" class="invisibleCalendarViews" value="{$INVISIBLE_CALENDAR_VIEWS_EXISTS}" />
		{*end*}
		<ul class="hide dummy">
			<li class="activitytype-indicator calendar-feed-indicator feed-indicator-template container-fluid">
				{* Modified by Phu Vo on 2021.05.21 to change Calendar feed indicator style *}
				<input class="toggleCalendarFeed cursorPointer" type="checkbox" data-calendar-sourcekey="" data-calendar-feed="" 
					data-calendar-feed-color="" data-calendar-fieldlabel="" 
					data-calendar-fieldname="" title="" data-calendar-type=""
					data-calendar-feed-textcolor="white"
				/>
				<span></span>
				<span class="activitytype-actions pull-right">
					<button class="btn btn-link dropdown-toggle" data-toggle="dropdown">
						<i class="far fa-ellipsis-v"></i>
					</button>
					<ul class="dropdown-menu" role="menu">
						<li>
							<a href="javascript:void(0)" class="editCalendarFeedColor cursorPointer">
								<i class="far fa-pen"></i> {vtranslate('LBL_EDIT_FEED')}
							</a>
						</li>
						<li>
							<a href="javascript:void(0)" class="redColor deleteCalendarFeed cursorPointer">
								<i class="far fa-trash-alt"></i> {vtranslate('LBL_DELETE_FEED')}
							</a>
						</li>
					</ul>
				</span>
				{* End Phu Vo *}
			</li>
		</ul>
	</div>
</div>
{/strip}