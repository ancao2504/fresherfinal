{*
    TimePeriodFilters
    Author: Phu Vo
    Date: 2020.08.24
*}

{strip}
    <div class="filterContainer boxSizingBorderBox">
        <input type="hidden" name="picklistDependency" value='{ZEND_JSON::encode(Vtiger_DependencyPicklist::getPicklistDependencyDatasource('CPPayment'))}'>
        <div class="row">
            <div class="col-sm-12">
                <div class="col-lg-4 fieldLabel" style="margin-top: 0.5em">
                    <span>{vtranslate('LBL_CPPAYMENT_CATEGORY', 'CPPayment')}</span>
                </div>
                <div class="col-lg-8 fieldValue">
                    <div class="inputElement-container">
                        {assign var=CPPAYMENT_CATEGORIES value=Vtiger_Util_Helper::getPickListValues('cppayment_category')}
                        <select name="cppayment_category" class="filter widgetFilter dislayed-filter select2 reloadOnChange inputElement">
                            <option value="">{vtranslate('LBL_SELECT_OPTION')}</option>
                            {foreach from=$CPPAYMENT_CATEGORIES item=value}
                                <option value="{$value}" {if $PARAMS.cppayment_category == $value}selected{/if}>{vtranslate($value, 'CPPayment')}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="col-lg-4 fieldLabel" style="margin-top: 0.5em">
                    <span>{vtranslate('LBL_CPPAYMENT_SUBCATEGORY', 'CPPayment')}</span>
                </div>
                <div class="col-lg-8 fieldValue">
                    <div class="inputElement-container">
                        {assign var=CPPAYMENT_SUBCATEGORIES value=Vtiger_Util_Helper::getPickListValues('cppayment_subcategory')}
                        <select name="cppayment_subcategory" class="filter widgetFilter dislayed-filter select2 reloadOnChange inputElement">
                            <option value="">{vtranslate('LBL_SELECT_OPTION')}</option>
                            {foreach from=$CPPAYMENT_SUBCATEGORIES item=value}
                                <option value="{$value}" {if $PARAMS.cppayment_subcategory == $value}selected{/if}>{vtranslate($value, 'CPPayment')}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="col-lg-4 fieldLabel" style="margin-top: 0.5em">
                    <span>{vtranslate('LBL_DASHBOARD_DEBIT', 'Home')}:</span>
                </div>
                <div class="col-lg-8 fieldValue">
                    <div class="inputElement-container">
                        <select name="debit" class="filter widgetFilter dislayed-filter select2 reloadOnChange inputElement">
                            <option value="">{vtranslate('LBL_SELECT_OPTION')}</option>
                            <option value="over_due" {if $PARAMS.debit == 'over_due'}selected{/if}>{vtranslate('LBL_DASHBOARD_OVERDUE', 'Home')}</option>
                            <option value="expected" {if $PARAMS.debit == 'expected'}selected{/if}>{vtranslate('LBL_DASHBOARD_EXPECTED', 'Home')}</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/strip}