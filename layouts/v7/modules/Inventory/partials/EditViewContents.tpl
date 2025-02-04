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

    {* Added by Hieu Nguyen on 2019-06-11 to show hidden input for main_owner_id *}
    {if !$RECORD && $RECORD_STRUCTURE_MODEL}
        {assign var="RECORD" value=$RECORD_STRUCTURE_MODEL->getRecord()}
    {/if}

    <input type="hidden" name="main_owner_id" value="{if $RECORD}{$RECORD->fetchedRow['main_owner_id']}{/if}"/>
    {* End Hieu Nguyen *}

    {* Modified by Kelvin Thang on 2018-09-05 to load custom code *}
    {if $DISPLAY_PARAMS['form'] && $DISPLAY_PARAMS['form']['hiddenFields']}
        {eval var=$DISPLAY_PARAMS['form']['hiddenFields']}
    {/if}

    {if $DISPLAY_PARAMS['scripts'] neq null}
        {eval var=$DISPLAY_PARAMS['scripts']}
    {/if}
    {* End Kelvin Thang *}

    {* Added by Hieu Nguyen on 2021-08-03 to support custom header in EditView *}
    {if $smarty.request.view == 'Edit'}
        <div class="row-fluid customEditViewHeader">
            {assign var="CUSTOM_HEADER_FILE" value="modules/$MODULE/tpls/EditViewCustomHeader.tpl"}

            {if file_exists($CUSTOM_HEADER_FILE)}
                {include file=$CUSTOM_HEADER_FILE}
            {/if}
        </div>
    {/if}
	{* End Hieu Nguyen *}
    
<div name='editContent'>
	{if $DUPLICATE_RECORDS}
		<div class="fieldBlockContainer duplicationMessageContainer">
			<div class="duplicationMessageHeader"><b>{vtranslate('LBL_DUPLICATES_DETECTED', $MODULE)}</b></div>
			<div>{getDuplicatesPreventionMessage($MODULE, $DUPLICATE_RECORDS)}</div>
		</div>
	{/if}
    {foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE name=blockIterator}
        {if $BLOCK_LABEL eq 'LBL_ITEM_DETAILS'}{continue}{/if}
         {if $BLOCK_FIELDS|@count gt 0}
             <div class='fieldBlockContainer' data-block="{$BLOCK_LABEL}">
                     <h4 class='fieldBlockHeader'>{vtranslate($BLOCK_LABEL, $MODULE)}</h4>
                 <hr>
                 <table class="table table-borderless {if $BLOCK_LABEL eq 'LBL_ADDRESS_INFORMATION'} addressBlock{/if}">
                     {if ($BLOCK_LABEL eq 'LBL_ADDRESS_INFORMATION') and ($MODULE neq 'PurchaseOrder')}
                         <!--Modified By Kelvin Thang -- Date: 2018-07-10-->
                         <tr>
                            <td class="fieldLabel " name="copyHeader1">
                                <label  name="togglingHeader">{vtranslate('LBL_BILLING_ADDRESS_FROM', $MODULE)}</label>
                            </td>
                            <td class="fieldValue" name="copyAddress1">
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="copyAddressFromRight" class="accountAddress" data-copy-address="shipping" checked="checked">
                                        &nbsp;{vtranslate('LBL_ACCOUNTS_ADDRESS', $MODULE)}
                                    </label>
                                </div>
                                <div class="radio">
                                    <label> 
                                        {if $MODULE eq 'Quotes'}
                                            <input type="radio" name="copyAddressFromRight" class="contactAddress" data-copy-address="shipping" checked="checked">
                                            &nbsp;{vtranslate('Related To', $MODULE)}
                                        {else}
                                            <input type="radio" name="copyAddressFromRight" class="contactAddress" data-copy-address="shipping" checked="checked">
                                            &nbsp;{vtranslate('LBL_CONTACTS_ADDRESS', $MODULE)}
                                        {/if}
                                    </label>
                                </div>
                                <div class="radio" name="togglingAddressContainerRight">
                                    <label>
                                        <input type="radio" name="copyAddressFromRight" class="shippingAddress" data-target="shipping" checked="checked">
                                        &nbsp;{vtranslate('Shipping Address', $MODULE)}
                                    </label>
                                </div>
                                <div class="radio hide" name="togglingAddressContainerLeft">
                                    <label>
                                        <input type="radio" name="copyAddressFromRight"  class="billingAddress" data-target="billing" checked="checked">
                                        &nbsp;{vtranslate('Billing Address', $MODULE)}
                                    </label>
                                </div>
                            </td>
                            <td class="fieldLabel" name="copyHeader2">
                                <label  name="togglingHeader">{vtranslate('LBL_SHIPPING_ADDRESS_FROM', $MODULE)}</label>
                            </td>
                            <td class="fieldValue" name="copyAddress2">
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="copyAddressFromLeft" class="accountAddress" data-copy-address="shipping" checked="checked">
                                        &nbsp;{vtranslate('LBL_ACCOUNTS_ADDRESS', $MODULE)}
                                    </label>
                                </div>
                                <div class="radio">
                                    <label>
                                        {if $MODULE eq 'Quotes'}
                                            <input type="radio" name="copyAddressFromLeft" class="contactAddress" data-copy-address="shipping" checked="checked">
                                            &nbsp;{vtranslate('Related To', $MODULE)}
                                        {else}
                                            <input type="radio" name="copyAddressFromLeft" class="contactAddress" data-copy-address="shipping" checked="checked">
                                            &nbsp;{vtranslate('LBL_CONTACTS_ADDRESS', $MODULE)}
                                        {/if}
                                    </label>
                                </div>
                                <div class="radio" name="togglingAddressContainerLeft">
                                    <label>
                                        <input type="radio" name="copyAddressFromLeft" class="billingAddress" data-target="billing" checked="checked">
                                        &nbsp;{vtranslate('Billing Address', $MODULE)}
                                    </label>
                                </div>
                                <div class="radio hide" name="togglingAddressContainerRight">
                                    <label>
                                        <input type="radio" name="copyAddressFromLeft" class="shippingAddress" data-target="shipping" checked="checked">
                                        &nbsp;{vtranslate('Shipping Address', $MODULE)}
                                    </label>
                                </div>
                            </td>
                        </tr>
                    {/if}
                     <tr>
                     {assign var=COUNTER value=0}
                     {foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS name=blockfields}
                         {assign var="isReferenceField" value=$FIELD_MODEL->getFieldDataType()}
                         {assign var="refrenceList" value=$FIELD_MODEL->getReferenceList()}
                         {assign var="refrenceListCount" value=count($refrenceList)}
                         {if $FIELD_MODEL->isEditable() eq true}
                             {if $FIELD_MODEL->get('uitype') eq "19"}
                                 {if $COUNTER eq '1'}
                                     <td></td><td></td></tr><tr>
                                     {assign var=COUNTER value=0}
                                 {/if}
                             {/if}
                             {if $COUNTER eq 2}
                                 </tr><tr>
                                 {assign var=COUNTER value=1}
                             {else}
                                 {assign var=COUNTER value=$COUNTER+1}
                             {/if}
                            {* Added by Phu Vo on 2019.06.12 to add fieldName class to field holder for Inventory modules *}
                            <td class="fieldLabel {$FIELD_MODEL->get('name')} alignMiddle">
                            {* End add fieldName class to field holder for Inventory modules *}
                             {if $isReferenceField eq "reference"}
                                 {if $refrenceListCount > 1}
                                     {assign var="REFERENCED_MODULE_ID" value=$FIELD_MODEL->get('fieldvalue')}
                                     {assign var="REFERENCED_MODULE_STRUCTURE" value=$FIELD_MODEL->getUITypeModel()->getReferenceModule($REFERENCED_MODULE_ID)}
                                     {if !empty($REFERENCED_MODULE_STRUCTURE)}
                                        {assign var="REFERENCED_MODULE_NAME" value=$REFERENCED_MODULE_STRUCTURE->get('name')}
                                     {/if}
                                     <select class="select2 referenceModulesList">
                                        {foreach key=index item=value from=$refrenceList}
                                            <option value="{$value}" {if $value eq $REFERENCED_MODULE_NAME} selected {/if}>{vtranslate($value, $value)}</option>
                                        {/foreach}
                                    </select>
                                 {else}
                                    {* Modified by Phuc Lu on 2019-08-01 to load custom code *}
                                    {if $DISPLAY_PARAMS['fields'][$FIELD_NAME] && $DISPLAY_PARAMS['fields'][$FIELD_NAME]['customLabel']}
                                        {eval var=$DISPLAY_PARAMS['fields'][$FIELD_NAME]['customLabel']}
                                    {else}
                                        {vtranslate($FIELD_MODEL->get('label'), $MODULE)}
                                    {/if}
                                    {* Ended by Phuc Lu *}
                                 {/if}
                             {else}
                                {* Modified by Phuc Lu on 2019-08-01 to load custom code *}
                                {if $DISPLAY_PARAMS['fields'][$FIELD_NAME] && $DISPLAY_PARAMS['fields'][$FIELD_NAME]['customLabel']}
                                    {eval var=$DISPLAY_PARAMS['fields'][$FIELD_NAME]['customLabel']}
                                {else}
                                    {vtranslate($FIELD_MODEL->get('label'), $MODULE)}
                                {/if}
                                {* Ended by Phuc Lu *}
                             {/if}
                             {if $FIELD_MODEL->isMandatory() eq true}&nbsp;<span class="redColor">*</span>{/if}
                             &nbsp;&nbsp;
                         </td>
                        {* Added by Phu Vo on 2019.06.12 to add fieldName class to field holder for Inventory modules *}
                        <td class="fieldValue {$FIELD_MODEL->get('name')}" {if $FIELD_MODEL->getFieldDataType() eq 'boolean'} style="width:25%" {/if} {if $FIELD_MODEL->get('uitype') eq '19'} colspan="3" {assign var=COUNTER value=$COUNTER+1} {/if}>
                        {* End add fieldName class to field holder for Inventory modules *}
                             {if $FIELD_MODEL->getFieldDataType() eq 'image' || $FIELD_MODEL->getFieldDataType() eq 'file'}
                                 <div class='col-lg-4 col-md-4 redColor'>
                                     {vtranslate('LBL_NOTE_EXISTING_ATTACHMENTS_WILL_BE_REPLACED', $MODULE)}
                                 </div>
                             {/if}
                            {* Modified by Hieu Nguyen on 2018-07-16 to load custom code *}
                            {if $DISPLAY_PARAMS['fields'][$FIELD_NAME] && $DISPLAY_PARAMS['fields'][$FIELD_NAME]['customTemplate']}
                                {eval var=$DISPLAY_PARAMS['fields'][$FIELD_NAME]['customTemplate']}
                            {else}
                                {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
                            {/if}
                            {* End Hieu Nguyen *}

                            {* [FieldGuide] Added by Hieu Nguyen on 2021-01-20 *}
                            {include file="modules/Vtiger/tpls/FieldGuideIcon.tpl" FIELD_MODEL=$FIELD_MODEL}
                            {* End Hieu Nguyen *}
                         </td>
                     {/if}
                     {/foreach}
                     {*If their are odd number of fields in edit then border top is missing so adding the check*}
                     {if $COUNTER is odd}
                         <td></td>
                         <td></td>
                     {/if}
                     </tr>
                 </table>
             </div>
         {/if}
     {/foreach}
</div>
{include file="partials/LineItemsEdit.tpl"|@vtemplate_path:'Inventory'}
