{*
    TopProductsBySalesNumberReportFilter.tpl
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

    <script>
        uimeta.field.set({Zend_Json::encode($FILTER_META.input_validators)});
    </script>

    <form id="form-filter" name="filter" action="" method="GET" class="filter-container recordEditView">
        <input type="hidden" name="module" value="Reports"/>
        <input type="hidden" name="view" value="Detail"/>
        <input type="hidden" name="record" value="{$smarty.get.record}" />
        <div class="filter-group">
            <div class="control-label fieldLabel col-sm-4">
                {vtranslate('LBL_REPORT_CHOOSE_TIME', 'Reports')}:
            </div>
            <input type="hidden" name="report_detail" value="1"/>
            <div class="control-label col-sm-8">
                <div class="time-group">    
                    <select name="period" id="period" class="filter dislayed-time">
                        <option value="month" {if isset($PARAMS['period']) && $PARAMS['period'] == 'month'}selected{/if}>{vtranslate('LBL_REPORT_MONTH', 'Reports')}</option>
                        <option value="quarter" {if isset($PARAMS['period']) && $PARAMS['period'] == 'quarter'}selected{/if}>{vtranslate('LBL_REPORT_QUARTER', 'Reports')}</option>
                        <option value="year" {if isset($PARAMS['period']) && $PARAMS['period'] == 'year'}selected{/if}>{vtranslate('LBL_REPORT_YEAR', 'Reports')}</option>
                        <option value="custom" {if isset($PARAMS['period']) && $PARAMS['period'] == 'custom'}selected{/if}>{vtranslate('LBL_REPORT_CUSTOM', 'Reports')}</option>
                    </select>

                    <div class="group-field-wraper period">
                        <select name="month" id="month" class="filter dislayed-time" style="width: 50%">
                            {for $INDEX=1 to 12}
                                <option value="{$INDEX}" {if $INDEX == $CURRENT_SELECTED_MONTH}selected{/if}>{$INDEX}</option>
                            {/for}
                        </select>

                        <select name="quarter" id="quarter" class="filter dislayed-time hide">
                            {foreach from=$QUARTER key=INDEX item=ROMAN_NUMBER}
                                <option value="{$INDEX}" {if $INDEX == $CURRENT_SELECTED_QUARTER}selected{/if}>{$ROMAN_NUMBER}</option>
                            {/foreach}
                        </select>

                        <select name="year" id="year" class="filter dislayed-time">
                            {for $INDEX=$FROM_YEAR to $TO_YEAR}
                                <option value="{$INDEX}" {if $INDEX == $CURRENT_SELECTED_YEAR}selected{/if}>{$INDEX}</option>
                            {/for}
                        </select>
                    </div>

                    <div class="group-field-wraper period">
                        <span class="date-time-field hide">{vtranslate('LBL_REPORT_FROM', 'Reports')}&nbsp;</span>
                        <div class="input-group date-time-field hide">
                            <input name="from_date" id="from-date" type="text" class="dateField form-control dislayed-time" data-fieldtype="date" 
                        <input name="from_date" id="from-date" type="text" class="dateField form-control dislayed-time" data-fieldtype="date" 
                            <input name="from_date" id="from-date" type="text" class="dateField form-control dislayed-time" data-fieldtype="date" 
                                value="{if isset($PARAMS['from_date'])}{$PARAMS['from_date']}{/if}" placeholder="" autocomplete="off" />
                            <span class="input-group-addon"><i class="far fa-calendar "></i></span>
                        </div>

                        <span class="date-time-field hide">{vtranslate('LBL_REPORT_TO', 'Reports')}&nbsp;</span>
                        <div class="input-group date-time-field hide">
                            <input name="to_date" id="to-date" type="text" class="dateField form-control dislayed-time" data-fieldtype="date"
                                data-specific-rules={literal}'[{"name":"greaterThanDependentField","params":["from_date"]}]'{/literal} 
                            data-specific-rules={literal}'[{"name":"greaterThanDependentField","params":["from_date"]}]'{/literal} 
                                data-specific-rules={literal}'[{"name":"greaterThanDependentField","params":["from_date"]}]'{/literal} 
                                value="{if isset($PARAMS['to_date'])}{$PARAMS['to_date']}{/if}" placeholder="" autocomplete="off" />
                            <span class="input-group-addon"><i class="far fa-calendar "></i></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="filter-group">
            <div class="control-label fieldLabel col-sm-4">
                {vtranslate('LBL_REPORT_DISPLAYED_BY', 'Reports')}:
            </div>
            <div class="control-label col-sm-8">
                <select name="top" id="top" class="filter dislayed-filter width-340">
                    <option value="10" {if isset($PARAMS['top']) && $PARAMS['top'] == '10'}selected{/if}>{vtranslate('LBL_REPORT_TOP', 'Reports')} 10</option>
                    <option value="5" {if isset($PARAMS['top']) && $PARAMS['top'] == '5'}selected{/if}>{vtranslate('LBL_REPORT_TOP', 'Reports')} 5</option>
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