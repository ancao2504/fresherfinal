{*
    CustomerHaveNoSOInPeriodWidget
    Author: Phu Vo
    Date: 2020.08.24
*}

{strip}
    <div class="filterContainer boxSizingBorderBox">
        <div class="row" style="margin-bottom: 6px">
            <div class="col-sm-12">
                <div class="col-lg-4 fieldLabel" style="margin-top: 0.5em">
                    <span>{vtranslate('LBL_REPORT_OVER', 'Reports')}:</span>
                </div>
                <div class="col-lg-8 fieldValue">
                    <input type="text" class="inputElement widgetFilter reloadOnChange" name="period_days" onkeyup="formatNumber(this, 'int')" value="{$PARAMS.period_days}" style="width: 60px;" />&nbsp;&nbsp;
                    <span>{vtranslate('LBL_REPORT_DAY', 'Reports')|lower} {vtranslate('LBL_REPORT_HAVE_NO_SO', 'Reports')|lower}</span>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="col-lg-4 fieldLabel" style="margin-top: 0.5em">
                    <span>{vtranslate('LBL_REPORT_CHOOSE_TARGET', 'Reports')}:</span>
                </div>
                <div class="col-lg-8 fieldValue">
                    <select name="target" class="filter widgetFilter dislayed-filter select2 reloadOnChange">
                        <option value="Account" {if $PARAMS.target == 'Account'}selected{/if}>{vtranslate('LBL_REPORT_CUSTOMER_COMPANY', 'Reports')}</option>
                        <option value="Contact" {if $PARAMS.target == 'Contact'}selected{/if}>{vtranslate('LBL_REPORT_CUSTOMER_CONTACT', 'Reports')}</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
{/strip}