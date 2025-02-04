{*
    SalesByCustomerGroupReportFilter.tpl
    Author: Phu Vo
    Date: 2020.08.19
*}

{* Moved Report Filter into seperated template file by Phu Vo on 2020-09-18 so that it can be loaded from Embedded Report Chart *}

{strip}
    {assign var="CURRENT_YEAR" value='Y'|date}
    {assign var="FROM_YEAR" value=$CURRENT_YEAR - 10}
    
    {if isset($PARAMS['year']) && !empty($PARAMS['year'])}
        {assign var="CURRENT_SELECTED_YEAR" value=$PARAMS['year']}
    {else}
        {assign var="CURRENT_SELECTED_YEAR" value='Y'|date}
    {/if}

    <form id="form-filter" name="filter" action="" method="GET" class="filter-container recordEditView">
        <input type="hidden" name="module" value="Reports"/>
        <input type="hidden" name="view" value="Detail"/>
        <input type="hidden" name="record" value="{$smarty.get.record}" />
        <div class="filter-group">
            <div class="control-label fieldLabel col-sm-4">
                {vtranslate('LBL_REPORT_CHOOSE_YEAR', 'Reports')}:
            </div>
            <div class="control-label col-sm-8">
                <select name="year" id="year" class="filter dislayed-time width-340">
                    {for $INDEX=$FROM_YEAR to $CURRENT_YEAR}
                        <option value="{$INDEX}" {if $INDEX == $CURRENT_SELECTED_YEAR}selected{/if}>{$INDEX}</option>
                    {/for}
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