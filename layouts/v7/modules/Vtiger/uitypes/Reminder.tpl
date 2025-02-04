{*<!--
/* ***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ************************************************************************************/
-->*}

{strip}
{if !$REMINDER_VALUES}
	{assign var=REMINDER_VALUES value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'))}
{/if}
{if $REMINDER_VALUES eq ''}
    {assign var=DAYS value=0}
	{assign var=HOURS value=0}
	{assign var=MINUTES value=1}
{else}
    {assign var=DAY value=$REMINDER_VALUES[0]}
	{assign var=HOUR value=$REMINDER_VALUES[1]}
	{assign var=MINUTE value=$REMINDER_VALUES[2]}
{/if}

<div id="js-reminder-controls">
	<div style="float:left;margin-top: 1%;">
		<input type=hidden name=set_reminder value=0 />
		<input type=checkbox name=set_reminder {if $REMINDER_VALUES neq ''}checked{/if} value=1 />&nbsp;&nbsp;
	</div>
	<div id="js-reminder-selections" style="float:left;visibility:{if $REMINDER_VALUES neq ''}visible{else}collapse{/if};">
		{* Modified by Hieu Nguyen on 2020-02-20 to load time selector template from extenal file *}
        {include file="layouts/v7/modules/Vtiger/partials/ReminderFieldTimeSelector.tpl"}
        {* End Hieu Nguyen *}
	</div>
	<div class="clearfix"></div>
</div>
{/strip}