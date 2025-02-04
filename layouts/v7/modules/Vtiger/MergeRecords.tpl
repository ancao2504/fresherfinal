{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
{* modules/Vtiger/views/MergeRecord.php *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
<div class="fc-overlay-modal">
    <form class="form-horizontal" name="massMerge" method="post" action="index.php">
        <div class="overlayHeader">
            {assign var=TITLE value="{{vtranslate('LBL_MERGE_RECORDS_IN', $MODULE)}|cat:' > '|cat:{vtranslate($MODULE,$MODULE)}}"}
            {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$TITLE}
        </div>
        <div class="overlayBody">
            <div class="container-fluid modal-body">
                <div class="row">
                    <div class="col-lg-12">
                            <input type="hidden" name=module value="{$MODULE}" />
                            <input type="hidden" name="action" value="ProcessDuplicates" />
                            <input type="hidden" name="records" value={Zend_Json::encode($RECORDS)} />
                            <div class="well well-sm" style="margin-bottom:8px">
                                {vtranslate('LBL_MERGE_RECORDS_DESCRIPTION',$MODULE)}
                            </div>
                            <div class="datacontent">
                                <table class="table table-bordered">
                                    <thead class='listViewHeaders'>
                                    <th>
                                        {vtranslate('LBL_FIELDS', $MODULE)}
                                    </th>
                                    {foreach item=RECORD from=$RECORDMODELS name=recordList}
                                        <th>
                                            <div class="checkbox">
                                                <label>
                                                <input {if $smarty.foreach.recordList.index eq 0}checked{/if} type=radio value="{$RECORD->getId()}" name="primaryRecord"/>
                                                &nbsp; {vtranslate('LBL_RECORD')} <a href="{$RECORD->getDetailViewUrl()}" target="_blank" style="color: #15c;">#{$RECORD->getId()}</a>
                                                </label>
                                            </div>
                                        </th>
                                    {/foreach}
                                    </thead>
                                    {foreach item=FIELD from=$FIELDS}
                                        {if $FIELD->isEditable()}
                                        <tr>
                                            <td>
                                                {vtranslate($FIELD->get('label'), $MODULE)}
                                            </td>
                                            {foreach item=RECORD from=$RECORDMODELS name=recordList}
                                                <td>
                                                    {* Modified by Hieu Nguyen on 2021-10-01 to save owner field in the right format *}
                                                    <div class="checkbox">
                                                        <label>
                                                            <input type="radio"
                                                                name="{$FIELD->getName()}"
                                                                data-id="{$RECORD->getId()}"
                                                                value="{$RECORD->get($FIELD->getName())}"
                                                                {if $smarty.foreach.recordList.index eq 0}checked="checked"{/if}
                                                            />
                                                            &nbsp; {$RECORD->getDisplayValue($FIELD->getName())}
                                                        </label>

                                                        {if $FIELD->getName() == 'assigned_user_id'}
                                                            <input type="radio"
                                                                name="main_owner_id"
                                                                data-id="{$RECORD->getId()}"
                                                                value="{$RECORD->get('main_owner_id')}"
                                                                class="hide"
                                                                {if $smarty.foreach.recordList.index eq 0}checked="checked"{/if}
                                                            />
                                                        {/if}
                                                   </div>
                                                   {* End Hieu Nguyen *}
                                                </td>
                                            {/foreach}
                                        </tr>
                                        {/if}
                                    {/foreach}
                                </table>
                             </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="overlayFooter">
            {assign var=BUTTON_NAME value=vtranslate('LBL_MERGE',$MODULE)}
            {include file="ModalFooter.tpl"|vtemplate_path:$MODULE}
        </div>
    </form>
</div>
