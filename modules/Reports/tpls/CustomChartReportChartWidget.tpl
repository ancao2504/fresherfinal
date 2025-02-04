{* Added by Hieu Nguyen on 2021-06-02 *}

{strip}
    <h4>{$CHART_TITLE}</h4>
    {assign var="PLACE_HOLDER_ID" value="custom-chart-widget-{$WIDGET_ID}"}

    <div id="{$PLACE_HOLDER_ID}">
        {if empty($DATA.series_data) && empty($DATA.series_datas)}
            <div class="text-center no-data">{vtranslate('LBL_NO_DATA', 'Reports')}</div>
        {/if}
    </div>

    <script type="text/javascript" src="{vresource_url("resources/CustomChartWidget.js")}"></script>
    <script type="text/javascript" src="{vresource_url("modules/Reports/resources/CustomChartReportChartWidget.js")}"></script>
    <script type="text/javascript">
        new CustomChartReportChartWidget('{$PLACE_HOLDER_ID}', {Zend_Json::encode($DATA)});
    </script>
{/strip}