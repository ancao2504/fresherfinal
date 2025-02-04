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
	{include file="partials/EditViewContents.tpl"|@vtemplate_path:'Vtiger'}
	<div name='editContent'>
        {* Removed custom invitees template by Hieu Nguyen on 2019-11-21 as it's already handled by custom code structure *}
        <input type="hidden" name="recurringEditMode" value="" />

        <!--Confirmation modal for updating Recurring Events-->
        {* Modified by Hieu Ngyen on 2020-03-17 to load template from extenal file for common usage *}
        {include file="layouts/v7/modules/Calendar/RecurringEditView.tpl" HIDE=true}
        {* End Hieu Nguyen *}
        <!--Confirmation modal for updating Recurring Events--> 
        {* End Hieu Nguyen *}
	</div>
{/strip}