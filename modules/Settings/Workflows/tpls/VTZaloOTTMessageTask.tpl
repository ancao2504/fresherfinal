{* Added by Hieu Nguyen on 2020-11-23 *}

{strip}
    <div class="row" style="margin-bottom: 70px">
        <div class="col-lg-9">
            <div class="row form-group phone_fields">
                <div class="col-lg-3 fieldLabel">{vtranslate('LBL_MESSAGE_PHONE_FIELDS', $QUALIFIED_MODULE)}<span class="redColor">*</span></div>
                <div class="col-lg-9 fieldValue">
                    <select name="phone_fields[]" data-rule-required="true" class="inputElement select2" style="width: 300px" data-placeholder="{vtranslate('LBL_SELECT_FIELD', $QUALIFIED_MODULE)}" multiple>
						<option></option>
                        {foreach key=META_KEY item=FIELD_MODEL from=$RECORD_STRUCTURE_MODEL->getFieldsByType('phone')}
                            <option value="{$META_KEY}" {if in_array($META_KEY, $TASK_OBJECT->phone_fields)}selected{/if}>{$FIELD_MODEL->get('workflow_columnlabel')}</option>
                        {/foreach}
                    </select>
                </div>
            </div>

            <div class="row form-group variable">
                <div class="col-lg-3 fieldLabel">{vtranslate('LBL_MESSAGE_VARIABLE', $QUALIFIED_MODULE)}</div>
                <div class="col-lg-9 fieldValue">
                    <select id="variable" class="inputElement select2" style="width: 300px" data-placeholder="{vtranslate('LBL_SELECT_FIELD', $QUALIFIED_MODULE)}">
						<option></option>
                        {$ALL_FIELD_OPTIONS}
                    </select>
                    &nbsp;
                    <button type="button" id="btnInsertVariable" class="btn btn-default">{vtranslate('LBL_INSERT_VARIABLE', $QUALIFIED_MODULE)}</button>
                </div>
            </div>

            <div class="row form-group message">
                <div class="col-lg-3 fieldLabel">{vtranslate('LBL_MESSAGE_CONTENT', $QUALIFIED_MODULE)}<span class="redColor">*</span></div>
                <div class="col-lg-6">
                    <textarea name="message" data-rule-required="true" class="inputElement fields" style="width: 100%; height: 100px">{$TASK_OBJECT->message}</textarea>
                </div>
            </div>
            <hr/>
        </div>
    </div>

    <link type="text/css" rel="stylesheet" href="{vresource_url("modules/Settings/Workflows/resources/VTZaloOTTMessageTask.css")}"></link>
    <script src="{vresource_url("resources/UIUtils.js")}"></script>
    <script src="{vresource_url("modules/Settings/Workflows/resources/VTZaloOTTMessageTask.js")}"></script>
{/strip}