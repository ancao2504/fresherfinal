{*
    PredictionSalesByEmployeeReportFilter.tpl
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
                {vtranslate('LBL_REPORT_CHOOSE_DEPARTMENT', 'Reports')}:
            </div>
            <div class="control-label col-sm-8">
                <select name="departments[]" {if $FILTER_META.report_object == 'DEPARTMENT'}data-rule-required="true"{/if} multiple id="departments" class="filter dislayed-filter select2 width-340">
                    {html_options options=$FILTER_META.departments selected=$PARAMS.departments}
                </select>
            </div>
        </div>

        {if $FILTER_META.report_object == 'EMPLOYEE'}
            <div class="filter-group">
                <div class="control-label fieldLabel col-sm-4">
                    {vtranslate('LBL_REPORT_CHOOSE_EMPLOYEE', 'Reports')}:
                </div>
                <div class="control-label col-sm-8">
                    <select name="employees[]" multiple id="employees" data-reference="deparments" data-rule-required="true" class="filter dislayed-filter select2 width-340">
                        {html_options options=$FILTER_META.filter_users selected=$PARAMS.employees}
                    </select>
                </div>
            </div>
        {/if}          

        <div class="filter-group">
            <div class="control-label fieldLabel col-sm-4">
                {vtranslate('LBL_REPORT_CHOOSE_TIME', 'Reports')}:
            </div>
            <div class="control-label col-sm-8">
                <select name="period" id="period" data-rule-required="true" class="filter dislayed-filter select2 width-340">
                    {html_options options=$FILTER_META.prediction_time_options selected=$PARAMS.period}
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