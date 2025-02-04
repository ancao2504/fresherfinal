{*
    Name: ContentFieldEditView.tpl
    Author: Phu Vo
    Date: 2020.12.03
*}

{strip}
    {* Added by Hieu Nguyen on 2021-11-16 to get provider info *}
    {assign var="PROVIDER" value=SMSNotifier_Provider_Model::getActiveGateway()}
    
    {if $PROVIDER}
        <script>
            var _PROVIDER_INFO = {Zend_Json::encode($PROVIDER->getInfo())}
        </script>
    {/if}
    {* End Hieu Nguyen *}

    <div id="message-container" style="flex: 1">
        {assign var="FIELD_INFO" value=$FIELD_MODEL->getFieldInfo()}
        {assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
        {if (!$FIELD_NAME)}{assign var="FIELD_NAME" value=$FIELD_MODEL->getFieldName()}{/if}

        {* Added by Hieu Nguyen on 2020-12-07 to insert variable *}
        <div id="variable-container">
            <select id="variable" class="inputElement select2" style="width: 300px">
                <option value="">{vtranslate('LBL_SELECT_VARIABLE', $QUALIFIED_MODULE)}</option>
                <option value="$salutationtype$">{vtranslate('Salutation')}</option>
                <option value="$firstname$">{vtranslate('LBL_FIRSTNAME', 'CPTarget')}</option>
                <option value="$lastname$">{vtranslate('LBL_LASTNAME', 'CPTarget')}</option>
                <option value="$full_name$">{vtranslate('LBL_FULL_NAME', 'CPTarget')}</option>
                <option value="$email$">{vtranslate('LBL_EMAIL', 'CPTarget')}</option>
                <option value="$mobile$">{vtranslate('LBL_MOBILE', 'CPTarget')}</option>
                <option value="$phone$">{vtranslate('LBL_PHONE', 'CPTarget')}</option>
            </select>
            &nbsp;
            <button type="button" id="btnInsertVariable" class="btn btn-default">{vtranslate('LBL_INSERT_VARIABLE', $QUALIFIED_MODULE)}</button>
        </div>
        {* End Hieu Nguyen *}

        <textarea id="message" rows="3" id="{$MODULE}_editView_fieldName_{$FIELD_NAME}"
            class="inputElement textAreaElement {if $FIELD_MODEL->isNameField()}nameField{/if}" name="{$FIELD_NAME}"
            {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if}
            {if $FIELD_INFO['mandatory'] eq true} data-rule-required="true" {/if}
            {if count($FIELD_INFO['validator'])}data-specific-rules='{ZEND_JSON::encode($FIELD_INFO['validator'])}' {/if}
            data-rule-asciiOnly="true"
            style="margin-top: 6px;"
        >
            {$FIELD_MODEL->get('fieldvalue')}
        </textarea>
        
        <div style="margin-top: 6px; text-align: right; padding-right: 48px;">
            {vtranslate('LBL_CHARACTER_COUNT', $MODULE)}: <span id="character-counter">0</span>
        </div>
    </div>
{/strip}