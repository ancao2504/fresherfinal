{*
    CustomerHaveNoSOInPeriodReportFilter.tpl
    Author: Phu Vo
    Date: 2020.08.19
*}

{* Moved Report Filter into seperated template file by Phu Vo on 2020-09-18 so that it can be loaded from Embedded Report Chart *}

{strip}
    {assign var="QUARTER" value=[1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV']}
    {assign var="CURRENT_YEAR" value='Y'|date}
    {assign var="FROM_YEAR" value=$CURRENT_YEAR - 5}
    {assign var="TO_YEAR" value=$CURRENT_YEAR + 5}
    
    {if isset($PARAMS['year']) && !empty($PARAMS['year'])}
        {assign var="CURRENT_SELECTED_YEAR" value=$PARAMS['year']}
    {else}
        {assign var="CURRENT_SELECTED_YEAR" value='Y'|date}
    {/if}

    {if isset($PARAMS['quarter']) && !empty($PARAMS['quarter'])}
        {assign var="CURRENT_SELECTED_QUARTER" value=$PARAMS['quarter']}
    {else}
        {assign var="CURRENT_SELECTED_QUARTER" value=('m'|date)/4+1}
    {/if}

    {if isset($PARAMS['month']) && !empty($PARAMS['month'])}
        {assign var="CURRENT_SELECTED_MONTH" value=$PARAMS['month']}
    {else}
        {assign var="CURRENT_SELECTED_MONTH" value='m'|date}
    {/if}

    <form id="form-filter" name="filter" action="" method="GET" class="filter-container recordEditView">
        <input type="hidden" name="module" value="Reports"/>
        <input type="hidden" name="view" value="Detail"/>
        <input type="hidden" name="record" value="{$smarty.get.record}" />
        <div class="filter-group">
            <div class="control-label fieldLabel col-sm-4">
                {vtranslate('LBL_REPORT_OVER', 'Reports')}:
            </div>
            <div class="control-label col-sm-8">
                <input type="text" name="period_days" data-rule-required="true" onkeyup="formatNumber(this, 'int')" class="inputElement dislayed-filter text-right" value="{$PARAMS.period_days}"/> {vtranslate('LBL_REPORT_DAY', 'Reports')|lower} {vtranslate('LBL_REPORT_HAVE_NO_SO', 'Reports')|lower}
            </div>
        </div>

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
            <div class="control-button">
                <button type="submit" class="btn btn-success saveButton">{vtranslate('LBL_REPORT_VIEW_REPORT', 'Reports')}</button>
            </div>
        </div>             
    </form>
{/strip}