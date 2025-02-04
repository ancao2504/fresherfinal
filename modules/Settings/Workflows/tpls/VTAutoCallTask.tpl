
 {* Added by Hieu Nguyen on 2020-07-27 *}

{strip}
    <div class="row" style="margin-bottom: 70px">
        <div class="col-lg-9">
            <div class="row form-group phone_field">
                <div class="col-lg-3 fieldLabel">{vtranslate('LBL_AUTO_CALL_TASK_PHONE_FIELD', $QUALIFIED_MODULE)}<span class="redColor">*</span></div>
                <div class="col-lg-9 fieldValue">
                    <select name="phone_field" class="inputElement select2" data-rule-required="true" style="width: 300px" data-placeholder="{vtranslate('LBL_SELECT_FIELD', $QUALIFIED_MODULE)}">
                        <option></option>
                        {foreach key=FIELD_NAME item=FIELD_MODEL from=$RECORD_STRUCTURE_MODEL->getFieldsByType('phone')}
                            {assign var="META_KEY" value="${$FIELD_NAME}"}
                            <option value="{$META_KEY}" {if $META_KEY == $TASK_OBJECT->phone_field}selected{/if}>{$FIELD_MODEL->get('workflow_columnlabel')}</option>
                        {/foreach}
                    </select>
                </div>
            </div>

            <div class="row form-group variable">
                <div class="col-lg-3 fieldLabel">{vtranslate('LBL_AUTO_CALL_TASK_VARIABLE', $QUALIFIED_MODULE)}</div>
                <div class="col-lg-9 fieldValue">
                    <select id="variable" class="inputElement select2" style="width: 300px" data-placeholder="{vtranslate('LBL_SELECT_FIELD', $QUALIFIED_MODULE)}">
						<option></option>
                        {$ALL_FIELD_OPTIONS}
                    </select>
                    &nbsp;
                    <button type="button" id="btnInsertVariable" class="btn btn-default">{vtranslate('LBL_INSERT_VARIABLE', $QUALIFIED_MODULE)}</button>
                </div>
            </div>

            <div class="row form-group text_to_call">
                <div class="col-lg-3 fieldLabel">{vtranslate('LBL_AUTO_CALL_TASK_TEXT_TO_CALL', $QUALIFIED_MODULE)}<span class="redColor">*</span></div>
                <div class="col-lg-6">
                    <textarea name="text_to_call" data-rule-required="true" class="inputElement fields" style="width: 100%; height: 100px">{$TASK_OBJECT->text_to_call}</textarea>
                </div>
            </div>
            <hr/>

            <div class="row form-group handle_response">
                <div class="col-lg-3 fieldLabel">{vtranslate('LBL_AUTO_CALL_TASK_HANDLE_RESPONSE', $QUALIFIED_MODULE)}</div>
                <div class="col-lg-9 fieldValue">
                    <input type="checkbox" name="handle_response" {if $TASK_OBJECT->handle_response}checked{/if} />
                </div>
            </div>

            <div class="row form-group confirm_key toggleRequired">
                <div class="col-lg-3 fieldLabel">{vtranslate('LBL_AUTO_CALL_TASK_CONFIRM_KEY', $QUALIFIED_MODULE)}<span class="redColor">*</span></div>
                <div class="col-lg-9 fieldValue">
                    <input type="number" name="confirm_key" value="{$TASK_OBJECT->confirm_key}" class="inputElement" style="width: 50px" data-rule-range=[0,9] data-rule-positive="true" />
                </div>
            </div>

            <div class="row form-group cancel_key toggleRequired">
                <div class="col-lg-3 fieldLabel">{vtranslate('LBL_AUTO_CALL_TASK_CANCEL_KEY', $QUALIFIED_MODULE)}<span class="redColor">*</span></div>
                <div class="col-lg-9 fieldValue">
                    <input type="number" name="cancel_key" value="{$TASK_OBJECT->cancel_key}" class="inputElement" style="width: 50px" data-rule-range=[0,9] data-rule-positive="true" />
                </div>
            </div>

            <div class="row form-group target_field toggleRequired">
                <div class="col-lg-3 fieldLabel">{vtranslate('LBL_AUTO_CALL_TASK_TARGET_FIELD', $QUALIFIED_MODULE)}<span class="redColor">*</span></div>
                <div class="col-lg-9 fieldValue">
                    <select name="target_field" class="inputElement select2" style="width: 200px" data-placeholder="{vtranslate('LBL_SELECT_FIELD', $QUALIFIED_MODULE)}">
						<option></option>
                        {foreach key=FIELD_NAME item=FIELD_INFO from=$PICKLIST_FIELDS}
                            <option value="{$FIELD_NAME}" {if $FIELD_NAME == $TASK_OBJECT->target_field}selected{/if}>{$FIELD_INFO.label}</option>
                        {/foreach}
                    </select>
                </div>
            </div>

            {assign var="TARGET_FIELD_OPTIONS" value=$PICKLIST_FIELDS[$TASK_OBJECT->target_field].options}

            <div class="row form-group confirmed_value toggleRequired">
                <div class="col-lg-3 fieldLabel">{vtranslate('LBL_AUTO_CALL_TASK_CONFIRMED_VALUE', $QUALIFIED_MODULE)}<span class="redColor">*</span></div>
                <div class="col-lg-9 fieldValue">
                    <select name="confirmed_value" class="inputElement select2" style="width: 200px" data-placeholder="{vtranslate('LBL_SELECT_VALUE', $QUALIFIED_MODULE)}">
						<option></option>
                        {foreach key=OPTION_KEY item=OPTION_INFO from=$TARGET_FIELD_OPTIONS}
                            <option value="{$OPTION_KEY}" {if $OPTION_KEY == $TASK_OBJECT->confirmed_value}selected{/if}>{$OPTION_INFO.label}</option>
                        {/foreach}
                    </select>
                </div>
            </div>

            <div class="row form-group cancelled_value toggleRequired">
                <div class="col-lg-3 fieldLabel">{vtranslate('LBL_AUTO_CALL_TASK_CANCELLED_VALUE', $QUALIFIED_MODULE)}<span class="redColor">*</span></div>
                <div class="col-lg-9 fieldValue">
                    <select name="cancelled_value" class="inputElement select2" style="width: 200px" data-placeholder="{vtranslate('LBL_SELECT_VALUE', $QUALIFIED_MODULE)}">
						<option></option>
                        {foreach key=OPTION_KEY item=OPTION_INFO from=$TARGET_FIELD_OPTIONS}
                            <option value="{$OPTION_KEY}" {if $OPTION_KEY == $TASK_OBJECT->cancelled_value}selected{/if}>{$OPTION_INFO.label}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
        </div>
    </div>

    <link type="text/css" rel="stylesheet" href="{vresource_url("modules/Settings/Workflows/resources/VTAutoCallTask.css")}"></link>
    <script>
        var _PICKLIST_FIELDS = {Zend_Json::encode($PICKLIST_FIELDS)};
    </script>
    <script src="{vresource_url("resources/UIUtils.js")}"></script>
    <script src="{vresource_url("modules/Settings/Workflows/resources/VTAutoCallTask.js")}"></script>
{/strip}