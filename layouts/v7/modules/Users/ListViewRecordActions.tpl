{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* Mofied by Hieu Nguyen on 2021-07-13 to display basic buttons at Record rows in ListView *}

{strip}
	<div class="table-actions">
        {if $IS_MODULE_EDITABLE && $LISTVIEW_ENTRY->get('status') eq 'Active'}
            <span>
                <a class="far fa-pen icon action edit" data-id="{$LISTVIEW_ENTRY->getId()}" href="{$LISTVIEW_ENTRY->getEditViewUrl()}&parentblock=LBL_USER_MANAGEMENT" name="editlink" title="{vtranslate('LBL_EDIT', $MODULE)}"></a>
            </span>
        {/if}

        {if $IS_MODULE_DELETABLE && $LISTVIEW_ENTRY->getId() != $USER_MODEL->getId()}
            {if $LISTVIEW_ENTRY->get('status') eq 'Active'}
                <span>
                    <a class="far fa-lock icon action deactivate" onclick="javascript:Settings_Users_List_Js.triggerDeleteUser('{$LISTVIEW_ENTRY->getDeleteUrl()}');" title="{vtranslate('LBL_DEACTIVATE_USER', $MODULE)}"></a>
                </span>
            {else}
                <span>
                    <a class="far fa-trash-alt icon action delete" onclick="javascript:Settings_Users_List_Js.triggerDeleteUser('{$LISTVIEW_ENTRY->getDeleteUrl()}', 'true');" title="{vtranslate('LBL_DELETE_USER', $MODULE)}"></a>
                </span>
            {/if}
        {/if}

        {* Modified by Hieu Nguyen on 2019-11-05 to load module custom listview row actions *}
        {assign var="CUSTOM_ROW_ACTIONS" value="modules/$MODULE/tpls/ListViewCustomRowActions.tpl"}

        {if file_exists($CUSTOM_ROW_ACTIONS)}
            {include file=$CUSTOM_ROW_ACTIONS}
        {/if}
        {* End Hieu Nguyen *}

		<span class="more dropdown action">
			<span href="javascript:;" class="dropdown-toggle" data-toggle="dropdown">
				<i title="{vtranslate("LBL_MORE_OPTIONS",$MODULE)}" class="far fa-ellipsis-v-alt icon"></i>
			</span>
			<ul class="dropdown-menu">
				{if $LISTVIEW_ENTRY->get('status') eq 'Active'}
					{if Users_Privileges_Model::isPermittedToChangeUsername($LISTVIEW_ENTRY->getId())}
						<li><a onclick="Settings_Users_List_Js.triggerChangeUsername('{$LISTVIEW_ENTRY->getChangeUsernameUrl()}');">{vtranslate('LBL_CHANGE_USERNAME', $MODULE)}</a></li>
					{/if}
					<li><a onclick="Settings_Users_List_Js.triggerChangePassword('{$LISTVIEW_ENTRY->getChangePwdUrl()}');">{vtranslate('LBL_CHANGE_PASSWORD', $MODULE)}</a></li>
				{/if}

                {if $LISTVIEW_ENTRY->get('status') neq 'Active'}
                    <span>
                        <a onclick="Settings_Users_List_Js.restoreUser({$LISTVIEW_ENTRY->getId()}, event);">{vtranslate('LBL_RESTORE_USER', $MODULE)}</a>
                    </span>
                {/if}

                {* Added by Hieu Nguyen on 2019-11-05 to load module custom listview row advanced actions *}
                {assign var="CUSTOM_ROW_ADVANCED_ACTIONS" value="modules/$MODULE/tpls/ListViewCustomRowAdvancedActions.tpl"}

                {if file_exists($CUSTOM_ROW_ADVANCED_ACTIONS)}
                    {include file=$CUSTOM_ROW_ADVANCED_ACTIONS}
                {/if}
                {* End Hieu Nguyen *}
			</ul>
		</span>
	</div>
{/strip}