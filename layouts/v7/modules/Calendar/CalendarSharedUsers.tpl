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
{assign var=SHARED_USER_INFO value= Zend_Json::encode($SHAREDUSERS_INFO)}
{assign var=CURRENT_USER_ID value= $CURRENTUSER_MODEL->getId()}
<input type="hidden" id="sharedUsersInfo" value= {Zend_Json::encode($SHAREDUSERS_INFO)} />
<div class="sidebar-widget-contents" name='calendarViewTypes'>
	<div id="calendarview-feeds">
		<ul class="list-group feedslist">
			<li class="activitytype-indicator calendar-feed-indicator" style="background-color: {$SHAREDUSERS_INFO[$CURRENT_USER_ID]['color']};">
				{* Modified by Phu Vo on 2021.05.21 to change Calendar feed indicator style *}
				<input class="toggleCalendarFeed cursorPointer" type="checkbox" data-calendar-sourcekey="Events_{$CURRENT_USER_ID}" data-calendar-feed="Events" 
					data-calendar-feed-color="{$SHAREDUSERS_INFO[$CURRENT_USER_ID]['color']}" data-calendar-fieldlabel="{vtranslate('LBL_MINE',$MODULE)}" 
					data-calendar-userid="{$CURRENT_USER_ID}" data-calendar-group="false" data-calendar-feed-textcolor="white"
				/>
				<span>
					{vtranslate('LBL_MINE',$MODULE)}
				</span>
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
					</ul>
				</span>
				{* End Phu Vo *}
			</li>
			{assign var=INVISIBLE_CALENDAR_VIEWS_EXISTS value='false'}
			{foreach key=ID item=USER from=$SHAREDUSERS}
				{if $SHAREDUSERS_INFO[$ID]['visible'] != '0'}
					<li class="activitytype-indicator calendar-feed-indicator" style="background-color: {$SHAREDUSERS_INFO[$ID]['color']};">
						{* Modified by Phu Vo on 2021.05.21 to change Calendar feed indicator style *}
						<input class="toggleCalendarFeed cursorPointer" type="checkbox" data-calendar-sourcekey="Events_{$ID}" data-calendar-feed="Events" 
							data-calendar-feed-color="{$SHAREDUSERS_INFO[$ID]['color']}" data-calendar-fieldlabel="{$USER}" 
							data-calendar-userid="{$ID}" data-calendar-group="false" data-calendar-feed-textcolor="white"
						/>
						<span class="userName textOverflowEllipsis" data-toggle="tooltip" title="{$USER}">
							{$USER}
						</span>
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
				{else}
					{assign var=INVISIBLE_CALENDAR_VIEWS_EXISTS value='true'}
				{/if}
			{/foreach}
			{foreach key=ID item=GROUP from=$SHAREDGROUPS}
				{if $SHAREDUSERS_INFO[$ID]['visible'] != '0'}
					<li class="activitytype-indicator calendar-feed-indicator" style="background-color: {$SHAREDUSERS_INFO[$ID]['color']};">
						{* Modified by Phu Vo on 2021.05.21 to change Calendar feed indicator style *}
						<input class="toggleCalendarFeed cursorPointer" type="checkbox" data-calendar-sourcekey="Events_{$ID}" data-calendar-feed="Events" 
							data-calendar-feed-color="{$SHAREDUSERS_INFO[$ID]['color']}" data-calendar-fieldlabel="{$GROUP}" 
							data-calendar-userid="{$ID}" data-calendar-group="true" data-calendar-feed-textcolor="white"
						/>
						<span class="userName textOverflowEllipsis" data-toggle="tooltip" title="{$GROUP}">
							{$GROUP}
						</span>
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
				{else}
					{assign var=INVISIBLE_CALENDAR_VIEWS_EXISTS value='true'}
				{/if}
			{/foreach}
		</ul>
		<ul class="hide dummy">
			<li class="activitytype-indicator calendar-feed-indicator feed-indicator-template">
				{* Modified by Phu Vo on 2021.05.21 to change Calendar feed indicator style *}
				<input class="toggleCalendarFeed cursorPointer"
					type="checkbox" data-calendar-sourcekey="" data-calendar-feed="Events" 
					data-calendar-feed-color="" data-calendar-fieldlabel="" 
					data-calendar-userid="" data-calendar-group="" data-calendar-feed-textcolor="white"
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
		<input type="hidden" class="invisibleCalendarViews" value="{$INVISIBLE_CALENDAR_VIEWS_EXISTS}" />
	</div>
</div>
{/strip}
