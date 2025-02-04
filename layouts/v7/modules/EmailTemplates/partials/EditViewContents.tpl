{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{strip}
    {if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
        <input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}' />
    {/if}
    <div name='editContent'>
        <div class='fieldBlockContainer'>
            <span>
                <h4 class='fieldBlockHeader'>{vtranslate('LBL_OVERVIEW', $MODULE)}</h4>
            </span>
            <hr>
            <table class="table table-borderless">
                <tbody>
                    <tr>
                        <td class="fieldLabel {$WIDTHTYPE} alignMiddle">{vtranslate('LBL_TEMPLATE_NAME', $MODULE)}&nbsp;<span class="redColor">*</span></td>
                        <td class="fieldValue {$WIDTHTYPE}" colspan="3">
                            {* Updated by Phuc on 2019.11.26 to display template name for system *}
                            {if $IS_SYSTEM_TEMPLATE_EDIT}<span>{$RECORD->get('templatename')}</span>{/if}
                            <input id="{$MODULE}_editView_fieldName_templatename" type="text" class="inputElement {if $IS_SYSTEM_TEMPLATE_EDIT} hide {/if}" data-rule-required="true" name="templatename" value="{$RECORD->get('templatename')}">
                        </td>
                    </tr>
                    <tr>
                        <td class="fieldLabel {$WIDTHTYPE} alignMiddle">{vtranslate('LBL_DESCRIPTION', $MODULE)}</td>
                        <td class="fieldValue {$WIDTHTYPE}" colspan="3">
                            <textarea class="inputElement col-lg-12" id="description" name="description">{$RECORD->get('description')}</textarea>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class='fieldBlockContainer'>
            <span>
                <h4 class='fieldBlockHeader'>{vtranslate('LBL_EMAIL_TEMPLATE', $MODULE)}</h4>
            </span>
            <hr>
            <table class="table table-borderless">
                <tbody>
                    <tr>
                        <td class="fieldLabel {$WIDTHTYPE}">{vtranslate('LBL_SELECT_FIELD_TYPE', $MODULE)}&nbsp;<span class="redColor">*</span></td>
                        <td class="fieldValue {$WIDTHTYPE}" colspan="3">
                            <span class="filterContainer" >
                                <input type=hidden name="moduleFields" data-value='{Vtiger_Functions::jsonEncode($ALL_FIELDS)}' />
                                <span class="col-sm-4 col-xs-4 conditionRow">
                                    <select class="inputElement select2" name="modulename" data-rule-required="true">
                                        <option value="">{vtranslate('LBL_SELECT_MODULE',$MODULE)}</option>
                                        {foreach key=MODULENAME item=FIELDS from=$ALL_FIELDS}
                                            <option value="{$MODULENAME}" {if $RECORD->get('module') eq $MODULENAME}selected{/if}>{vtranslate($MODULENAME, $MODULENAME)}</option>
                                        {/foreach}
                                    </select>
                                </span>&nbsp;&nbsp;
                                <span class="col-sm-6 col-xs-6">
                                    <select class="inputElement select2 col-sm-5 col-xs-5" id="templateFields" name="templateFields">
                                        <option value="">{vtranslate('LBL_NONE',$MODULE)}</option>
                                    </select>
                                </span>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="fieldLabel {$WIDTHTYPE}">{vtranslate('LBL_GENERAL_FIELDS', $MODULE)}</td>
                        <td class="fieldValue {$WIDTHTYPE}" colspan="3">
                            <span class="col-sm-6 col-xs-6">
                                <select class="inputElement select2 col-sm5 col-xs-5" id="generalFields" name="generalFields">
                                    <option value="">{vtranslate('LBL_NONE',$MODULE)}</option>
                                    <optgroup label="{vtranslate('LBL_COMPANY_DETAILS','Settings:Vtiger')}">
                                        {foreach key=index item=COMPANY_FIELD from=$COMPANY_FIELDS}
                                            <option value="{{$COMPANY_FIELD[1]}}">{$COMPANY_FIELD[0]}</option>
                                        {/foreach}
                                    </optgroup>
                                    <optgroup label="{vtranslate('LBL_GENERAL_FIELDS', $MODULE)}">
                                        {foreach key=index item=GENERAL_FIELD from=$GENERAL_FIELDS}
                                            <option value="{$GENERAL_FIELD[1]}">{$GENERAL_FIELD[0]}</option>
                                        {/foreach}
                                    </optgroup>
                                </select>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="fieldLabel {$WIDTHTYPE}">{vtranslate('LBL_SUBJECT', $MODULE)}&nbsp;<span class="redColor">*</span></td>
                        <td class="fieldValue {$WIDTHTYPE}" colspan="3">
                            {* Modified by Hieu Nguyen on 2020-05-28 to make subject field longer to display long text *}
                            <div class="col-sm-12 col-xs-12">
                                <input id="{$MODULE}_editView_fieldName_subject" type="text" class="inputElement col-lg-12" data-rule-required="true" name="subject" value="{$RECORD->get('subject')}"  spellcheck="true" style="width:100%" /> {* Remove by Phuc on 2019.11.26 to apply for name *}
                            </div>
                            {* End Hieu Nguyen *}
                            </div>
                        </td>
                    </tr>

                    {* Added by Hieu Nguyen on 2020-11-05 to support saving template without purified *}
                    {assign var="CURRENT_USER" value=getGlobalVariable('current_user')}

                    {if $CURRENT_USER->is_admin == 'on'}
                        <tr>
                            <td class="fieldLabel {$WIDTHTYPE}">{vtranslate('LBL_SAFE_CONTENT', $MODULE)}</td>
                            <td class="fieldValue {$WIDTHTYPE}" colspan="3">
                                <div class="col-sm-12 col-xs-12">
                                    <input id="{$MODULE}_editView_fieldName_safe_content" type="checkbox" class="inputElement" name="safe_content" {if {$RECORD->get('safe_content')} == '1'}checked{/if} />
                                    &nbsp;&nbsp;<span style="color:red">{vtranslate('LBL_SAFE_CONTENT_HINT', $MODULE)}</span>
                                </div>
                            </td>
                        </tr>
                    {/if}
                    {* End Hieu Nguyen *}

                    <tr>
                        <td class="fieldLabel {$WIDTHTYPE}">{vtranslate('Message', $MODULE)}</td>
                        <td class="fieldValue {$WIDTHTYPE}" colspan="3">
                            {assign var="TEMPLATE_CONTENT" value=$RECORD->get('body')}
                            <textarea id="templatecontent" name="templatecontent" {if $IS_SYSTEM_TEMPLATE_EDIT} data-rule-required="true" {/if} >
                                {if !empty($TEMPLATE_CONTENT)}
                                    {$TEMPLATE_CONTENT}
                                {else}
                                    {include file="DefaultContentForTemplates.tpl"|@vtemplate_path:$MODULE}
                                {/if}
                            </textarea>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>