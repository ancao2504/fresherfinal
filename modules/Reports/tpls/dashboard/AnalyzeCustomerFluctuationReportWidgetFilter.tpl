{*
    AnalyzeSalesFluctuationReportWidgetFilter.tpl
    Author: Phuc Lu
    Date: 2020.08.19
*}

{strip}

    <div class="form-group">
        <label class="control-label fieldLabel col-sm-5">
            <strong>{vtranslate('LBL_REPORT_CHART_TITLE', 'Reports')}:</strong>
        </label>
        <div class="controls fieldValue col-sm-7">
            <input name="filter[chart_title]" class="form-control widgetFilter reloadOnChange" value="{$PARAMS.chart_title}" />
        </div>
        <div style="clear: both;"></div>

        <label class="control-label fieldLabel col-sm-5">
            <strong>{vtranslate('LBL_REPORT_CHOOSE_TIME', 'Reports')}:</strong>
        </label>
        <div class="controls fieldValue col-sm-7">
            <select name="filter[displayed_by]"  id="displayed_by" class="inputElement select2 select2-offscreen form-control widgetFilter reloadOnChange">
                <option value="year" {if $PARAMS.displayed_by == 'year'}selected{/if}>{vtranslate('LBL_REPORT_YEAR', 'Reports')}</option>
                <option value="month" {if $PARAMS.displayed_by == 'month'}selected{/if}>{vtranslate('LBL_REPORT_MONTH', 'Reports')}</option>
            </select>  
        </div>
        <div style="clear: both;"></div>

        <label class="control-label fieldLabel col-sm-5">
            <strong>{vtranslate('LBL_REPORT_CUSTOMER_TYPE', 'Reports')}:</strong>
        </label>
        <div class="controls fieldValue col-sm-7">
            <select name="filter[customer_type]" id="customer_type" class="inputElement select2 select2-offscreen form-control widgetFilter reloadOnChange">
                <option value="Account" {if $PARAMS.customer_type == 'Account'}selected{/if}>{vtranslate('LBL_REPORT_CUSTOMER_COMPANY', 'Reports')}</option>
                <option value="Contact" {if $PARAMS.customer_type == 'Contact'}selected{/if}>{vtranslate('LBL_REPORT_CUSTOMER_CONTACT', 'Reports')}</option>
                <option value="All" {if $PARAMS.customer_type == 'All'}selected{/if}>{vtranslate('LBL_ALL', 'Reports')}</option>
            </select>  
        </div>
        <div style="clear: both;"></div>
    </div>
{/strip}