{strip}
    <div class="form-group">
        <label class="control-label fieldLabel col-sm-5">
            <strong>{vtranslate('LBL_LEADS_BY_SOURCE_REPORT_FILTER_CHART_TITLE', 'Reports')}</strong>
        </label>
        <div class="controls fieldValue col-sm-7">
            <input name="filter[chart_title]" class="form-control widgetFilter reloadOnChange" value="{$PARAMS.chart_title}" />
        </div>

        <label class="control-label fieldLabel col-sm-5">
            <strong>{vtranslate('LBL_LEADS_BY_SOURCE_REPORT_FILTER_DEPARTMENT', 'Reports')}</strong>
        </label>
        <div class="controls fieldValue col-sm-7">
            <select name="filter[department]" class="select2 form-control widgetFilter reloadOnChange">
                <option value="sales1">SALES1</option>
                <option value="sales2">SALES2</option>
            </select>
        </div>

        <label class="control-label fieldLabel col-sm-5">
            <strong>{vtranslate('LBL_LEADS_BY_SOURCE_REPORT_FILTER_USER', 'Reports')}</strong>
        </label>
        <div class="controls fieldValue col-sm-7">
            <select name="filter[user]" class="select2 form-control widgetFilter reloadOnChange">
                <option value="user1">USER1</option>
                <option value="user2">USER2</option>
            </select>
        </div>
    </div>
{/strip}