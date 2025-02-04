{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{strip}
    {* Added by Hieu Nguyen on 2020-11-25 *}
    <script type="text/javascript" src="{vresource_url('modules/Reports/resources/CustomReportHelper.js')}"></script>
    {* End Hieu Nguyen *}

    <div class="">
        <div class="reportsDetailHeader">
            <input type="hidden" name="date_filters" data-value='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($DATE_FILTERS))}' />
            {include file="DetailViewActions.tpl"|vtemplate_path:$MODULE}
            <div class="filterElements contactAdd filterConditionsDiv">  {* Modified by Hieu Nguyen on 2020-04-09 to display filter the same as List Report *}
                <form name='chartDetailForm' id='chartDetailForm' method="POST">
                    <input type="hidden" name="module" value="{$MODULE}" />
                    <input type="hidden" name="action" value="ChartSave" />
                    <input type="hidden" name="recordId" id="recordId" value="{$RECORD}" />
                    <input type="hidden" name="reportname" value="{$REPORT_MODEL->get('reportname')}" />
                    <input type="hidden" name="folderid" value="{$REPORT_MODEL->get('folderid')}" />
                    <input type="hidden" name="reports_description" value="{$REPORT_MODEL->get('reports_description')}" />
                    <input type="hidden" name="primary_module" value="{$PRIMARY_MODULE}" />
                    <input type="hidden" name="secondary_modules" value={ZEND_JSON::encode($SECONDARY_MODULES)} />
                    <input type="hidden" name="advanced_filter" id="advanced_filter" value={ZEND_JSON::encode($ADVANCED_FILTERS)} />
                    <input type="hidden" name='groupbyfield' value={$CHART_MODEL->getGroupByField()} />
                    <input type="hidden" name='datafields' value={Zend_JSON::encode($CHART_MODEL->getDataFields())} />
                    <input type="hidden" name='charttype' value="{$CHART_MODEL->getChartType()}" />

                    {assign var=RECORD_STRUCTURE value=array()}
                    {assign var=PRIMARY_MODULE_LABEL value=vtranslate($PRIMARY_MODULE, $PRIMARY_MODULE)}
                    {foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$PRIMARY_MODULE_RECORD_STRUCTURE}
                        {assign var=PRIMARY_MODULE_BLOCK_LABEL value=vtranslate($BLOCK_LABEL, $PRIMARY_MODULE)}
                        {assign var=key value="$PRIMARY_MODULE_LABEL $PRIMARY_MODULE_BLOCK_LABEL"}
                        {if $LINEITEM_FIELD_IN_CALCULATION eq false && $BLOCK_LABEL eq 'LBL_ITEM_DETAILS'}
                            {* dont show the line item fields block when Inventory fields are selected for calculations *}
                        {else}
                            {$RECORD_STRUCTURE[$key] = $BLOCK_FIELDS}
                        {/if}
                    {/foreach}
                    {foreach key=MODULE_LABEL item=SECONDARY_MODULE_RECORD_STRUCTURE from=$SECONDARY_MODULE_RECORD_STRUCTURES}
                        {assign var=SECONDARY_MODULE_LABEL value=vtranslate($MODULE_LABEL, $MODULE_LABEL)}
                        {foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$SECONDARY_MODULE_RECORD_STRUCTURE}
                            {assign var=SECONDARY_MODULE_BLOCK_LABEL value=vtranslate($BLOCK_LABEL, $MODULE_LABEL)}
                            {assign var=key value="$SECONDARY_MODULE_LABEL $SECONDARY_MODULE_BLOCK_LABEL"}
                            {$RECORD_STRUCTURE[$key] = $BLOCK_FIELDS}
                        {/foreach}
                    {/foreach}

                    {* Added display condition by Hieu Nguyen on 2020-04-09 to prevent editing report critera for normal users *}
                    <div class="{if !$REPORT_MODEL->isEditableBySharing()}hide{/if}">
                        <div class="well filterConditionContainer">
                            <div>
                                <div class='row'>
                                    <span class="col-lg-4">
                                        <div><span>{vtranslate('LBL_SELECT_GROUP_BY_FIELD', $MODULE)}</span><span class="redColor">*</span></div><br>
                                        <div>
                                            <select id='groupbyfield' name='groupbyfield' class="col-lg-10" data-validation-engine="validate[required]" style='min-width:300px;'></select>
                                        </div>
                                    </span>
                                    <span class="col-lg-2">&nbsp;</span>
                                    <span class="col-lg-4">
                                        <div><span>{vtranslate('LBL_SELECT_DATA_FIELD', $MODULE)}</span><span class="redColor">*</span></div><br>
                                        <div>
                                            <select id='datafields' name='datafields[]' class="col-lg-10" data-validation-engine="validate[required]" style='min-width:300px;'>
                                            </select></div>
                                    </span>
                                </div>
                                <br>

                                <div class='hide'>
                                    {include file="chartReportHiddenContents.tpl"|vtemplate_path:$MODULE}
                                </div>
                            </div>
                        </div>
                    </div>
                    {* End Hieu Nguyen *}

                    {* Modified by Hieu Nguyen on 2022-01-24 to fix report header layout *}
                    {assign var=filterConditionNotExists value=(count($SELECTED_ADVANCED_FILTER_FIELDS[1]['columns']) eq 0 and count($SELECTED_ADVANCED_FILTER_FIELDS[2]['columns']) eq 0)}

                    <div>
                        <button type="button" id="toggle-filter" name="modify_condition" class="btn btn-default" data-val="{$filterConditionNotExists}">
                            <strong>{vtranslate('LBL_MODIFY_CONDITION', $MODULE)}</strong>&nbsp;&nbsp;
                            <i class="far fa-chevron-down"></i>
                        </button>
                    </div>
                    {* End Hieu Nguyen *}

                    <div id='filterContainer' class='{if $filterConditionNotExists eq true} hide {/if}'>
                        {include file='AdvanceFilter.tpl'|@vtemplate_path RECORD_STRUCTURE=$RECORD_STRUCTURE ADVANCE_CRITERIA=$SELECTED_ADVANCED_FILTER_FIELDS COLUMNNAME_API=getReportFilterColumnName}
                    </div>
                    
                    {* Modified by Hieu Nguyen on 2020-04-09 to show both Generate and Save buttons in Chart Report DetailView *}
                    <div class="row">
                        <div class="textAlignCenter hide reportActionButtons">
                            <button class="btn btn-default generateReportChart" data-mode="generate" value="{vtranslate('LBL_GENERATE_NOW', $MODULE)}" />
                                <strong>{vtranslate('LBL_GENERATE_NOW', $MODULE)}</strong>
                            </button>&nbsp;
                            {if $REPORT_MODEL->isEditableBySharing()}
                                <button class="btn btn-success generateReportChart" data-mode="save" value="{vtranslate('LBL_SAVE', $MODULE)}" />
                                    <strong>{vtranslate('LBL_SAVE', $MODULE)}</strong>
                                </button>
                            {/if}
                        </div>
                    </div>
                    {* End Hieu Nguyen *}
            </div>
            </form>
        </div>
    </div>
    <div id="reportContentsDiv">
    {/strip}