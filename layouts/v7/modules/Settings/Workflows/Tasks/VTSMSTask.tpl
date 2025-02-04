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
    <div class="row" style="margin-bottom: 70px;">
        <div class="col-lg-9">
            <div class="row form-group">
                <div class="col-lg-2">{vtranslate('LBL_RECEPIENTS',$QUALIFIED_MODULE)}<span class="redColor">*</span></div>
                <div class="col-lg-8">
                    <div class="row">
                        <div class="col-lg-5">
                            <input type="text" class="inputElement fields" data-rule-required="true" name="sms_recepient" value="{$TASK_OBJECT->sms_recepient}" />
                        </div>
                        <div class="col-lg-6">
                            <select class="select2 task-fields" style="min-width: 150px;" data-placeholder="{vtranslate('LBL_SELECT_FIELDS', $QUALIFIED_MODULE)}">
                                <option></option>
                                {* Modified by Hieu Nguyen on 2020-07-01 to display field label clearly and make code more readble *}
                                {foreach key=META_KEY item=FIELD_MODEL from=$RECORD_STRUCTURE_MODEL->getFieldsByType('phone')}
                                    <option value=",${$META_KEY}">{$FIELD_MODEL->get('workflow_columnlabel')}</option>
                                {/foreach}
                                {* End Hieu Nguyen *}
                            </select>	
                        </div>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-lg-2">{vtranslate('LBL_ADD_FIELDS',$QUALIFIED_MODULE)}</div>
                <div class="col-lg-10">
                    <select class="select2 task-fields" style="min-width: 150px;" data-placeholder="{vtranslate('LBL_SELECT_FIELDS', $QUALIFIED_MODULE)}">
						<option></option>
                        {$ALL_FIELD_OPTIONS}
                    </select>	
                </div>
                <div class="col-lg-2"> &nbsp; </div>
                <div class="col-lg-10"> &nbsp; </div>
                <div class="col-lg-2">{vtranslate('LBL_SMS_TEXT',$QUALIFIED_MODULE)}</div>
                <div class="col-lg-6">
                    <textarea name="content" class="inputElement fields" style="height: inherit;">{$TASK_OBJECT->content}</textarea>
                </div>
            </div>
        </div>
    </div>
{/strip}	
