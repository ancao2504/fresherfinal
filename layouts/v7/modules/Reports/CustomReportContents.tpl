{*
    CustomReportContents
    Author: Hieu Nguyen
    Date: 2018-12-09
    Purpose: to display custom reports content
*}

{strip}
    <div class="contents-topscroll">
        <div class="topscroll-div">
            {if !empty($CALCULATION_FIELDS)}
                <table class="summary-table table-bordered table-condensed marginBottom10px" width="100%"> {* Modified by Phu Vo on 2021.12.23 *}
                    <thead>
                        <tr class="blockHeader">
                            <th>{vtranslate('LBL_FIELD_NAMES',$MODULE)}</th>
                            <th>{vtranslate('LBL_SUM',$MODULE)}</th>
                            <th>{vtranslate('LBL_AVG',$MODULE)}</th>
                            <th>{vtranslate('LBL_MIN',$MODULE)}</th>
                            <th>{vtranslate('LBL_MAX',$MODULE)}</th>
                        </tr>
                    </thead>
                    {assign var=ESCAPE_CHAR value=array('_SUM','_AVG','_MIN','_MAX')}
                    {foreach from=$CALCULATION_FIELDS item=CALCULATION_FIELD key=index}
                        <tr>
                            {assign var=CALCULATION_FIELD_KEYS value=array_keys($CALCULATION_FIELD)}
                            {assign var=CALCULATION_FIELD_KEYS value=$CALCULATION_FIELD_KEYS|replace:$ESCAPE_CHAR:''}
                            {assign var=FIELD_IMPLODE value=explode('_',$CALCULATION_FIELD_KEYS['0'])}
                            {assign var=MODULE_NAME value=$FIELD_IMPLODE['0']}
                            {assign var=FIELD_LABEL value=" "|implode:$FIELD_IMPLODE}
                            {assign var=FIELD_LABEL value=str_replace($MODULE_NAME, '', $FIELD_LABEL)}
                            <td>{vtranslate($MODULE_NAME,$MODULE_NAME)} {vtranslate(trim($FIELD_LABEL), $MODULE_NAME)}</td>
                            {foreach from=$CALCULATION_FIELD item=CALCULATION_VALUE}
                                <td width="15%">{$CALCULATION_VALUE}</td>
                            {/foreach}
                        </tr>
                    {/foreach}
                </table>
                {if $REPORT_MODEL->isInventoryModuleSelected()}
                    <div class="alert alert-info">
                        {assign var=BASE_CURRENCY_INFO value=Vtiger_Util_Helper::getUserCurrencyInfo()}
                        <i class="far fa-info-circle"></i>&nbsp;&nbsp;
                        {vtranslate('LBL_CALCULATION_CONVERSION_MESSAGE', $MODULE)} - {$BASE_CURRENCY_INFO['currency_name']} ({$BASE_CURRENCY_INFO['currency_code']})
                    </div>
                {/if}
            {/if}
        </div>
    </div>
    <div id="reportDetails" class="contents-bottomscroll">
        <div class="bottomscroll-div">
            {$CUSTOM_REPORT_HANDLER->display()}
        </div>
    </div>
    <br>
{/strip}