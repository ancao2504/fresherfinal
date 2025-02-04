{*
    CustomerWillReachHigherMemberLevelWidget
    Author: Phu Vo
    Date: 2020.08.28
*}

{strip}
    <div class="filterContainer boxSizingBorderBox">
        <div class="row" style="margin-bottom: 6px">
            <div class="col-sm-12">
                <div class="col-lg-4 fieldLabel" style="margin-top: 0.5em">
                    <span>{vtranslate('LBL_REPORT_CHOOSE_TARGET', 'Reports')}:</span>
                </div>
                <div class="col-lg-8 fieldValue">
                    <select name="target" class="inputElement select2 reloadOnChange">
                        <option value="Account" {if $PARAMS.target == 'Account'}selected{/if}>{vtranslate('LBL_REPORT_CUSTOMER_COMPANY', 'Reports')}</option>
                        <option value="Contact" {if $PARAMS.target == 'Contact'}selected{/if}>{vtranslate('LBL_REPORT_CUSTOMER_CONTACT', 'Reports')}</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="col-lg-4 fieldLabel" style="margin-top: 0.5em">
                    <span>{vtranslate('LBL_REPORT_CHOOSE_GROUP', 'Reports')}:</span>
                </div>
                <div class="col-lg-8 fieldValue">
                    <select name="customer_group" class="inputElement select2 reloadOnChange">
                        {html_options options=$WIDGET_META['customer_groups'] selected=$PARAMS.customer_group}
                    </select>
                </div>
            </div>
        </div>
    </div>
{/strip}