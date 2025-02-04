{*
    Name: DescriptionFieldEditView.tpl
    Author: Phu Vo
    Date: 2020.12.03
*}

{strip}
    <div id="sendSmsContainer" class="form-group">
        {assign var="FIELD_INFO" value=$FIELD_MODEL->getFieldInfo()}
        {assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
        {if (!$FIELD_NAME)}{assign var="FIELD_NAME" value=$FIELD_MODEL->getFieldName()}{/if}

        <textarea id="message" rows="3" id="{$MODULE}_editView_fieldName_{$FIELD_NAME}"
            class="inputElement textAreaElement col-lg-12 {if $FIELD_MODEL->isNameField()}nameField{/if}" name="{$FIELD_NAME}"
            {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if}
            {if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if}
            {if count($FIELD_INFO['validator'])}data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}' {/if}
            data-rule-asciiOnly="true"
        >
            {$FIELD_MODEL->get('fieldvalue')}
        </textarea>
    </div>
    <div id="smsCounter" class="form-group">
        {vtranslate('LBL_CHARACTER_COUNT', $MODULE)}: <span id="smsCount">0</span>
    </div>
{/strip}