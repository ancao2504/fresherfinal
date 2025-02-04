{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* modules/Vtiger/views/MassActionAjax.php *}
    
{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
    <div class="modal-dialog">
        <div class="modal-content">
            <form class="form-horizontal" id="changeOwner" name="changeOwner" method="post" action="index.php">
                <input type="hidden" name="module" value="{$MODULE}" />
                <input type="hidden" name="action" value="TransferOwnership" />
            
                {assign var=HEADER_TITLE value={vtranslate('LBL_TRANSFER_OWNERSHIP', $MODULE)}}
                {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
                
                <div class="modal-body">
                    <div class="form-group">
                            <label class="col-lg-4 control-label">{vtranslate('LBL_SELECT_RELATED_MODULES',$MODULE)}<span class="redColor"> *</span></label> {* Add reddot by Phu Vo on 2019.06.20 *}
                            <div class="col-lg-6">
                                <select multiple class="form-control select2" id="related_modules" data-placeholder="{vtranslate('LBL_SELECT_RELATED_MODULES',$MODULE)}" name="related_modules[]" data-rule-required="true">
                                    {foreach item=RELATED_MODULE from=$RELATED_MODULES}
                                        {if !in_array($RELATED_MODULE->get('relatedModuleName'), $SKIP_MODULES)}
                                            <option value="{$RELATED_MODULE->get('relation_id')}">{vtranslate($RELATED_MODULE->get('label'), $RELATED_MODULE->getParentModuleName())}</option> {* Added by Phuc on 2020.07.09 to get label from parent module *}
                                        {/if}
                                    {/foreach}
                                </select>
                            </div>
                    </div>
                    <div class="form-group">
                            {* Modified by Phu Vo to process custom owner field *}
                            <label class="col-lg-4 control-label">{vtranslate('LBL_ASSIGNED_TO', $MODULE)}<span class="redColor"> *</span></label>
                            <div class="col-lg-6 fieldValue assigned_user_id">
                            
                                <input type="text" name="transferOwnerId" id="transferOwnerId"
                                    class="select2" multiple="false" data-user-only="false" 
                                    data-assignable-users-only="true"
                                    data-rule-required="true" data-rule-main-owner="true" style="width: 100%"
                                /> {* Added by Phuc on 2020.07.09 to fix with 100% *}

                                {* Comment out by Phu Vo on 2019-06-17 to boost performance *}
                                {* {assign var=ALL_ACTIVEUSER_LIST value=$USER_MODEL->getAccessibleUsers()}
                                {assign var=ALL_ACTIVEGROUP_LIST value=$USER_MODEL->getAccessibleGroups()}
                                {assign var=CURRENT_USER_ID value=$USER_MODEL->get('id')}
                                {assign var=ACCESSIBLE_USER_LIST value=$USER_MODEL->getAccessibleUsersForModule($MODULE)}
                                {assign var=ACCESSIBLE_GROUP_LIST value=$USER_MODEL->getAccessibleGroupForModule($MODULE)}

                                <select class="form-control select2" name="transferOwnerId" id="transferOwnerId">
                                    <optgroup label="{vtranslate('LBL_USERS')}">
                                        {foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEUSER_LIST}
                                            <option value="{$OWNER_ID}" data-picklistvalue= '{$OWNER_NAME}' {if $FIELD_VALUE eq $OWNER_ID} selected {/if}
                                                    {if array_key_exists($OWNER_ID, $ACCESSIBLE_USER_LIST)} data-recordaccess=true {else} data-recordaccess=false {/if}
                                                    data-userId="{$CURRENT_USER_ID}">
                                                {$OWNER_NAME}
                                            </option>
                                        {/foreach}
                                    </optgroup>
                                    <optgroup label="{vtranslate('LBL_GROUPS')}">
                                        {foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEGROUP_LIST}
                                            <option value="{$OWNER_ID}" data-picklistvalue= '{$OWNER_NAME}'
                                                    {if array_key_exists($OWNER_ID, $ACCESSIBLE_GROUP_LIST)} data-recordaccess=true {else} data-recordaccess=false {/if} >
                                                {$OWNER_NAME}
                                            </option>
                                        {/foreach}
                                    </optgroup>
                                </select> *}
                            </div>
                            {* End apply custom owner field *}
                    </div>
                </div>
                {include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
            </form>
        </div>
    </div>
{/strip}