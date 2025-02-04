{*
    NearlyReachNewLevelCustomerReportFilter.tpl
    Author: Phu Vo
    Date: 2020.08.19
*}

{* Moved Report Filter into seperated template file by Phu Vo on 2020-09-18 so that it can be loaded from Embedded Report Chart *}

{strip}
    <form id="form-filter" name="filter" action="" method="GET" class="filter-container recordEditView">
        <input type="hidden" name="module" value="Reports"/>
        <input type="hidden" name="view" value="Detail"/>
        <input type="hidden" name="record" value="{$smarty.get.record}" />

        <div class="filter-group">
            <div class="control-label fieldLabel col-sm-4">
                {vtranslate('LBL_REPORT_CHOOSE_TARGET', 'Reports')}:
            </div>
            <div class="control-label col-sm-8">
                <select name="target" id="target" class="filter dislayed-filter select2 width-340">
                    <option value="Account" {if $PARAMS.target == 'Account'}selected{/if}>{vtranslate('LBL_REPORT_CUSTOMER_COMPANY', 'Reports')}</option>
                    <option value="Contact" {if $PARAMS.target == 'Contact'}selected{/if}>{vtranslate('LBL_REPORT_CUSTOMER_CONTACT', 'Reports')}</option>
                </select>
            </div>
        </div>

        <div class="filter-group">
            <div class="control-label fieldLabel col-sm-4">
                {vtranslate('LBL_REPORT_CHOOSE_GROUP', 'Reports')}:
            </div>
            <div class="control-label col-sm-8">
                <select name="customer_group" data-rule-required="true" id="customer_group" class="filter dislayed-filter select2 width-340">
                    {html_options options=$FILTER_META.customer_groups selected=$PARAMS.customer_group}
                </select>
            </div>
        </div>

        <div class="filter-group">
            <div class="control-button">
                <button type="submit" class="btn btn-success saveButton">{vtranslate('LBL_REPORT_VIEW_REPORT', 'Reports')}</button>
            </div>
        </div>             
    </form>
{/strip}