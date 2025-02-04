{strip}
    <h4>{$CHART_TITLE}</h4>
    {assign var="PLACE_HOLDER_ID" value="custom-chart-widget-{$WIDGET_ID}"}

    <div id="{$PLACE_HOLDER_ID}">REPORT CHART RENDER HERE</div>

    <script type="text/javascript" src="{vresource_url("resources/CustomChartWidget.js")}"></script>
    <script type="text/javascript" src="{vresource_url("modules/Reports/resources/LeadsBySourceReportChart.js")}"></script>
    <script type="text/javascript">
        new LeadsBySourceReportChart('{$PLACE_HOLDER_ID}', {Zend_Json::encode($DATA)});
    </script>
{/strip}