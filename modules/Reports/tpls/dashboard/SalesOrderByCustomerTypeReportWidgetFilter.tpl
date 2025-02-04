{*
    TopCustomerBySalesReportWidgetFilter.tpl
    Author: Phuc Lu
    Date: 2020.04.14
*}

{strip}
    {assign var="QUARTER" value=[1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV']}
    {assign var="CURRENT_YEAR" value='Y'|date}
    {assign var="FROM_YEAR" value=$CURRENT_YEAR - 10}

    <div class="form-group">
        <label class="control-label fieldLabel col-sm-5">
            <strong>{vtranslate('LBL_REPORT_CHART_TITLE', 'Reports')}:</strong>
        </label>
        <div class="controls fieldValue col-sm-7">
            <input name="filter[chart_title]" class="form-control widgetFilter reloadOnChange" value="{$PARAMS.chart_title}" />
        </div>

        <label class="control-label fieldLabel col-sm-5">
            <strong>{vtranslate('LBL_REPORT_DISPLAYED_BY', 'Reports')}::</strong>
        </label>
        <div class="controls fieldValue col-sm-7">
            <select name="displayed_by" id="displayed_by" class="inputElement select2 select2-offscreen form-control widgetFilter reloadOnChange">
                <option value="month" {if isset($PARAMS.displayed_by) && $PARAMS.displayed_by == 'month'}selected{/if}>{vtranslate('LBL_REPORT_MONTH', 'Reports')}</option>
                <option value="quarter" {if isset($PARAMS.displayed_by) && $PARAMS.displayed_by == 'quarter'}selected{/if}>{vtranslate('LBL_REPORT_QUARTER', 'Reports')}</option>
            </select> 
        </div>

        <label class="control-label fieldLabel col-sm-5 year-field">
            <strong>{vtranslate('LBL_REPORT_YEAR', 'Reports')}:</strong>
        </label>
        <div class="controls fieldValue col-sm-7 year-field">    
            <select name="filter[year]" id="year" class="inputElement select2 select2-offscreen form-control widgetFilter reloadOnChange">
                {for $INDEX=$FROM_YEAR to $CURRENT_YEAR}
                    <option value="{$INDEX}" {if $INDEX == $PARAMS.year}selected{/if}>{$INDEX}</option>
                {/for}
            </select>        
        </div>
    </div>
{/strip}