
{*
    AnalyzeSalesFluctuationReportFilter.tpl
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
                {vtranslate('LBL_REPORT_DISPLAYED_BY', 'Reports')}:
            </div>
            <div class="control-label col-sm-8">
                <select name="displayed_by" id="displayed_by" class="filter dislayed-time width-340">
                    <option value="year" {if isset($PARAMS['displayed_by']) && $PARAMS['displayed_by'] == 'year'}selected{/if}>{vtranslate('LBL_REPORT_YEAR', 'Reports')}</option>
                    <option value="month" {if isset($PARAMS['displayed_by']) && $PARAMS['displayed_by'] == 'month'}selected{/if}>{vtranslate('LBL_REPORT_MONTH', 'Reports')}</option>
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