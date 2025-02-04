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
{*<!-- Added By Kelvin Thang -- Date 2020-01-02-->*}
{strip}
    {assign var="FIELD_INFO" value=Zend_Json::encode($FIELD_MODEL->getFieldInfo())}
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
    <div class="" >
        <input type="text" name="{$FIELD_MODEL->get('name')}" class="listSearchContributor inputElement" onkeyup="formatNumber (this, '{$FIELD_TYPE_JS}')" value="{$SEARCH_INFO['searchValue']}" data-field-type="{$FIELD_MODEL->getFieldDataType()}" maxlength="13" data-fieldinfo='{$FIELD_INFO|escape}'/>
    </div>
{/strip}