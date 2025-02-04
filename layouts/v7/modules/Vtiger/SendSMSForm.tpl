{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* modules/Vtiger/views/MassActionAjax.php *}

<div id="sendSmsContainer" class='modal-xs modal-dialog'>
    <div class = "modal-content">
        {assign var=TITLE value="{vtranslate('LBL_SEND_SMS', $MODULE)}"}
        {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$TITLE}

        <form class="form-horizontal" id="massSave" method="post" action="index.php">
            <input type="hidden" name="module" value="{$MODULE}" />
            <input type="hidden" name="source_module" value="{$SOURCE_MODULE}" />
            <input type="hidden" name="action" value="MassSaveAjax" />
            <input type="hidden" name="viewname" value="{$VIEWNAME}" />
            <input type="hidden" name="selected_ids" value={ZEND_JSON::encode($SELECTED_IDS)}>
            <input type="hidden" name="excluded_ids" value={ZEND_JSON::encode($EXCLUDED_IDS)}>
            <input type="hidden" name="search_key" value= "{$SEARCH_KEY}" />
            <input type="hidden" name="operator" value="{$OPERATOR}" />
            <input type="hidden" name="search_value" value="{$ALPHABET_VALUE}" />
            <input type="hidden" name="search_params" value='{ZEND_JSON::encode($SEARCH_PARAMS)}' />

            {* Added by Hieu Nguyen on 2020-11-11 to support multi-channel message *}
            <input type="hidden" name="channel" value="SMS" />
            {* End Hieu Nguyen *}

            {* [SMS] Feature #89: Modified by Phu Vo on 2020.02.06, validate with dynamic config *}
            {assign var=SMS_CONFIG value=vglobal('smsConfig')}
            {assign var=SMS_MAX_CHARACTERS value="{if $SMS_CONFIG && $SMS_CONFIG['max_characters']}{$SMS_CONFIG['max_characters']}{else}0{/if}"}
            <input type="hidden" name="max_characters" value="{$SMS_MAX_CHARACTERS}" />
            <script>window._SMS_CONFIG = {json_encode($SMS_CONFIG)}</script>

            {* Added by Hieu Nguyen on 2021-11-16 *}
            <script>window._PROVIDER_INFO = {json_encode($PROVIDER_INFO)}</script>
            {* End Hieu Nguyen *}
            
            <div class="modal-body">
                {* Modified by Hieu Nguyen on 2020-10-19 *}
                <div>
                    <span><strong>{vtranslate('LBL_STEP_1', $MODULE)}:</strong></span>
                    &nbsp;
                    {vtranslate('LBL_SELECT_THE_PHONE_NUMBER_FIELDS_TO_SEND', $MODULE)}
                </div>
                {* End Hieu Nguyen *}

                <div>
                    <div>
                        <select name="fields[]" data-placeholder="{vtranslate('LBL_SELECT_THE_PHONE_NUMBER_FIELDS_TO_SEND',$MODULE)}" data-rule-required="true" multiple class = "select2 form-control">
                            {foreach item=PHONE_FIELD from=$PHONE_FIELDS}
                                {assign var=PHONE_FIELD_NAME value=$PHONE_FIELD->get('name')}
                                <option value="{$PHONE_FIELD_NAME}">
                                    {if !empty($SINGLE_RECORD)}
                                        {assign var=FIELD_VALUE value=$SINGLE_RECORD->get($PHONE_FIELD_NAME)}
                                    {/if}
                                    {vtranslate($PHONE_FIELD->get('label'), $SOURCE_MODULE)}{if !empty($FIELD_VALUE)} ({$FIELD_VALUE}){/if}
                                </option>
                            {/foreach}
                        </select>
                    </div>
                </div>
                <hr>
                
                {* Modified by Hieu Nguyen on 2020-10-19 to support sending sms with variable *}
                <div class="form-group" style="padding: 0px 15px">
                    <div>
                        <span><strong>{vtranslate('LBL_STEP_2', $MODULE)}:</strong></span>
                        &nbsp;
                        {vtranslate('LBL_SELECT_AN_EXISTING_TEMPLATE', $MODULE)}
                    </div>
                    <select id="sms-template" class="select2" placeholder="{vtranslate('LBL_SELECT_A_TEMPLATE', $MODULE)}" style="width: 100%">
                        {html_options options=$SMS_TEMPLATES}
                    </select>
                    <br/><br/>

                    {vtranslate('LBL_OR_TYPE_A_NEW_MESSAGE', $MODULE)}
                    <div id="variables">
                        <select id="variable" class="inputElement select2" style="width: 300px" data-placeholder="{vtranslate('LBL_SELECT_FIELD', $MODULE)}">
                            <option value="">{vtranslate('LBL_SELECT_VARIABLE', $MODULE)}</option>
                            {$VARIABLE_OPTIONS}
                        </select>
                        &nbsp;
                        <button type="button" id="btnInsertVariable" class="btn btn-primary">{vtranslate('LBL_INSERT_VARIABLE', $MODULE)}</button>
                    </div>

                    {* Modified by Phu Vo on 2020.12.03 to add validate rule ascii-only *}
                    <textarea id="message" name="message"
                        class="form-control smsTextArea"
                        data-rule-required="true"
                        data-rule-asciiOnly="true"
                        maxlength="{$SMS_MAX_CHARACTERS}"
                        placeholder="{vtranslate('LBL_WRITE_YOUR_MESSAGE_HERE', $MODULE)}"
                        style="resize: vertical;"
                    ></textarea>
                    {* End Phu Vo *}
                    
                    <br/>

                    <div id="character-counter-container">
                        {vtranslate('LBL_REMAINING_CHARACTERS')} <span id="character-remain">0</span>/<span id="character-limit">0</span>
                    </div>
                    <br/>

                    <div id="warning">
                        <i class="far fa-exclamation-triangle" aria-hidden="true"></i>
                        {vtranslate('LBL_WARNING', $MODULE)}
                    </div>
                </div>
                {* End Hieu Nguyen *}
            </div>
            {* End Feature #89 *}
            <div>
                <div class="modal-footer">
                    <center>
                        <button class="btn btn-success" type="submit" name="saveButton"><strong>{vtranslate('LBL_SEND', $MODULE)}</strong></button>
                        <a class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                    </center>
                </div>
            </div>
        </form>
    </div>
</div>

{* [SMS]Feature #89: Modified by Phu Vo on 2020.02.06, validate with dynamic config *}
<script src="{vresource_url('resources/SMSNotifierHelper.js')}"></script>
{* End Feature #89 *}

{* Added by Hieu Nguyen on 2020-10-19 *}
<script src="{vresource_url('resources/UIUtils.js')}"></script>
<script src="{vresource_url('modules/SMSNotifier/resources/SMSPopup.js')}"></script>
{* End Hieu Nguyen *}