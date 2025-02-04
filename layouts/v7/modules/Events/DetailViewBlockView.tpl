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
{if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
   <input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}' />
{/if}
{include file='DetailViewBlockView.tpl'|@vtemplate_path:'Vtiger' RECORD_STRUCTURE=$RECORD_STRUCTURE MODULE_NAME=$MODULE_NAME}

{* Removed custom invitees template by Hieu Nguyen on 2019-11-22 as it's already handled by custom code structure *}
<div class="block block_LBL_INVITE_USER_BLOCK">
    {assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
    {assign var="IS_HIDDEN" value=false}
    {assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
</div>
{* End Hieu Nguyen *}
{/strip}