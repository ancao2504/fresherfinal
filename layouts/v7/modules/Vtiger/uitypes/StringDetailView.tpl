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

{* TODO: Review the order of parameters - good to eliminate $RECORD->getId, $RECORD should be used *}
{if $FIELD_MODEL->getFieldDataType() eq 'picklist' and $MODULE neq 'Users'}
    {assign var=PICKLIST_COLOR value=Settings_Picklist_Module_Model::getPicklistColorByValue($FIELD_MODEL->getName(), $FIELD_MODEL->get('fieldvalue'))}  
    <span {if !empty($PICKLIST_COLOR)} class="picklist-color" style="background-color: {$PICKLIST_COLOR}; line-height:15px; color: {Settings_Picklist_Module_Model::getTextColor($PICKLIST_COLOR)};" {/if}>
        {$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'), $RECORD->getId(), $RECORD)}
    </span>
{else if $FIELD_MODEL->getFieldDataType() eq 'multipicklist' and $MODULE neq 'Users'}
    {include file="layouts/v7/modules/Vtiger/uitypes/MultiPicklistFormatedValue.tpl" RAW_VALUES_STRING=$RECORD->get($FIELD_MODEL->getName())}
{else if $FIELD_MODEL->getFieldDataType() eq 'currency'}
    {assign var=CURRENT_USER_MODEL value=Users_Record_Model::getCurrentUserModel()}
    {assign var=SYMBOL_PLACEMENT value=$CURRENT_USER_MODEL->get('currency_symbol_placement')}
    {if ($FIELD_MODEL->get('uitype') eq '72') && ($FIELD_MODEL->getName() eq 'unit_price')}
        {assign var=CURRENCY_SYMBOL value=$BASE_CURRENCY_SYMBOL}
    {else if $FIELD_MODEL->get('uitype') eq '71'}
        {assign var=CURRENCY_INFO value=getCurrencySymbolandCRate($CURRENT_USER_MODEL->get('currency_id'))}
        {assign var=CURRENCY_SYMBOL value=$CURRENCY_INFO['symbol']}
    {/if}
    {if $SYMBOL_PLACEMENT eq '$1.0'}
        {$CURRENCY_SYMBOL}&nbsp;<span class="currencyValue">{$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'))}</span>
    {else}
        <span class="currencyValue">{$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'))}</span>&nbsp;{$CURRENCY_SYMBOL}
    {/if}
{else if  $FIELD_MODEL->get('name') eq 'signature'}
	{decode_html($FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'), $RECORD->getId(), $RECORD))}
{else}
    {$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'), $RECORD->getId(), $RECORD)}
{/if}
