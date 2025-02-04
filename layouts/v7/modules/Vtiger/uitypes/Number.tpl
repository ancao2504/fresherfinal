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
{assign var="FIELD_INFO" value=$FIELD_MODEL->getFieldInfo()}
{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
{if $MODULE eq 'HelpDesk' && ($FIELD_MODEL->get('name') eq 'days' || $FIELD_MODEL->get('name') eq 'hours')}
	{assign var="FIELD_VALUE" value=$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'))}
	{assign var="FIELD_TYPE_JS" value='int'}
{else if $FIELD_MODEL->getFieldDataType() eq 'double'}
	{assign var="FIELD_VALUE" value=$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'))}
    {assign var="FIELD_TYPE_JS" value='float'}
{else}
	{assign var="FIELD_VALUE" value=$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'))}
    {assign var="FIELD_TYPE_JS" value='int'}
{/if}
{if (!$FIELD_NAME)}
  {assign var="FIELD_NAME" value=$FIELD_MODEL->getFieldName()}
{/if}
<!-- Added By Kelvin Thang -- Date 2018-07-25-->
<input id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" type="text" {if !empty($FIELD_TYPE_JS)} onkeyup="formatNumber(this, '{$FIELD_TYPE_JS}')" data-field-type="{$FIELD_INFO['type']}" maxlength="13" {/if} class="inputElement {$FIELD_TEST}" name="{$FIELD_NAME}"
value="{$FIELD_VALUE}" {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if}
{if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if}
{if count($FIELD_INFO['validator'])}
    data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}'
{/if}
/>
{/strip}
