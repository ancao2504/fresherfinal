{*
    File ReportConfig.tpl
    Author: Phuc Lu
    Date: 2020.03.30
    Purpose: to render the UI for Report config
*}

{strip}
    <!-- Main Form -->
    <form name="settings">
        <div class="editViewBody">
            <div class="editViewContents">
                <div class="fieldBlockContainer">
                    <h4 class="fieldBlockHeader">{vtranslate('LBL_REPORT_CONFIG_REPORT_CONFIG', $MODULE_NAME)}</h4>
                    <hr />

                    <div class="contents tabbable">
                        <ul class="nav nav-tabs marginBottom10px">
                            <li class="reportSalesForecast active"><a data-toggle="tab" href="#reportSalesForecast"><strong>{vtranslate('LBL_REPORT_CONFIG_SALES_FORECAST', $MODULE_NAME)}</strong></a></li>
                            <li class="customerGroup"><a data-toggle="tab" href="#customerGroup"><strong>{vtranslate('LBL_REPORT_CONFIG_CUSTOMER_GROUP', $MODULE_NAME)}</strong></a></li>
                        </ul>
                        <div class="tab-content overflowVisible">
                            <div class="tab-pane active" id="reportSalesForecast">
                                <div class="row form-group">
                                    <div class="control-label fieldLabel col-sm-5">
                                        <span>{vtranslate('LBL_REPORT_CONFIG_MIN_SUCCESSFUL_PERCENTAGE', $MODULE_NAME)} (%)</span>
                                        &nbsp;
                                        <span class="redColor">*</span>
                                    </div>
                                    <div class="controls fieldValue col-sm-6">
                                        <div class="input-group" style="margin-bottom: 3px">
                                            <input type="text" class="inputElement" data-fieldtype="number" data-rule-required="true" name="min_successful_percentage" maxlength="3" value="{$CURRENT_CONFIG['sales_forecast']['min_successful_percentage']}"/><span class="input-group-addon"><i class="far fa-percent "></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row form-group">
                                    <div class="control-label fieldLabel col-sm-5">
                                    </div>
                                    <div class="controls fieldValue col-sm-6 sales-forecase-example">
                                        <div class="input-group" style="margin-bottom: 3px">
                                            <strong>{vtranslate('LBL_REPORT_CONFIG_EXAMPLE', $MODULE_NAME)}</strong>
                                            <ul>
                                                <li>{vtranslate('LBL_REPORT_CONFIG_EXAMPLE_SALES_FORECAST_LINE_1', $MODULE_NAME)}</li>
                                                <li>{vtranslate('LBL_REPORT_CONFIG_EXAMPLE_SALES_FORECAST_LINE_2', $MODULE_NAME)}</li>
                                                <li><i class="far fa-long-arrow-right" aria-hidden="true"></i> {vtranslate('LBL_REPORT_CONFIG_EXAMPLE_SALES_FORECAST_LINE_3', $MODULE_NAME)}</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>                            
                            <div class="tab-pane" id="customerGroup">
                                <div class="row form-group">
                                    <div class="control-label fieldLabel col-sm-5">{vtranslate('LBL_REPORT_CONFIG_CURRENCY', $MODULE_NAME)}:&nbsp;&nbsp;&nbsp;{$BASE_CURRENCY['currency_code']}
                                    </div>
                                    <div class="controls col-sm-5">{vtranslate('LBL_REPORT_CONFIG_CALCULATE_BY', $MODULE_NAME)}:&nbsp;&nbsp;&nbsp;
                                        <label><input type="radio" name="customer_group_calculate_by" value="year" {if $CURRENT_CONFIG['customer_groups']['customer_group_calculate_by'] == 'year'}checked{/if} />&nbsp;{vtranslate('LBL_REPORT_CONFIG_YEAR', $MODULE_NAME)}</label>&nbsp;&nbsp;&nbsp;
                                        <label><input type="radio" name="customer_group_calculate_by" value="cummulation" {if $CURRENT_CONFIG['customer_groups']['customer_group_calculate_by'] == 'cummulation'}checked{/if}/>&nbsp;{vtranslate('LBL_REPORT_CONFIG_CUMMULATION', $MODULE_NAME)}</label>
                                    </div>
                                </div>
                                <hr>

                                <div class="customer-group hide">
                                    <div class="row form-group">
                                        <div class="control-label col-sm-5">
                                            <div class="control-label col-sm-4">{vtranslate('LBL_REPORT_CONFIG_GROUP_NAME', $MODULE_NAME)}:</div>
                                            <div class="control-label col-sm-8">
                                                <input type="text" class="inputElement group-name">
                                                <input type="hidden" class="group-id" value=""/>
                                            </div>
                                        </div>
                                        <div class="control-label col-sm-6">
                                            <div class="control-label col-sm-1">{vtranslate('LBL_REPORT_CONFIG_FROM', $MODULE_NAME)}:</div>
                                            <div class="control-label col-sm-5 currencry-container">
                                                <span class="input-group-addon">{$BASE_CURRENCY['currency_symbol']}</span>
                                                <input type="text" class="inputElement from-value" data-field-type="currency" disabled>
                                            </div>
                                            <div class="control-label col-sm-1">{vtranslate('LBL_REPORT_CONFIG_TO', $MODULE_NAME)}:</div>
                                            <div class="control-label col-sm-5 currencry-container">
                                                <span class="input-group-addon">{$BASE_CURRENCY['currency_symbol']}</span>
                                                <input type="text" class="inputElement to-value" data-field-type="currency">
                                            </div>
                                        </div>
                                        <div class="control-label col-sm-1 delete-session"><i class="far fa-trash-alt delete-button"></i></div>
                                    </div>
                                    <div class="row form-group">
                                        <div class="control-label col-sm-5">
                                            <div class="control-label col-sm-4"></div>
                                            <div class="control-label col-sm-8"><input type="checkbox" class="chx-alert-group" />&nbsp;{vtranslate('LBL_REPORT_CONFIG_ALERT_NEARLY_REACHING_GROUP', $MODULE_NAME)}</div>
                                        </div>
                                        <div class="control-label col-sm-6 alert-group {if $GROUP['alert_group'] == 0}hide{/if}">
                                            <div class="control-label col-sm-1"></div>
                                            <div class="control-label col-sm-5 currencry-container">
                                                <span class="input-group-addon">{$BASE_CURRENCY['currency_symbol']}</span>
                                                <input type="text" class="inputElement alert-value" data-rule-required="true" data-field-type="currency">
                                            </div>
                                            <div class="control-label col-sm-6">{vtranslate('LBL_REPORT_CONFIG_MORE_VALUE_TO_REACH_GROUP', $MODULE_NAME)}</div>
                                        </div>
                                    </div>
                                </div> 

                                {assign var="COUNT_GROUP" value=$CURRENT_CONFIG['customer_groups']['groups']|count}
                                
                                {foreach from=$CURRENT_CONFIG['customer_groups']['groups'] key=KEY item=GROUP}
                                <div class="customer-group">
                                    <div class="row form-group">
                                        <div class="control-label col-sm-5">
                                            <div class="control-label col-sm-4">{vtranslate('LBL_REPORT_CONFIG_GROUP_NAME', $MODULE_NAME)}:</div>
                                            <div class="control-label col-sm-8">
                                                <input type="text" class="inputElement group-name" value="{$GROUP['group_name']}" data-rule-required="true" name="group-name-{$KEY + 1}">
                                                <input type="hidden" class="group-id" value="{$GROUP['group_id']}"/>
                                            </div>
                                        </div>
                                        <div class="control-label col-sm-6">
                                            <div class="control-label col-sm-1">{vtranslate('LBL_REPORT_CONFIG_FROM', $MODULE_NAME)}:</div>
                                            <div class="control-label col-sm-5 currencry-container">
                                                <span class="input-group-addon">{$BASE_CURRENCY['currency_symbol']}</span>
                                                <input type="text" class="inputElement from-value" data-field-type="currency" disabled value="{currencyField::convertToUserFormat($GROUP['from_value'])}" name="from-value-{$KEY + 1}">
                                            </div>
                                            <div class="control-label col-sm-1">{vtranslate('LBL_REPORT_CONFIG_TO', $MODULE_NAME)}:</div>
                                            <div class="control-label col-sm-5 currencry-container">
                                                <span class="input-group-addon">{$BASE_CURRENCY['currency_symbol']}</span>
                                                <input type="text" class="inputElement to-value" data-field-type="currency" {if $COUNT_GROUP == $KEY + 1}disabled{/if} value="{if $GROUP['to_value'] > 0}{currencyField::convertToUserFormat($GROUP['to_value'])}{/if}" name="to-value-{$KEY + 1}" data-rule-required="true">
                                            </div>
                                        </div>
                                        <div class="control-label col-sm-1 delete-session"><i class="far fa-trash-alt delete-button"></i></div>
                                    </div>
                                    <div class="row form-group">
                                        <div class="control-label col-sm-5">
                                            <div class="control-label col-sm-4"></div>
                                            <div class="control-label col-sm-8"><input type="checkbox" class="chx-alert-group" {if $GROUP['alert_group'] == 1}checked{/if}/>&nbsp;{vtranslate('LBL_REPORT_CONFIG_ALERT_NEARLY_REACHING_GROUP', $MODULE_NAME)}</div>
                                        </div>
                                        <div class="control-label col-sm-6 alert-group {if $GROUP['alert_group'] == 0}hide{/if}">
                                            <div class="control-label col-sm-1"></div>
                                            <div class="control-label col-sm-5 currencry-container">
                                                <span class="input-group-addon">{$BASE_CURRENCY['currency_symbol']}</span>
                                                <input type="text" class="inputElement alert-value" data-field-type="currency" value="{currencyField::convertToUserFormat($GROUP['alert_value'])}" data-rule-required="true">
                                            </div>
                                            <div class="control-label col-sm-6">{vtranslate('LBL_REPORT_CONFIG_MORE_VALUE_TO_REACH_GROUP', $MODULE_NAME)}</div>
                                        </div>
                                    </div>
                                </div>                               
                                <hr>

                                {/foreach}
                                <div class="row form-group add-action-group">
                                    <div class="control-label col-sm-10">
                                        <div class="control-label col-sm-5">
                                            <a href="javascript:void(0)" id="add-group"><i class="far fa-plus" aria-hidden="true"></i>&nbsp;{vtranslate('LBL_REPORT_CONFIG_ADD_GROUP', $MODULE_NAME)}</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-overlay-footer clearfix">
            <div class="row clear-fix">
                <div class="textAlignCenter col-lg-12 col-md-12 col-sm-12">
                    <button type="submit" class="btn btn-success saveButton">{vtranslate('LBL_SAVE')}</button>
                </div>
            </div> 
        </div>
    </form>

    <link rel="stylesheet" href="{vresource_url('libraries/jquery/bootstrapswitch/css/bootstrap3/bootstrap-switch.min.css')}"/>
    <script src="{vresource_url('libraries/jquery/bootstrapswitch/js/bootstrap-switch.min.js')}"></script>
{strip}