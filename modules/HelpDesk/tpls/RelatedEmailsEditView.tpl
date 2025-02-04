{* Added by Tin Bui on 2022.03.16 - Custom related emails field UI *}
{strip}
	{assign var="FIELD_INFO" value=$FIELD_MODEL->getFieldInfo()}
	{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}

	{if (!$FIELD_NAME)}
		{assign var="FIELD_NAME" value=$FIELD_MODEL->getFieldName()}
	{/if}
	<input id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" type="text" data-fieldname="{$FIELD_NAME}" data-fieldtype="string" class="inputElement {if $FIELD_MODEL->isNameField()}nameField{/if}" name="{$FIELD_NAME}" value="{$FIELD_MODEL->get('fieldvalue')}"
		{if !empty($SPECIAL_VALIDATOR)}data-validator="{Zend_Json::encode($SPECIAL_VALIDATOR)}"{/if}
		{if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if}
		{if count($FIELD_INFO['validator'])}
			data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}'
		{/if}
    />
{/strip}
{* Ended by Tin Bui *}