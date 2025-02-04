{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is: vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}
{strip}
	<div class="">
	{assign var=ASSIGNED_USER_ID value=$FIELD_MODEL->get('name')}

    {* Comment out by Hieu Nguyen on 2019-05-14 to boost performance *}
	{*{assign var=ALL_ACTIVEUSER_LIST value=$USER_MODEL->getAccessibleUsers()}*}
    {* End Hieu Nguyen *}

	{assign var=SEARCH_VALUES value=explode(',',$SEARCH_INFO['searchValue'])}
	{assign var=SEARCH_VALUES value=array_map("trim",$SEARCH_VALUES)}

    {* Comment out by Hieu Nguyen on 2019-05-14 to boost performance *}
	{*{if $FIELD_MODEL->get('uitype') eq '52' || $FIELD_MODEL->get('uitype') eq '77'}
		{assign var=ALL_ACTIVEGROUP_LIST value=array()}
	{else}
		{assign var=ALL_ACTIVEGROUP_LIST value=$USER_MODEL->getAccessibleGroups()}
	{/if}

	{assign var=ACCESSIBLE_USER_LIST value=$USER_MODEL->getAccessibleUsersForModule($MODULE)}
	{assign var=ACCESSIBLE_GROUP_LIST value=$USER_MODEL->getAccessibleGroupForModule($MODULE)}*}
    {* End Hieu Nguyen *}

	{* Edit by Phu Vo on 2019.06.14 to init Custom Owner template *}
	<input type="text" autocomplete="off" class="select2 listSearchContributor {$ASSIGNED_USER_ID}" style="width: 100%" data-rule-required="true" data-rule-main-owner="true"
		data-fieldtype="owner" data-fieldname="{$ASSIGNED_USER_ID}" data-name="{$ASSIGNED_USER_ID}" name="{$ASSIGNED_USER_ID}"
	/>
	{* End init Custom Owner template *}

	</div>
{/strip}