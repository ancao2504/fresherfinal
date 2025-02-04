{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

{* Commented out these lines by Hieu Nguyen on 2021-06-02 to disable default chart rendering *}
{*<input type='hidden' name='charttype' value="{$CHART_TYPE}" />
<input type='hidden' name='data' value='{Vtiger_Functions::jsonEncode($DATA)}' />
<input type='hidden' name='clickthrough' value="{$CLICK_THROUGH}" />*}
{* End Hieu Nguyen *}

{* Modified by Hieu Nguyen on 2022-03-09 to refactor html structure *}
<div id="chartContainer">
    <input type="hidden" class="yAxisFieldType" value="{$YAXIS_FIELD_TYPE}" />
    <div class="border1px" style="padding:30px;">
        {* Modified by Hieu Nguyen on 2021-06-02 to render chart using HighCharts *}
        <div id="chartcontent" name="chartcontent" style="min-height:400px;" data-mode="Reports">
            {$CUSTOM_REPORT_HANDLER->display()}
        </div>
        {* End Hieu Nguyen *}

        {if $CLICK_THROUGH neq 'true'}
            <br>
            <div class='row-fluid alert-info'>
                <span class='col-lg-4 offset4'> &nbsp;</span>
                <span class='span alert-info'>
                    <i class="icon-info-sign"></i>
                    {vtranslate('LBL_CLICK_THROUGH_NOT_AVAILABLE', $MODULE)}
                </span>
            </div>
        {/if}

        {if $REPORT_MODEL->isInventoryModuleSelected()}
            <br>
            <div class="alert alert-info">
                {assign var=BASE_CURRENCY_INFO value=Vtiger_Util_Helper::getUserCurrencyInfo()}
                <i class="icon-info-sign" style="margin-top: 1px;"></i>&nbsp;&nbsp;
                {vtranslate('LBL_CALCULATION_CONVERSION_MESSAGE', $MODULE)} - {$BASE_CURRENCY_INFO['currency_name']} ({$BASE_CURRENCY_INFO['currency_code']})
            </div>
        {/if}
    </div>
</div>