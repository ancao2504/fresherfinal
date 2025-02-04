{*
    SalesFunnelReportWidgetFilter.tpl
    Author: Phuc Lu
    Date: 2020.06.04
*}

{strip}
    {assign var="QUARTER" value=[1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV']}
    {assign var="CURRENT_YEAR" value='Y'|date}
    {assign var="FROM_YEAR" value=$CURRENT_YEAR - 5}
    {assign var="TO_YEAR" value=$CURRENT_YEAR + 5}

    <div class="form-group">
        <label class="control-label fieldLabel col-sm-5">
            <strong>{vtranslate('LBL_REPORT_CHART_TITLE', 'Reports')}:</strong>
        </label>
        <div class="controls fieldValue col-sm-7">
            <input name="filter[chart_title]" class="form-control widgetFilter reloadOnChange" value="{$PARAMS.chart_title}" />
        </div>
        <label class="control-label fieldLabel col-sm-5">
            <strong>{vtranslate('LBL_REPORT_CHOOSE_TIME', 'Reports')}:</strong>
        </label>
        <div class="controls fieldValue col-sm-7">
            <select name="filter[period]"  id="period" class="inputElement select2 select2-offscreen form-control widgetFilter reloadOnChange">
                <option value="month" {if $PARAMS.period == 'month'}selected{/if}>{vtranslate('LBL_REPORT_MONTH', 'Reports')}</option>
                <option value="quarter" {if $PARAMS.period == 'quarter'}selected{/if}>{vtranslate('LBL_REPORT_QUARTER', 'Reports')}</option>
                <option value="year" {if $PARAMS.period == 'year'}selected{/if}>{vtranslate('LBL_REPORT_YEAR', 'Reports')}</option>
                <option value="custom" {if $PARAMS.period == 'custom'}selected{/if}>{vtranslate('LBL_REPORT_CUSTOM', 'Reports')}</option>
            </select>  
        </div>

        <label class="control-label fieldLabel col-sm-5 month-field">
            <strong>{vtranslate('LBL_REPORT_MONTH', 'Reports')}:</strong>
        </label>
        <div class="controls fieldValue col-sm-7 month-field">       
            <select name="filter[month]" id="month" class="inputElement select2 select2-offscreen form-control widgetFilter reloadOnChange">
                {for $INDEX=1 to 12}
                    <option value="{$INDEX}" {if $INDEX == $PARAMS.month}selected{/if}>{$INDEX}</option>
                {/for}
            </select>        
        </div>

        <label class="control-label fieldLabel col-sm-5 quarter-field">
            <strong>{vtranslate('LBL_REPORT_QUARTER', 'Reports')}:</strong>
        </label>
        <div class="controls fieldValue col-sm-7 quarter-field">    
            <select name="filter[quarter]" id="quarter" class="inputElement select2 select2-offscreen form-control widgetFilter reloadOnChange">
                {foreach from=$QUARTER key=INDEX item=ROMAN_NUMBER}
                    <option value="{$INDEX}" {if $INDEX == $PARAMS.quarter}selected{/if}>{$ROMAN_NUMBER}</option>
                {/foreach}
            </select>        
        </div>

        <label class="control-label fieldLabel col-sm-5 year-field">
            <strong>{vtranslate('LBL_REPORT_YEAR', 'Reports')}:</strong>
        </label>
        <div class="controls fieldValue col-sm-7 year-field">    
            <select name="filter[year]" id="year" class="inputElement select2 select2-offscreen form-control widgetFilter reloadOnChange">
                {for $INDEX=$FROM_YEAR to $TO_YEAR}
                    <option value="{$INDEX}" {if $INDEX == $PARAMS.year}selected{/if}>{$INDEX}</option>
                {/for}
            </select>        
        </div>

        <label class="control-label fieldLabel col-sm-5 custom-field">
            <strong>{vtranslate('LBL_REPORT_FROM', 'Reports')}:</strong>
        </label>
        <div class="controls fieldValue col-sm-7 custom-field">
            <div class="input-group date-time-field">
                <input name="filter[from_date]" type="text" class="dateField form-control widgetFilter reloadOnChange" data-fieldtype="date" value="{$PARAMS.from_date}" placeholder=""><span class="input-group-addon"><i class="far fa-calendar"></i></span>
            </div>
        </div>
        <div style="clear: both;"></div>

        <label class="control-label fieldLabel col-sm-5 custom-field">
            <strong>{vtranslate('LBL_REPORT_TO', 'Reports')}:</strong>
        </label>
        <div class="controls fieldValue col-sm-7 custom-field">    
            <div class="input-group date-time-field">
                <input name="filter[to_date]" type="text" class="dateField form-control widgetFilter reloadOnChange" data-fieldtype="date"  value="{$PARAMS.to_date}" placeholder=""><span class="input-group-addon"><i class="far fa-calendar"></i></span>
            </div>
        </div>
        <div style="clear: both;"></div>
        
        <label class="control-label fieldLabel col-sm-5 ">
            <strong>{vtranslate('LBL_REPORT_DISPLAYED_BY', 'Reports')}</strong>
        </label>
        <div class="controls fieldValue col-sm-7">
            {assign var="DISPLAYED_BY_OPTIONS" value=Reports_CustomReport_Helper::getDisplayedByForSalesFunnelReport()}
             <select name="filter[displayed_by]" class="inputElement select2 select2-offscreen form-control widgetFilter funnel-filter reloadOnChange">
                {html_options options=$DISPLAYED_BY_OPTIONS selected=$PARAMS.displayed_by}
            </select>
        </div>        
        <div style="clear: both;"></div>
        
        <label class="control-label fieldLabel col-sm-5 ">
            <strong>{vtranslate('LBL_REPORT_CHOOSE_DEPARTMENT', 'Reports')}</strong>
        </label>
        <div class="controls fieldValue col-sm-7">
            {assign var="DEPARTMENTS" value=Reports_CustomReport_Helper::getRoleForFilter()}
             <select name="filter[department]" class="inputElement select2 select2-offscreen form-control widgetFilter reloadOnChange">
                {html_options options=$DEPARTMENTS selected=$PARAMS.departments}
            </select>
        </div>        
        <div style="clear: both;"></div>
        
        <label class="control-label fieldLabel col-sm-5 ">
            <strong>{vtranslate('LBL_REPORT_CHOOSE_EMPLOYEE', 'Reports')}</strong>
        </label>
        <div class="controls fieldValue col-sm-7">
            {assign var="FILTER_USERS" value=Reports_CustomReport_Helper::getUsersByDepartment($PARAMS.employee, false, true)}
             <select name="filter[employee]" class="inputElement select2 select2-offscreen form-control widgetFilter reloadOnChange">
                {html_options options=$FILTER_USERS selected=$PARAMS.employee}
            </select>
        </div>        
        <div style="clear: both;"></div>

        <label class="control-label fieldLabel col-sm-5 ">
            <strong>{vtranslate('LBL_REPORT_CHOOSE_CAMPAIGN', 'Reports')}</strong>
        </label>
        <div class="controls fieldValue col-sm-7">
            {assign var="ALL_CAMPAIGNS" value=Campaigns_Data_Model::getAllCampaigns(true)}
            <select name="filter[campaign]" class="inputElement select2 select2-offscreen form-control widgetFilter reloadOnChange">
                {html_options options=$ALL_CAMPAIGNS selected=$PARAMS.campaign}
            </select>
        </div>        
        <div style="clear: both;"></div>
    </div>

    {literal}
    <script>
        jQuery(function ($) {
            $('[name="filter[displayed_by]"].funnel-filter').on('change loadAtFirst', function() {
                var formGroup = $(this).closest('.form-group');
                if ($(this).val() == 'all') {
                    formGroup.find('[name="filter[department]"]').parent().addClass('hide');
                    formGroup.find('[name="filter[department]"]').parent().prev().addClass('hide');
                    formGroup.find('[name="filter[employee]"]').parent().addClass('hide');
                    formGroup.find('[name="filter[employee]"]').parent().prev().addClass('hide');
                    formGroup.find('[name="filter[campaign]"]').parent().addClass('hide');
                    formGroup.find('[name="filter[campaign]"]').parent().prev().addClass('hide');
                }
                else if ($(this).val() == 'employee') {            
                    formGroup.find('[name="filter[department]"]').parent().removeClass('hide');
                    formGroup.find('[name="filter[department]"]').parent().prev().removeClass('hide');
                    formGroup.find('[name="filter[employee]"]').parent().removeClass('hide');
                    formGroup.find('[name="filter[employee]"]').parent().prev().removeClass('hide');
                    formGroup.find('[name="filter[campaign]"]').parent().addClass('hide');
                    formGroup.find('[name="filter[campaign]"]').parent().prev().addClass('hide');
                }
                else {            
                    formGroup.find('[name="filter[department]"]').parent().addClass('hide');
                    formGroup.find('[name="filter[department]"]').parent().prev().addClass('hide');
                    formGroup.find('[name="filter[employee]"]').parent().addClass('hide');
                    formGroup.find('[name="filter[employee]"]').parent().prev().addClass('hide');
                    formGroup.find('[name="filter[campaign]"]').parent().removeClass('hide');
                    formGroup.find('[name="filter[campaign]"]').parent().prev().removeClass('hide');
                }
            })

            $('[name="filter[displayed_by]"].funnel-filter').trigger('loadAtFirst');
        })
    </script>
    {/literal}
{/strip}